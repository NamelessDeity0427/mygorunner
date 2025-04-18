<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedemptionRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'redemption_requests'; // Explicitly define table name

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rider_id', // [cite: MIGRATION_TABLES.pdf] (Based on create_redemption_requests_table migration)
        'requested_amount', // [cite: MIGRATION_TABLES.pdf] (Based on create_redemption_requests_table migration)
        'status', // [cite: MIGRATION_TABLES.pdf] (Based on create_redemption_requests_table migration)
        'processed_by', // [cite: MIGRATION_TABLES.pdf] (Based on create_redemption_requests_table migration)
        'processed_at', // [cite: MIGRATION_TABLES.pdf] (Based on create_redemption_requests_table migration)
        'notes', // [cite: MIGRATION_TABLES.pdf] (Based on create_redemption_requests_table migration)
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requested_amount' => 'decimal:2', // [cite: MIGRATION_TABLES.pdf] (Based on create_redemption_requests_table migration)
        'processed_at' => 'datetime', // [cite: MIGRATION_TABLES.pdf] (Based on create_redemption_requests_table migration)
        'status' => 'string', // Cast enum
    ];

    /**
     * Get the rider who made the redemption request.
     */
    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }

    /**
     * Get the staff member who processed the request.
     */
    public function processor(): BelongsTo
    {
        // Assuming 'processed_by' stores the staff ID, adjust if it stores user_id
        return $this->belongsTo(Staff::class, 'processed_by');
    }
}