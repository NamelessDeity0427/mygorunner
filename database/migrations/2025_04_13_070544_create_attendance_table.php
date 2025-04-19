<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rider_id')->constrained('riders')->onDelete('cascade');
            $table->timestamp('check_in');
            $table->timestamp('check_out')->nullable();
            $table->decimal('total_hours', 5, 2)->default(0);
            $table->point('check_in_location')->nullable();
            $table->point('check_out_location')->nullable();
            $table->string('qr_code_hash')->unique()->nullable();
            $table->timestamps();
            $table->index('check_in');
            $table->index('check_out');
            $table->spatialIndex('check_in_location');
            $table->spatialIndex('check_out_location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};