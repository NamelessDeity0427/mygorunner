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
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id() [cite: 139]
            // Use foreignUuid for foreign keys
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade'); // User (Rider/Customer) [cite: 139]
            $table->foreignUuid('booking_id')->nullable()->constrained('bookings')->onDelete('set null'); // Associated booking, if any [cite: 139]
            $table->point('location'); // Spatial column [cite: 139]
            $table->timestamp('created_at')->useCurrent(); // Log time [cite: 139]

            // Indexes [cite: 139]
            $table->index('created_at');
            // Add spatial index if needed and supported [cite: 139]
            // $table->spatialIndex('location');
        });

        // Add spatial index separately if needed
        // DB::statement('ALTER TABLE location_logs ADD SPATIAL INDEX(location);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_logs');
    }
};