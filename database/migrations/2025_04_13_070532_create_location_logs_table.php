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
        Schema::create('location_logs', function (Blueprint $table) {
            $table->id(); // [cite: 57]
            $table->foreignId('user_id')->constrained('users'); // User whose location it is (rider or customer) [cite: 58]
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null'); // Link location to a specific booking if applicable [cite: 58]

            // Replace lat and lng with a single point column
            // $table->decimal('lat', 10, 8); // [cite: 59] Removed
            // $table->decimal('lng', 11, 8); // [cite: 59] Removed
            $table->point('location'); // Added spatial column

            $table->timestamp('created_at')->nullable(); // Only created_at needed [cite: 59]

            // Indexes
            // $table->index(['lat', 'lng'], 'idx_location_logs_location'); // [cite: 60] Removed
            $table->spatialIndex('location'); // Added spatial index
            $table->index('created_at', 'idx_location_logs_created_at'); // [cite: 60]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_logs'); // [cite: 61]
    }
};