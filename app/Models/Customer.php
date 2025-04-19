<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait
// Spatial integration
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @property Point|null $default_location // Updated property hint for nullability
 */
class Customer extends Model
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
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'address',
        'default_location', // Spatial field
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'default_location' => Point::class, // Spatial cast
    ];

    // --- Relationships ---

    /**
     * Get the user that owns the customer profile.
     */
    public function user()
    {
        // Foreign key 'user_id' relates to User 'id' (UUID)
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the bookings made by the customer.
     */
    public function bookings()
    {
        // Foreign key 'customer_id' relates to Booking 'id' (UUID)
        return $this->hasMany(Booking::class, 'customer_id', 'id');
    }

    /**
     * Get the feedback provided by the customer.
     */
    public function feedback()
    {
        // Foreign key 'customer_id' relates to CustomerFeedback 'id' (UUID)
        return $this->hasMany(CustomerFeedback::class, 'customer_id', 'id');
    }
}