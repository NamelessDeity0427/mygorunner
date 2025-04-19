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
        Schema::create('staff', function (Blueprint $table) {
            // Use UUID for primary key
            $table->uuid('id')->primary(); // Changed from id()
            // Use foreignUuid for the foreign key
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade'); // Changed from foreignId() [cite: 111]
            $table->string('position', 100); // [cite: 111]
            $table->boolean('is_dispatcher')->default(false); // [cite: 111]
            $table->boolean('is_admin')->default(false); // [cite: 111]
            $table->timestamps();

            // Indexes [cite: 111]
            $table->index('is_dispatcher');
            $table->index('is_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};