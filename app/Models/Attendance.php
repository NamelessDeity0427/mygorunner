<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait
// Spatial integration
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;


/**
 * @property Point|null $check_in_location
 * @property Point|null $check_out_location
 */
class Attendance extends Model
{
    // Add HasSpatial if using check_in/out locations
    use HasUuids, HasFactory, HasSpatial; // Added HasUuids and HasSpatial

    protected $table = 'attendance'; // Explicit table name

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
     * 'total_hours' should be calculated, not fillable.
     * Locations should likely be set explicitly.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rider_id',
        'check_in',
        'check_out', // Nullable
        // 'total_hours', // Calculated field
        'check_in_location', // Added
        'check_out_location', // Added
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_hours' => 'decimal:2',
        'check_in_location' => Point::class, // Added cast
        'check_out_location' => Point::class, // Added cast
    ];

    // --- Relationships ---

    /**
     * Get the rider associated with the attendance record.
     */
    public function rider()
    {
        return $this->belongsTo(Rider::class, 'rider_id', 'id');
    }

    /**
     * REMOVED: Relationship to AttendancePhoto
     * public function photos() { ... }
     */
}