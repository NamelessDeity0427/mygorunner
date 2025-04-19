<?php
namespace App\Listeners;

use App\Events\BookingCreated;
use App\Events\BookingStatusUpdated;
use App\Models\BookingStatusHistory;
use Illuminate\Support\Facades\Auth;

class LogBookingActivity
{
    public function handle($event)
    {
        $booking = $event->booking;

        try {
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'status' => $booking->status,
                'changed_by' => Auth::id(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log booking activity', [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}