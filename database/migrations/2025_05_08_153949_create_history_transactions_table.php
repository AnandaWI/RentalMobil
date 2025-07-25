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
        Schema::create('history_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');
            $table->enum('transaction_type', ['in', 'out']);
            $table->uuid('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_now', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_transactions');
    }
};
