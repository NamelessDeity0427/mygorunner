<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    // Explicitly define table name if it doesn't follow convention (plural snake_case)
    // protected $table = 'staff'; // Not needed here as 'staff' pluralizes correctly

    /**
     * The attributes that are mass assignable.
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

    /**
     * Get the user that owns the staff profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the remittances processed by the staff member.
     */
    public function processedRemittances()
    {
        return $this->hasMany(Remittance::class);
    }

    /**
     * Get the support tickets assigned to the staff member.
     */
    public function assignedSupportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to');
    }
}