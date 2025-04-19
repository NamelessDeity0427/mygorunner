<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Exception;
use Illuminate\Validation\Rule;

class RiderManagementController extends Controller
{
    public function index(): View
    {
        $riders = Rider::with('user:id,name,email,phone,user_type')
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['required', 'string', 'max:20', Rule::unique(User::class)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'address' => ['required', 'string', 'max:1000'],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique(Rider::class)],
            'is_active' => ['sometimes', 'boolean'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
        ]);

        DB::beginTransaction();
        try {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'user_type' => 'rider',
                'email_verified_at' => now(),
            ];

            $user = User::create($userData);

            $riderData = [
                'user_id' => $user->id,
                'address' => $validated['address'],
                'vehicle_type' => $validated['vehicle_type'],
                'plate_number' => $validated['plate_number'],
                'is_active' => $request->boolean('is_active'),
                'status' => 'offline',
                'current_location' => null,
                'location_updated_at' => null,
            ];

            if ($request->filled('latitude') && $request->filled('longitude')) {
                $riderData['current_location'] = new Point($validated['latitude'], $validated['longitude']);
                $riderData['location_updated_at'] = now();
            }

            Rider::create($riderData);
            DB::commit();
            return redirect()->route('admin.riders.index')->with('success', "Rider {$validated['name']} registered successfully.");
        } catch (Exception $e) {
            DB::rollback();
            Log::error("Rider creation failed", [
                'admin_user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Failed to register rider due to a server error. Please try again.');
        }
    }

    public function edit(Rider $rider): View
    {
        $rider->load('user');
        return view('admin.riders.edit', compact('rider'));
    }

    public function update(Request $request, Rider $rider): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($rider->user->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique(User::class)->ignore($rider->user->id)],
            'address' => ['required', 'string', 'max:1000'],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique(Rider::class)->ignore($rider->id)],
            'is_active' => ['sometimes', 'boolean'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
        ]);

        DB::beginTransaction();
        try {
            $rider->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);

            $riderData = [
                'address' => $validated['address'],
                'vehicle_type' => $validated['vehicle_type'],
                'plate_number' => $validated['plate_number'],
                'is_active' => $request->boolean('is_active'),
            ];

            if ($request->filled('latitude') && $request->filled('longitude')) {
                $riderData['current_location'] = new Point($validated['latitude'], $validated['longitude']);
                $riderData['location_updated_at'] = now();
            }

            $rider->update($riderData);
            DB::commit();
            return redirect()->route('admin.riders.index')->with('success', "Rider {$validated['name']} updated successfully.");
        } catch (Exception $e) {
            DB::rollback();
            Log::error("Rider update failed", [
                'admin_user_id' => Auth::id(),
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Failed to update rider due to a server error. Please try again.');
        }
    }

    public function destroy(Rider $rider): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $user = $rider->user;
            $rider->delete();
            $user->delete();
            DB::commit();
            return redirect()->route('admin.riders.index')->with('success', 'Rider deleted successfully.');
        } catch (Exception $e) {
            DB::rollback();
            Log::error("Rider deletion failed", [
                'admin_user_id' => Auth::id(),
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Failed to delete rider due to a server error. Please try again.');
        }
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rider_ids' => ['required', 'array'],
            'rider_ids.*' => ['exists:riders,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        try {
            Rider::whereIn('id', $validated['rider_ids'])->update([
                'is_active' => $validated['is_active'],
            ]);
            $action = $validated['is_active'] ? 'activated' : 'deactivated';
            return redirect()->route('admin.riders.index')->with('success', "Selected riders $action successfully.");
        } catch (Exception $e) {
            Log::error("Bulk rider update failed", [
                'admin_user_id' => Auth::id(),
                'rider_ids' => $validated['rider_ids'],
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update riders.');
        }
    }

    public function storeDocuments(Request $request, Rider $rider): RedirectResponse
    {
        $validated = $request->validate([
            'documents' => ['required', 'array'],
            'documents.*' => ['file', 'mimes:pdf,jpg,png', 'max:2048'],
        ]);

        try {
            foreach ($validated['documents'] as $document) {
                $path = $document->store('rider_documents', 'public');
                $rider->documents()->create([
                    'file_path' => $path,
                    'file_name' => $document->getClientOriginalName(),
                    'file_type' => $document->getClientMimeType(),
                ]);
            }
            return back()->with('success', 'Documents uploaded successfully.');
        } catch (Exception $e) {
            Log::error("Rider document upload failed", [
                'rider_id' => $rider->id,
                'admin_user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to upload documents.');
        }
    }
}