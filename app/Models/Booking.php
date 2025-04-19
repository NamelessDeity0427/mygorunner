<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Booking extends Model
{
    use HasUuids, HasFactory, HasSpatial, SoftDeletes;

    protected $table = 'bookings';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'rider_id',
        'service_type',
        'pickup_address',
        'pickup_location',
        'delivery_address',
        'delivery_location',
        'special_instructions',
        'status',
        'estimated_distance',
        'estimated_duration',
    ];

    protected $casts = [
        'pickup_location' => Point::class,
        'delivery_location' => Point::class,
        'estimated_distance' => 'decimal:2',
        'estimated_duration' => 'decimal:2',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class, 'rider_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class, 'booking_id', 'id');
    }

    public function statusHistory()
    {
        return $this->hasMany(BookingStatusHistory::class, 'booking_id', 'id')->orderBy('created_at', 'asc');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'booking_id', 'id');
    }

    public function feedback()
    {
        return $this->hasOne(CustomerFeedback::class, 'booking_id', 'id');
    }

    public function locationLogs()
    {
        return $this->hasMany(LocationLog::class, 'booking_id', 'id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['accepted', 'assigned', 'at_pickup', 'picked_up', 'on_the_way', 'at_delivery']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForRider($query, $riderId)
    {
        return $query->where('rider_id', $riderId);
    }

    // Helper Methods
    public function isAssignable(): bool
    {
        return in_array($this->status, ['pending', 'accepted']);
    }

    public function calculateTotalAmount(): float
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * ($item->price ?? 0);
        });
    }

    // Accessor for formatted status
    public function getFormattedStatusAttribute(): string
    {
        return str_replace('_', ' ', ucwords($this->status));
    }

    // Boot method for event listeners
    protected static function boot()
    {
        parent::boot();
        static::created(function ($booking) {
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'status' => $booking->status,
                'notes' => 'Booking created',
            ]);
        });
        static::updated(function ($booking) {
            if ($booking->isDirty('status')) {
                BookingStatusHistory::create([
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                    'notes' => 'Status updated to ' . $booking->status,
                ]);
            }
        });
    }
}