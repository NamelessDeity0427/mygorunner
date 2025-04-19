<?php
namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Rider;
use App\Rules\ValidPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:rider']);
    }

    public function index(): View
    {
        $rider = Auth::user()->rider;

        $currentAttendance = Attendance::where('rider_id', $rider->id)
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        $todayAttendance = Attendance::where('rider_id', $rider->id)
            ->whereDate('check_in', today())
            ->orderBy('check_in', 'desc')
            ->get();

        $qrCodeValue = config('system_settings.current_attendance_qr_code');

        if (!$qrCodeValue) {
            Log::error('Attendance QR code missing in system settings.');
            session()->flash('error', 'Attendance system unavailable. Contact support.');
        }

        return view('rider.attendance.index', [
            'isCheckedIn' => !is_null($currentAttendance),
            'adminQrCodeValue' => $qrCodeValue,
            'currentAttendance' => $currentAttendance,
            'todayAttendance' => $todayAttendance,
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $rider = Auth::user()->rider;

        $validated = $request->validate([
            'qr_code_data' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'point' => [new ValidPoint('latitude', 'longitude')],
        ]);

        $expectedQrCode = config('system_settings.current_attendance_qr_code');
        if (!$expectedQrCode || $validated['qr_code_data'] !== $expectedQrCode) {
            Log::warning('Rider check-in QR mismatch', [
                'rider_id' => $rider->id,
                'expected' => $expectedQrCode,
                'received' => $validated['qr_code_data'],
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid or expired QR code.'], 400);
        }

        if (Attendance::where('rider_id', $rider->id)->whereNull('check_out')->exists()) {
            return response()->json(['success' => false, 'message' => 'You are already checked in.'], 400);
        }

        try {
            return DB::transaction(function () use ($validated, $rider) {
                $attendance = Attendance::create([
                    'rider_id' => $rider->id,
                    'check_in' => now(),
                    'check_in_location' => new Point($validated['latitude'], $validated['longitude']),
                    'qr_code_hash' => $validated['qr_code_data'],
                ]);

                $rider->update(['status' => 'available']);

                event(new \App\Events\RiderStatusUpdated($rider));
                event(new \App\Events\RiderLocationUpdated($rider));

                return response()->json([
                    'success' => true,
                    'message' => 'Check-in successful.',
                    'check_in_time' => $attendance->check_in->format('h:i A'),
                    'attendance_id' => $attendance->id,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Rider check-in failed', [
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to check in: ' . $e->getMessage()], 500);
        }
    }

    public function checkOut(Request $request): JsonResponse
    {
        $rider = Auth::user()->rider;

        $validated = $request->validate([
            'qr_code_data' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'point' => [new ValidPoint('latitude', 'longitude')],
        ]);

        $expectedQrCode = config('system_settings.current_attendance_qr_code');
        if (!$expectedQrCode || $validated['qr_code_data'] !== $expectedQrCode) {
            Log::warning('Rider check-out QR mismatch', [
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
            return response()->json(['success' => false, 'message' => 'No active check-in found.'], 400);
        }

        try {
            return DB::transaction(function () use ($validated, $attendance, $rider) {
                $checkOutTime = now();
                $totalHours = Carbon::parse($attendance->check_in)->diffInHours($checkOutTime, false);

                $attendance->update([
                    'check_out' => $checkOutTime,
                    'check_out_location' => new Point($validated['latitude'], $validated['longitude']),
                    'total_hours' => $totalHours,
                ]);

                $rider->update(['status' => 'offline']);

                event(new \App\Events\RiderStatusUpdated($rider));
                event(new \App\Events\RiderLocationUpdated($rider));

                return response()->json([
                    'success' => true,
                    'message' => 'Check-out successful.',
                    'check_out_time' => $checkOutTime->format('h:i A'),
                    'total_hours' => $totalHours,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Rider check-out failed', [
                'rider_id' => $rider->id,
                'attendance_id' => $attendance->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to check out: ' . $e->getMessage()], 500);
        }
    }

    public function startShift(Request $request): JsonResponse
    {
        $rider = Auth::user()->rider;

        $validated = $request->validate([
            'shift_type' => ['required', 'in:morning,evening,night'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'point' => [new ValidPoint('latitude', 'longitude')],
        ]);

        if (Attendance::where('rider_id', $rider->id)->whereNull('check_out')->exists()) {
            return response()->json(['success' => false, 'message' => 'You are already in an active shift.'], 400);
        }

        try {
            return DB::transaction(function () use ($validated, $rider) {
                $attendance = Attendance::create([
                    'rider_id' => $rider->id,
                    'check_in' => now(),
                    'shift_type' => $validated['shift_type'],
                    'check_in_location' => new Point($validated['latitude'], $validated['longitude']),
                ]);

                $rider->update(['status' => 'available']);

                event(new \App\Events\RiderStatusUpdated($rider));
                event(new \App\Events\RiderLocationUpdated($rider));

                return response()->json([
                    'success' => true,
                    'message' => 'Shift started successfully.',
                    'shift_type' => $validated['shift_type'],
                    'attendance_id' => $attendance->id,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Shift start failed', [
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to start shift: ' . $e->getMessage()], 500);
        }
    }

    public function startBreak(Request $request, Attendance $attendance): JsonResponse
    {
        $rider = Auth::user()->rider;

        if ($attendance->rider_id !== $rider->id || !is_null($attendance->check_out)) {
            return response()->json(['success' => false, 'message' => 'Invalid attendance record.'], 403);
        }

        if ($attendance->break_start && !$attendance->break_end) {
            return response()->json(['success' => false, 'message' => 'You are already on a break.'], 400);
        }

        try {
            return DB::transaction(function () use ($attendance, $rider) {
                $attendance->update(['break_start' => now()]);
                $rider->update(['status' => 'on_break']);

                event(new \App\Events\RiderStatusUpdated($rider));

                return response()->json(['success' => true, 'message' => 'Break started successfully.']);
            });
        } catch (\Exception $e) {
            Log::error('Break start failed', [
                'rider_id' => $rider->id,
                'attendance_id' => $attendance->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to start break: ' . $e->getMessage()], 500);
        }
    }

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
            return DB::transaction(function () use ($attendance, $rider) {
                $breakDuration = $attendance->break_start->diffInMinutes(now());
                $attendance->update([
                    'break_end' => now(),
                    'break_duration' => ($attendance->break_duration ?? 0) + $breakDuration,
                ]);

                $rider->update(['status' => 'available']);

                event(new \App\Events\RiderStatusUpdated($rider));

                return response()->json(['success' => true, 'message' => 'Break ended successfully.']);
            });
        } catch (\Exception $e) {
            Log::error('Break end failed', [
                'rider_id' => $rider->id,
                'attendance_id' => $attendance->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to end break: ' . $e->getMessage()], 500);
        }
    }
}