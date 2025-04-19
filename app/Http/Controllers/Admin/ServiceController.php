<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Needed for image handling
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception; // Added for exception handling

class ServiceController extends Controller
{
    // Apply admin/staff middleware to all methods via routes or constructor

    /**
     * Display a listing of the services.
     */
    public function index(): View
    {
        $services = Service::latest()->paginate(10);
        return view('admin.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create(): View
    {
        return view('admin.services.create');
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name', // Ensure name is unique
            'description' => 'nullable|string|max:2000',
            'price' => 'nullable|numeric|min:0|max:99999.99', // Added max
            'category' => 'nullable|string|max:100',
            // Stricter image validation: ensure it's an image, specific types, reasonable size
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB Max, added webp
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $serviceData = $validated;
            // Handle boolean checkbox input securely
            $serviceData['is_active'] = $request->boolean('is_active');

            if ($request->hasFile('image')) {
                 // Validate again if it's a valid image before storing
                 if ($request->file('image')->isValid()) {
                    // Store image in a structured path, use hash name for uniqueness
                    $path = $request->file('image')->store('public/services'); // Stored in storage/app/public/services
                     // Store the relative path accessible via Storage facade or asset() helper
                    $serviceData['image_path'] = str_replace('public/', '', $path); // Store 'services/filename.jpg'
                 } else {
                     return back()->withInput()->with('error', 'Invalid image file uploaded.');
                 }
            }

            Service::create($serviceData); // Uses UUID automatically if HasUuids trait is used

            return redirect()->route('admin.services.index')
                ->with('success', 'Service "' . $validated['name'] . '" created successfully.');

        } catch (Exception $e) {
             Log::error("Service creation failed", ['admin_user_id' => Auth::id(), 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create service due to a server error.');
        }
    }

    /**
     * Display the specified resource (redirects to edit).
     */
    public function show(Service $service): RedirectResponse
    {
        // Typically redirect to edit for admin management
        return redirect()->route('admin.services.edit', $service->id); // Use ID (UUID)
    }

    /**
     * Show the form for editing the specified service.
     * Route model binding finds Service by UUID.
     */
    public function edit(Service $service): View
    {
        return view('admin.services.edit', compact('service'));
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            // Ensure name is unique, ignoring the current service's name
            'name' => ['required', 'string', 'max:255', Rule::unique(Service::class)->ignore($service->id)],
            'description' => 'nullable|string|max:2000',
            'price' => 'nullable|numeric|min:0|max:99999.99',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'sometimes|boolean',
            'remove_image' => 'sometimes|boolean', // Added checkbox to remove image
        ]);

         try {
            $serviceData = $validated;
            $serviceData['is_active'] = $request->boolean('is_active');

            // Handle image removal request
            if ($request->boolean('remove_image') && $service->image_path) {
                Storage::disk('public')->delete($service->image_path);
                $serviceData['image_path'] = null; // Clear path in database
            }
            // Handle new image upload
            elseif ($request->hasFile('image')) {
                 if ($request->file('image')->isValid()) {
                    // Delete old image if it exists
                    if ($service->image_path) {
                        Storage::disk('public')->delete($service->image_path);
                    }
                    // Store new image
                    $path = $request->file('image')->store('public/services');
                    $serviceData['image_path'] = str_replace('public/', '', $path);
                 } else {
                      return back()->withInput()->with('error', 'Invalid image file uploaded.');
                 }
            }
            // Note: If neither remove_image nor a new image is provided, image_path remains unchanged.

            $service->update($serviceData);

            return redirect()->route('admin.services.index')
                ->with('success', 'Service "' . $validated['name'] . '" updated successfully.');

         } catch (Exception $e) {
             Log::error("Service update failed", ['service_id' => $service->id, 'admin_user_id' => Auth::id(), 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update service due to a server error.');
        }
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Service $service): RedirectResponse
    {
         try {
             $serviceName = $service->name;
            // Delete the associated image file first if it exists
            if ($service->image_path) {
                Storage::disk('public')->delete($service->image_path);
            }
            $service->delete(); // Deletes the database record

            return redirect()->route('admin.services.index')
                ->with('success', 'Service "' . $serviceName . '" deleted successfully.');
         } catch (Exception $e) {
              Log::error("Service deletion failed", ['service_id' => $service->id, 'admin_user_id' => Auth::id(), 'error' => $e->getMessage()]);
             // Check for foreign key constraints if deletion fails
             if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                 return redirect()->route('admin.services.index')
                     ->with('error', 'Failed to delete service "' . $service->name . '". It might be linked to other records.');
             }
             return redirect()->route('admin.services.index')
                 ->with('error', 'Failed to delete service due to a server error.');
         }
    }
}