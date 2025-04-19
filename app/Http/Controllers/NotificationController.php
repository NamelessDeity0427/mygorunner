<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,staff']);
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'recipient_type' => ['required', 'in:all,riders,staff,admins'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $users = match ($validated['recipient_type']) {
                'all' => User::all(),
                'riders' => User::where('user_type', 'rider')->get(),
                'staff' => User::where('user_type', 'staff')->get(),
                'admins' => User::where('user_type', 'admin')->get(),
            };

            Notification::send($users, new GeneralNotification(
                $validated['message'],
                ['mail', 'database'],
                $validated['title'] ?? 'System Notification'
            ));

            return redirect()->back()->with('success', 'Notification sent successfully.');
        } catch (\Exception $e) {
            Log::error('Notification sending failed', [
                'user_id' => Auth::id(),
                'recipient_type' => $validated['recipient_type'],
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to send notification: ' . $e->getMessage());
        }
    }
}