<?php
namespace App\Listeners;

use App\Events\BookingCreated;
use App\Events\BookingStatusUpdated;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;
use App\Models\User;

class NotifyUsers
{
    /**
     * Handle the event.
     */
    public function handle($event)
    {
        $booking = $event->booking;
        $message = '';

        if ($event instanceof BookingCreated) {
            $message = "New booking #{$booking->booking_number} created.";
        } elseif ($event instanceof BookingStatusUpdated) {
            $message = "Booking #{$booking->booking_number} status updated to {$booking->status}.";
        }

        // Notify admins and the assigned rider (if applicable)
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'staff']);
        })->get();

        if ($booking->rider && $booking->rider->user) {
            $users->push($booking->rider->user);
        }

        Notification::send($users, new GeneralNotification(
            $message,
            'mail',
            'Booking Update'
        ));
    }
}