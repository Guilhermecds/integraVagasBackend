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
        Schema::create('usuariovaga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idusuario')->constrained('usuario');
            $table->foreignId('idvaga')->constrained('vaga');
            $table->foreignId('idempresa')->constrained('empresa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('usuariovaga');
    }
};