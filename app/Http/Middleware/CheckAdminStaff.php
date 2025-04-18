<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Import Auth
use Symfony\Component\HttpFoundation\Response;

class CheckAdminStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated AND is admin or staff
        if (!Auth::check() || !(Auth::user()->isAdmin() || Auth::user()->isStaff())) {
             // You can redirect them, show an error, or abort
             // abort(403, 'Unauthorized action.'); // Simple forbidden error
             return redirect('/')->with('error', 'Access Denied: You do not have permission to access this area.'); // Redirect with message
        }

        return $next($request);
    }
}