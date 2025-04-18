<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Added for spatial integration
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @property Point $location // Added property hint
 * @property \Illuminate\Support\Carbon|null $created_at // Added hint
 */
class LocationLog extends Model
{
    use HasFactory;
    use HasSpatial; // Added trait

    // Disable updated_at timestamp as it's not in the migration [cite: 158]
    public $timestamps = ["created_at"]; // [cite: 159] Only enable created_at
    const UPDATED_AT = null; // [cite: 159] Explicitly disable updated_at

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // [cite: 160]
        'booking_id', // [cite: 160]
        // 'lat', // [cite: 160] Removed
        // 'lng', // [cite: 160] Removed
        'location', // Added spatial field
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'lat' => 'decimal:8', // [cite: 162] Removed
        // 'lng' => 'decimal:8', // [cite: 162] Removed
        'location' => Point::class, // Added spatial cast
        'created_at' => 'datetime', // [cite: 162]
    ];

    /**
     * Get the user whose location was logged.
     */
    public function user()
    {
        return $this->belongsTo(User::class); // [cite: 163]
    }

    /**
     * Get the booking associated with the location log (if any).
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class); // [cite: 164]
    }
}