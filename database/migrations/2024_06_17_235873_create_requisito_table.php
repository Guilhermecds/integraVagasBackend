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
        Schema::create('requisito', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->foreignId('idempresa')->nullable()->constrained('empresa');
            $table->boolean('idsituacaorequisito')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisito');
    }
};