<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Rules\ValidPoint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use MatanYadaev\EloquentSpatial\Objects\Point;

class RiderStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
        $this->middleware(['role:admin,staff'])->only('getActiveRiders');
        $this->middleware(['role:rider'])->except('getActiveRiders');
    }

    public function getActiveRiders(Request $request): JsonResponse
    {
        try {
            $riders = Rider::with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
                ->where('status', 'available')
                ->whereNotNull('current_location')
                ->where('location_updated_at', '>=', now()->subHour())
                ->select(['id', 'user_id', 'current_location', 'status', 'location_updated_at'])
                ->get()
                ->map(function ($rider) {
                    return [
                        'id' => $rider->id,
                        'name' => $rider->user?->name ?? 'Unknown Rider',
                        'latitude' => $rider->current_location->latitude,
                        'longitude' => $rider->current_location->longitude,
                        'status' => $rider->status,
                        'location_updated_at' => $rider->location_updated_at?->diffForHumans() ?? null,
                    ];
                });

            return response()->json(['riders' => $riders]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch active riders', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Could not retrieve rider data.', 'riders' => []], 500);
        }
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $rider = auth()->user()->rider;

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'point' => [new ValidPoint('latitude', 'longitude')],
        ]);

        try {
            $rider->update([
                'current_location' => new Point($validated['latitude'], $validated['longitude']),
                'location_updated_at' => now(),
            ]);

            event(new \App\Events\RiderLocationUpdated($rider));

            return response()->json(['message' => 'Location updated successfully.']);
        } catch (\Exception $e) {
            Log::error('Rider location update failed', [
                'rider_id' => $rider->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to update location: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $rider = auth()->user()->rider;

        $validated = $request->validate([
            'status' => ['required', 'in:available,offline'],
        ]);

        if ($rider->status === 'assigned' && $validated['status'] !== 'assigned') {
            return response()->json(['error' => 'Cannot change status while assigned to a task.'], 400);
        }

        if ($rider->status === $validated['status']) {
            return response()->json(['message' => 'Status unchanged.', 'current_status' => $rider->status]);
        }

        try {
            $rider->update(['status' => $validated['status']]);

            event(new \App\Events\RiderStatusUpdated($rider));

            return response()->json(['message' => 'Status updated successfully.', 'new_status' => $validated['status']]);
        } catch (\Exception $e) {
            Log::error('Rider status update failed', [
                'rider_id' => $rider->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to update status: ' . $e->getMessage()], 500);
        }
    }
}