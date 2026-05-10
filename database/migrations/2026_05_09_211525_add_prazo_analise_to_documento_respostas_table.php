<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona campos para controlar o PRAZO DE ANÁLISE da vigilância sanitária.
 *
 * Conceito: quando o regulado envia uma resposta (defesa, documentos, etc.),
 * a vigilância tem um prazo interno para analisar essa resposta.
 *
 * Esse prazo é independente do prazo original do documento (que é para o regulado responder).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documento_respostas', function (Blueprint $table) {
            // Quando o prazo de análise começou a contar (geralmente = created_at da resposta)
            $table->timestamp('prazo_analise_iniciado_em')->nullable()->after('avaliado_em');

            // Quantos dias a vigilância tem para analisar (cópia do tipo_documento.prazo_analise_dias)
            $table->integer('prazo_analise_dias')->nullable()->after('prazo_analise_iniciado_em');

            // Data limite para a análise ser feita (calculada a partir do início + dias)
            $table->date('prazo_analise_data_limite')->nullable()->after('prazo_analise_dias');

            // Prorrogação do prazo de análise (quando vigilância precisa de mais tempo)
            $table->integer('prazo_analise_prorrogado_dias')->nullable()->after('prazo_analise_data_limite');
            $table->timestamp('prazo_analise_prorrogado_em')->nullable()->after('prazo_analise_prorrogado_dias');
            $table->foreignId('prazo_analise_prorrogado_por')->nullable()->after('prazo_analise_prorrogado_em')
                ->constrained('usuarios_internos')->nullOnDelete();
            $table->text('prazo_analise_prorrogado_motivo')->nullable()->after('prazo_analise_prorrogado_por');

            $table->index('prazo_analise_data_limite');
        });
    }

    public function down(): void
    {
        Schema::table('documento_respostas', function (Blueprint $table) {
            $table->dropIndex(['prazo_analise_data_limite']);
            $table->dropConstrainedForeignId('prazo_analise_prorrogado_por');
            $table->dropColumn([
                'prazo_analise_iniciado_em',
                'prazo_analise_dias',
                'prazo_analise_data_limite',
                'prazo_analise_prorrogado_dias',
                'prazo_analise_prorrogado_em',
                'prazo_analise_prorrogado_motivo',
            ]);
        });
    }
};
