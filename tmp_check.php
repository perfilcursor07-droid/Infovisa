<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = \App\Models\UsuarioInterno::where('nome', 'like', '%Erick Vinicius%')
    ->orWhere('nome', 'like', '%erick vinicius%')
    ->first();

if (!$u) {
    echo "Usuário não encontrado\n";
    exit;
}

echo "Usuário: {$u->id} - {$u->nome}\n";
echo "Nível: " . $u->nivel_acesso->value . "\n";
echo "Setores: " . json_encode($u->getSetoresCodigos()) . "\n";
echo "Município ID: " . ($u->municipio_id ?? 'null') . "\n";
echo "isEstadual: " . ($u->isEstadual() ? 'sim' : 'não') . "\n";
echo "isMunicipal: " . ($u->isMunicipal() ? 'sim' : 'não') . "\n";
echo "isAdmin: " . ($u->isAdmin() ? 'sim' : 'não') . "\n";

// Busca o processo 138 e ve qual o setor
$processo = \App\Models\Processo::where('id', 138)
    ->with(['tipoProcesso.tipoSetor', 'tipoProcesso.setoresMunicipais.tipoSetor', 'estabelecimento'])
    ->first();

if ($processo) {
    echo "\n=== Processo 138 ===\n";
    echo "Tipo: {$processo->tipo}\n";
    echo "Setor atual: {$processo->setor_atual}\n";
    echo "Responsável atual id: " . ($processo->responsavel_atual_id ?? 'null') . "\n";
    echo "Estabelecimento: {$processo->estabelecimento->nome_fantasia}\n";
    echo "Município estab: " . ($processo->estabelecimento->municipio_id ?? 'null') . "\n";
    echo "Competência estadual? " . ($processo->estabelecimento->isCompetenciaEstadual() ? 'sim' : 'não') . "\n";
    echo "Tipo setor (análise inicial estadual): " . ($processo->tipoProcesso->tipoSetor->codigo ?? 'null') . "\n";

    echo "\nSetores municipais mapeados:\n";
    foreach ($processo->tipoProcesso->setoresMunicipais ?? [] as $sm) {
        echo "  - municipio_id={$sm->municipio_id}, tipo_setor={$sm->tipoSetor->codigo}\n";
    }
}

// Simular query de documentos pendentes do usuário
echo "\n=== Teste do filtro ===\n";
$controller = new class {
    public function testar($u) {
        $q = \App\Models\ProcessoDocumento::where('status_aprovacao', 'pendente')
            ->where('tipo_usuario', 'externo');
        
        $setoresUsuario = $u->getSetoresCodigos();
        $q->whereHas('processo', function($p) use ($u, $setoresUsuario) {
            $p->where(function ($q) use ($u, $setoresUsuario) {
                $q->where('responsavel_atual_id', $u->id);
                if (!empty($setoresUsuario)) {
                    $q->orWhereIn('setor_atual', $setoresUsuario);
                    $q->orWhereHas('tipoProcesso', function ($tp) use ($setoresUsuario, $u) {
                        $tp->where(function ($tpq) use ($setoresUsuario, $u) {
                            $tpq->whereHas('tipoSetor', function ($ts) use ($setoresUsuario) {
                                $ts->whereIn('codigo', $setoresUsuario);
                            });
                            if ($u->isMunicipal() && $u->municipio_id) {
                                $tpq->orWhereHas('setoresMunicipais', function ($sm) use ($setoresUsuario, $u) {
                                    $sm->where('municipio_id', $u->municipio_id)
                                        ->whereHas('tipoSetor', function ($ts) use ($setoresUsuario) {
                                            $ts->whereIn('codigo', $setoresUsuario);
                                        });
                                });
                            }
                        });
                    });
                }
            });
        });

        return $q;
    }
};

$query = $controller->testar($u);
echo "SQL: " . $query->toSql() . "\n";
echo "Bindings: " . json_encode($query->getBindings()) . "\n";
echo "Total: " . $query->count() . "\n";

$docs = $query->limit(20)->get(['id', 'processo_id', 'tipo_documento_obrigatorio_id', 'nome_original', 'status_aprovacao']);
foreach ($docs as $d) {
    echo "  - [{$d->id}] proc={$d->processo_id} obrig_id=" . ($d->tipo_documento_obrigatorio_id ?? 'null') . " nome={$d->nome_original}\n";
}
