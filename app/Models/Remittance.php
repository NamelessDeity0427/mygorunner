<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait

class Remittance extends Model
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
     * 'status', 'amount', 'staff_id' should be set explicitly during processing.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rider_id',
        'staff_id', // Staff who verified
        'amount',
        'payment_method', // How rider paid staff
        'reference_number',
        'notes', // Staff notes
        'status',
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
    ];

    // --- Relationships ---

    /**
     * Get the rider who made the remittance.
     */
    public function rider()
    {
        return $this->belongsTo(Rider::class, 'rider_id', 'id');
    }

    /**
     * Get the staff member who processed the remittance.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }

    /**
     * Get the details (specific bookings) included in this remittance.
     */
    public function details()
    {
        return $this->hasMany(RemittanceDetail::class, 'remittance_id', 'id');
    }
}