<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\RiderManagementController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\CustomerDashboardController;
use App\Http\Controllers\Dashboard\RiderDashboardController;
use App\Http\Controllers\Rider\AttendanceController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SupportController;

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard Route for Role-Based Redirection
Route::middleware('auth')->get('/dashboard', function () {
    $user = Auth::user();
    if ($user->hasRole('admin') || $user->hasRole('staff')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->customer) {
        return redirect()->route('customer.dashboard');
    } elseif ($user->rider) {
        return redirect()->route('rider.dashboard');
    }
    return redirect()->route('home');
})->name('dashboard');

// Breeze Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // Admin/Staff Routes
    Route::middleware('admin.staff')->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/attendance', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::get('/admin/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics.index');
        Route::get('/admin/riders', [RiderManagementController::class, 'index'])->name('admin.riders.index');
        Route::get('/admin/riders/create', [RiderManagementController::class, 'create'])->name('admin.riders.create');
        Route::post('/admin/riders', [RiderManagementController::class, 'store'])->name('admin.riders.store');
        Route::get('/admin/riders/{rider}/edit', [RiderManagementController::class, 'edit'])->name('admin.riders.edit');
        Route::put('/admin/riders/{rider}', [RiderManagementController::class, 'update'])->name('admin.riders.update');
        Route::delete('/admin/riders/{rider}', [RiderManagementController::class, 'destroy'])->name('admin.riders.destroy');
        Route::post('/admin/riders/bulk-update', [RiderManagementController::class, 'bulkUpdate'])->name('admin.riders.bulk-update');
        Route::post('/admin/riders/{rider}/documents', [RiderManagementController::class, 'storeDocuments'])->name('admin.riders.documents.store');
        Route::get('/admin/services', [ServiceController::class, 'index'])->name('admin.services.index');
        Route::get('/admin/services/create', [ServiceController::class, 'create'])->name('admin.services.create');
        Route::post('/admin/services', [ServiceController::class, 'store'])->name('admin.services.store');
        Route::get('/admin/services/{service}', [ServiceController::class, 'show'])->name('admin.services.show');
        Route::get('/admin/services/{service}/edit', [ServiceController::class, 'edit'])->name('admin.services.edit');
        Route::put('/admin/services/{service}', [ServiceController::class, 'update'])->name('admin.services.update');
        Route::delete('/admin/services/{service}', [ServiceController::class, 'destroy'])->name('admin.services.destroy');
        Route::post('/admin/notifications', [NotificationController::class, 'send'])->name('admin.notifications.send');
        Route::get('/admin/support', [SupportController::class, 'index'])->name('admin.support.index');
    });

    // Customer Routes
    Route::middleware('customer')->group(function () {
        Route::get('/customer/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');
        Route::post('/customer/profile', [CustomerDashboardController::class, 'updateProfile'])->name('customer.profile.update');
        Route::post('/customer/addresses', [CustomerDashboardController::class, 'storeAddress'])->name('customer.addresses.store');
        Route::get('/customer/bookings', [BookingController::class, 'index'])->name('customer.bookings.index');
        Route::get('/customer/bookings/create', [BookingController::class, 'create'])->name('customer.bookings.create');
        Route::post('/customer/bookings', [BookingController::class, 'store'])->name('customer.bookings.store');
        Route::post('/customer/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('customer.bookings.cancel');
        Route::post('/customer/bookings/{booking}/payment', [PaymentController::class, 'process'])->name('customer.bookings.payment');
    });

    // Rider Routes
    Route::middleware('rider')->group(function () {
        Route::get('/rider/dashboard', [RiderDashboardController::class, 'index'])->name('rider.dashboard');
        Route::post('/rider/bookings/{booking}/accept', [RiderDashboardController::class, 'acceptBooking'])->name('rider.bookings.accept');
        Route::get('/rider/earnings', [RiderDashboardController::class, 'earnings'])->name('rider.earnings');
        Route::get('/rider/attendance', [AttendanceController::class, 'index'])->name('rider.attendance.index');
        Route::post('/rider/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('rider.attendance.check-in');
        Route::post('/rider/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('rider.attendance.check-out');
        Route::post('/rider/attendance/start-shift', [AttendanceController::class, 'startShift'])->name('rider.attendance.start-shift');
        Route::post('/rider/attendance/{attendance}/start-break', [AttendanceController::class, 'startBreak'])->name('rider.attendance.start-break');
        Route::post('/rider/attendance/{attendance}/end-break', [AttendanceController::class, 'endBreak'])->name('rider.attendance.end-break');
    });

    // Support Routes (accessible to authenticated users)
    Route::get('/support/create', [SupportController::class, 'create'])->name('support.create');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store');

    // Booking Status Update (shared by customers and riders)
    Route::post('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status.update');
});