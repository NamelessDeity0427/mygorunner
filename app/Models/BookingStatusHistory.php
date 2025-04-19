<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait

class BookingStatusHistory extends Model
{
    use HasUuids, HasFactory; // Added HasUuids

    protected $table = 'booking_status_history'; // Explicit table name

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
     * Indicates if the model should be timestamped.
     * Only 'created_at' is used based on the migration.
     *
     * @var bool
     */
    public $timestamps = true; // Keep true to manage created_at

    /**
     * Define the constant for the updated_at column to null.
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     * 'status' and 'created_by' should ideally be set explicitly.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'status',
        'notes',
        'created_by', // User ID (UUID) who triggered the change
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string', // Cast enum
        'created_at' => 'datetime',
    ];

    // --- Relationships ---

    /**
     * Get the booking associated with the status change.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    /**
     * Get the user who created this status entry.
     */
    public function creator()
    {
        // 'created_by' links to the User model's 'id' (UUID)
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}