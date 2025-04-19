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
        Schema::create('services', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            $table->string('name'); // [cite: 165]
            $table->text('description')->nullable(); // [cite: 165]
            $table->string('image_path')->nullable(); // [cite: 165]
            $table->decimal('price', 10, 2)->nullable(); // Standard price, if applicable [cite: 165]
            $table->string('category')->nullable(); // e.g., food, errand, documents [cite: 165]
            $table->boolean('is_active')->default(true); // [cite: 165]
            $table->timestamps(); // [cite: 165]

            $table->index('is_active'); // [cite: 165]
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};