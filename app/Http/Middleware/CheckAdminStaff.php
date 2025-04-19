<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            Log::warning('Unauthorized access attempt: User not authenticated', [
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);
            return redirect()->route('login')->with('error', 'Please log in to access this area.');
        }

        $user = Auth::user();
        if ($user->user_type !== 'admin' && $user->user_type !== 'staff') {
            Log::warning('Unauthorized access attempt: Insufficient role', [
                'user_id' => $user->id,
                'user_type' => $user->user_type,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);

            $redirectRoute = match ($user->user_type) {
                'customer' => 'customer.dashboard',
                'rider' => 'rider.dashboard',
                default => 'home',
            };

            return redirect()->route($redirectRoute)->with('error', 'Access denied: Insufficient permissions.');
        }

        return $next($request);
    }
}