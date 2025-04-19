<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait

class SupportMessage extends Model
{
    use HasUuids, HasFactory; // Added HasUuids

    protected $table = 'support_messages'; // Explicit table name

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
     * Indicates if the model should be timestamped. Only 'created_at'.
     *
     * @var bool
     */
    public $timestamps = true; // Manages created_at

    const UPDATED_AT = null; // Disable updated_at

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'user_id', // User who wrote the message
        'message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    // --- Relationships ---

    /**
     * Get the support ticket that the message belongs to.
     */
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id', 'id');
    }

    /**
     * Get the user who wrote the message.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}