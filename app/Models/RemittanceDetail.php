<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait

class RemittanceDetail extends Model
{
    use HasUuids, HasFactory; // Added HasUuids

    protected $table = 'remittance_details'; // Explicit table name

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
        'remittance_id',
        'booking_id',
        'amount', // Portion of booking amount in this remittance
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // --- Relationships ---

    /**
     * Get the parent remittance record.
     */
    public function remittance()
    {
        return $this->belongsTo(Remittance::class, 'remittance_id', 'id');
    }

    /**
     * Get the booking associated with this remittance detail.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }
}