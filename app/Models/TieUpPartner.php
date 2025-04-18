<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Added for spatial integration
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @property Point $location // Added property hint
 * @property bool $is_active // Added hint
 */
class TieUpPartner extends Model
{
    use HasFactory;
    use HasSpatial; // Added trait

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', // [cite: 109]
        'address', // [cite: 109]
        'contact_person', // [cite: 109]
        'phone', // [cite: 109]
        'email', // [cite: 109]
        // 'lat', // [cite: 109] Removed
        // 'lng', // [cite: 109] Removed
        'location', // Added spatial field
        'is_active', // [cite: 109]
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'lat' => 'decimal:8', // [cite: 110] Removed
        // 'lng' => 'decimal:8', // [cite: 110] Removed
        'location' => Point::class, // Added spatial cast
        'is_active' => 'boolean', // [cite: 110]
    ];

    /**
     * Get the bookings associated with the tie-up partner.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class); // [cite: 111]
    }
}