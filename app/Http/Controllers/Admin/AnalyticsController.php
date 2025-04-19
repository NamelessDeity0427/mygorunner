<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->input('period', 'monthly');
        $startDate = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $totalBookings = Booking::where('created_at', '>=', $startDate)->count();
        $completedBookings = Booking::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->count();
        $totalRevenue = Booking::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->sum('estimated_cost');
        $activeRiders = Rider::where('is_active', true)->count();

        return view('admin.analytics.index', compact(
            'totalBookings',
            'completedBookings',
            'totalRevenue',
            'activeRiders',
            'period'
        ));
    }
}