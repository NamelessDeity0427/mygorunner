<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait
// Spatial integration
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @property Point $location // Spatial point
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class LocationLog extends Model
{
    use HasUuids, HasFactory, HasSpatial; // Added HasUuids and HasSpatial

    protected $table = 'location_logs'; // Explicit table name

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
     * Indicates if the model should be timestamped. Only 'created_at'.
     *
     * @var bool
     */
    public $timestamps = true; // Manages created_at

    const UPDATED_AT = null; // Disable updated_at

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // User (Rider/Customer) whose location it is
        'booking_id', // Optional associated booking
        'location', // Spatial field
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'location' => Point::class, // Spatial cast
        'created_at' => 'datetime',
    ];

    // --- Relationships ---

    /**
     * Get the user whose location was logged.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the booking associated with the location log (if any).
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }
}