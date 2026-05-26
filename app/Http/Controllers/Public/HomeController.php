<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoProcesso;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\ListaDocumento;
use App\Models\Atividade;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Exibe a página inicial pública
     */
    public function index()
    {
        return view('public.home');
    }

    /**
     * Exibe a página de fila de processos
     */
    public function filaProcessos()
    {
        // Busca tipos de processo com fila pública ativada
        $tiposComFilaPublica = TipoProcesso::where('exibir_fila_publica', true)
            ->where('ativo', true)
            ->ordenado()
            ->get();

        // Para cada tipo, busca os processos que têm todos os documentos obrigatórios aprovados
        $filaProcessos = [];
        foreach ($tiposComFilaPublica as $tipo) {
            $processos = Processo::where('tipo', $tipo->codigo)
                ->whereIn('status', ['aberto', 'em_analise', 'pendente', 'parado'])
                ->with(['estabelecimento:id,nome_fantasia,razao_social,cnpj,cpf,nome_completo,atividades_exercidas,tipo_setor,municipio_id', 'unidades', 'pastas', 'documentos'])
                ->orderBy('created_at', 'asc') // Mais antigo primeiro
                ->get();

            $processosAptos = [];
            foreach ($processos as $processo) {
                // Se o processo tem pastas, verifica se todas estão concluídas
                // Se todas concluídas, processo sai da fila (não é apto)
                $todasPastas = $processo->pastas;
                if ($todasPastas->isNotEmpty()) {
                    $pastasAtivas = $todasPastas->where('status', '!=', 'concluida');
                    if ($pastasAtivas->isEmpty()) {
                        // Todas as pastas/unidades foram concluídas — sai da fila
                        continue;
                    }
                }

                // Verifica se todos os documentos obrigatórios estão aprovados
                $statusDocs = $this->verificarDocumentosObrigatorios($processo, $tipo);

                if ($statusDocs['completo']) {
                    $dataDocumentosCompletos = $statusDocs['data_ultimo_aprovado'] ?? $processo->created_at;
                    $dataRef = $processo->getDataReferenciaFilaPublica($dataDocumentosCompletos);
                    $hoje = Carbon::now();
                    $tempoTotalSegundos = max(0, $dataRef->diffInSeconds($hoje) - $processo->getTempoTotalParadoConsiderandoParadaAtual());
                    
                    // Considera apenas o tempo efetivo em fila, desconsiderando períodos parados.
                    $dias = intdiv($tempoTotalSegundos, 86400);
                    $horas = intdiv($tempoTotalSegundos % 86400, 3600);
                    
                    // Calcula prazo restante
                    $grupoRisco = $processo->estabelecimento ? $processo->estabelecimento->getGrupoRisco() : null;
                    $prazo = $tipo->getPrazoFilaPublicaPorRisco($grupoRisco);
                    if ($prazo) {
                        $dataLimite = $processo->calcularDataLimiteFilaPublica($dataRef, $prazo);
                        $diasRestantes = (int) round(Carbon::now()->diffInDays($dataLimite, false));
                    } else {
                        $diasRestantes = null;
                    }
                    
                    $processosAptos[] = [
                        'numero_processo' => $processo->numero_processo,
                        'estabelecimento' => $processo->estabelecimento ? 
                            ($processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->nome_completo ?? 'Não informado') : 
                            'Não vinculado',
                        'status' => $processo->status,
                        'data_abertura' => Carbon::parse($processo->created_at)->format('d/m/Y H:i'),
                        'data_documentos_completos' => Carbon::parse($dataDocumentosCompletos)->format('d/m/Y H:i'),
                        'data_referencia_prazo' => $dataRef->format('d/m/Y H:i'),
                        'dias_decorridos' => $dias,
                        'horas_decorridas' => $horas,
                        'tempo_formatado' => $dias > 0 ? "{$dias}d {$horas}h" : "{$horas}h",
                        'prazo' => $prazo,
                        'dias_restantes' => $diasRestantes,
                        'atrasado' => $prazo ? $diasRestantes < 0 : false,
                        'pausado' => $processo->status === 'parado',
                        'prazo_reiniciado' => $processo->prazoFilaPublicaFoiReiniciado($dataDocumentosCompletos),
                        'data_referencia_prazo_sort' => $dataRef->timestamp,
                        'unidades_prazo' => $this->calcularPrazosUnidades($processo, $tipo, $prazo),
                    ];
                }
            }

            // Ordena pela referência atual do prazo (mais antiga primeiro) e adiciona posição
            usort($processosAptos, function($a, $b) {
                return $a['data_referencia_prazo_sort'] <=> $b['data_referencia_prazo_sort'];
            });
            
            foreach ($processosAptos as $index => &$proc) {
                $proc['posicao'] = $index + 1;
            }

            if (count($processosAptos) > 0) {
                $filaProcessos[] = [
                    'tipo' => $tipo->nome,
                    'codigo' => $tipo->codigo,
                    'prazo_analise' => $tipo->prazo_fila_publica,
                    'processos' => $processosAptos
                ];
            }
        }

        return view('public.fila-processos', compact('filaProcessos'));
    }

    /**
     * Calcula prazos por unidade para um processo
     */
    private function calcularPrazosUnidades($processo, $tipoProcesso, $prazo)
    {
        $unidadesPrazo = [];
        // Considera todas as pastas, exceto as concluídas
        $pastasUnidade = $processo->pastas->where('status', '!=', 'concluida');
        if ($pastasUnidade->isEmpty() || !$prazo) return $unidadesPrazo;

        // Busca os tipos de documento obrigatório do processo (mesma lógica do verificarDocumentosObrigatorios)
        $tiposObrigatorios = $this->buscarTiposDocumentoObrigatorios($processo, $tipoProcesso);

        // Para cada pasta, verifica se TODOS os docs obrigatórios estão aprovados naquela pasta
        foreach ($pastasUnidade as $pasta) {
            $docsAprovadosNaPasta = $processo->documentos
                ->where('pasta_id', $pasta->id)
                ->where('status_aprovacao', 'aprovado')
                ->whereNotNull('tipo_documento_obrigatorio_id');

            if ($docsAprovadosNaPasta->isEmpty()) continue;

            // Verifica se todos os tipos obrigatórios têm documento aprovado nesta pasta
            if ($tiposObrigatorios->isNotEmpty()) {
                $tiposAprovadosNaPasta = $docsAprovadosNaPasta->pluck('tipo_documento_obrigatorio_id')->unique();
                $faltaAlgum = $tiposObrigatorios->pluck('id')->diff($tiposAprovadosNaPasta)->isNotEmpty();
                if ($faltaAlgum) {
                    // Tem doc obrigatório pendente/não aprovado nesta unidade — não conta para fila
                    continue;
                }
            }

            // Pega a data do último doc aprovado na pasta
            $dataUltimoAprov = $docsAprovadosNaPasta
                ->sortByDesc(fn ($d) => $d->aprovado_em ?? $d->updated_at)
                ->first();
            $dataRef = $dataUltimoAprov->aprovado_em ?? $dataUltimoAprov->updated_at;

            if (!$dataRef) continue;

            $dataRefPrazo = $processo->getDataReferenciaFilaPublica($dataRef);
            $dataLimite = $processo->calcularDataLimiteFilaPublica($dataRef, $prazo);
            $diasRestantes = (int) round(Carbon::now()->diffInDays($dataLimite, false));

            $unidadesPrazo[] = [
                'nome' => $pasta->nome,
                'dias_restantes' => $diasRestantes,
                'atrasado' => $diasRestantes < 0,
                'pausado' => $processo->status === 'parado',
            ];
        }

        return $unidadesPrazo;
    }

    /**
     * Verifica se todos os documentos obrigatórios de um processo estão aprovados
     * Retorna array com status e data do último documento aprovado
     */
    private function verificarDocumentosObrigatorios($processo, $tipoProcesso)
    {
        $estabelecimento = $processo->estabelecimento;
        $tipoProcessoId = $tipoProcesso->id ?? null;
        
        if (!$tipoProcessoId || !$estabelecimento) {
            return ['completo' => false, 'data_ultimo_aprovado' => null];
        }

        // Verifica se é um processo especial (Projeto Arquitetônico ou Análise de Rotulagem)
        $isProcessoEspecial = in_array($tipoProcesso->codigo, ['projeto_arquitetonico', 'analise_rotulagem']);

        // Pega as atividades exercidas do estabelecimento
        $atividadesExercidas = $estabelecimento->atividades_exercidas ?? [];
        
        // Para processos especiais, não precisa de atividades
        if (!$isProcessoEspecial && empty($atividadesExercidas)) {
            return ['completo' => false, 'data_ultimo_aprovado' => null];
        }

        $atividadeIds = collect();
        
        // Só busca atividades se não for processo especial e tiver atividades exercidas
        if (!$isProcessoEspecial && !empty($atividadesExercidas)) {
            $codigosCnae = collect($atividadesExercidas)->map(function($atividade) {
                $codigo = is_array($atividade) ? ($atividade['codigo'] ?? null) : $atividade;
                return $codigo ? preg_replace('/[^0-9]/', '', $codigo) : null;
            })->filter()->values()->toArray();

            if (!empty($codigosCnae)) {
                $atividadeIds = Atividade::where('ativo', true)
                    ->where(function($query) use ($codigosCnae) {
                        foreach ($codigosCnae as $codigo) {
                            $query->orWhere('codigo_cnae', $codigo);
                        }
                    })
                    ->pluck('id');
            }
        }

        // Busca listas de documentos aplicáveis
        $listasQuery = ListaDocumento::where('ativo', true)
            ->where('tipo_processo_id', $tipoProcessoId)
            ->with(['tiposDocumentoObrigatorio' => function($q) {
                $q->orderBy('lista_documento_tipo.ordem');
            }]);
            
        // Para processos especiais: busca listas SEM atividades vinculadas
        // Para processos normais: busca listas COM atividades que correspondem às do estabelecimento
        if ($isProcessoEspecial) {
            $listasQuery->whereDoesntHave('atividades');
        } else {
            if ($atividadeIds->isEmpty()) {
                return ['completo' => false, 'data_ultimo_aprovado' => null];
            }
            $listasQuery->whereHas('atividades', function($q) use ($atividadeIds) {
                $q->whereIn('atividades.id', $atividadeIds);
            });
        }

        // Filtra por escopo (estadual ou do município do estabelecimento)
        $listasQuery->where(function($q) use ($estabelecimento) {
            $q->where('escopo', 'estadual');
            if ($estabelecimento->municipio_id) {
                $q->orWhere(function($q2) use ($estabelecimento) {
                    $q2->where('escopo', 'municipal')
                       ->where('municipio_id', $estabelecimento->municipio_id);
                });
            }
        });

        $listas = $listasQuery->get();

        // Coleta todos os tipos de documento obrigatório das listas
        $docsObrigatorios = collect();
        foreach ($listas as $lista) {
            foreach ($lista->tiposDocumentoObrigatorio as $tipoDoc) {
                // Só adiciona se for obrigatório (pivot) e aplicável ao tipo de setor do estabelecimento
                $tipoSetorEnum = $estabelecimento->tipo_setor;
                $tipoSetor = $tipoSetorEnum instanceof \App\Enums\TipoSetor ? $tipoSetorEnum->value : ($tipoSetorEnum ?? 'privado');
                
                if ($tipoDoc->pivot->obrigatorio && $tipoDoc->aplicaAoTipoSetor($tipoSetor) && !$docsObrigatorios->contains('id', $tipoDoc->id)) {
                    $docsObrigatorios->push($tipoDoc);
                }
            }
        }

        if ($docsObrigatorios->isEmpty()) {
            // Se não tem documentos obrigatórios, considera como completo pela data de abertura
            return ['completo' => true, 'data_ultimo_aprovado' => $processo->created_at];
        }

        // Verifica o status de cada documento obrigatório no processo
        // Considera apenas documentos que NÃO estão em pasta vinculada a unidade
        // (docs do processo principal/raiz) e NÃO estão em pasta concluída
        $pastasUnidadeIds = $processo->pastas->whereNotNull('unidade_id')->pluck('id')->toArray();
        $pastasConcluidasIds = $processo->pastas->where('status', 'concluida')->pluck('id')->toArray();

        $todosAprovados = true;
        $dataUltimoAprovado = null;
        
        foreach ($docsObrigatorios as $docObrigatorio) {
            $documentoQuery = ProcessoDocumento::where('processo_id', $processo->id)
                ->where('tipo_documento_obrigatorio_id', $docObrigatorio->id)
                ->where('status_aprovacao', 'aprovado');

            if (!empty($pastasUnidadeIds)) {
                $documentoQuery->where(function ($q) use ($pastasUnidadeIds) {
                    $q->whereNull('pasta_id')
                      ->orWhereNotIn('pasta_id', $pastasUnidadeIds);
                });
            }

            if (!empty($pastasConcluidasIds)) {
                $documentoQuery->where(function ($q) use ($pastasConcluidasIds) {
                    $q->whereNull('pasta_id')
                      ->orWhereNotIn('pasta_id', $pastasConcluidasIds);
                });
            }

            $documento = $documentoQuery
                ->orderByRaw('COALESCE(aprovado_em, updated_at) DESC')
                ->first();
            
            if (!$documento) {
                $todosAprovados = false;
                break;
            }
            
            // Guarda a data mais recente de aprovação
            $dataReferenciaAprovacao = $documento->aprovado_em ?? $documento->updated_at;

            if ($dataReferenciaAprovacao && (!$dataUltimoAprovado || $dataReferenciaAprovacao > $dataUltimoAprovado)) {
                $dataUltimoAprovado = $dataReferenciaAprovacao;
            }
        }

        return [
            'completo' => $todosAprovados,
            'data_ultimo_aprovado' => $dataUltimoAprovado
        ];
    }

    /**
     * Retorna a coleção de tipos de documento obrigatórios para um processo (sem checar aprovação).
     * Reaproveita a mesma lógica de verificarDocumentosObrigatorios mas só retorna os tipos.
     */
    private function buscarTiposDocumentoObrigatorios($processo, $tipoProcesso)
    {
        $estabelecimento = $processo->estabelecimento;
        $tipoProcessoId = $tipoProcesso->id ?? null;

        if (!$tipoProcessoId || !$estabelecimento) {
            return collect();
        }

        $isProcessoEspecial = in_array($tipoProcesso->codigo, ['projeto_arquitetonico', 'analise_rotulagem']);
        $atividadesExercidas = $estabelecimento->atividades_exercidas ?? [];

        if (!$isProcessoEspecial && empty($atividadesExercidas)) {
            return collect();
        }

        $atividadeIds = collect();
        if (!$isProcessoEspecial && !empty($atividadesExercidas)) {
            $codigosCnae = collect($atividadesExercidas)->map(function($atividade) {
                $codigo = is_array($atividade) ? ($atividade['codigo'] ?? null) : $atividade;
                return $codigo ? preg_replace('/[^0-9]/', '', $codigo) : null;
            })->filter()->values()->toArray();

            if (!empty($codigosCnae)) {
                $atividadeIds = Atividade::where('ativo', true)
                    ->where(function($query) use ($codigosCnae) {
                        foreach ($codigosCnae as $codigo) {
                            $query->orWhere('codigo_cnae', $codigo);
                        }
                    })
                    ->pluck('id');
            }
        }

        $listasQuery = ListaDocumento::where('ativo', true)
            ->where('tipo_processo_id', $tipoProcessoId)
            ->with(['tiposDocumentoObrigatorio' => function($q) {
                $q->orderBy('lista_documento_tipo.ordem');
            }]);

        if ($isProcessoEspecial) {
            $listasQuery->whereDoesntHave('atividades');
        } else {
            if ($atividadeIds->isEmpty()) {
                return collect();
            }
            $listasQuery->whereHas('atividades', function($q) use ($atividadeIds) {
                $q->whereIn('atividades.id', $atividadeIds);
            });
        }

        $listasQuery->where(function($q) use ($estabelecimento) {
            $q->where('escopo', 'estadual');
            if ($estabelecimento->municipio_id) {
                $q->orWhere(function($q2) use ($estabelecimento) {
                    $q2->where('escopo', 'municipal')
                       ->where('municipio_id', $estabelecimento->municipio_id);
                });
            }
        });

        $listas = $listasQuery->get();

        $docsObrigatorios = collect();
        foreach ($listas as $lista) {
            foreach ($lista->tiposDocumentoObrigatorio as $tipoDoc) {
                $tipoSetorEnum = $estabelecimento->tipo_setor;
                $tipoSetor = $tipoSetorEnum instanceof \App\Enums\TipoSetor ? $tipoSetorEnum->value : ($tipoSetorEnum ?? 'privado');

                if ($tipoDoc->pivot->obrigatorio && $tipoDoc->aplicaAoTipoSetor($tipoSetor) && !$docsObrigatorios->contains('id', $tipoDoc->id)) {
                    $docsObrigatorios->push($tipoDoc);
                }
            }
        }

        return $docsObrigatorios;
    }

    /**
     * Consulta processo por CNPJ
     */
    public function consultarProcesso(Request $request)
    {
        $request->validate([
            'cnpj' => 'required|string|min:14|max:18',
        ]);

        // TODO: Implementar lógica de consulta
        return redirect()->back()->with('success', 'Consulta realizada com sucesso!');
    }

    /**
     * Verifica autenticidade de documento
     */
    public function verificarDocumento(Request $request)
    {
        $request->validate([
            'codigo_verificador' => 'required|string|min:6',
        ]);

        // TODO: Implementar lógica de verificação
        return redirect()->back()->with('success', 'Documento verificado com sucesso!');
    }
}

