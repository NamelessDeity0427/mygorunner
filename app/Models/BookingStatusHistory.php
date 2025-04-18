<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingStatusHistory extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'booking_status_history';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'booking_id',
        'status',
        'notes',
        'created_by',
    ];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // Scopes
    public function scopeForBooking($query, $bookingId)
    {
        return $query->where('booking_id', $bookingId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}