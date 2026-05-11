<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cria a tabela de subcategorias vinculadas a um tipo de documento.
     * Exemplo: tipo "Alvará Sanitário" -> subcategorias "Provisório", "Administrativo", "Definitivo".
     */
    public function up(): void
    {
        Schema::create('tipo_documento_subcategorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_documento_id')
                ->constrained('tipo_documentos')
                ->cascadeOnDelete();
            $table->string('nome');
            $table->string('codigo')->nullable();
            $table->integer('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['tipo_documento_id', 'ativo']);
            $table->unique(['tipo_documento_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_documento_subcategorias');
    }
};
