<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona configuração do prazo de análise interna da vigilância por tipo de documento.
 *
 * Padrão: 5 dias corridos para a vigilância analisar a resposta do regulado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            // Prazo em dias para vigilância analisar resposta do regulado
            $table->integer('prazo_analise_dias')->default(5)->after('permite_resposta');
            // 'corridos' ou 'uteis'
            $table->string('tipo_prazo_analise', 10)->default('corridos')->after('prazo_analise_dias');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->dropColumn(['prazo_analise_dias', 'tipo_prazo_analise']);
        });
    }
};
