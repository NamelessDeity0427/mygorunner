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
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('address');
            $table->string('vehicle_type', 50);
            $table->string('plate_number', 20);
            $table->boolean('is_active')->default(true);

            // Changed: Removed ->nullable() to allow spatial index
            // If a rider *must* have a location when active, this makes sense.
            // Consider application logic for offline riders if needed.
            $table->point('current_location'); // Spatial column must be NOT NULL for index

            $table->timestamp('location_updated_at')->nullable();
            $table->enum('status', ['offline', 'available', 'on_task', 'on_break'])->default('offline');
            $table->timestamps();

            // Indexes
            $table->index('status', 'idx_riders_status');
            $table->index('is_active', 'idx_riders_is_active');
            // $table->spatialIndex('current_location'); // Add spatial index (Now possible)
            $table->index('location_updated_at', 'idx_riders_location_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};