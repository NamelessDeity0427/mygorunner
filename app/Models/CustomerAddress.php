<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\SpatialTrait;

class CustomerAddress extends Model
{
    use SpatialTrait;

    protected $fillable = ['customer_id', 'label', 'address', 'location'];

    protected $spatialFields = ['location'];

    /**
     * Get the customer that owns the address.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}