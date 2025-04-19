<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,staff']);
    }

    public function index(Request $request): View
    {
        $period = $request->input('period', 'monthly');
        $startDate = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $cacheKey = "analytics_{$period}_" . $startDate->format('Ymd');
        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($startDate) {
            $totalBookings = Booking::where('created_at', '>=', $startDate)->count();
            $completedBookings = Booking::where('status', 'completed')
                ->where('created_at', '>=', $startDate)
                ->count();
            $totalRevenue = Payment::where('status', 'paid')
                ->where('created_at', '>=', $startDate)
                ->sum('amount');
            $activeRiders = Rider::where('status', 'available')->count();

            return compact('totalBookings', 'completedBookings', 'totalRevenue', 'activeRiders');
        });

        return view('admin.analytics.index', array_merge($data, ['period' => $period]));
    }
}