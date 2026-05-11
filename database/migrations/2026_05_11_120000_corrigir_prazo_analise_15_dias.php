<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Corrige o prazo de análise do técnico de 5 para 15 dias.
 * 
 * O prazo_analise_dias é o tempo que o técnico da vigilância tem para analisar
 * a resposta do estabelecimento. Estava com default 5, mas o correto é 15 dias.
 * 
 * Isso NÃO afeta o prazo_padrao_dias (prazo para empresa responder).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Alterar o default da coluna prazo_analise_dias na tabela tipo_documentos de 5 para 15
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->integer('prazo_analise_dias')->default(15)->change();
        });

        // 2. Atualizar TODOS os tipos de documento que estão com prazo_analise_dias = 5 para 15
        DB::table('tipo_documentos')
            ->where('prazo_analise_dias', 5)
            ->update(['prazo_analise_dias' => 15]);

        // 3. Recalcular prazo_analise_data_limite de TODAS as respostas pendentes que tinham 5 dias
        $respostas = DB::table('documento_respostas')
            ->where('status', 'pendente')
            ->where('prazo_analise_dias', 5)
            ->whereNotNull('prazo_analise_iniciado_em')
            ->select('id', 'prazo_analise_iniciado_em')
            ->get();

        foreach ($respostas as $resposta) {
            $inicio = Carbon::parse($resposta->prazo_analise_iniciado_em)->startOfDay();
            $novaDataLimite = $inicio->copy()->addDays(15)->toDateString();

            DB::table('documento_respostas')
                ->where('id', $resposta->id)
                ->update([
                    'prazo_analise_dias' => 15,
                    'prazo_analise_data_limite' => $novaDataLimite,
                ]);
        }
    }

    public function down(): void
    {
        // Reverter default
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->integer('prazo_analise_dias')->default(5)->change();
        });

        // Reverter tipos de documento para 5 dias
        DB::table('tipo_documentos')
            ->where('prazo_analise_dias', 15)
            ->update(['prazo_analise_dias' => 5]);

        // Recalcular prazos das respostas pendentes de volta para 5 dias
        $respostas = DB::table('documento_respostas')
            ->where('status', 'pendente')
            ->where('prazo_analise_dias', 15)
            ->whereNotNull('prazo_analise_iniciado_em')
            ->select('id', 'prazo_analise_iniciado_em')
            ->get();

        foreach ($respostas as $resposta) {
            $inicio = Carbon::parse($resposta->prazo_analise_iniciado_em)->startOfDay();
            $dataLimite = $inicio->copy()->addDays(5)->toDateString();

            DB::table('documento_respostas')
                ->where('id', $resposta->id)
                ->update([
                    'prazo_analise_dias' => 5,
                    'prazo_analise_data_limite' => $dataLimite,
                ]);
        }
    }
};
