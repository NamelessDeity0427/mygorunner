<?php
namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Rider;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Carbon\Carbon;
use Exception;

class AttendanceController extends Controller
{
    /**
     * Display the attendance check-in/out page.
     */
    public function index(): View | RedirectResponse
    {
        $user = Auth::user();
        $rider = $user->rider;

        if (!$rider) {
            Log::warning("User attempted to access attendance without a rider profile.", ['user_id' => $user->id]);
            return redirect()->route('rider.dashboard')->with('error', 'Rider profile not found.');
        }

        $currentAttendance = Attendance::where('rider_id', $rider->id)
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        $isCheckedIn = !is_null($currentAttendance);

        $todayAttendance = Attendance::where('rider_id', $rider->id)
            ->whereDate('check_in', today())
            ->orderBy('check_in', 'desc')
            ->get();

        $adminQrCodeValue = SystemSetting::getValue('current_attendance_qr_code', null);

        if (!$adminQrCodeValue) {
            Log::error("Attendance QR Code Setting is missing in system_settings table.");
            session()->flash('error', 'Attendance system is currently unavailable. Please contact support.');
        }

        return view('rider.attendance.index', compact(
            'isCheckedIn',
            'adminQrCodeValue',
            'currentAttendance',
            'todayAttendance'
        ));
    }

    /**
     * Process Rider Check-in via AJAX.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $user = Auth::user();
        $rider = $user->rider;

        if (!$rider) {
            return response()->json(['success' => false, 'message' => 'Rider profile not found.'], 403);
        }

        $validated = $request->validate([
            'qr_code_data' => ['required', 'string'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
        ]);

        $expectedQrCode = SystemSetting::getValue('current_attendance_qr_code');
        if (!$expectedQrCode || $validated['qr_code_data'] != $expectedQrCode) {
            Log::warning("Rider Check-in QR mismatch", [
                'rider_id' => $rider->id,
                'expected' => $expectedQrCode,
                'received' => $validated['qr_code_data'],
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid or expired QR code.'], 400);
        }

        $existingCheckIn = Attendance::where('rider_id', $rider->id)
            ->whereNull('check_out')
            ->exists();

        if ($existingCheckIn) {
            return response()->json(['success' => false, 'message' => 'You are already checked in.'], 400);
        }

        $attendanceData = [
            'rider_id' => $rider->id,
            'check_in' => now(),
            'check_out' => null,
            'total_hours' => null,
            'check_in_location' => null,
        ];

        if ($request->filled('latitude') && $request->filled('longitude')) {
            try {
                $attendanceData['check_in_location'] = new Point($validated['latitude'], $validated['longitude']);
            } catch (Exception $e) {
                Log::error("Invalid location data during check-in", [
                    'rider_id' => $rider->id,
                    'data' => $validated,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $attendance = Attendance::create($attendanceData);
            $rider->status = 'available';
            $rider->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Check-in successful',
                'check_in_time' => $attendance->check_in->format('h:i A'),
            ]);
        } catch (Exception $e) {
            DB::rollback();
            Log::error("Check-in database operation failed", [
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred during check-in. Please try again.'], 500);
        }
    }

    /**
     * Process Rider Check-out via AJAX.
     */
    public function checkOut(Request $request): JsonResponse
    {
        $user = Auth::user();
        $rider = $user->rider;

        if (!$rider) {
            return response()->json(['success' => false, 'message' => 'Rider profile not found.'], 403);
        }

        $validated = $request->validate([
            'qr_code_data' => ['required', 'string'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
        ]);

        $expectedQrCode = SystemSetting::getValue('current_attendance_qr_code');
        if (!$expectedQrCode || $validated['qr_code_data'] != $expectedQrCode) {
            Log::warning("Rider Check-out QR mismatch", [
                'rider_id' => $rider->id,
                'expected' => $expectedQrCode,
                'received' => $validated['qr_code_data'],
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid or expired QR code.'], 400);
        }

        $attendance = Attendance::where('rider_id', $rider->id)
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'You are not currently checked in.'], 400);
        }

        $checkOutTime = now();
        $checkInTime = Carbon::parse($attendance->check_in);
        $totalHours = round($checkInTime->diffInMinutes($checkOutTime) / 60, 2);

        $updateData = [
            'check_out' => $checkOutTime,
            'total_hours' => $totalHours,
            'check_out_location' => null,
        ];

        if ($request->filled('latitude') && $request->filled('longitude')) {
            try {
                $updateData['check_out_location'] = new Point($validated['latitude'], $validated['longitude']);
            } catch (Exception $e) {
                Log::error("Invalid location data during check-out", [
                    'rider_id' => $rider->id,
                    'data' => $validated,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $attendance->update($updateData);
            $rider->status = 'offline';
            $rider->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Check-out successful',
                'check_out_time' => $checkOutTime->format('h:i A'),
                'total_hours' => $totalHours,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            Log::error("Check-out database operation failed", [
                'attendance_id' => $attendance->id,
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred during check-out. Please try again.'], 500);
        }
    }

    /**
     * Start a shift for the rider.
     */
    public function startShift(Request $request): JsonResponse
    {
        $rider = Auth::user()->rider;
        if (!$rider) {
            return response()->json(['success' => false, 'message' => 'Rider profile not found.'], 403);
        }

        $validated = $request->validate([
            'shift_type' => ['required', 'in:morning,evening,night'],
        ]);

        $existingShift = Attendance::where('rider_id', $rider->id)
            ->whereNull('check_out')
            ->exists();

        if ($existingShift) {
            return response()->json(['success' => false, 'message' => 'You are already in an active shift.'], 400);
        }

        try {
            $attendance = Attendance::create([
                'rider_id' => $rider->id,
                'check_in' => now(),
                'shift_type' => $validated['shift_type'],
                'check_in_location' => null,
            ]);
            $rider->update(['status' => 'available']);
            return response()->json([
                'success' => true,
                'message' => 'Shift started successfully.',
                'shift_type' => $validated['shift_type'],
            ]);
        } catch (Exception $e) {
            Log::error("Shift start failed", [
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to start shift.'], 500);
        }
    }

    /**
     * Start a break during an active shift.
     */
    public function startBreak(Request $request, Attendance $attendance): JsonResponse
    {
        $rider = Auth::user()->rider;
        if ($attendance->rider_id !== $rider->id || !is_null($attendance->check_out)) {
            return response()->json(['success' => false, 'message' => 'Invalid attendance record.'], 403);
        }

        if ($attendance->break_start) {
            return response()->json(['success' => false, 'message' => 'You are already on a break.'], 400);
        }

        try {
            $attendance->update(['break_start' => now()]);
            $rider->update(['status' => 'on_break']);
            return response()->json(['success' => true, 'message' => 'Break started successfully.']);
        } catch (Exception $e) {
            Log::error("Break start failed", [
                'attendance_id' => $attendance->id,
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to start break.'], 500);
        }
    }

    /**
     * End a break during an active shift.
     */
    public function endBreak(Request $request, Attendance $attendance): JsonResponse
    {
        $rider = Auth::user()->rider;
        if ($attendance->rider_id !== $rider->id || !is_null($attendance->check_out)) {
            return response()->json(['success' => false, 'message' => 'Invalid attendance record.'], 403);
        }

        if (!$attendance->break_start || $attendance->break_end) {
            return response()->json(['success' => false, 'message' => 'No active break found.'], 400);
        }

        try {
            $breakDuration = $attendance->break_start->diffInMinutes(now());
            $attendance->update([
                'break_end' => now(),
                'break_duration' => ($attendance->break_duration ?? 0) + $breakDuration,
            ]);
            $rider->update(['status' => 'available']);
            return response()->json(['success' => true, 'message' => 'Break ended successfully.']);
        } catch (Exception $e) {
            Log::error("Break end failed", [
                'attendance_id' => $attendance->id,
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to end break.'], 500);
        }
    }
}