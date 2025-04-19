<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class CustomerDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $recentBookings = Booking::where('customer_id', $user->customer?->id)
            ->with(['rider.user:id,name', 'items'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('customer.dashboard', compact('recentBookings'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            return back()->with('error', 'Customer profile not found.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
            ]);
            $customer->update(['address' => $validated['address']]);
            return back()->with('success', 'Profile updated successfully.');
        } catch (Exception $e) {
            Log::error("Customer profile update failed", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Failed to update profile.');
        }
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            return back()->with('error', 'Customer profile not found.');
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        try {
            CustomerAddress::create([
                'customer_id' => $customer->id,
                'label' => $validated['label'],
                'address' => $validated['address'],
                'location' => new \MatanYadaev\EloquentSpatial\Objects\Point($validated['latitude'], $validated['longitude']),
            ]);
            return back()->with('success', 'Address saved successfully.');
        } catch (Exception $e) {
            Log::error("Customer address save failed", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Failed to save address.');
        }
    }
}