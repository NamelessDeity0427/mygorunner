<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait

class CustomerFeedback extends Model
{
    use HasUuids, HasFactory; // Added HasUuids

    protected $table = 'customer_feedback'; // Explicit table name

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
        'booking_id',
        'customer_id',
        'rider_id',
        'rating',
        'comments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer', // Ensure rating is within expected range (e.g., 1-5) via validation
    ];

    // --- Relationships ---

    /**
     * Get the booking the feedback is for.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    /**
     * Get the customer who gave the feedback.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Get the rider who received the feedback.
     */
    public function rider()
    {
        return $this->belongsTo(Rider::class, 'rider_id', 'id');
    }
}