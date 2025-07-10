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
        Schema::create('m_car_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('m_car_categories')->onDelete('cascade');
            $table->string('car_name');
            $table->integer('capacity');
            $table->decimal('rent_price', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_car_types');
    }
};
