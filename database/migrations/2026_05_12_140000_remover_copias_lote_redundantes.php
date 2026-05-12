<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Remove as cópias redundantes criadas pelo fan-out de documentos de lote.
 * 
 * O sistema agora mostra o documento original em todos os processos via processos_ids.
 * As cópias individuais por processo não são mais necessárias.
 * 
 * Critério de identificação das cópias:
 * - Têm processo_id dentro do array processos_ids do original
 * - Têm o mesmo tipo_documento_id, usuario_criador_id, nome e os_id do original
 * - NÃO têm processos_ids (são cópias, não originais)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Busca documentos originais de lote
        $originais = DB::table('documentos_digitais')
            ->whereNotNull('processos_ids')
            ->whereNotNull('os_id')
            ->whereNull('deleted_at')
            ->select('id', 'numero_documento', 'os_id', 'processos_ids',
                     'tipo_documento_id', 'usuario_criador_id', 'nome')
            ->get();

        $totalRemovidos = 0;

        foreach ($originais as $original) {
            $processosIds = json_decode($original->processos_ids, true);
            if (empty($processosIds)) continue;

            // Busca as cópias: mesmo tipo/criador/nome/os_id, vinculadas a processos do lote, sem processos_ids
            $copias = DB::table('documentos_digitais')
                ->whereIn('processo_id', $processosIds)
                ->where('tipo_documento_id', $original->tipo_documento_id)
                ->where('usuario_criador_id', $original->usuario_criador_id)
                ->where('nome', $original->nome)
                ->where('os_id', $original->os_id)
                ->where('id', '!=', $original->id)
                ->whereNull('processos_ids')
                ->whereNull('deleted_at')
                ->select('id', 'numero_documento', 'arquivo_pdf')
                ->get();

            foreach ($copias as $copia) {
                // Remove assinaturas vinculadas
                DB::table('documento_assinaturas')
                    ->where('documento_digital_id', $copia->id)
                    ->delete();

                // Remove respostas vinculadas
                DB::table('documento_respostas')
                    ->where('documento_digital_id', $copia->id)
                    ->delete();

                // Remove versões vinculadas
                DB::table('documento_digital_versoes')
                    ->where('documento_digital_id', $copia->id)
                    ->delete();

                // Remove eventos de processo que referenciam esta cópia (se existir coluna)
                // DB::table('processo_eventos')->where('documento_digital_id', $copia->id)->delete();

                // Remove ProcessoDocumento vinculado à cópia
                DB::table('processo_documentos')
                    ->where('observacoes', 'Documento Digital: ' . $copia->numero_documento)
                    ->delete();

                // Remove o arquivo PDF do storage se existir
                if ($copia->arquivo_pdf && Storage::disk('public')->exists($copia->arquivo_pdf)) {
                    Storage::disk('public')->delete($copia->arquivo_pdf);
                }

                // Soft delete da cópia
                DB::table('documentos_digitais')
                    ->where('id', $copia->id)
                    ->update(['deleted_at' => now()]);

                $totalRemovidos++;
            }
        }

        Log::info("Remoção de cópias de lote: {$totalRemovidos} cópias removidas.");
    }

    public function down(): void
    {
        // Não é possível reverter a remoção de forma segura
        // Os documentos foram soft-deleted, então podem ser restaurados manualmente se necessário
    }
};
