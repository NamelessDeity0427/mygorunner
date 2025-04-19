<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('vehicle_type', 50);
            $table->string('plate_number', 20);
            $table->boolean('is_active')->default(true);
            $table->point('current_location')->nullable();
            $table->timestamp('location_updated_at')->nullable();
            $table->enum('status', ['offline', 'available', 'on_task', 'on_break'])->default('offline');
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('is_active');
            $table->index('location_updated_at');
            $table->spatialIndex('current_location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};