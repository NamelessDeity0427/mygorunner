<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->string('label');
            $table->string('address', 1000);
            $table->point('location');
            $table->timestamps();
            $table->spatialIndex('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};