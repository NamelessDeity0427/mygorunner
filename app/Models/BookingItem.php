<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait

class BookingItem extends Model
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
     * 'price' might be set by admin/system or rider, review flow.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'name',
        'quantity',
        'notes',
        'price', // Price per item
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    // --- Relationships ---

    /**
     * Get the booking that the item belongs to.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }
}