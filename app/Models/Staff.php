<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait

class Staff extends Model
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
     * The table associated with the model.
     * Laravel correctly pluralizes 'staff' to 'staff', so this is optional.
     * protected $table = 'staff';
     */

    /**
     * The attributes that are mass assignable.
     * Be cautious with 'is_dispatcher' and 'is_admin'. Set explicitly where possible.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'position',
        'is_dispatcher',
        'is_admin',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_dispatcher' => 'boolean',
        'is_admin' => 'boolean',
    ];

    // --- Relationships ---

    /**
     * Get the user that owns the staff profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the remittances processed by the staff member.
     */
    public function processedRemittances()
    {
        return $this->hasMany(Remittance::class, 'staff_id', 'id');
    }

    /**
     * Get the support tickets assigned to the staff member.
     */
    public function assignedSupportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to', 'id');
    }

    /**
     * Get the redemption requests processed by this staff member.
     */
    public function processedRedemptionRequests()
    {
        return $this->hasMany(RedemptionRequest::class, 'processed_by', 'id');
    }
}