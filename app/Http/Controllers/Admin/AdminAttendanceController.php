<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $attendances = Attendance::with('rider.user')
            ->whereNull('check_out')
            ->latest('check_in')
            ->paginate(15);

        return view('admin.attendance.index', compact('attendances'));
    }
}