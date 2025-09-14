<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('document')->nullable();
            $table->string('document_type')->nullable(); // CPF / CNPJ
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('vehicle')->nullable();
            $table->json('address')->nullable();
            $table->foreignId('franchise_id')->nullable()->constrained('franchises')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
