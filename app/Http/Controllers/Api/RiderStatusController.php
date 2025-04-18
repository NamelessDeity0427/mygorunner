<?php
// app/Http/Controllers/Api/RiderStatusController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RiderStatusController extends Controller
{
    /**
     * Get active riders with their current locations.
     */
    public function getActiveRiders(Request $request): JsonResponse
    {
        $riders = Rider::with('user:id,name') // Only select needed user fields
            ->where('is_active', true)
            ->whereNotNull('current_location') // Only fetch riders with a location set
            // Optionally filter by status: ->whereIn('status', ['available', 'on_task', 'on_break'])
            ->get()
            ->map(function ($rider) {
                // Format data for the map frontend
                return [
                    'id' => $rider->id,
                    'name' => $rider->user->name ?? 'Unknown Rider',
                    'latitude' => $rider->current_location->latitude, // Access spatial data
                    'longitude' => $rider->current_location->longitude,
                    'status' => $rider->status,
                     // Add any other relevant info, like last update time
                     'location_updated_at' => $rider->location_updated_at?->diffForHumans(),
                ];
            });

        return response()->json(['riders' => $riders]);
    }

     /**
     * Endpoint for a rider to update their location (Example)
     * Needs authentication (Sanctum recommended for APIs)
     */
    public function updateLocation(Request $request): JsonResponse
    {
         // Ensure this route is protected by auth middleware (e.g., auth:sanctum) in api.php
         $user = $request->user();
         if (!$user || !$user->isRider() || !$user->rider) {
             return response()->json(['error' => 'Unauthorized or rider profile not found.'], 403);
         }

         $validated = $request->validate([
             'latitude' => ['required', 'numeric', 'between:-90,90'],
             'longitude' => ['required', 'numeric', 'between:-180,180'],
         ]);

         try {
             $point = new \MatanYadaev\EloquentSpatial\Objects\Point($validated['latitude'], $validated['longitude']);

             $user->rider->update([
                 'current_location' => $point,
                 'location_updated_at' => now()
             ]);

             // Optionally broadcast an event here for real-time updates
             // event(new \App\Events\RiderLocationUpdated($user->rider));

             return response()->json(['message' => 'Location updated successfully.']);

         } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error("Rider location update failed for user {$user->id}: " . $e->getMessage());
             return response()->json(['error' => 'Failed to update location.'], 500);
         }
    }
}