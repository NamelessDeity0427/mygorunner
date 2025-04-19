<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Rider extends Model
{
    use HasUuids, HasFactory, HasSpatial, SoftDeletes;

    protected $table = 'riders';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'current_location',
        'status',
    ];

    protected $casts = [
        'current_location' => Point::class,
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'rider_id', 'id');
    }

    public function earnings()
    {
        return $this->hasMany(RiderEarning::class, 'rider_id', 'id');
    }

    public function redemptionRequests()
    {
        return $this->hasMany(RedemptionRequest::class, 'rider_id', 'id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'rider_id', 'id');
    }

    public function locationLogs()
    {
        return $this->hasMany(LocationLog::class, 'user_id', 'id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeNear($query, Point $location, float $distance = 10)
    {
        return $query->whereSpatialDistance('current_location', $location, '<=', $distance);
    }

    // Helper Methods
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->attendance()->active()->exists();
    }

    public function updateLocation(Point $location): void
    {
        $this->update(['current_location' => $location]);
        LocationLog::create([
            'user_id' => $this->user_id,
            'location' => $location,
        ]);
    }

    // Accessor for formatted vehicle type
    public function getFormattedVehicleTypeAttribute(): string
    {
        return ucwords(str_replace('_', ' ', $this->vehicle_type));
    }
}