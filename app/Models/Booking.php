<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait
// Spatial integration
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**
 * @property Point|null $pickup_location
 * @property Point|null $delivery_location
 * @property string $status // Enum status
 */
class Booking extends Model
{
    use HasUuids, HasFactory, HasSpatial; // Added HasUuids and HasSpatial

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
     * The attributes that are mass assignable.
     * Review carefully. Calculated fields (fees, distances, durations, status)
     * should NOT be fillable. They should be set via specific methods or observers.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_number',
        'customer_id',
        'rider_id', // Nullable, assigned later
        // 'tie_up_partner_id', // REMOVED
        // 'booking_type', // REMOVED
        'service_type',
        'pickup_address',
        'pickup_location', // Spatial
        'delivery_address',
        'delivery_location', // Spatial
        'special_instructions',
        'reference_number', // Optional customer reference
        'scheduled_at',
        // 'status', // Status managed explicitly
        // 'estimated_distance', // Calculated
        // 'estimated_duration', // Calculated
        // 'actual_duration', // Calculated
        // 'service_fee', // Calculated/Admin Set
        // 'rider_fee', // Calculated/Admin Set
        // 'items_cost', // Calculated/Rider Input
        // 'total_amount', // Calculated
        // 'completed_at', // Set on completion
        // 'cancelled_at', // Set on cancellation
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pickup_location' => Point::class, // Spatial cast
        'delivery_location' => Point::class, // Spatial cast
        'scheduled_at' => 'datetime',
        'service_type' => 'string', // Enum cast
        'status' => 'string', // Enum cast
        'estimated_distance' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'rider_fee' => 'decimal:2',
        'items_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime', // Added cast
        'estimated_duration' => 'integer', // Cast duration (seconds)
        'actual_duration' => 'integer', // Cast duration (seconds)
    ];

    // --- Relationships ---

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function rider()
    {
        // Rider can be null if unassigned
        return $this->belongsTo(Rider::class, 'rider_id', 'id');
    }

    /**
     * REMOVED: TieUpPartner relationship
     * public function tieUpPartner() { ... }
     */

    public function items()
    {
        return $this->hasMany(BookingItem::class, 'booking_id', 'id');
    }

    public function statusHistory()
    {
        // Order by most recent status first
        return $this->hasMany(BookingStatusHistory::class, 'booking_id', 'id')->orderBy('created_at', 'desc');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'booking_id', 'id');
    }

    public function remittanceDetails()
    {
        return $this->hasMany(RemittanceDetail::class, 'booking_id', 'id');
    }

    public function feedback()
    {
        // A booking typically has one feedback entry
        return $this->hasOne(CustomerFeedback::class, 'booking_id', 'id');
    }

    public function supportTicket()
    {
        // A booking might be associated with one support ticket
        return $this->hasOne(SupportTicket::class, 'booking_id', 'id');
    }

    public function locationLogs()
    {
        return $this->hasMany(LocationLog::class, 'booking_id', 'id');
    }

    public function earnings()
    {
        // Get rider earnings associated with this booking
        return $this->hasMany(RiderEarning::class, 'booking_id', 'id');
    }
}