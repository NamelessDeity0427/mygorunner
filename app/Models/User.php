<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Uncomment if email verification is needed
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // For API authentication
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids trait

// class User extends Authenticatable implements MustVerifyEmail // Add MustVerifyEmail if needed
class User extends Authenticatable
{
    // Order traits for clarity: UUIDs first, then others
    use HasUuids, HasApiTokens, HasFactory, Notifiable; // Added HasUuids

    /**
     * The primary key type. Use 'string' for UUIDs.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing. Set to false for UUIDs.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     * Carefully review which fields should be settable via mass assignment.
     * 'user_type' should likely NOT be mass assignable for security.
     * Set it explicitly in controllers/services when creating users.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        // 'user_type', // REMOVED from fillable for security
        'password',
        'email_verified_at', // Allow setting verification time if needed during creation/update
        'phone_verified_at', // Allow setting verification time if needed
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Essential for security to hide sensitive data.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     * Ensures data type integrity.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed', // Automatically handles hashing
        'user_type' => 'string', // Cast enum
    ];

    // --- Relationships ---

    /**
     * Get the customer record associated with the user (if user_type is 'customer').
     */
    public function customer()
    {
        // Ensure foreign key 'user_id' matches the User model's primary key (UUID)
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }

    /**
     * Get the rider record associated with the user (if user_type is 'rider').
     */
    public function rider()
    {
        // Ensure foreign key 'user_id' matches the User model's primary key (UUID)
        return $this->hasOne(Rider::class, 'user_id', 'id');
    }

    /**
     * Get the staff record associated with the user (if user_type is 'staff' or 'admin').
     */
    public function staff()
    {
        // Ensure foreign key 'user_id' matches the User model's primary key (UUID)
        return $this->hasOne(Staff::class, 'user_id', 'id');
    }

    /**
     * Get the booking status history entries created by this user.
     */
    public function bookingStatusHistory()
    {
        return $this->hasMany(BookingStatusHistory::class, 'created_by', 'id');
    }

    /**
     * Get the payments processed by this user.
     * Renamed from collectedPayments for clarity based on migration changes.
     */
    public function processedPayments()
    {
        return $this->hasMany(Payment::class, 'processed_by', 'id');
    }

    /**
     * Get the location logs recorded for this user.
     */
    public function locationLogs()
    {
        return $this->hasMany(LocationLog::class, 'user_id', 'id');
    }

    /**
     * Get the support tickets created by this user.
     */
    public function createdSupportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'user_id', 'id');
    }

    /**
     * Get the support messages written by this user.
     */
    public function supportMessages()
    {
        return $this->hasMany(SupportMessage::class, 'user_id', 'id');
    }

    // --- Helper Methods ---

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

    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isStaffOrAdmin(): bool
    {
        return $this->isStaff() || $this->isAdmin();
    }
}