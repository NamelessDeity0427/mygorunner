<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Rider; // Added Rider model
use App\Models\SystemSetting; // Added for QR Code retrieval
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Added for transactions
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use MatanYadaev\EloquentSpatial\Objects\Point; // Added for location
use Carbon\Carbon; // Added for time calculations
use Exception; // Added for exception handling

class AttendanceController extends Controller
{
    // Apply 'auth' middleware for riders via routes

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

        // Fetch current open attendance record (checked in, not checked out)
        $currentAttendance = Attendance::where('rider_id', $rider->id)
            ->whereNull('check_out')
            ->latest('check_in') // Get the most recent check-in
            ->first();

        $isCheckedIn = !is_null($currentAttendance);

        // Fetch today's attendance records for display history (optional)
        $todayAttendance = Attendance::where('rider_id', $rider->id)
            ->whereDate('check_in', today())
            ->orderBy('check_in', 'desc')
            ->get();

        // Get the current valid QR Code value from a secure source (e.g., SystemSetting)
        // This value should ideally be generated/updated by an admin process.
        // Example: A setting 'current_attendance_qr_code' holds the expected value.
        $adminQrCodeValue = SystemSetting::getValue('current_attendance_qr_code', null); // Default to null if not set

        if (!$adminQrCodeValue) {
            Log::error("Attendance QR Code Setting is missing in system_settings table.");
            // Handle missing QR code setting - maybe disable check-in/out?
            session()->flash('error', 'Attendance system is currently unavailable. Please contact support.'); // Flash message
        }

        return view('rider.attendance.index', compact(
            'isCheckedIn',
            'adminQrCodeValue', // Pass expected value to view for comparison/display if needed
            'currentAttendance',
            'todayAttendance'
        ));
    }

    /**
     * Process Rider Check-in via AJAX.
     * Expects QR data and potentially location.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $user = Auth::user();
        $rider = $user->rider;

        if (!$rider) {
            return response()->json(['success' => false, 'message' => 'Rider profile not found.'], 403);
        }

        // Validate request data
        $validated = $request->validate([
            'qr_code_data' => 'required|string',
            // Optional: Get location from the request (e.g., via browser geolocation)
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
             // REMOVED: 'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // --- 1. Validate QR Code ---
        $expectedQrCode = SystemSetting::getValue('current_attendance_qr_code');
        if (!$expectedQrCode || $validated['qr_code_data'] !== $expectedQrCode) {
            Log::warning("Rider Check-in QR mismatch", [
                'rider_id' => $rider->id,
                'expected' => $expectedQrCode,
                'received' => $validated['qr_code_data']
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid or expired QR Code.'], 400);
        }

        // --- 2. Check if already checked in ---
        $existingCheckin = Attendance::where('rider_id', $rider->id)
            ->whereNull('check_out')
            ->exists();
        if ($existingCheckin) {
            return response()->json(['success' => false, 'message' => 'You are already checked in.'], 400);
        }

        // --- 3. Prepare Attendance Data ---
        $attendanceData = [
            'rider_id' => $rider->id,
            'check_in' => now(),
            'check_out' => null,
            'total_hours' => null,
            'check_in_location' => null, // Default to null
        ];

        // Add location if provided and valid
        if ($request->filled('latitude') && $request->filled('longitude')) {
             try {
                $attendanceData['check_in_location'] = new Point($validated['latitude'], $validated['longitude']);
             } catch(Exception $e) {
                 Log::error("Invalid location data during check-in", ['rider_id' => $rider->id, 'data' => $validated, 'error' => $e->getMessage()]);
                 // Decide whether to proceed without location or fail
                 // return response()->json(['success' => false, 'message' => 'Invalid location data provided.'], 400);
             }
        }

        // --- 4. Create Attendance Record (Transaction) ---
        DB::beginTransaction();
        try {
            $attendance = Attendance::create($attendanceData);

            // REMOVED: AttendancePhoto creation logic

            // --- 5. Update Rider Status (Optional but recommended) ---
            // Set status to 'available' or 'offline' based on business rules after check-in
            $rider->status = 'available'; // Example: Rider becomes available after check-in
            $rider->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful!',
                'check_in_time' => $attendance->check_in->format('h:i A'), // Format time for display
                // REMOVED: 'photo_url'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Check-in database operation failed", [
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred during check-in. Please try again.'], 500);
        }
    }

    /**
     * Process Rider Check-out via AJAX.
     * Expects QR data and potentially location.
     */
    public function checkOut(Request $request): JsonResponse
    {
         $user = Auth::user();
         $rider = $user->rider;

        if (!$rider) {
            return response()->json(['success' => false, 'message' => 'Rider profile not found.'], 403);
        }

        // Validate request data
        $validated = $request->validate([
            'qr_code_data' => 'required|string',
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
             // REMOVED: 'photo' validation
        ]);

        // --- 1. Validate QR Code ---
        // Use the SAME QR code for check-out or a different one based on requirements
        $expectedQrCode = SystemSetting::getValue('current_attendance_qr_code'); // Or 'current_checkout_qr_code'
        if (!$expectedQrCode || $validated['qr_code_data'] !== $expectedQrCode) {
            Log::warning("Rider Check-out QR mismatch", [
                'rider_id' => $rider->id,
                'expected' => $expectedQrCode,
                'received' => $validated['qr_code_data']
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid or expired QR Code.'], 400);
        }

        // --- 2. Find the Open Attendance Record ---
        $attendance = Attendance::where('rider_id', $rider->id)
            ->whereNull('check_out')
            ->latest('check_in') // Ensure we get the most recent open record
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'You are not currently checked in.'], 400);
        }

        // --- 3. Prepare Update Data ---
        $checkOutTime = now();
        $checkInTime = Carbon::parse($attendance->check_in); // Parse check-in time
        // Calculate total hours (in decimal format, e.g., 8.5 hours)
        $totalHours = round($checkInTime->diffInMinutes($checkOutTime) / 60, 2);

        $updateData = [
            'check_out' => $checkOutTime,
            'total_hours' => $totalHours,
            'check_out_location' => null, // Default to null
        ];

        // Add location if provided
        if ($request->filled('latitude') && $request->filled('longitude')) {
             try {
                 $updateData['check_out_location'] = new Point($validated['latitude'], $validated['longitude']);
             } catch (Exception $e) {
                  Log::error("Invalid location data during check-out", ['rider_id' => $rider->id, 'data' => $validated, 'error' => $e->getMessage()]);
                 // Decide: proceed without location or fail?
             }
        }

        // --- 4. Update Attendance Record (Transaction) ---
        DB::beginTransaction();
        try {
            $attendance->update($updateData);

            // REMOVED: AttendancePhoto logic for check-out

            // --- 5. Update Rider Status ---
            // Set status to 'offline' after check-out
            $rider->status = 'offline';
            $rider->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Check-out successful!',
                'check_out_time' => $checkOutTime->format('h:i A'),
                'total_hours' => $totalHours
            ]);

        } catch (Exception $e) {
            DB::rollBack();
             Log::error("Check-out database operation failed", [
                'attendance_id' => $attendance->id,
                'rider_id' => $rider->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'An error occurred during check-out. Please try again.'], 500);
        }
    }
}