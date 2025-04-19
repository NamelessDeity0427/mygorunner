<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Attendance;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RiderDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:rider']);
    }

    public function index(): View
    {
        $rider = Auth::user()->rider;

        $assignedBookings = Booking::where('rider_id', $rider->id)
            ->whereIn('status', ['assigned', 'at_pickup', 'picked_up', 'on_the_way', 'at_delivery'])
            ->with([
                'customer.user' => function ($query) {
                    $query->select('id', 'name');
                },
                'items'
            ])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $todayAttendance = Attendance::where('rider_id', $rider->id)
            ->whereDate('check_in', today())
            ->orderBy('check_in', 'desc')
            ->first();

        return view('rider.dashboard', compact('assignedBookings', 'todayAttendance'));
    }

    public function acceptBooking(Request $request, Booking $booking): RedirectResponse
    {
        $rider = Auth::user()->rider;

        if ($booking->rider_id !== $rider->id || $booking->status !== 'assigned') {
            return back()->with('error', 'You are not authorized to accept this booking.');
        }

        try {
            return DB::transaction(function () use ($booking) {
                $booking->update(['status' => 'at_pickup']);
                event(new \App\Events\BookingStatusUpdated($booking));
                return back()->with('success', "Booking #{$booking->id} accepted.");
            });
        } catch (\Exception $e) {
            Log::error('Booking acceptance failed', [
                'booking_id' => $booking->id,
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to accept booking: ' . $e->getMessage());
        }
    }

    public function earnings(Request $request): View
    {
        $rider = Auth::user()->rider;
        $period = $request->input('period', 'daily');

        $startDate = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => now()->startOfDay(),
        };

        $earnings = Payment::whereHas('booking', function ($query) use ($rider, $startDate) {
            $query->where('rider_id', $rider->id)
                  ->where('status', 'completed')
                  ->where('created_at', '>=', $startDate);
        })->sum('amount');

        return view('rider.earnings', compact('earnings', 'period'));
    }
}