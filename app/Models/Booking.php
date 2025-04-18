<?php
// app/Models/Booking.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial; // <-- Add
use MatanYadaev\EloquentSpatial\Objects\Point; // <-- Add

class Booking extends Model
{
    use HasFactory, HasSpatial; // <-- Add HasSpatial

    protected $fillable = [
        'booking_number',
        'customer_id',
        'rider_id',
        'tie_up_partner_id',
        'booking_type',
        'service_type',
        'pickup_address',        // <-- Add
        'pickup_location',       // <-- Add
        'delivery_address',      // <-- Add
        'delivery_location',     // <-- Add
        'special_instructions',
        'reference_number',
        'scheduled_at',
        'is_recurring',
        'recurring_pattern',
        'status',
        'estimated_distance',
        'estimated_duration',
        'actual_duration',
        'service_fee',
        'rider_fee',
        'total_amount',
        'completed_at',
    ];

    protected $casts = [
        'pickup_location' => Point::class, // <-- Add
        'delivery_location' => Point::class, // <-- Add
        'scheduled_at' => 'datetime',
        'is_recurring' => 'boolean',
        'booking_type' => 'string',
        'service_type' => 'string',
        'status' => 'string',
        'estimated_distance' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'rider_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    // --- Relationships ---
    public function customer() {
         return $this->belongsTo(Customer::class);
    }
    public function rider() {
        return $this->belongsTo(Rider::class);
    }
    public function tieUpPartner() {
        return $this->belongsTo(TieUpPartner::class);
    }
    public function items() {
         return $this->hasMany(BookingItem::class);
    }
    public function statusHistory() {
        return $this->hasMany(BookingStatusHistory::class)->orderBy('created_at');
    }
    public function payments() {
        return $this->hasMany(Payment::class);
    }
     public function remittanceDetails() {
        return $this->hasMany(RemittanceDetail::class);
    }
    public function feedback() {
         return $this->hasOne(CustomerFeedback::class);
    }
    public function supportTicket() {
        return $this->hasOne(SupportTicket::class);
    }
    // Add relationship if LocationLog links back strongly
    public function locationLogs() {
       return $this->hasMany(LocationLog::class);
    }
}