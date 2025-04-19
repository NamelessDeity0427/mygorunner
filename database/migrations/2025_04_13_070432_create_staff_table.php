<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('position', 100);
            $table->boolean('is_dispatcher')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index('is_dispatcher');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};