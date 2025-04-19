<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;

class RiderDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $rider = $user->rider;

        $assignedBookings = Booking::where('rider_id', $rider?->id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with(['customer.user:id,name', 'items'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $todayAttendance = Attendance::where('rider_id', $rider?->id)
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
            $booking->update(['status' => 'in_progress']);
            event(new \App\Events\BookingStatusUpdated($booking));
            return back()->with('success', "Booking #{$booking->booking_number} accepted.");
        } catch (Exception $e) {
            Log::error("Booking acceptance failed", [
                'booking_id' => $booking->id,
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to accept booking.');
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

        $earnings = Booking::where('rider_id', $rider->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->sum('estimated_cost');

        return view('rider.earnings', compact('earnings', 'period'));
    }
}