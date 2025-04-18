<?php
// app/Http/Controllers/Admin/RiderManagementController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\User; // <-- Add User model
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash; // <-- Add Hash facade
use Illuminate\Support\Facades\DB; // <-- Add DB facade for transactions
use Illuminate\Validation\Rules; // <-- Add Rules for password validation
use MatanYadaev\EloquentSpatial\Objects\Point; // Required if setting default location

class RiderManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $riders = Rider::with('user')->latest()->paginate(15);
        return view('admin.riders.index', compact('riders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.riders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // User fields
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // Rider fields
            'address' => ['required', 'string', 'max:1000'],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'plate_number' => ['required', 'string', 'max:20'],
            // Add validation for driver's license, other docs later
            'is_active' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:offline,available,on_task,on_break'], // Include valid rider statuses
        ]);

        DB::beginTransaction(); // Start transaction

        try {
            // Create User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'user_type' => 'rider', // Set user type
                'email_verified_at' => now(), // Optionally auto-verify email for admin creation
            ]);

            // Create Rider linked to User
            $riderData = [
                'user_id' => $user->id,
                'address' => $validated['address'],
                'vehicle_type' => $validated['vehicle_type'],
                'plate_number' => $validated['plate_number'],
                'is_active' => $request->has('is_active'),
                'status' => $request->input('status', 'offline'), // Default status if not provided
                // Set a default location (e.g., office) or leave null if nullable
                 'current_location' => null, // Or new Point(0, 0), or specific coordinates
                 //'location_updated_at' => now(), // Only set if location is set
            ];

             // Handle default location if needed and coordinates are available
             // if ($request->filled('latitude') && $request->filled('longitude')) {
             //     $riderData['current_location'] = new Point($request->latitude, $request->longitude);
             //     $riderData['location_updated_at'] = now();
             // }


            Rider::create($riderData);

            DB::commit(); // Commit transaction

            return redirect()->route('admin.riders.index')
                             ->with('success', 'Rider registered successfully.');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // Log the error: Log::error('Rider creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to register rider. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource. (We excluded this route)
     */
    // public function show(Rider $rider)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rider $rider): View
    {
        $rider->load('user'); // Eager load user data
        return view('admin.riders.edit', compact('rider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rider $rider): RedirectResponse
    {
        $user = $rider->user; // Get the associated user

        $validated = $request->validate([
            // User fields - make email/phone unique check ignore current user
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email,'.$user->id],
            'phone' => ['required', 'string', 'max:20', 'unique:'.User::class.',phone,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()], // Password is optional on update
            // Rider fields
            'address' => ['required', 'string', 'max:1000'],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'plate_number' => ['required', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:offline,available,on_task,on_break'],
        ]);

         DB::beginTransaction();

        try {
            // Update User data
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ];
            // Update password only if provided
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }
            $user->update($userData);

            // Update Rider data
             $riderData = [
                'address' => $validated['address'],
                'vehicle_type' => $validated['vehicle_type'],
                'plate_number' => $validated['plate_number'],
                'is_active' => $request->has('is_active'),
                'status' => $request->input('status', $rider->status), // Keep current status if not provided
            ];
            $rider->update($riderData);

            DB::commit();

            return redirect()->route('admin.riders.index')
                             ->with('success', 'Rider updated successfully.');

         } catch (\Exception $e) {
            DB::rollBack();
             // Log the error: Log::error('Rider update failed: ' . $e->getMessage());
             return back()->withInput()->with('error', 'Failed to update rider. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rider $rider): RedirectResponse
    {
         DB::beginTransaction();
        try {
            // IMPORTANT: Deleting the User will likely cascade delete the Rider
            // due to the foreign key constraint `onDelete('cascade')` defined
            // in the create_riders_table migration [cite: MIGRATION_TABLES.pdf].
            // So, deleting the user is usually sufficient. Double-check your migration.

            $user = $rider->user;
            // Optionally delete related rider files first (documents, photos)
            // Storage::delete(...);

            $user->delete(); // This should trigger rider deletion via cascade

            // If cascade delete is not set or fails, uncomment the line below:
            // $rider->delete();

            DB::commit();

            return redirect()->route('admin.riders.index')
                             ->with('success', 'Rider deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Rider deletion failed: ' . $e->getMessage());
            return redirect()->route('admin.riders.index')
                             ->with('error', 'Failed to delete rider.');
        }
    }
}