<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_number',
        'user_id', // Creator
        'booking_id',
        'subject',
        'description',
        'status',
        'assigned_to', // Staff ID
        'resolved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string', // Cast enum
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the user who created the support ticket.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the booking associated with the support ticket (if any).
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the staff member assigned to the support ticket.
     */
    public function assignee()
    {
        return $this->belongsTo(Staff::class, 'assigned_to');
    }

    /**
     * Get the messages for the support ticket.
     */
    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id')->orderBy('created_at');
    }
}