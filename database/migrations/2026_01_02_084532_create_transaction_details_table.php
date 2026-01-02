<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            // Dia merujuk ke tabel 'transactions' (pakai 's' di belakang)
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->string('item_name');
            $table->integer('price');
            $table->integer('qty');
            $table->integer('subtotal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
