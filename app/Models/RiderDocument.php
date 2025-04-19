<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiderDocument extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'rider_documents';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'rider_id',
        'file_path',
    ];

    // Relationships
    public function rider()
    {
        return $this->belongsTo(Rider::class, 'rider_id', 'id');
    }
}