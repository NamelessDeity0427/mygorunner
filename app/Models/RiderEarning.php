<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait

class RiderEarning extends Model
{
    use HasUuids, HasFactory; // Added HasUuids

    protected $table = 'rider_earnings'; // Explicit table name

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
     * 'status', 'amount', 'cleared_at' should typically be set explicitly.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rider_id',
        'booking_id',
        'amount',
        'type', // e.g., 'delivery_fee', 'tip', 'bonus', 'adjustment'
        'status',
        'cleared_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'cleared_at' => 'datetime',
        'status' => 'string', // Cast enum
        'type' => 'string',
    ];

    // --- Relationships ---

    /**
     * Get the rider associated with the earning.
     */
    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'rider_id', 'id');
    }

    /**
     * Get the booking associated with the earning.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }
}