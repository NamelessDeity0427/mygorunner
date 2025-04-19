<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiderDocument extends Model
{
    protected $fillable = ['rider_id', 'file_path', 'file_name', 'file_type'];

    /**
     * Get the rider that owns the document.
     */
    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }
}