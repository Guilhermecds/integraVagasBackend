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
        Schema::create('empresa', function (Blueprint $table) {
            $table->id();
            $table->string('nome_fantasia');
            $table->string('razao_social');
            $table->string('cnpj')->unique();
            $table->string('telefone_corporativo');
            $table->string('email')->unique();
            $table->string('cep');
            $table->string('logradouro');
            $table->string('numero');
            $table->string('bairro'); 
            $table->string('complemento')->nullable();
            $table->string('cidade');
            $table->string('uf');
            $table->text('foto')->nullable();
            $table->boolean('idsituacaoempresa')->default(true);
            $table->foreignId('idtipousuario')->constrained('tipousuario');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};