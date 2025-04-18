<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderQueue extends Model
{
    use HasFactory;

    // Specify table name if it differs from pluralized model name convention
    protected $table = 'rider_queue';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rider_id',
        'check_in_time',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_time' => 'datetime',
        'status' => 'string', // Cast enum
    ];

    /**
     * Get the rider associated with this queue entry.
     */
    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }
}