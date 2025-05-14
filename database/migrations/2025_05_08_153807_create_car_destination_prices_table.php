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
        Schema::create('car_destination_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained('m_destinations')->onDelete('cascade');
            $table->foreignId('car_type_id')->constrained('m_car_types')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_destination_prices');
    }
};
