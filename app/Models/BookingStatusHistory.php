<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingStatusHistory extends Model
{
    use HasFactory;

    // Disable updated_at timestamp as it's not in the migration
    public $timestamps = ["created_at"]; // Only enable created_at
    const UPDATED_AT = null; // Explicitly disable updated_at


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'status',
        'notes',
        'created_by', // User ID
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string', // Cast enum
        'created_at' => 'datetime',
    ];

    /**
     * Get the booking associated with the status change.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user who created this status entry.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}