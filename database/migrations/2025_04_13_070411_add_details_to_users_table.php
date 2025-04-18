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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->unique()->after('email_verified_at');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->enum('user_type', ['customer', 'rider', 'staff', 'admin'])->after('password');
        
            $table->index('user_type', 'idx_users_user_type');
            $table->index('created_at', 'idx_users_created_at');
        
            $table->string('name')->nullable(false)->change();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_users_user_type');
            $table->dropIndex('idx_users_created_at');

            // Drop columns
            $table->dropColumn(['phone', 'phone_verified_at', 'user_type']);

            // Optionally revert changes to name/email if needed,
            // but usually dropping columns is sufficient for reversal.
            // $table->string('name')->nullable()->change();
            // $table->dropUnique(['email']); // Be careful if other unique constraints exist
        });
    }
};