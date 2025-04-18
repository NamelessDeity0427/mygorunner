<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remittance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rider_id',
        'staff_id',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
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

    /**
     * Get the rider who made the remittance.
     */
    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    /**
     * Get the staff member who processed the remittance.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get the details (specific bookings) included in this remittance.
     */
    public function details()
    {
        return $this->hasMany(RemittanceDetail::class);
    }
}