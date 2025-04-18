<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderEarning extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rider_earnings'; // Explicitly define table name

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rider_id', // [cite: MIGRATION_TABLES.pdf] (Based on create_rider_earnings_table migration)
        'booking_id', // [cite: MIGRATION_TABLES.pdf] (Based on create_rider_earnings_table migration)
        'amount', // [cite: MIGRATION_TABLES.pdf] (Based on create_rider_earnings_table migration)
        'type', // [cite: MIGRATION_TABLES.pdf] (Based on create_rider_earnings_table migration)
        'status', // [cite: MIGRATION_TABLES.pdf] (Based on create_rider_earnings_table migration)
        'cleared_at', // [cite: MIGRATION_TABLES.pdf] (Based on create_rider_earnings_table migration)
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2', // [cite: MIGRATION_TABLES.pdf] (Based on create_rider_earnings_table migration)
        'cleared_at' => 'datetime', // [cite: MIGRATION_TABLES.pdf] (Based on create_rider_earnings_table migration)
        'status' => 'string', // Cast enum
    ];

    /**
     * Get the rider associated with the earning.
     */
    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }

    /**
     * Get the booking associated with the earning.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}