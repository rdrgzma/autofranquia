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
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('franchise_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['entrada', 'saida', 'ajuste', 'transferencia_entrada', 'transferencia_saida']);
    $table->decimal('quantity', 10, 3);
    $table->decimal('previous_quantity', 10, 3);
    $table->decimal('new_quantity', 10, 3);
    $table->string('reason');
    $table->text('notes')->nullable();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('reference_franchise_id')->nullable()->constrained('franchises')->nullOnDelete();
    $table->timestamps();

    $table->index(['franchise_id', 'created_at']);
    $table->index(['product_id', 'type']);

    });}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
