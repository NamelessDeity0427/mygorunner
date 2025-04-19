<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use MatanYadaev\EloquentSpatial\Objects\Point; // Use Point object
use Exception; // Added for exception handling

class RiderStatusController extends Controller
{
    /**
     * Get active riders with their current locations.
     * Used by Admin Dashboard Map.
     * Apply appropriate middleware in api routes (e.g., auth:sanctum, admin/staff check).
     */
    public function getActiveRiders(Request $request): JsonResponse
    {
        try {
            // Select only necessary fields for performance and security
            // Filter by active status and ensure location is set
            // Consider adding a time filter (e.g., location_updated_at > 1 hour ago) if needed
            $riders = Rider::with('user:id,name') // Eager load only user ID and name
                ->where('is_active', true)
                ->whereNotNull('current_location')
                // ->whereIn('status', ['available', 'on_task', 'on_break']) // Optional: Filter by status
                ->select(['id', 'user_id', 'current_location', 'status', 'location_updated_at']) // Select only needed rider fields
                ->get()
                ->map(function ($rider) {
                    // Safely access user name, provide default if relationship fails
                    $userName = optional($rider->user)->name ?? 'Unknown Rider';
                    // Format data clearly for the frontend map component
                    return [
                        'id' => $rider->id, // Rider's UUID
                        'name' => $userName,
                         // Ensure location is accessed correctly
                        'latitude' => $rider->current_location?->latitude,
                        'longitude' => $rider->current_location?->longitude,
                        'status' => $rider->status,
                        // Provide human-readable time difference, handle null case
                        'location_updated_at' => optional($rider->location_updated_at)->diffForHumans(),
                    ];
                });

            return response()->json(['riders' => $riders]);

        } catch (Exception $e) {
            Log::error("Failed to fetch active riders for admin map", ['error' => $e->getMessage()]);
            // Return an empty array or an error structure
            return response()->json(['error' => 'Could not retrieve rider data.', 'riders' => []], 500);
        }
    }

    /**
     * Endpoint for a rider to update their location via API.
     * Apply 'auth:sanctum' middleware in api routes.
     */
    public function updateLocation(Request $request): JsonResponse
    {
        // --- Authentication & Authorization ---
        $user = $request->user(); // Get authenticated user via Sanctum token
        if (!$user || !$user->isRider() || !$user->rider) {
            // Log unauthorized access attempt
            Log::warning("Unauthorized attempt to update rider location.", ['user_id' => $user?->id, 'ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized or rider profile not found.'], 403);
        }

        // Get the rider model associated with the authenticated user
        $rider = $user->rider;

        // --- Validation ---
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            // Optional: 'accuracy' => ['nullable', 'numeric', 'min:0'],
            // Optional: 'timestamp' => ['nullable', 'integer'] // Client timestamp
        ]);

        // --- Database Update ---
        try {
            $point = new Point($validated['latitude'], $validated['longitude']);

            // Update rider's location and timestamp
            $rider->update([
                'current_location' => $point,
                'location_updated_at' => now()
            ]);

            // --- Real-time Event (Optional but recommended) ---
            // Broadcast an event using Laravel Echo / Pusher / Reverb
            // Ensure the event listener on the frontend updates the admin map
            // Example:
            // event(new \App\Events\RiderLocationUpdated($rider));

            return response()->json(['message' => 'Location updated successfully.']);

        } catch (Exception $e) {
            Log::error("Rider location update failed", [
                'rider_id' => $rider->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                 'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to update location due to a server error.'], 500);
        }
    }

     /**
      * Endpoint for a rider to update their status via API (e.g., toggle break).
      * Apply 'auth:sanctum' middleware in api routes.
      */
    // public function updateStatus(Request $request): JsonResponse
    // {
    //     $user = $request->user();
    //     if (!$user || !$user->isRider() || !$user->rider) {
    //         return response()->json(['error' => 'Unauthorized or rider profile not found.'], 403);
    //     }
    //     $rider = $user->rider;

    //     $validated = $request->validate([
    //         // Allow only specific status changes initiated by the rider
    //         'status' => ['required', 'string', Rule::in(['available', 'on_break', 'offline'])],
    //     ]);

    //     // Business Logic: Prevent certain status changes (e.g., cannot go 'offline' while 'on_task')
    //     if ($rider->status === 'on_task' && $validated['status'] !== 'on_task') {
    //          return response()->json(['error' => 'Cannot change status while on an active task.'], 400);
    //     }
    //      if ($validated['status'] === $rider->status) {
    //          return response()->json(['message' => 'Status already set.', 'current_status' => $rider->status]);
    //      }

    //     try {
    //         $rider->update(['status' => $validated['status']]);

    //         // Optionally broadcast status change event
    //         // event(new \App\Events\RiderStatusUpdated($rider));

    //         return response()->json(['message' => 'Status updated successfully.', 'new_status' => $validated['status']]);

    //     } catch (Exception $e) {
    //          Log::error("Rider status update failed", ['rider_id' => $rider->id, 'user_id' => $user->id, 'error' => $e->getMessage()]);
    //         return response()->json(['error' => 'Failed to update status.'], 500);
    //     }
    // }
}