<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Use DB facade for transactions
use Illuminate\Support\Facades\Log; // Use Log facade
use Illuminate\Validation\Rules;
use MatanYadaev\EloquentSpatial\Objects\Point; // Required if setting location
use Exception; // Added for exception handling
use Illuminate\Validation\Rule; // Added for unique checks ignoring self

class RiderManagementController extends Controller
{
    /**
     * Display a listing of riders.
     * Apply admin/staff middleware in routes.
     */
    public function index(): View
    {
        // Eager load user data for efficiency
        $riders = Rider::with('user:id,name,email,phone,user_type') // Select only necessary user fields
            ->latest('created_at') // Order by creation date
            ->paginate(15);
        return view('admin.riders.index', compact('riders'));
    }

    /**
     * Show the form for creating a new rider.
     * Apply admin/staff middleware in routes.
     */
    public function create(): View
    {
        return view('admin.riders.create');
    }

    /**
     * Store a newly created rider resource in storage.
     * Apply admin/staff middleware in routes.
     */
    public function store(Request $request): RedirectResponse
    {
        // --- Validation ---
        $validated = $request->validate([
            // User fields
            'name' => ['required', 'string', 'max:255'],
            // Ensure email and phone are unique in the users table
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['required', 'string', 'max:20', Rule::unique(User::class)],
            // Require strong password confirmation on creation
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // Rider fields
            'address' => ['required', 'string', 'max:1000'],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique(Rider::class)], // Ensure plate number is unique
            // Optional fields - 'sometimes' means validate only if present
            'is_active' => ['sometimes', 'boolean'],
            // Optional: Allow setting initial location
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            // Add validation for other required documents (license, etc.) here later
        ]);

        // --- Database Insertion (Use Transaction) ---
        DB::beginTransaction();
        try {
            // 1. Create User record
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'user_type' => 'rider', // Explicitly set user type - DO NOT allow from request
                'email_verified_at' => now(), // Auto-verify email on admin creation
                // 'phone_verified_at' => now(), // Optionally auto-verify phone
            ];
             // User::create() respects $fillable, but we construct the array manually here for clarity
            $user = User::create($userData);

            // 2. Prepare Rider data
            $riderData = [
                'user_id' => $user->id, // Link to the created user's UUID
                'address' => $validated['address'],
                'vehicle_type' => $validated['vehicle_type'],
                'plate_number' => $validated['plate_number'],
                // Handle checkbox for is_active (defaults to false if not present)
                'is_active' => $request->boolean('is_active'),
                // Set default status securely, not from request input on creation
                'status' => 'offline', // Default status for new riders
                'current_location' => null, // Default to null unless coordinates provided
                'location_updated_at' => null,
            ];

            // Set location if coordinates are provided
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $riderData['current_location'] = new Point($validated['latitude'], $validated['longitude']);
                $riderData['location_updated_at'] = now();
            }

            // 3. Create Rider record linked to the User
            Rider::create($riderData); // Assumes Rider model has correct $fillable

            DB::commit(); // Commit transaction

            return redirect()->route('admin.riders.index')
                ->with('success', 'Rider ' . $validated['name'] . ' registered successfully.');

        } catch (Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            Log::error("Rider creation failed", [
                'admin_user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
             ]);
            // Provide a specific but safe error message if possible, otherwise generic
            return back()->withInput()->with('error', 'Failed to register rider due to a server error. Please check logs or try again.');
        }
    }


    /**
     * Display the specified resource. (Excluded in routes, redirect to edit)
     */
    // public function show(Rider $rider) { abort(404); }

    /**
     * Show the form for editing the specified rider.
     * Apply admin/staff middleware in routes.
     * Route model binding implicitly finds Rider by UUID.
     */
    public function edit(Rider $rider): View
    {
        // Eager load user data to avoid extra queries in the view
        $rider->load('user');
        return view('admin.riders.edit', compact('rider'));
    }

    /**
     * Update the specified rider resource in storage.
     * Apply admin/staff middleware in routes.
     */
    public function update(Request $request, Rider $rider): RedirectResponse
    {
        $user = $rider->user; // Get the associated user

        // --- Validation ---
        $validated = $request->validate([
            // User fields - Ignore current user's email/phone for unique checks
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique(User::class)->ignore($user->id)],
            // Password is optional on update
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            // Rider fields
            'address' => ['required', 'string', 'max:1000'],
            'vehicle_type' => ['required', 'string', 'max:50'],
             // Ignore current rider's plate number for unique check
            'plate_number' => ['required', 'string', 'max:20', Rule::unique(Rider::class)->ignore($rider->id)],
            'is_active' => ['sometimes', 'boolean'],
            // Allow updating status via admin interface, ensure valid enum values
            'status' => ['sometimes', 'string', Rule::in(['offline', 'available', 'on_task', 'on_break'])],
            // Allow updating location
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
        ]);

        // --- Database Update (Use Transaction) ---
        DB::beginTransaction();
        try {
            // 1. Update User data
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ];
            // Update password only if a new one is provided
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($validated['password']);
            }
            $user->update($userData); // Update user record

            // 2. Prepare Rider data
            $riderData = [
                'address' => $validated['address'],
                'vehicle_type' => $validated['vehicle_type'],
                'plate_number' => $validated['plate_number'],
                // Handle checkbox: present means true, absent means false
                'is_active' => $request->boolean('is_active'),
                // Update status only if provided in the request
                'status' => $request->filled('status') ? $validated['status'] : $rider->status,
            ];

             // Update location if coordinates are provided
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $riderData['current_location'] = new Point($validated['latitude'], $validated['longitude']);
                $riderData['location_updated_at'] = now();
            } elseif ($request->has('latitude') || $request->has('longitude')) {
                // If one is provided but not the other, or if clearing location
                 $riderData['current_location'] = null;
                 $riderData['location_updated_at'] = null;
            }

            // 3. Update Rider data
            $rider->update($riderData); // Update rider record

            DB::commit();

            return redirect()->route('admin.riders.index')
                ->with('success', 'Rider ' . $validated['name'] . ' updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Rider update failed", [
                'rider_id' => $rider->id,
                'admin_user_id' => Auth::id(),
                'error' => $e->getMessage(),
                 'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Failed to update rider. Please check the logs or try again.');
        }
    }

    /**
     * Remove the specified rider resource from storage.
     * Apply admin/staff middleware in routes.
     */
    public function destroy(Rider $rider): RedirectResponse
    {
        DB::beginTransaction();
        try {
            // IMPORTANT: Deleting the User model will cascade delete the Rider
            // due to the foreign key constraint with onDelete('cascade').
            $user = $rider->user;
            $riderName = $user->name; // Get name before deletion

            // Optionally delete related files first (e.g., license scans if stored)
            // Storage::delete(...);

            // Deleting the user triggers the cascade delete for the rider profile
            $user->delete();

            // If cascade on delete wasn't set or fails, delete rider explicitly (less ideal)
            // $rider->delete();

            DB::commit();

            return redirect()->route('admin.riders.index')
                ->with('success', 'Rider ' . $riderName . ' deleted successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Rider deletion failed", [
                'rider_id' => $rider->id,
                'admin_user_id' => Auth::id(),
                 'error' => $e->getMessage()
            ]);
            return redirect()->route('admin.riders.index')
                ->with('error', 'Failed to delete rider. The rider might have related records.');
        }
    }
}