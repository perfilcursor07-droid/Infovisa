<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill: preenche os_id e atividade_index nas cópias de documentos
 * distribuídos em lote (fan-out) que não herdaram esses campos do original.
 *
 * Lógica: o documento original tem processos_ids (array) e os_id preenchido.
 * As cópias têm processo_id individual mas os_id NULL.
 * Identificamos as cópias pelo mesmo tipo_documento_id, usuario_criador_id,
 * nome e finalizado_em do original, vinculadas a processos do array processos_ids.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Busca documentos originais de lote (têm processos_ids e os_id)
        $originais = DB::table('documentos_digitais')
            ->whereNotNull('processos_ids')
            ->whereNotNull('os_id')
            ->whereNull('deleted_at')
            ->select('id', 'os_id', 'atividade_index', 'processos_ids',
                     'tipo_documento_id', 'usuario_criador_id', 'nome', 'finalizado_em')
            ->get();

        $totalCorrigidos = 0;

        foreach ($originais as $original) {
            $processosIds = json_decode($original->processos_ids, true);
            if (empty($processosIds)) continue;

            // Busca cópias: mesmo tipo/criador/nome/finalizado_em, vinculadas a um dos processos do lote, sem os_id
            $copiasAtualizadas = DB::table('documentos_digitais')
                ->whereIn('processo_id', $processosIds)
                ->where('tipo_documento_id', $original->tipo_documento_id)
                ->where('usuario_criador_id', $original->usuario_criador_id)
                ->where('nome', $original->nome)
                ->whereNull('os_id')
                ->whereNull('deleted_at')
                ->update([
                    'os_id' => $original->os_id,
                    'atividade_index' => $original->atividade_index,
                ]);

            $totalCorrigidos += $copiasAtualizadas;
        }

        \Illuminate\Support\Facades\Log::info("Backfill os_id em cópias de lote: {$totalCorrigidos} documentos corrigidos.");
    }

    public function down(): void
    {
        // Não é seguro reverter — deixa os campos como estão
    }
};
