<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Backfill: preenche o prazo de análise das respostas que já existem no banco.
 * Usa 5 dias corridos a partir da data de criação como padrão.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Atualiza respostas antigas que ainda não têm prazo definido
        $respostas = DB::table('documento_respostas')
            ->whereNull('prazo_analise_iniciado_em')
            ->select('id', 'created_at', 'documento_digital_id')
            ->get();

        foreach ($respostas as $resposta) {
            // Tenta buscar prazo específico do tipo de documento
            $prazoDias = DB::table('documentos_digitais as dd')
                ->join('tipo_documentos as td', 'dd.tipo_documento_id', '=', 'td.id')
                ->where('dd.id', $resposta->documento_digital_id)
                ->value('td.prazo_analise_dias');

            $prazoDias = $prazoDias ?: 5; // Padrão 5 dias

            $inicio = Carbon::parse($resposta->created_at);
            $dataLimite = $inicio->copy()->startOfDay()->addDays($prazoDias)->toDateString();

            DB::table('documento_respostas')
                ->where('id', $resposta->id)
                ->update([
                    'prazo_analise_iniciado_em' => $resposta->created_at,
                    'prazo_analise_dias' => $prazoDias,
                    'prazo_analise_data_limite' => $dataLimite,
                ]);
        }
    }

    public function down(): void
    {
        DB::table('documento_respostas')->update([
            'prazo_analise_iniciado_em' => null,
            'prazo_analise_dias' => null,
            'prazo_analise_data_limite' => null,
        ]);
    }
};
