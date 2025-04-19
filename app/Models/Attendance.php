<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use MatanYadaev\EloquentSpatial\Objects\Point;

class Attendance extends Model
{
    use HasUuids, HasFactory, HasSpatial;

    protected $table = 'attendance';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'check_in',
        'check_out',
        'check_in_location',
        'check_out_location',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_hours' => 'decimal:2',
        'check_in_location' => Point::class,
        'check_out_location' => Point::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    // Accessor for total_hours
    public function getTotalHoursAttribute()
    {
        if ($this->check_in && $this->check_out) {
            return round($this->check_in->diffInHours($this->check_out), 2);
        }
        return 0;
    }

    // Scopes
    public function scopeForRider($query, $riderId)
    {
        return $query->where('user_id', $riderId);
    }

    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('check_in', $date);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('check_out');
    }

    // Helper Methods
    public function isActive(): bool
    {
        return !is_null($this->check_in) && is_null($this->check_out);
    }

    // Boot method for event listeners
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($attendance) {
            if ($attendance->isDirty('check_out') && $attendance->check_out) {
                $attendance->total_hours = $attendance->getTotalHoursAttribute();
            }
        });
    }
}