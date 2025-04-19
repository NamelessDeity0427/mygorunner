<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $table = 'customers';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'customer_id', 'id');
    }

    public function feedback()
    {
        return $this->hasMany(CustomerFeedback::class, 'customer_id', 'id');
    }

    // Scopes
    public function scopeWithPreferences($query, string $key, $value)
    {
        return $query->whereJsonContains('preferences->' . $key, $value);
    }

    // Helper Methods
    public function getDefaultAddressAttribute(): ?string
    {
        return $this->addresses()->first()?->address;
    }

    public function updatePreferences(array $preferences): void
    {
        $this->update(['preferences' => array_merge($this->preferences ?? [], $preferences)]);
    }
}