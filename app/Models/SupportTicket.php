<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait

class SupportTicket extends Model
{
    use HasUuids, HasFactory; // Added HasUuids

    protected $table = 'support_tickets'; // Explicit table name

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
     * 'ticket_number', 'status', 'assigned_to', 'resolved_at' should be set explicitly.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_number', // Usually generated automatically
        'user_id', // Creator (Customer/Rider/Staff/Admin)
        'booking_id', // Optional associated booking
        'subject',
        'description',
        // 'status', // Managed explicitly
        // 'assigned_to', // Staff ID (UUID) - Assigned explicitly
        // 'resolved_at', // Set explicitly
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

    // --- Relationships ---

    /**
     * Get the user who created the support ticket.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the booking associated with the support ticket (if any).
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    /**
     * Get the staff member assigned to the support ticket.
     */
    public function assignee()
    {
        return $this->belongsTo(Staff::class, 'assigned_to', 'id');
    }

    /**
     * Get the messages for the support ticket.
     */
    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id', 'id')->orderBy('created_at', 'asc'); // Order messages chronologically
    }
}