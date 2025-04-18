<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'image_path',
        'price', // [cite: MIGRATION_TABLES.pdf] (Based on create_services_table migration)
        'category', // [cite: MIGRATION_TABLES.pdf] (Based on create_services_table migration)
        'is_active', // [cite: MIGRATION_TABLES.pdf] (Based on create_services_table migration)
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2', // [cite: MIGRATION_TABLES.pdf] (Based on create_services_table migration)
        'is_active' => 'boolean', // [cite: MIGRATION_TABLES.pdf] (Based on create_services_table migration)
    ];

    // Add relationships here if a Service is linked to other models (e.g., bookings)
    // Example:
    // public function bookings()
    // {
    //     // Assuming a booking might reference a standard service item
    //     // This relationship depends on how you link services to bookings
    //     // return $this->hasMany(Booking::class); // Or perhaps through BookingItem
    // }
}