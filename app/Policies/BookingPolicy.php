<?php
namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->user_type, ['admin', 'staff', 'customer', 'rider']);
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->user_type === 'admin' ||
               $user->user_type === 'staff' ||
               ($user->user_type === 'customer' && $booking->customer->user_id === $user->id) ||
               ($user->user_type === 'rider' && $booking->rider_id === $user->rider->id);
    }

    public function create(User $user): bool
    {
        return $user->user_type === 'customer' && !is_null($user->customer);
    }

    public function update(User $user, Booking $booking): bool
    {
        return $user->user_type === 'admin' ||
               $user->user_type === 'staff' ||
               ($user->user_type === 'rider' && $booking->rider_id === $user->rider->id);
    }

    public function delete(User $user, Booking $booking): bool
    {
        return ($user->user_type === 'customer' && $booking->customer->user_id === $user->id && $booking->status === 'pending') ||
               in_array($user->user_type, ['admin', 'staff']);
    }

    public function restore(User $user, Booking $booking): bool
    {
        return in_array($user->user_type, ['admin', 'staff']);
    }

    public function forceDelete(User $user, Booking $booking): bool
    {
        return $user->user_type === 'admin';
    }
}