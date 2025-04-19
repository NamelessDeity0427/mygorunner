<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Booking;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $activeRiders = Attendance::whereNull('check_out')
            ->distinct('rider_id')
            ->count('rider_id');

        $pendingBookings = Booking::where('status', 'pending')->count();

        $recentTickets = SupportTicket::with('user')
            ->latest()
            ->take(5)
            ->get();

        $recentBookings = Booking::with(['customer.user', 'rider.user'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'activeRiders',
            'pendingBookings',
            'recentTickets',
            'recentBookings'
        ));
    }
}