<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Staff extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'staff';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'position',
        'is_dispatcher',
        'is_admin',
    ];

    protected $casts = [
        'is_dispatcher' => 'boolean',
        'is_admin' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function processedPayments()
    {
        return $this->hasMany(Payment::class, 'processed_by', 'id');
    }

    public function processedRemittances()
    {
        return $this->hasMany(Remittance::class, 'processed_by', 'id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to', 'id');
    }

    // Scopes
    public function scopeDispatchers($query)
    {
        return $query->where('is_dispatcher', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    // Helper Methods
    public function isDispatcher(): bool
    {
        return $this->is_dispatcher;
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
}