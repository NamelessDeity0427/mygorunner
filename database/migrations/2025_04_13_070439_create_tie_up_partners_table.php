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
        Schema::create('tie_up_partners', function (Blueprint $table) {
            $table->id(); // [cite: 27]
            $table->string('name'); // [cite: 27]
            $table->text('address'); // [cite: 27]
            $table->string('contact_person'); // [cite: 27]
            $table->string('phone', 20); // [cite: 27]
            $table->string('email')->nullable(); // [cite: 27]

            // Replace lat and lng with a single point column
            // $table->decimal('lat', 10, 8); // [cite: 27] Removed
            // $table->decimal('lng', 11, 8); // [cite: 28] Removed
            $table->point('location'); // Added spatial column (not nullable based on original schema)

            $table->boolean('is_active')->default(true); // [cite: 28]
            $table->timestamps(); // [cite: 28]

            // Indexes
            $table->index('name', 'idx_tie_up_partners_name'); // [cite: 28]
            $table->index('is_active', 'idx_tie_up_partners_is_active'); // [cite: 28]
            // $table->index(['lat', 'lng'], 'idx_tie_up_partners_location'); // [cite: 28] Removed
            $table->spatialIndex('location'); // Added spatial index
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tie_up_partners'); // [cite: 29]
    }
};