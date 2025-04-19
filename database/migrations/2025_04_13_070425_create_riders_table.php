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
        Schema::create('riders', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for the foreign key
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade'); // Changed from foreignId() [cite: 108]
            $table->text('address'); // [cite: 108]
            $table->string('vehicle_type', 50); // [cite: 108]
            $table->string('plate_number', 20); // [cite: 108]
            $table->boolean('is_active')->default(true); // [cite: 108]
            // Spatial column (Point) for current location, made nullable [cite: 108, 161]
            $table->point('current_location')->nullable();
            $table->timestamp('location_updated_at')->nullable(); // [cite: 108]
            $table->enum('status', ['offline', 'available', 'on_task', 'on_break'])->default('offline'); // [cite: 108]
            $table->timestamps();

            // Indexes [cite: 108]
            $table->index('status');
            $table->index('is_active');
            $table->index('location_updated_at');
            // Add spatial index if needed and supported [cite: 108]
            // $table->spatialIndex('current_location'); // Enable if using MySQL >= 5.7.5 or PostGIS
        });

        // Add spatial index separately if needed
        // DB::statement('ALTER TABLE riders ADD SPATIAL INDEX(current_location);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};