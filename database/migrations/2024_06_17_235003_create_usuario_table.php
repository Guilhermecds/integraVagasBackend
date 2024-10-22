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
        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->boolean('sou_deficiente')->default(false);
            $table->date('data_nascimento');
            $table->foreignId('idtipousuario')->constrained('tipousuario');
            $table->string('email')->unique();
            $table->string('telefone')->nullable();
            $table->string('cpf')->unique(); 
            $table->string('cep')->nullable(); 
            $table->string('logradouro')->nullable(); 
            $table->string('numero')->nullable(); 
            $table->string('complemento')->nullable(); 
            $table->string('cidade')->nullable(); 
            $table->string('curriculo')->nullable();
            $table->boolean('idsituacaousuario');
            $table->foreignId('idformacao')->constrained('formacao');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};