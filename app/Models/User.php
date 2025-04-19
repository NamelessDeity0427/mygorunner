<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasUuids, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'user_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function customer()
    {
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }

    public function rider()
    {
        return $this->hasOne(Rider::class, 'user_id', 'id');
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'user_id', 'id');
    }

    public function bookingStatusHistory()
    {
        return $this->hasMany(BookingStatusHistory::class, 'created_by', 'id');
    }

    public function processedPayments()
    {
        return $this->hasMany(Payment::class, 'processed_by', 'id');
    }

    public function locationLogs()
    {
        return $this->hasMany(LocationLog::class, 'user_id', 'id');
    }

    public function createdSupportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'user_id', 'id');
    }

    public function supportMessages()
    {
        return $this->hasMany(SupportMessage::class, 'user_id', 'id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'rider_id', 'id');
    }

    public function remittances()
    {
        return $this->hasMany(Remittance::class, 'staff_id', 'id');
    }

    // Scopes
    public function scopeCustomers($query)
    {
        return $query->where('user_type', 'customer');
    }

    public function scopeRiders($query)
    {
        return $query->where('user_type', 'rider');
    }

    public function scopeStaff($query)
    {
        return $query->where('user_type', 'staff');
    }

    public function scopeAdmins($query)
    {
        return $query->where('user_type', 'admin');
    }

    // Helper Methods
    public function isCustomer(): bool
    {
        return $this->user_type === 'customer';
    }

    public function isRider(): bool
    {
        return $this->user_type === 'rider';
    }

    public function isStaff(): bool
    {
        return $this->user_type === 'staff';
    }

    public function isStaffOrAdmin(): bool
    {
        return $this->isStaff() || $this->user_type === 'admin';
    }

    // Boot method for event listeners
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            if (!$user->user_type) {
                $user->user_type = 'customer';
            }
        });
    }
}