<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class NotificationController extends Controller
{
    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'recipient_type' => ['required', 'in:all,riders,admins'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $users = match ($validated['recipient_type']) {
                'all' => User::all(),
                'riders' => User::where('user_type', 'rider')->get(),
                'admins' => User::whereHas('roles', fn($query) => $query->whereIn('name', ['admin', 'staff']))->get(),
            };

            Notification::send($users, new GeneralNotification(
                $validated['message'],
                'mail',
                $validated['title']
            ));

            return redirect()->back()->with('success', 'Notification sent successfully.');
        } catch (Exception $e) {
            Log::error("Notification sending failed", [
                'admin_user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to send notification.');
        }
    }
}