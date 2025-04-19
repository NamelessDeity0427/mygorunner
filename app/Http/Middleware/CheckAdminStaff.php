<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminStaff
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            Log::warning("Unauthorized access attempt: User not authenticated", [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);
            return redirect('/')->with('error', 'Please log in to access this area.');
        }

        $user = Auth::user();
        if (!$user->hasAnyPermission(['manage-riders', 'manage-bookings', 'view-analytics'])) {
            Log::warning("Unauthorized access attempt: Insufficient permissions", [
                'user_id' => $user->id,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);

            // Redirect based on user type
            $redirectRoute = match ($user->user_type) {
                'customer' => 'customer.dashboard',
                'rider' => 'rider.dashboard',
                default => '/',
            };

            return redirect()->route($redirectRoute)->with('error', 'Access Denied: You do not have permission to access this area.');
        }

        return $next($request);
    }
}