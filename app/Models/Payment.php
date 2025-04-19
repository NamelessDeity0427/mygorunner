<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'payments';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'status',
        'processed_by',
        'reference_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function processedBy()
    {
        return $this->belongsTo(Staff::class, 'processed_by', 'id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'paid');
    }

    // Helper Methods
    public function markAsCompleted($referenceNumber = null): void
    {
        $this->update([
            'status' => 'paid',
            'reference_number' => $referenceNumber ?? $this->reference_number,
        ]);
    }

    // Accessor for formatted amount
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }
}