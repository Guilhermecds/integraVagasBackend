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
            $table->date('data_nascimento')->nullable();
            $table->foreignId('idtipousuario')->constrained('tipousuario');
            $table->string('email')->unique();
            $table->string('telefone');
            $table->string('cpf')->unique(); 
            $table->string('cep'); 
            $table->string('logradouro'); 
            $table->string('numero'); 
            $table->string('bairro'); 
            $table->string('complemento')->nullable(); 
            $table->string('uf'); 
            $table->string('cidade'); 
            $table->string('curriculo')->nullable();
            $table->boolean('idsituacaousuario')->default(true);
            $table->text('foto')->nullable();
            $table->foreignId('idformacao')->nullable()->constrained('formacao');
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