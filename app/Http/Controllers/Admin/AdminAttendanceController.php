<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,staff']);
    }

    public function index(Request $request): View
    {
        $query = Attendance::with(['rider.user' => function ($query) {
            $query->select('id', 'name');
        }]);

        if ($request->has('date')) {
            $query->whereDate('check_in', $request->input('date'));
        } else {
            $query->whereNull('check_out');
        }

        $attendances = $query->latest('check_in')->paginate(15);

        return view('admin.attendance.index', compact('attendances'));
    }
}