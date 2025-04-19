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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id(); // Standard ID is fine here
            $table->string('key')->unique(); // Setting name (e.g., 'service_fee_per_km') [cite: 155]
            $table->text('value'); // Setting value [cite: 155]
            $table->timestamps(); // [cite: 155]
        });

        // Add default settings if needed
        // DB::table('system_settings')->insert(['key' => 'base_delivery_fee', 'value' => '50.00', 'created_at' => now(), 'updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};