<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remittance_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('remittance_id')->constrained('remittances')->onDelete('cascade');
            $table->foreignUuid('booking_id')->constrained('bookings')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remittance_details');
    }
};