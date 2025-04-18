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
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('position', 100);
            $table->boolean('is_dispatcher')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('is_dispatcher', 'idx_staff_is_dispatcher');
            $table->index('is_admin', 'idx_staff_is_admin');
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