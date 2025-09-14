<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('franchise_id')->constrained('franchises')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->string('location')->nullable(); // ex: Estoque A1
            $table->integer('min_stock')->default(0);
            $table->timestamps();

            $table->unique(['product_id','franchise_id'], 'inventory_product_franchise_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories');
    }
}
