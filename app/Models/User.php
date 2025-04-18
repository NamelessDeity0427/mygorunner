<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // Uncomment if email verification is needed
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // If using Sanctum for API authentication

class User extends Authenticatable // implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable; // Add HasApiTokens if needed

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone', // Added
        'user_type', // Added
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime', // Added
        'password' => 'hashed', // Use Laravel's default hashing
        'user_type' => 'string', // Cast enum to string
    ];

    // Relationships

    /**
     * Get the customer record associated with the user.
     */
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    /**
     * Get the rider record associated with the user.
     */
    public function rider()
    {
        return $this->hasOne(Rider::class);
    }

    /**
     * Get the staff record associated with the user.
     */
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * Get the booking status history created by the user.
     */
    public function bookingStatusHistory()
    {
        return $this->hasMany(BookingStatusHistory::class, 'created_by');
    }

    /**
     * Get the payments collected by the user.
     */
    public function collectedPayments()
    {
        return $this->hasMany(Payment::class, 'collected_by');
    }

    /**
     * Get the location logs for the user.
     */
    public function locationLogs()
    {
        return $this->hasMany(LocationLog::class);
    }

    /**
     * Get the support tickets created by the user.
     */
    public function createdSupportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'user_id');
    }

    /**
     * Get the support messages written by the user.
     */
    public function supportMessages()
    {
        return $this->hasMany(SupportMessage::class);
    }

    // Add helper methods if needed, e.g., checking user type
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
}