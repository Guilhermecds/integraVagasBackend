<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('vacina', function (Blueprint $table) {
            $table->id();
            $table->string('nome_vacina');
            $table->integer('quantidade_disponivel')->unsigned();
            $table->date('data_limite_vacinacao')->nullable();
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('vacinas');
    }
};