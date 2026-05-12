<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Expande o número do processo de 5 dígitos (2026/00211) para 9 dígitos (2026/000000211).
 * Atualiza todos os registros existentes para o novo formato.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Atualiza todos os processos existentes para o formato de 9 dígitos
        $processos = DB::table('processos')
            ->whereNotNull('numero_processo')
            ->select('id', 'numero_processo')
            ->get();

        foreach ($processos as $processo) {
            $partes = explode('/', $processo->numero_processo);
            
            if (count($partes) === 2) {
                $ano = $partes[0];
                $numero = (int) $partes[1];
                $novoNumero = sprintf('%s/%09d', $ano, $numero);

                if ($novoNumero !== $processo->numero_processo) {
                    DB::table('processos')
                        ->where('id', $processo->id)
                        ->update(['numero_processo' => $novoNumero]);
                }
            }
        }
    }

    public function down(): void
    {
        // Reverte para formato de 5 dígitos
        $processos = DB::table('processos')
            ->whereNotNull('numero_processo')
            ->select('id', 'numero_processo')
            ->get();

        foreach ($processos as $processo) {
            $partes = explode('/', $processo->numero_processo);
            
            if (count($partes) === 2) {
                $ano = $partes[0];
                $numero = (int) $partes[1];
                $novoNumero = sprintf('%s/%05d', $ano, $numero);

                if ($novoNumero !== $processo->numero_processo) {
                    DB::table('processos')
                        ->where('id', $processo->id)
                        ->update(['numero_processo' => $novoNumero]);
                }
            }
        }
    }
};
