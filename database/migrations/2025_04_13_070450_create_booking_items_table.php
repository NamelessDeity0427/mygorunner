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
        Schema::create('booking_items', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for the foreign key
            $table->foreignUuid('booking_id')->constrained('bookings')->onDelete('cascade'); // Changed from foreignId() [cite: 124]
            $table->string('name'); // [cite: 125]
            $table->integer('quantity')->default(1); // [cite: 125]
            $table->text('notes')->nullable(); // [cite: 125]
            $table->decimal('price', 10, 2)->nullable(); // Price per item, if known [cite: 125]
            $table->timestamps(); // [cite: 125]

            $table->index('name'); // [cite: 125]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};