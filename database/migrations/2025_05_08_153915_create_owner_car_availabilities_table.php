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
        Schema::create('owner_car_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained('owner_cars')->onDelete('cascade');
            $table->dateTime('not_available_at');
            $table->dateTime('available_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_car_availabilities');
    }
};
