<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiderEarning extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'rider_earnings';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'rider_id',
        'booking_id',
        'amount',
        'type',
        'status',
        'cleared_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cleared_at' => 'datetime',
    ];

    // Relationships
    public function rider()
    {
        return $this->belongsTo(Rider::class, 'rider_id', 'id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Helper Methods
    public static function totalEarningsForRider($riderId, $startDate = null, $endDate = null): float
    {
        $query = self::where('rider_id', $riderId)->where('status', 'paid');
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        return $query->sum('amount');
    }
}