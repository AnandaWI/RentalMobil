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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('destination_id')->constrained('m_destinations')->onDelete('cascade');
            $table->integer('day');
            $table->decimal('total_price', 10, 2);
            $table->date('rent_date');
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'failed', 'success', 'done'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
