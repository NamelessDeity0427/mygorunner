<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\User;
use App\Rules\ValidPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Validation\Rules;

class RiderManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,staff']);
    }

    public function index(): View
    {
        $riders = Rider::with(['user' => function ($query) {
            $query->select('id', 'name', 'email', 'phone', 'user_type');
        }])
            ->latest('created_at')
            ->paginate(15);

        return view('admin.riders.index', compact('riders'));
    }

    public function create(): View
    {
        return view('admin.riders.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'plate_number' => ['required', 'string', 'max:20', 'unique:riders,plate_number'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'point' => [new ValidPoint('latitude', 'longitude')],
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'password' => Hash::make($validated['password']),
                    'user_type' => 'rider',
                    'email_verified_at' => now(),
                ]);

                $riderData = [
                    'user_id' => $user->id,
                    'vehicle_type' => $validated['vehicle_type'],
                    'plate_number' => $validated['plate_number'],
                    'status' => 'offline',
                ];

                if ($validated['latitude'] && $validated['longitude']) {
                    $riderData['current_location'] = new Point($validated['latitude'], $validated['longitude']);
                    $riderData['location_updated_at'] = now();
                }

                Rider::create($riderData);

                return redirect()->route('admin.riders.index')->with('success', "Rider {$validated['name']} registered successfully.");
            });
        } catch (\Exception $e) {
            Log::error('Rider creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Failed to register rider: ' . $e->getMessage());
        }
    }

    public function edit(Rider $rider): View
    {
        $rider->load(['user' => function ($query) {
            $query->select('id', 'name', 'email', 'phone');
        }]);
        return view('admin.riders.edit', compact('rider'));
    }

    public function update(Request $request, Rider $rider): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $rider->user->id],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,' . $rider->user->id],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'plate_number' => ['required', 'string', 'max:20', 'unique:riders,plate_number,' . $rider->id],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'point' => [new ValidPoint('latitude', 'longitude')],
        ]);

        try {
            return DB::transaction(function () use ($validated, $rider) {
                $rider->user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                ]);

                $riderData = [
                    'vehicle_type' => $validated['vehicle_type'],
                    'plate_number' => $validated['plate_number'],
                ];

                if ($validated['latitude'] && $validated['longitude']) {
                    $riderData['current_location'] = new Point($validated['latitude'], $validated['longitude']);
                    $riderData['location_updated_at'] = now();
                }

                $rider->update($riderData);

                return redirect()->route('admin.riders.index')->with('success', "Rider {$validated['name']} updated successfully.");
            });
        } catch (\Exception $e) {
            Log::error('Rider update failed', [
                'user_id' => Auth::id(),
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Failed to update rider: ' . $e->getMessage());
        }
    }

    public function destroy(Rider $rider): RedirectResponse
    {
        try {
            return DB::transaction(function () use ($rider) {
                $user = $rider->user;
                $rider->delete();
                $user->delete();
                return redirect()->route('admin.riders.index')->with('success', 'Rider deleted successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Rider deletion failed', [
                'user_id' => Auth::id(),
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to delete rider: ' . $e->getMessage());
        }
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rider_ids' => ['required', 'array'],
            'rider_ids.*' => ['exists:riders,id'],
            'status' => ['required', 'in:available,offline'],
        ]);

        try {
            Rider::whereIn('id', $validated['rider_ids'])->update([
                'status' => $validated['status'],
            ]);
            $action = $validated['status'] === 'available' ? 'activated' : 'deactivated';
            return redirect()->route('admin.riders.index')->with('success', "Selected riders $action successfully.");
        } catch (\Exception $e) {
            Log::error('Bulk rider update failed', [
                'user_id' => Auth::id(),
                'rider_ids' => $validated['rider_ids'],
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update riders: ' . $e->getMessage());
        }
    }

    public function storeDocuments(Request $request, Rider $rider): RedirectResponse
    {
        $validated = $request->validate([
            'documents' => ['required', 'array', 'max:5'],
            'documents.*' => ['file', 'mimes:pdf,jpg,png', 'max:2048'],
        ]);

        try {
            return DB::transaction(function () use ($validated, $rider) {
                foreach ($validated['documents'] as $document) {
                    $path = $document->store('rider_documents', 'public');
                    $rider->documents()->create([
                        'file_path' => $path,
                    ]);
                }
                return back()->with('success', 'Documents uploaded successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Rider document upload failed', [
                'user_id' => Auth::id(),
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to upload documents: ' . $e->getMessage());
        }
    }
}