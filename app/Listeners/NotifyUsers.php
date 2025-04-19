<?php
namespace App\Listeners;

use App\Events\BookingCreated;
use App\Events\BookingStatusUpdated;
use App\Notifications\BookingNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class NotifyUsers
{
    public function handle($event)
    {
        $booking = $event->booking;
        $title = '';
        $message = '';

        if ($event instanceof BookingCreated) {
            $title = 'New Booking Created';
            $message = "New booking #{$booking->id} has been created.";
        } elseif ($event instanceof BookingStatusUpdated) {
            $title = 'Booking Status Updated';
            $message = "Booking #{$booking->id} status updated to {$booking->status}.";
        }

        $users = User::whereIn('user_type', ['admin', 'staff'])->get();

        if ($booking->rider && $booking->rider->user) {
            $users->push($booking->rider->user);
        }

        if ($booking->customer && $booking->customer->user) {
            $users->push($booking->customer->user);
        }

        Notification::send($users, new BookingNotification($message, ['mail', 'database'], $title));
    }
}