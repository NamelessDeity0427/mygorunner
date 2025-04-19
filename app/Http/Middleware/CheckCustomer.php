<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckCustomer
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->customer) {
            return redirect()->route('home')->with('error', 'You need a customer profile to access this area.');
        }
        return $next($request);
    }
}