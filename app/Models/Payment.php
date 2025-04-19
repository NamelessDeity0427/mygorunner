<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait

class Payment extends Model
{
    use HasUuids, HasFactory; // Added HasUuids

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
     * 'status', 'amount', 'processed_by', 'paid_at' should be set explicitly.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'reference_number',
        'status',
        'processed_by', // Renamed from collected_by (User ID - UUID)
        'paid_at',      // Renamed from collected_at
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_method' => 'string', // Cast enum
        'status' => 'string', // Cast enum
        'paid_at' => 'datetime', // Renamed cast
    ];

    // --- Relationships ---

    /**
     * Get the booking associated with the payment.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    /**
     * Get the user (rider/staff) who processed the payment record.
     * Renamed from collector.
     */
    public function processor()
    {
        // 'processed_by' links to User 'id' (UUID)
        return $this->belongsTo(User::class, 'processed_by', 'id');
    }
}