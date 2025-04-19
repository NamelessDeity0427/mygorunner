<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'support_tickets';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'booking_id',
        'subject',
        'description',
        'assigned_to',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function assignee()
    {
        return $this->belongsTo(Staff::class, 'assigned_to', 'id');
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id', 'id')->orderBy('created_at', 'asc');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeAssignedTo($query, $staffId)
    {
        return $query->where('assigned_to', $staffId);
    }

    // Helper Methods
    public function isResolved(): bool
    {
        return $this->status === 'resolved' && !is_null($this->resolved_at);
    }

    public function assignTo($staffId): void
    {
        $this->update(['assigned_to' => $staffId]);
    }

    // Boot method for event listeners
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($ticket) {
            if (!$ticket->ticket_number) {
                $ticket->ticket_number = 'TICKET-' . now()->format('YmdHis') . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
            }
        });
        static::updating(function ($ticket) {
            if ($ticket->isDirty('status') && $ticket->status === 'resolved') {
                $ticket->resolved_at = now();
            }
        });
    }
}