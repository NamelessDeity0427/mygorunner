<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Added for spatial integration
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @property Point $default_location // Added property hint
 */
class Customer extends Model
{
    use HasFactory;
    use HasSpatial; // Added trait

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', // [cite: 86]
        'address', // [cite: 86]
        // 'default_lat', // [cite: 86] Removed
        // 'default_lng', // [cite: 86] Removed
        'default_location', // Added spatial field
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'default_lat' => 'decimal:8', // [cite: 88] Removed
        // 'default_lng' => 'decimal:8', // [cite: 88] Removed
        'default_location' => Point::class, // Added spatial cast
    ];

    /**
     * Get the user that owns the customer profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class); // [cite: 89]
    }

    /**
     * Get the bookings for the customer.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class); // [cite: 90]
    }

    /**
     * Get the feedback provided by the customer.
     */
    public function feedback()
    {
        // Ensure CustomerFeedback model exists or correct the class name
        return $this->hasMany(CustomerFeedback::class); // [cite: 91]
    }
}