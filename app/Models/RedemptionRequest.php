<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait

class RedemptionRequest extends Model
{
    use HasUuids, HasFactory; // Added HasUuids

    protected $table = 'redemption_requests'; // Explicit table name

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
     * 'status', 'processed_by', 'processed_at' should be set explicitly.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rider_id',
        'requested_amount',
        'status',
        'processed_by', // Staff ID (UUID)
        'processed_at',
        'notes', // Admin/Staff notes
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requested_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'status' => 'string', // Cast enum
    ];

    // --- Relationships ---

    /**
     * Get the rider who made the redemption request.
     */
    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'rider_id', 'id');
    }

    /**
     * Get the staff member who processed the request.
     * Renamed from processor.
     */
    public function processedByStaff(): BelongsTo
    {
        // 'processed_by' links to Staff 'id' (UUID)
        return $this->belongsTo(Staff::class, 'processed_by', 'id');
    }
}