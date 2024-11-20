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
        Schema::create('vaga', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->boolean('vaga_apenas_deficiente')->default(false);
            $table->string('cep');
            $table->string('logradouro');
            $table->string('numero');
            $table->string('bairro'); 
            $table->string('complemento')->nullable();
            $table->string('cidade');
            $table->string('uf');
            $table->text('descricao');
            $table->foreignId('idempresa')->constrained('empresa');
            $table->boolean('idsituacaovaga')->default(true);
            $table->foreignId('idformacao')->constrained('formacao');
            $table->text('requisitos_bonus');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaga');
    }
};