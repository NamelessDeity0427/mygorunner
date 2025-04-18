<?php
// routes/api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RiderStatusController; // <-- Add

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- Admin API Routes ---
// Protect this route: only authenticated admins/staff should access it.
// Apply appropriate middleware here. If using web session auth for admin,
// this might need to be in web.php instead or use a different auth guard.
// Assuming web session or a dedicated admin API guard:
Route::middleware(['auth', 'admin.staff'])->prefix('admin')->group(function () { // Example using web middleware
     Route::get('/active-riders', [RiderStatusController::class, 'getActiveRiders'])->name('api.admin.active_riders');
});

// --- Rider API Routes ---
Route::middleware('auth:sanctum')->prefix('rider')->group(function () { // Example using Sanctum for mobile/JS clients
    Route::post('/update-location', [RiderStatusController::class, 'updateLocation'])->name('api.rider.update_location');
    // Add other rider API endpoints
});