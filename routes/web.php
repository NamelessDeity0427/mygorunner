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
Route::get('/', fn () => view('welcome'))->name('home');

// Authentication Routes (Breeze)
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

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Dashboard Redirection
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return match ($user->user_type) {
            'admin', 'staff' => redirect()->route('admin.dashboard'),
            'customer' => redirect()->route('customer.dashboard'),
            'rider' => redirect()->route('rider.dashboard'),
            default => redirect()->route('home'),
        };
    })->name('dashboard');

    // Authentication Management
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
    Route::middleware('role:admin,staff')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

        Route::resource('riders', RiderManagementController::class)->except(['show']);
        Route::post('/riders/bulk-update', [RiderManagementController::class, 'bulkUpdate'])->name('riders.bulk-update');
        Route::post('/riders/{rider}/documents', [RiderManagementController::class, 'storeDocuments'])->name('riders.documents.store');

        Route::resource('services', ServiceController::class);
        Route::post('/notifications', [NotificationController::class, 'send'])->name('notifications.send');

        Route::get('/support', [SupportController::class, 'index'])->name('support.index');
        Route::get('/support/{supportTicket}', [SupportController::class, 'show'])
            ->middleware('can:view,supportTicket')
            ->name('support.show');
        Route::put('/support/{supportTicket}', [SupportController::class, 'update'])
            ->middleware('can:update,supportTicket')
            ->name('support.update');
    });

    // Customer Routes
    Route::middleware('role:customer')->prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
        Route::put('/profile', [CustomerDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/addresses', [CustomerDashboardController::class, 'storeAddress'])->name('addresses.store');

        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('/bookings/{booking}/payment', [PaymentController::class, 'process'])->name('bookings.payment');
    });

    // Rider Routes
    Route::middleware('role:rider')->prefix('rider')->name('rider.')->group(function () {
        Route::get('/dashboard', [RiderDashboardController::class, 'index'])->name('dashboard');
        Route::post('/bookings/{booking}/accept', [RiderDashboardController::class, 'acceptBooking'])->name('bookings.accept');
        Route::get('/earnings', [RiderDashboardController::class, 'earnings'])->name('earnings');

        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
        Route::post('/attendance/start-shift', [AttendanceController::class, 'startShift'])->name('attendance.start-shift');
        Route::post('/attendance/{attendance}/start-break', [AttendanceController::class, 'startBreak'])->name('attendance.start-break');
        Route::post('/attendance/{attendance}/end-break', [AttendanceController::class, 'endBreak'])->name('attendance.end-break');
    });

    // Shared Routes
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/create', [SupportController::class, 'create'])
            ->middleware('role:customer')
            ->name('create');
        Route::post('/', [SupportController::class, 'store'])
            ->middleware('role:customer')
            ->name('store');
        Route::get('/{supportTicket}', [SupportController::class, 'show'])
            ->middleware('can:view,supportTicket')
            ->name('show');
        Route::put('/{supportTicket}', [SupportController::class, 'update'])
            ->middleware('can:update,supportTicket')
            ->name('update');
    });

    Route::post('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])
        ->middleware('can:update,booking')
        ->name('bookings.status.update');
});