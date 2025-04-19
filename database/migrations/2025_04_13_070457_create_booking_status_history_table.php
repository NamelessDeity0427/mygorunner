<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_status_history', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for foreign keys
            $table->foreignUuid('booking_id')->constrained('bookings')->onDelete('cascade'); // [cite: 127]
            // Use the same statuses as the bookings table for consistency
            $table->enum('status', [ // [cite: 127] - Use updated status list from bookings table
                'pending', 'accepted', 'assigned', 'at_pickup', 'picked_up',
                'on_the_way', 'at_delivery', 'completed', 'cancelled', 'failed'
            ]);
            $table->text('notes')->nullable(); // Reason for status change, etc. [cite: 127]
            // User (Admin, Staff, Rider, or System) who triggered the change
            $table->foreignUuid('created_by')->nullable()->constrained('users')->onDelete('set null'); // [cite: 127]
            $table->timestamp('created_at')->useCurrent(); // Use current time, no updated_at needed [cite: 127]

            // Indexes [cite: 127]
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_status_history');
    }
};