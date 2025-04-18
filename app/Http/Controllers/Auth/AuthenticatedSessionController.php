<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Change the redirect logic here
        // return redirect()->intended(RouteServiceProvider::HOME); // Default Breeze redirect

        // Use the same helper method as in RegisteredUserController
        return redirect()->intended($this->getRedirectPath());
    }

    protected function getRedirectPath(): string
    {
        $user = Auth::user();
        switch ($user->user_type) {
            case 'admin':
            case 'staff':
                return route('admin.dashboard');
            case 'rider':
                return route('rider.dashboard');
            case 'customer':
            default:
                return route('customer.dashboard');
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
