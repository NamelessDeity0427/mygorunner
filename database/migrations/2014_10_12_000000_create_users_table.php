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
        Schema::create('users', function (Blueprint $table) {
            // Use UUID for primary key for enhanced security
            $table->uuid('id')->primary(); // Changed from id()
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            // Added phone fields based on add_details_to_users_table migration
            $table->string('phone', 20)->unique()->nullable(); // Nullable initially until verified?
            $table->timestamp('phone_verified_at')->nullable();
            // Added user_type based on add_details_to_users_table migration
            $table->enum('user_type', ['customer', 'rider', 'staff', 'admin']);
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            // Add indexes from add_details_to_users_table migration
            $table->index('user_type'); // Removed custom index name for simplicity
            $table->index('created_at'); // Removed custom index name
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};