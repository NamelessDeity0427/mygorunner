<?php
namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->user_type, ['admin', 'staff', 'customer']);
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        return $user->user_type === 'admin' ||
               $user->user_type === 'staff' ||
               ($user->user_type === 'customer' && $ticket->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->user_type === 'customer';
    }

    public function update(User $user, SupportTicket $ticket): bool
    {
        return $user->user_type === 'admin' ||
               $user->user_type === 'staff' ||
               ($user->user_type === 'customer' && $ticket->user_id === $user->id && $ticket->status === 'open');
    }

    public function delete(User $user, SupportTicket $ticket): bool
    {
        return $user->user_type === 'admin';
    }

    public function restore(User $user, SupportTicket $ticket): bool
    {
        return $user->user_type === 'admin';
    }

    public function forceDelete(User $user, SupportTicket $ticket): bool
    {
        return $user->user_type === 'admin';
    }
}