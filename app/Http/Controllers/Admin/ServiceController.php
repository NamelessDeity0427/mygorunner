<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,staff']);
    }

    public function index(): View
    {
        $services = Service::latest()->paginate(10);
        return view('admin.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:services,name'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'category' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        try {
            return DB::transaction(function () use ($request, $validated) {
                $serviceData = $validated;
                $serviceData['is_active'] = $request->boolean('is_active');

                if ($request->hasFile('image') && $request->file('image')->isValid()) {
                    $path = $request->file('image')->store('services', 'public');
                    $serviceData['image_path'] = $path;
                }

                $service = Service::create($serviceData);

                return redirect()->route('admin.services.index')
                    ->with('success', "Service '{$service->name}' created successfully.");
            });
        } catch (\Exception $e) {
            Log::error('Service creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Failed to create service: ' . $e->getMessage());
        }
    }

    public function show(Service $service): RedirectResponse
    {
        return redirect()->route('admin.services.edit', $service->id);
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Service::class)->ignore($service->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'category' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            'remove_image' => ['sometimes', 'boolean'],
        ]);

        try {
            return DB::transaction(function () use ($request, $service, $validated) {
                $serviceData = $validated;
                $serviceData['is_active'] = $request->boolean('is_active');

                if ($request->boolean('remove_image') && $service->image_path) {
                    Storage::disk('public')->delete($service->image_path);
                    $serviceData['image_path'] = null;
                } elseif ($request->hasFile('image') && $request->file('image')->isValid()) {
                    if ($service->image_path) {
                        Storage::disk('public')->delete($service->image_path);
                    }
                    $path = $request->file('image')->store('services', 'public');
                    $serviceData['image_path'] = $path;
                }

                $service->update($serviceData);

                return redirect()->route('admin.services.index')
                    ->with('success', "Service '{$service->name}' updated successfully.");
            });
        } catch (\Exception $e) {
            Log::error('Service update failed', [
                'user_id' => auth()->id(),
                'service_id' => $service->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Failed to update service: ' . $e->getMessage());
        }
    }

    public function destroy(Service $service): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($service) {
                $serviceName = $service->name;
                if ($service->image_path) {
                    Storage::disk('public')->delete($service->image_path);
                }
                $service->delete();

                return redirect()->route('admin.services.index')
                    ->with('success', "Service '{$serviceName}' deleted successfully.");
            });
        } catch (\Exception $e) {
            Log::error('Service deletion failed', [
                'user_id' => auth()->id(),
                'service_id' => $service->id,
                'error' => $e->getMessage(),
            ]);
            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                return redirect()->route('admin.services.index')
                    ->with('error', "Failed to delete service '{$service->name}'. It might be linked to other records.");
            }
            return redirect()->route('admin.services.index')
                ->with('error', 'Failed to delete service: ' . $e->getMessage());
        }
    }
}