<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Corrige o prazo de análise padrão de 5 para 15 dias.
 * 
 * - Documentos de licenciamento mantêm 5 dias (configurado no tipo_documento)
 * - Demais documentos (notificação, etc.) passam a ter 15 dias como padrão
 * - Recalcula prazo_analise_data_limite das respostas pendentes afetadas
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Alterar o default da coluna prazo_analise_dias na tabela tipo_documentos
        Schema::table('tipo_documentos', function (Blueprint $table) {
            $table->integer('prazo_analise_dias')->default(15)->change();
        });

        // 2. Atualizar tipos de documento que ainda estão com 5 dias (exceto os de licenciamento)
        // Tipos que NÃO são exclusivos de licenciamento recebem 15 dias
        DB::table('tipo_documentos')
            ->where('prazo_analise_dias', 5)
            ->whereNotIn('id', function ($query) {
                // Mantém 5 dias apenas para tipos usados exclusivamente em licenciamento
                $query->select('tipo_documento_id')
                    ->from('documentos_digitais')
                    ->join('processos', 'processos.id', '=', 'documentos_digitais.processo_id')
                    ->where('processos.tipo', 'licenciamento')
                    ->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('documentos_digitais as dd2')
                            ->join('processos as p2', 'p2.id', '=', 'dd2.processo_id')
                            ->whereColumn('dd2.tipo_documento_id', 'documentos_digitais.tipo_documento_id')
                            ->where('p2.tipo', '!=', 'licenciamento');
                    })
                    ->distinct();
            })
            ->update(['prazo_analise_dias' => 15]);

        // 3. Recalcular prazo_analise_data_limite das respostas pendentes que tinham 5 dias
        //    mas cujo processo NÃO é de licenciamento
        $respostas = DB::table('documento_respostas as dr')
            ->join('documentos_digitais as dd', 'dd.id', '=', 'dr.documento_digital_id')
            ->join('processos as p', 'p.id', '=', 'dd.processo_id')
            ->where('dr.status', 'pendente')
            ->where('dr.prazo_analise_dias', 5)
            ->where('p.tipo', '!=', 'licenciamento')
            ->select('dr.id', 'dr.prazo_analise_iniciado_em')
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
        $respostas = DB::table('documento_respostas as dr')
            ->join('documentos_digitais as dd', 'dd.id', '=', 'dr.documento_digital_id')
            ->join('processos as p', 'p.id', '=', 'dd.processo_id')
            ->where('dr.status', 'pendente')
            ->where('dr.prazo_analise_dias', 15)
            ->where('p.tipo', '!=', 'licenciamento')
            ->select('dr.id', 'dr.prazo_analise_iniciado_em')
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
