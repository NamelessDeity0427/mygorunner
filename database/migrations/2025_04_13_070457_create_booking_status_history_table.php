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
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->enum('status', ['pending', 'assigned', 'picked_up', 'in_progress', 'completed', 'cancelled']);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users'); // User who triggered the status change
            $table->timestamp('created_at')->nullable(); // Only created_at needed as per schema

            // Indexes
            $table->index('status', 'idx_booking_status_history_status');
            $table->index('created_at', 'idx_booking_status_history_created_at');
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