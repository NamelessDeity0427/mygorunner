<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Rider;
use App\Rules\ValidPoint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Point;

class BookingDispatchController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin,staff']);
    }

    public function dispatch(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->rider_id || $booking->status !== 'pending') {
            return response()->json(['error' => 'Booking is already assigned or not in pending status.'], 400);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'point' => [new ValidPoint('latitude', 'longitude')],
        ]);

        try {
            return DB::transaction(function () use ($booking, $validated) {
                $point = new Point($validated['latitude'], $validated['longitude']);

                $nearestRider = Rider::query()
                    ->where('status', 'available')
                    ->whereNotNull('current_location')
                    ->where('location_updated_at', '>=', now()->subMinutes(30))
                    ->select(['id', 'user_id', 'current_location'])
                    ->orderByRaw("ST_Distance_Sphere(current_location, ?)", [$point])
                    ->first();

                if (!$nearestRider) {
                    return response()->json(['error' => 'No available riders found nearby.'], 404);
                }

                $booking->update([
                    'rider_id' => $nearestRider->id,
                    'status' => 'assigned',
                ]);

                event(new \App\Events\BookingStatusUpdated($booking));

                return response()->json([
                    'message' => 'Booking assigned successfully.',
                    'rider_id' => $nearestRider->id,
                    'booking_id' => $booking->id,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Booking dispatch failed', [
                'booking_id' => $booking->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to dispatch booking: ' . $e->getMessage()], 500);
        }
    }
}