<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Remittance extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'remittances';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'rider_id',
        'processed_by',
        'amount',
        'payment_method',
        'status',
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

    public function details()
    {
        return $this->hasMany(RemittanceDetail::class, 'remittance_id', 'id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper Methods
    public function calculateTotal(): float
    {
        return $this->details->sum('amount');
    }
}