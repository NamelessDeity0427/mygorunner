<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CustomerAddress;
use App\Rules\ValidPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use MatanYadaev\EloquentSpatial\Objects\Point;

class CustomerDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:customer']);
    }

    public function index(): View
    {
        $recentBookings = Booking::where('customer_id', Auth::user()->customer->id)
            ->with([
                'rider.user' => function ($query) {
                    $query->select('id', 'name');
                },
                'items'
            ])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('customer.dashboard', compact('recentBookings'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $customer = Auth::user()->customer;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,' . Auth::id()],
        ]);

        try {
            return DB::transaction(function () use ($validated, $customer) {
                Auth::user()->update([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                ]);
                return back()->with('success', 'Profile updated successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Customer profile update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $customer = Auth::user()->customer;

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'point' => [new ValidPoint('latitude', 'longitude')],
        ]);

        try {
            return DB::transaction(function () use ($validated, $customer) {
                CustomerAddress::create([
                    'customer_id' => $customer->id,
                    'label' => $validated['label'],
                    'address' => $validated['address'],
                    'location' => new Point($validated['latitude'], $validated['longitude']),
                ]);
                return back()->with('success', 'Address saved successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Customer address save failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return back()->withInput()->with('error', 'Failed to save address: ' . $e->getMessage());
        }
    }
}