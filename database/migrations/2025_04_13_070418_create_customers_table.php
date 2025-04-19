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
        Schema::create('customers', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for the foreign key
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade'); // Changed from foreignId() [cite: 104]
            $table->text('address'); // [cite: 104]
            // Spatial column (Point) for default location [cite: 104]
            $table->point('default_location')->nullable(); // Made nullable, requires app logic for default
            $table->timestamps();

            // Add spatial index if needed and supported [cite: 105]
            // $table->spatialIndex('default_location'); // Enable if using MySQL >= 5.7.5 or PostGIS
        });

        // Add spatial index separately if needed (MySQL specific syntax might differ)
        // DB::statement('ALTER TABLE customers ADD SPATIAL INDEX(default_location);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};