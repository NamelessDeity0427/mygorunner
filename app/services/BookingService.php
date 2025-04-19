<?php
namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Carbon\Carbon;
use Exception;

class BookingService
{
    /**
     * Create a new booking with associated items.
     *
     * @param array $data Validated booking data
     * @param \App\Models\Customer $customer The customer creating the booking
     * @return \App\Models\Booking The created booking
     * @throws \Exception If creation fails
     */
    public function createBooking(array $data, $customer): Booking
    {
        DB::beginTransaction();
        try {
            $scheduled_at = null;
            if (!empty($data['scheduled_at_date']) && !empty($data['scheduled_at_time'])) {
                $scheduled_at = Carbon::parse($data['scheduled_at_date'] . ' ' . $data['scheduled_at_time']);
            }

            $bookingData = [
                'customer_id' => $customer->id,
                'service_type' => $data['service_type'],
                'pickup_address' => $data['pickup_address'],
                'pickup_location' => new Point($data['pickup_latitude'], $data['pickup_longitude']),
                'delivery_address' => $data['delivery_address'],
                'delivery_location' => new Point($data['delivery_latitude'], $data['delivery_longitude']),
                'scheduled_at' => $scheduled_at,
                'special_instructions' => $data['special_instructions'] ?? null,
                'status' => 'pending',
                'booking_number' => 'BKG-' . strtoupper(uniqid()),
            ];

            $booking = Booking::create($bookingData);

            if (!empty($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    if (!empty($itemData['name'])) {
                        $booking->items()->create([
                            'name' => $itemData['name'],
                            'quantity' => $itemData['quantity'] ?? 1,
                            'notes' => $itemData['notes'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            event(new \App\Events\BookingCreated($booking));
            return $booking;
        } catch (Exception $e) {
            DB::rollback();
            Log::error("Booking creation failed", [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate estimated cost based on distance and service type.
     *
     * @param array $data Booking data with pickup and delivery locations
     * @return float Estimated cost
     */
    public function calculateCost(array $data): float
    {
        // Placeholder: Use a geospatial library or API to calculate distance
        $distance = $this->calculateDistance(
            $data['pickup_latitude'],
            $data['pickup_longitude'],
            $data['delivery_latitude'],
            $data['delivery_longitude']
        );

        $baseRate = config('booking.rates.' . $data['service_type'], 5.0); // Per km rate
        $cost = $baseRate * $distance;

        return round($cost, 2);
    }

    /**
     * Calculate distance between two points using Haversine formula.
     *
     * @param float $lat1 Latitude of first point
     * @param float $lon1 Longitude of first point
     * @param float $lat2 Latitude of second point
     * @param float $lon2 Longitude of second point
     * @return float Distance in kilometers
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDiff / 2) * sin($lonDiff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}