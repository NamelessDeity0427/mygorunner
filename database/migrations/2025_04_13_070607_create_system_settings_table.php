<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->text('value');
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            ['key' => 'base_delivery_fee', 'value' => '50.00', 'created_at' => now(), 'updated_at' => now()]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};