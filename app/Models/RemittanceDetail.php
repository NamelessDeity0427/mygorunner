<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RemittanceDetail extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'remittance_details';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'remittance_id',
        'booking_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function remittance()
    {
        return $this->belongsTo(Remittance::class, 'remittance_id', 'id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    // Scopes
    public function scopeForRemittance($query, $remittanceId)
    {
        return $query->where('remittance_id', $remittanceId);
    }
}