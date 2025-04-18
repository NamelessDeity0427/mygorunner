<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20', 'unique:'.User::class], // Add validation for phone
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone, // Add phone to creation
            'password' => Hash::make($request->password),
            // 'user_type' will default to 'customer' based on migration
        ]);

        // Optional: Create associated Customer profile here if needed
        // $user->customer()->create(['address' => 'Default Address']); // Example

        event(new Registered($user));

        Auth::login($user);

        // Redirect based on role after registration (similar to login redirect)
        return redirect($this->getRedirectPath()); // Use helper method below
        // Or directly: return redirect(RouteServiceProvider::HOME); // Breeze default
    }

    // Helper method for redirection (used in store and AuthenticatedSessionController)
    protected function getRedirectPath(): string
    {
        $user = Auth::user();
        switch ($user->user_type) {
            case 'admin':
            case 'staff': // Assuming staff and admin share a dashboard initially
                return route('admin.dashboard');
            case 'rider':
                return route('rider.dashboard');
            case 'customer':
            default:
                return route('customer.dashboard'); // Changed from RouteServiceProvider::HOME
        }
    }
}
