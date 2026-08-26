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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('total_amount');
            $table->integer('grand_total');
            $table->string('payment_method'); // Cash atau QRIS
            $table->integer('amount_paid')->default(0); // Uang yang diberi
            $table->integer('change_amount')->default(0); // Kembalian
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
