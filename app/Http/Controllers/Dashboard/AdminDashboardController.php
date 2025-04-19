<?php
namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Booking;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,staff']);
    }

    public function index(): View
    {
        $cacheKey = 'admin_dashboard_data_' . now()->format('Ymd');
        $data = Cache::remember($cacheKey, now()->addMinutes(15), function () {
            $activeRiders = Attendance::whereNull('check_out')
                ->distinct('rider_id')
                ->count('rider_id');

            $pendingBookings = Booking::where('status', 'pending')->count();

            $recentTickets = SupportTicket::with(['user' => function ($query) {
                $query->select('id', 'name');
            }])
                ->latest()
                ->take(5)
                ->get();

            $recentBookings = Booking::with([
                'customer.user' => function ($query) {
                    $query->select('id', 'name');
                },
                'rider.user' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
                ->latest()
                ->take(5)
                ->get();

            return compact('activeRiders', 'pendingBookings', 'recentTickets', 'recentBookings');
        });

        return view('admin.dashboard', $data);
    }
}