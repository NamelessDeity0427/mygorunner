<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RedemptionRequest extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'redemption_requests';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'rider_id',
        'amount',
        'payment_method',
        'status',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function rider()
    {
        return $this->belongsTo(Rider::class, 'rider_id', 'id');
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

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Helper Methods
    public function approve($staffId): void
    {
        $this->update([
            'status' => 'approved',
            'processed_by' => $staffId,
        ]);
    }
}