<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Added for spatial integration
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @property Point $current_location // Added property hint
 * @property \Illuminate\Support\Carbon|null $location_updated_at // Added hint
 * @property bool $is_active // Added hint
 * @property string $status // Added hint
 */
class Rider extends Model
{
    use HasFactory;
    use HasSpatial; // Added trait

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // [cite: 92]
        'address', // [cite: 92]
        'vehicle_type', // [cite: 92]
        'plate_number', // [cite: 92]
        'is_active', // [cite: 93]
        // 'current_lat', // [cite: 93] Removed
        // 'current_lng', // [cite: 93] Removed
        'current_location', // Added spatial field
        'location_updated_at', // [cite: 93]
        'status', // [cite: 93]
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean', // [cite: 93]
        // 'current_lat' => 'decimal:8', // [cite: 93] Removed
        // 'current_lng' => 'decimal:8', // [cite: 93] Removed
        'current_location' => Point::class, // Added spatial cast
        'location_updated_at' => 'datetime', // [cite: 93]
        'status' => 'string', // [cite: 93] Cast enum to string (or use PHP 8.1 Enum)
    ];

    /**
     * Get the user that owns the rider profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class); // [cite: 94]
    }

    /**
     * Get the bookings assigned to the rider.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class); // [cite: 95]
    }

    /**
     * Get the remittances made by the rider.
     */
    public function remittances()
    {
        return $this->hasMany(Remittance::class); // [cite: 96]
    }

    /**
     * Get the queue entries for the rider.
     */
    public function queueEntries()
    {
        // Ensure RiderQueue model exists or correct the class name
        return $this->hasMany(RiderQueue::class); // [cite: 97]
    }

    /**
     * Get the attendance records for the rider.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(Attendance::class); // [cite: 99]
    }

    /**
     * Get the feedback received by the rider.
     */
    public function feedbackReceived()
    {
        // Ensure CustomerFeedback model exists or correct the class name
        return $this->hasMany(CustomerFeedback::class); // [cite: 99]
    }

    // Inside Rider.php
    public function earnings()
    {
        return $this->hasMany(RiderEarning::class);
    }

    public function redemptionRequests()
    {
        return $this->hasMany(RedemptionRequest::class);
    }
}