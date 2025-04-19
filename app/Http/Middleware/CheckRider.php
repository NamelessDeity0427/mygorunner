<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRider
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->rider) {
            return redirect()->route('home')->with('error', 'You need a rider profile to access this area.');
        }
        return $next($request);
    }
}