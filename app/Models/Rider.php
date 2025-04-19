<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait
// Spatial integration
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @property Point|null $current_location // Updated property hint for nullability
 * @property \Illuminate\Support\Carbon|null $location_updated_at
 * @property bool $is_active
 * @property string $status
 */
class Rider extends Model
{
    use HasUuids, HasFactory, HasSpatial; // Added HasUuids and HasSpatial

    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     * 'is_active' and 'status' might be better controlled explicitly.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'address',
        'vehicle_type',
        'plate_number',
        // 'is_active', // Consider managing activation status explicitly
        'current_location', // Spatial field
        'location_updated_at',
        // 'status', // Rider status should be managed via specific methods/events
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'current_location' => Point::class, // Spatial cast
        'location_updated_at' => 'datetime',
        'status' => 'string', // Cast enum
    ];

    // --- Relationships ---

    /**
     * Get the user that owns the rider profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the bookings assigned to the rider.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'rider_id', 'id');
    }

    /**
     * Get the remittances made by the rider.
     */
    public function remittances()
    {
        return $this->hasMany(Remittance::class, 'rider_id', 'id');
    }

    /**
     * REMOVED: Relationship to RiderQueue.
     * public function queueEntries() { ... }
     */

    /**
     * Get the attendance records for the rider.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(Attendance::class, 'rider_id', 'id');
    }

    /**
     * Get the feedback received by the rider.
     */
    public function feedbackReceived()
    {
        return $this->hasMany(CustomerFeedback::class, 'rider_id', 'id');
    }

    /**
     * Get the earnings records for the rider.
     */
    public function earnings()
    {
        return $this->hasMany(RiderEarning::class, 'rider_id', 'id');
    }

    /**
     * Get the redemption requests made by the rider.
     */
    public function redemptionRequests()
    {
        return $this->hasMany(RedemptionRequest::class, 'rider_id', 'id');
    }
}