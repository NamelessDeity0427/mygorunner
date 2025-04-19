<?php

// routes/web.php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// --- Controllers ---
// Core & Profile
use App\Http\Controllers\ProfileController;

// Dashboards
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\CustomerDashboardController;
use App\Http\Controllers\Dashboard\RiderDashboardController;

// Customer Features
use App\Http\Controllers\BookingController;

// Rider Features (Add these later)
// use App\Http\Controllers\Rider\TaskController;
use App\Http\Controllers\Rider\AttendanceController;
// use App\Http\Controllers\Rider\EarningsController;
// use App\Http\Controllers\Rider\RedemptionController as RiderRedemptionController;

// Admin Features
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\RiderManagementController;
// use App\Http\Controllers\Admin\DispatchController;
// use App\Http\Controllers\Admin\ReportingController;
// use App\Http\Controllers\Admin\RedemptionController as AdminRedemptionController;
// use App\Http\Controllers\Admin\AttendanceManagementController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// --- Default Welcome Route (Optional) ---
// Consider redirecting if logged in, handled by the '/' route below
// Route::get('/welcome', function () {
//     return view('welcome');
// })->name('welcome');


// --- Customer Routes ---
Route::middleware(['auth', /* 'verified' - add email verification later if needed */])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {

    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    // Booking Routes
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    // Example: Cancellation route (implement logic in BookingController)
    // Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // --- Add other customer routes here (e.g., browsing services) ---
    // Route::get('/services', [CustomerServiceController::class, 'index'])->name('services.index');

});

// --- Rider Routes ---
Route::middleware(['auth', /* 'verified' */])
    ->prefix('rider')
    ->name('rider.')
    ->group(function () {

    Route::get('/dashboard', [RiderDashboardController::class, 'index'])->name('dashboard');

    // --- Add other rider-specific routes here ---
    // Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    // Route::get('/tasks/{booking}', [TaskController::class, 'show'])->name('tasks.show');
    // Route::patch('/tasks/{booking}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index'); // View QR or status
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    // Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');

    // Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings.index');
    // Route::resource('/redemptions', RiderRedemptionController::class)->only(['index', 'create', 'store']); // Rider requests redemption

});

// --- Admin/Staff Routes ---
Route::middleware(['auth', 'admin.staff']) // Apply Auth and Admin/Staff check
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Service Management
    Route::resource('services', ServiceController::class);

    // Rider Management
    Route::resource('riders', RiderManagementController::class)->except(['show']);

    // --- Add other admin/staff routes here ---
    // Route::get('/dispatch', [DispatchController::class, 'index'])->name('dispatch.index');
    // Route::post('/dispatch/assign/{booking}', [DispatchController::class, 'assignRider'])->name('dispatch.assign');
    // Route::post('/dispatch/batch-assign', [DispatchController::class, 'batchAssign'])->name('dispatch.batchAssign');

    // Route::get('/reports', [ReportingController::class, 'index'])->name('reports.index');
    // Route::get('/reports/attendance', [ReportingController::class, 'attendance'])->name('reports.attendance');
    // Route::get('/reports/financial', [ReportingController::class, 'financial'])->name('reports.financial');

    // Route::get('/redemption-requests', [AdminRedemptionController::class, 'index'])->name('redemptions.index');
    // Route::patch('/redemption-requests/{redemptionRequest}/approve', [AdminRedemptionController::class, 'approve'])->name('redemptions.approve');
    // Route::patch('/redemption-requests/{redemptionRequest}/reject', [AdminRedemptionController::class, 'reject'])->name('redemptions.reject');
    // Route::patch('/redemption-requests/{redemptionRequest}/pay', [AdminRedemptionController::class, 'markAsPaid'])->name('redemptions.pay');

    // Route::get('/attendance-logs', [AttendanceManagementController::class, 'index'])->name('attendance.logs.index');
    // Route::get('/attendance-logs/{attendance}', [AttendanceManagementController::class, 'show'])->name('attendance.logs.show'); // Show photo verification maybe
    // Route::patch('/attendance-photo/{attendancePhoto}/verify', [AttendanceManagementController::class, 'verifyPhoto'])->name('attendance.photo.verify');

});

// --- Breeze Profile Routes (Common for all logged-in users) ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- Breeze Authentication Routes ---
// Includes login, registration, password reset, email verification routes
require __DIR__.'/auth.php';

// --- Fallback Root Route ---
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        // Redirect logged-in users to their respective dashboards
        if ($user->isAdmin() || $user->isStaff()) { // Use helper methods
            return redirect()->route('admin.dashboard');
        } elseif ($user->isRider()) {
            return redirect()->route('rider.dashboard');
        } else { // Default to customer
            return redirect()->route('customer.dashboard');
        }
    }
    // If not logged in, show the welcome/landing page or redirect to login
     return view('welcome');
    // return redirect()->route('login'); // Redirect guests directly to login
});

// --- Optional: Catch-all for undefined routes ---
// Route::fallback(function() {
//     // return redirect('/')->with('error', 'Page not found.'); // Or show a 404 view
// });