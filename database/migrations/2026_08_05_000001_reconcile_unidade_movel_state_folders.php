<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('estabelecimento_municipios_atuacao')
            || !Schema::hasTable('processos')
            || !Schema::hasTable('processo_pastas')) {
            return;
        }

        $nome = Schema::hasColumn('estabelecimento_municipios_atuacao', 'municipio_nome')
            ? 'ema.municipio_nome'
            : 'm.nome';

        $municipios = DB::table('estabelecimento_municipios_atuacao as ema')
            ->leftJoin('municipios as m', 'm.id', '=', 'ema.municipio_id')
            ->where('ema.competencia', 'estadual')
            ->whereNotNull(DB::raw($nome))
            ->selectRaw("ema.estabelecimento_id, {$nome} as municipio_nome")
            ->orderBy('ema.id')
            ->get();

        foreach ($municipios as $municipio) {
            $processoBase = DB::table('processos')
                ->where('estabelecimento_id', $municipio->estabelecimento_id)
                ->where('tipo', 'credenciamento_movel')
                ->whereIn('status', ['aberto', 'parado'])
                ->whereNull('deleted_at');

            $processo = (clone $processoBase)
                ->whereExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('processo_pastas')
                        ->whereColumn('processo_pastas.processo_id', 'processos.id')
                        ->where('processo_pastas.protegida', true);
                })
                ->orderByDesc('id')
                ->first();

            $processo ??= $processoBase->orderByDesc('id')->first();

            if (!$processo) {
                continue;
            }

            $pastaExiste = DB::table('processo_pastas')
                ->where('processo_id', $processo->id)
                ->where('protegida', true)
                ->whereRaw('LOWER(nome) = ?', [mb_strtolower($municipio->municipio_nome)])
                ->exists();

            if ($pastaExiste) {
                continue;
            }

            $maxOrdem = DB::table('processo_pastas')
                ->where('processo_id', $processo->id)
                ->max('ordem') ?? 0;

            DB::table('processo_pastas')->insert([
                'processo_id' => $processo->id,
                'nome' => $municipio->municipio_nome,
                'descricao' => "Documentos para o municipio de {$municipio->municipio_nome}",
                'protegida' => true,
                'ordem' => $maxOrdem + 1,
                'status' => 'aberta',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Data repair is intentionally irreversible because folders may receive files.
    }
};
