<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
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
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:2000',
            'price' => 'nullable|numeric|min:0|max:99999.99',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $serviceData = $validated;
            $serviceData['is_active'] = $request->boolean('is_active');

            if ($request->hasFile('image')) {
                if ($request->file('image')->isValid()) {
                    $path = $request->file('image')->store('public/services');
                    $serviceData['image_path'] = str_replace('public/', '', $path);
                } else {
                    return back()->withInput()->with('error', 'Invalid image file uploaded.');
                }
            }

            Service::create($serviceData);

            return redirect()->route('admin.services.index')
                ->with('success', 'Service "' . $validated['name'] . '" created successfully.');
        } catch (Exception $e) {
            Log::error("Service creation failed", ['admin_user_id' => Auth::id(), 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create service due to a server error.');
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
            'description' => 'nullable|string|max:2000',
            'price' => 'nullable|numeric|min:0|max:99999.99',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'sometimes|boolean',
            'remove_image' => 'sometimes|boolean',
        ]);

        try {
            $serviceData = $validated;
            $serviceData['is_active'] = $request->boolean('is_active');

            if ($request->boolean('remove_image') && $service->image_path) {
                Storage::disk('public')->delete($service->image_path);
                $serviceData['image_path'] = null;
            } elseif ($request->hasFile('image')) {
                if ($request->file('image')->isValid()) {
                    if ($service->image_path) {
                        Storage::disk('public')->delete($service->image_path);
                    }
                    $path = $request->file('image')->store('public/services');
                    $serviceData['image_path'] = str_replace('public/', '', $path);
                } else {
                    return back()->withInput()->with('error', 'Invalid image file uploaded.');
                }
            }

            $service->update($serviceData);

            return redirect()->route('admin.services.index')
                ->with('success', 'Service "' . $validated['name'] . '" updated successfully.');
        } catch (Exception $e) {
            Log::error("Service update failed", ['service_id' => $service->id, 'admin_user_id' => Auth::id(), 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update service due to a server error.');
        }
    }

    public function destroy(Service $service): RedirectResponse
    {
        try {
            $serviceName = $service->name;
            if ($service->image_path) {
                Storage::disk('public')->delete($service->image_path);
            }
            $service->delete();

            return redirect()->route('admin.services.index')
                ->with('success', 'Service "' . $serviceName . '" deleted successfully.');
        } catch (Exception $e) {
            Log::error("Service deletion failed", ['service_id' => $service->id, 'admin_user_id' => Auth::id(), 'error' => $e->getMessage()]);
            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                return redirect()->route('admin.services.index')
                    ->with('error', 'Failed to delete service "' . $service->name . '". It might be linked to other records.');
            }
            return redirect()->route('admin.services.index')
                ->with('error', 'Failed to delete service due to a server error.');
        }
    }
}