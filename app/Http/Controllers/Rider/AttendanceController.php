<?php
// app/Http/Controllers/Rider/AttendanceController.php
namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendancePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse; // For AJAX responses
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display the attendance check-in/out page.
     */
    public function index(): View
    {
        $rider = Auth::user()->rider;
        // Fetch current attendance status (e.g., last check-in without check-out)
        $currentAttendance = Attendance::where('rider_id', $rider->id)
                                       ->whereNull('check_out')
                                       ->latest('check_in')
                                       ->first();

        $isCheckedIn = !is_null($currentAttendance);

        // TODO: Need a way to get the Admin's current QR Code value
        // This might come from System Settings, a specific table, or be generated dynamically.
        $adminQrCodeValue = "PLACEHOLDER_QR_CODE_" . now()->format('Ymd'); // Replace with actual logic

        return view('rider.attendance.index', compact('isCheckedIn', 'adminQrCodeValue', 'currentAttendance'));
    }

    /**
     * Process Rider Check-in.
     * Expects AJAX request with QR data and photo file.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code_data' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB Max
        ]);

        $rider = Auth::user()->rider;

        // --- 1. Validate QR Code ---
        // TODO: Implement robust QR code validation logic.
        // Compare $request->qr_code_data against the expected value (e.g., from settings).
        // It should likely be time-sensitive or unique per day/session.
        $expectedQrCode = "PLACEHOLDER_QR_CODE_" . now()->format('Ymd'); // Replace with actual logic
        if ($request->qr_code_data !== $expectedQrCode) {
             Log::warning("Rider Check-in QR mismatch for Rider ID: {$rider->id}. Expected: {$expectedQrCode}, Got: {$request->qr_code_data}");
            return response()->json(['success' => false, 'message' => 'Invalid or expired QR Code.'], 400);
        }

        // --- 2. Check if already checked in ---
        $existingCheckin = Attendance::where('rider_id', $rider->id)
                                      ->whereNull('check_out')
                                      ->exists();
        if ($existingCheckin) {
            return response()->json(['success' => false, 'message' => 'You are already checked in.'], 400);
        }

        // --- 3. Store Photo ---
        try {
            $photoPath = $request->file('photo')->store('attendance_photos', 'public');
        } catch (\Exception $e) {
            Log::error("Attendance photo upload failed for Rider ID: {$rider->id}. Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to upload photo.'], 500);
        }

        // --- 4. Create Attendance Record ---
        try {
             DB::beginTransaction();

             $attendance = Attendance::create([
                'rider_id' => $rider->id,
                'check_in' => now(),
                'check_out' => null,
                'total_hours' => null,
            ]);

            // --- 5. Link Photo to Attendance ---
            AttendancePhoto::create([
                'attendance_id' => $attendance->id,
                'photo_path' => $photoPath,
                 // Verification fields are nullable by default
            ]);

            // --- 6. Update Rider Status (Optional) ---
             // $rider->update(['status' => 'available']); // Or 'offline' depending on rules

             DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful!',
                'check_in_time' => $attendance->check_in->format('h:i A'),
                'photo_url' => Storage::url($photoPath) // Send back URL for confirmation
            ]);

        } catch (\Exception $e) {
             DB::rollBack();
             // Clean up uploaded file if DB transaction failed
             if (isset($photoPath)) {
                 Storage::disk('public')->delete($photoPath);
             }
            Log::error("Check-in database operation failed for Rider ID: {$rider->id}. Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred during check-in.'], 500);
        }
    }

    // TODO: Implement checkOut method similar to checkIn
    // public function checkOut(Request $request): JsonResponse { ... }
}