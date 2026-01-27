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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            // Usuario que provocó el movimiento (puede ser null si es sistema, o admin)
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->enum('type', ['entry', 'exit', 'sale', 'return']); // tipo_movimiento
            $table->integer('quantity');
            $table->string('reason')->nullable(); // motivo
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
