<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // 👇 This line tells Laravel to use the singular table name
    protected $table = 'attendance';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rider_id',
        'check_in',
        'check_out',
        'total_hours',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'total_hours' => 'decimal:2',
    ];

    /**
     * Get the rider associated with the attendance record.
     */
    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function photos()
    {
        return $this->hasMany(AttendancePhoto::class);
    }
}
