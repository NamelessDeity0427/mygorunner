<?php
// [Generated Migration File Name]
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            // Change existing point to nullable. Assumes default was NOT NULL.
            $table->point('current_location')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            // Revert back to NOT NULL. This might fail if rows have NULL. Handle carefully.
            // Consider setting a default point or handling NULLs before reverting.
             $table->point('current_location')->nullable(false)->change();
        });
    }
};