<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemittanceDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'remittance_id',
        'booking_id',
        'amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the parent remittance record.
     */
    public function remittance()
    {
        return $this->belongsTo(Remittance::class);
    }

    /**
     * Get the booking associated with this remittance detail.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}