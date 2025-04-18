<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendancePhoto extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendance_photos'; // Explicitly define table name

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_id', // [cite: MIGRATION_TABLES.pdf] (Based on create_attendance_photos_table migration)
        'photo_path', // [cite: MIGRATION_TABLES.pdf] (Based on create_attendance_photos_table migration)
        'verified_at', // [cite: MIGRATION_TABLES.pdf] (Based on create_attendance_photos_table migration)
        'verified_by', // [cite: MIGRATION_TABLES.pdf] (Based on create_attendance_photos_table migration)
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified_at' => 'datetime', // [cite: MIGRATION_TABLES.pdf] (Based on create_attendance_photos_table migration)
    ];

    /**
     * Get the attendance record associated with this photo.
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the staff member who verified the photo.
     */
    public function verifier(): BelongsTo
    {
        // Assuming 'verified_by' stores the staff ID, adjust if it stores user_id
        return $this->belongsTo(Staff::class, 'verified_by');
    }
}