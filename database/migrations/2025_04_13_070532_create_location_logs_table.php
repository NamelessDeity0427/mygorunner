<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade')->index();
            $table->foreignUuid('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->point('location');
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at');
            $table->spatialIndex('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_logs');
    }
};