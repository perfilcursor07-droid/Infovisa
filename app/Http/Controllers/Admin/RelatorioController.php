<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\OrdemServico;
use App\Models\DocumentoDigital;
use App\Models\TipoDocumento;
use App\Models\UsuarioInterno;
use App\Models\Atividade;
use App\Models\AtividadeEquipamentoRadiacao;
use App\Models\EquipamentoRadiacao;
use App\Models\Municipio;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PesquisaSatisfacao;
use App\Models\TipoAcao;
use App\Models\PesquisaSatisfacaoResposta;
use App\Models\ConfiguracaoSistema;
use Illuminate\Support\Facades\Http;

class RelatorioController extends Controller
{
    private function normalizarCodigoCnae(?string $codigo): ?string
    {
        if (!$codigo) {
            return null;
        }

        $normalizado = preg_replace('/[^0-9]/', '', (string) $codigo);

        return $normalizado !== '' ? $normalizado : null;
    }

    private function formatarCodigoCnae(?string $codigo): string
    {
        $normalizado = $this->normalizarCodigoCnae($codigo);

        if (!$normalizado) {
            return '-';
        }

        if (strlen($normalizado) === 7) {
            return substr($normalizado, 0, 4) . '-' . substr($normalizado, 4, 1) . '/' . substr($normalizado, 5, 2);
        }

        return $normalizado;
    }

    private function estabelecimentoDentroEscopoRelatorio(Estabelecimento $estabelecimento, UsuarioInterno $usuario, ?string $competenciaFiltro = null): bool
    {
        if ($usuario->isMunicipal()) {
            if (!$usuario->municipio_id || (int) $estabelecimento->municipio_id !== (int) $usuario->municipio_id) {
                return false;
            }

            return $estabelecimento->isCompetenciaMunicipal();
        }

        if ($usuario->isEstadual()) {
            return $estabelecimento->isCompetenciaEstadual();
        }

        if ($competenciaFiltro === 'estadual') {
            return $estabelecimento->isCompetenciaEstadual();
        }

        if ($competenciaFiltro === 'municipal') {
            return $estabelecimento->isCompetenciaMunicipal();
        }

        return true;
    }

    private function obterDescricaoCnaeDoEstabelecimento(Estabelecimento $estabelecimento, string $codigo, array $catalogoAtividades): string
    {
        foreach (($estabelecimento->atividades_exercidas ?? []) as $atividade) {
            if (!is_array($atividade)) {
                continue;
            }

            $codigoAtividade = $this->normalizarCodigoCnae($atividade['codigo'] ?? null);

            if ($codigoAtividade !== $codigo) {
                continue;
            }

            $descricao = trim((string) ($atividade['descricao'] ?? $atividade['nome'] ?? ''));

            if ($descricao !== '') {
                return $descricao;
            }
        }

        if ($this->normalizarCodigoCnae($estabelecimento->cnae_fiscal) === $codigo && !empty($estabelecimento->cnae_fiscal_descricao)) {
            return $estabelecimento->cnae_fiscal_descricao;
        }

        foreach (($estabelecimento->cnaes_secundarios ?? []) as $cnaeSecundario) {
            if (!is_array($cnaeSecundario)) {
                continue;
            }

            $codigoSecundario = $this->normalizarCodigoCnae($cnaeSecundario['codigo'] ?? null);

            if ($codigoSecundario !== $codigo) {
                continue;
            }

            $descricao = trim((string) ($cnaeSecundario['descricao'] ?? $cnaeSecundario['nome'] ?? ''));

            if ($descricao !== '') {
                return $descricao;
            }
        }

        return $catalogoAtividades[$codigo] ?? 'CNAE sem descrição cadastrada';
    }

    private function montarCnaesRelatorioEstabelecimento(Estabelecimento $estabelecimento, array $catalogoAtividades): array
    {
        return collect($estabelecimento->getTodasAtividades())
            ->map(fn($codigo) => $this->normalizarCodigoCnae($codigo))
            ->filter()
            ->unique()
            ->map(function ($codigo) use ($estabelecimento, $catalogoAtividades) {
                return [
                    'codigo' => $codigo,
                    'codigo_formatado' => $this->formatarCodigoCnae($codigo),
                    'descricao' => $this->obterDescricaoCnaeDoEstabelecimento($estabelecimento, $codigo, $catalogoAtividades),
                ];
            })
            ->sortBy('codigo_formatado')
            ->values()
            ->all();
    }

    private function paginarColecao($itens, int $porPagina, Request $request, string $pageName = 'page'): LengthAwarePaginator
    {
        $paginaAtual = LengthAwarePaginator::resolveCurrentPage($pageName);
        $colecao = $itens instanceof \Illuminate\Support\Collection ? $itens->values() : collect($itens)->values();
        $fatia = $colecao->slice(($paginaAtual - 1) * $porPagina, $porPagina)->values();

        return new LengthAwarePaginator(
            $fatia,
            $colecao->count(),
            $porPagina,
            $paginaAtual,
            [
                'path' => url($request->path()),
                'pageName' => $pageName,
                'query' => $request->query(),
            ]
        );
    }

    private function calcularMediaRespostaPesquisa(PesquisaSatisfacaoResposta $resposta): ?float
    {
        $perguntasEscalaIds = $resposta->pesquisa?->perguntas
            ?->where('tipo', 'escala_1_5')
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all() ?? [];

        if (empty($perguntasEscalaIds)) {
            return null;
        }

        $mapPerguntas = array_flip($perguntasEscalaIds);
        $respostasJson = is_array($resposta->respostas) ? $resposta->respostas : [];
        $somaNotas = 0;
        $totalNotas = 0;

        foreach ($respostasJson as $item) {
            $perguntaId = (int) ($item['pergunta_id'] ?? 0);
            $valor = $item['valor'] ?? null;

            if (!isset($mapPerguntas[$perguntaId])) {
                continue;
            }

            $nota = is_numeric($valor) ? (int) $valor : null;

            if ($nota === null || $nota < 1 || $nota > 5) {
                continue;
            }

            $somaNotas += $nota;
            $totalNotas++;
        }

        return $totalNotas > 0 ? round($somaNotas / $totalNotas, 1) : null;
    }

    private function calcularMediaGeralNotasPesquisa($respostasFiltradas): ?float
    {
        if ($respostasFiltradas->isEmpty()) {
            return null;
        }

        $pesquisaIds = $respostasFiltradas->pluck('pesquisa_id')->filter()->unique()->values();

        if ($pesquisaIds->isEmpty()) {
            return null;
        }

        $perguntasEscalaIds = \App\Models\PesquisaSatisfacaoPergunta::whereIn('pesquisa_id', $pesquisaIds)
            ->where('tipo', 'escala_1_5')
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();

        if (empty($perguntasEscalaIds)) {
            return null;
        }

        $perguntasEscalaMap = array_flip($perguntasEscalaIds);
        $somaNotas = 0;
        $totalNotas = 0;

        foreach ($respostasFiltradas as $resposta) {
            $respostasJson = is_array($resposta->respostas) ? $resposta->respostas : [];

            foreach ($respostasJson as $item) {
                $perguntaId = (int) ($item['pergunta_id'] ?? 0);
                $valor = $item['valor'] ?? null;

                if (!isset($perguntasEscalaMap[$perguntaId])) {
                    continue;
                }

                $nota = is_numeric($valor) ? (int) $valor : null;

                if ($nota === null || $nota < 1 || $nota > 5) {
                    continue;
                }

                $somaNotas += $nota;
                $totalNotas++;
            }
        }

        return $totalNotas > 0 ? round($somaNotas / $totalNotas, 1) : null;
    }

    private function montarQueryRespostasPesquisaSatisfacao(Request $request)
    {
        $query = PesquisaSatisfacaoResposta::with([
            'pesquisa.perguntas', 'ordemServico', 'estabelecimento',
            'usuarioInterno', 'usuarioExterno',
        ])->orderByDesc('created_at');

        if ($request->filled('pesquisa_id')) {
            $query->where('pesquisa_id', $request->pesquisa_id);
        }

        if ($request->filled('tipo_respondente')) {
            $query->where('tipo_respondente', $request->tipo_respondente);
        }

        if ($request->filled('ordem_servico_id')) {
            $query->where('ordem_servico_id', $request->ordem_servico_id);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('respondente_nome', 'ilike', "%{$busca}%")
                    ->orWhere('respondente_email', 'ilike', "%{$busca}%");
            });
        }

        return $query;
    }

    /**
     * Exibe a página principal de relatórios
     */
    public function index()
    {
        return view('admin.relatorios.index');
    }

    /**
     * Relatório de estabelecimentos por CNAE com escopo automático por perfil.
     */
    public function estabelecimentosPorCnae(Request $request)
    {
        $usuario = auth('interno')->user();
        $competenciaFiltro = $usuario->isAdmin() ? $request->input('competencia') : null;

        $catalogoAtividades = Atividade::ativas()
            ->get(['codigo_cnae', 'descricao', 'nome'])
            ->mapWithKeys(function ($atividade) {
                $codigo = preg_replace('/[^0-9]/', '', (string) $atividade->codigo_cnae);
                $descricao = trim((string) ($atividade->descricao ?: $atividade->nome));

                return $codigo !== '' ? [$codigo => $descricao] : [];
            })
            ->toArray();

        $query = Estabelecimento::query()
            ->with('municipio')
            ->orderByRaw('COALESCE(nome_fantasia, razao_social) asc');

        if ($usuario->isMunicipal() && $usuario->municipio_id) {
            $query->where('municipio_id', $usuario->municipio_id);
        }

        if ($usuario->isAdmin() && $request->filled('municipio_id')) {
            $query->where('municipio_id', $request->integer('municipio_id'));
        }

        if ($request->filled('busca_estabelecimento')) {
            $buscaEstabelecimento = trim($request->busca_estabelecimento);

            $query->where(function ($subQuery) use ($buscaEstabelecimento) {
                $subQuery->where('nome_fantasia', 'ilike', "%{$buscaEstabelecimento}%")
                    ->orWhere('razao_social', 'ilike', "%{$buscaEstabelecimento}%")
                    ->orWhere('cnpj', 'ilike', "%{$buscaEstabelecimento}%")
                    ->orWhere('cpf', 'ilike', "%{$buscaEstabelecimento}%");
            });
        }

        $estabelecimentosEscopo = $query->get()
            ->filter(fn($estabelecimento) => $this->estabelecimentoDentroEscopoRelatorio($estabelecimento, $usuario, $competenciaFiltro))
            ->map(function ($estabelecimento) use ($catalogoAtividades) {
                $estabelecimento->cnaes_relatorio = collect($this->montarCnaesRelatorioEstabelecimento($estabelecimento, $catalogoAtividades));
                return $estabelecimento;
            })
            ->filter(fn($estabelecimento) => $estabelecimento->cnaes_relatorio->isNotEmpty())
            ->values();

        $resumoCompleto = $estabelecimentosEscopo
            ->flatMap(function ($estabelecimento) {
                return $estabelecimento->cnaes_relatorio->map(function ($cnae) use ($estabelecimento) {
                    return [
                        'codigo' => $cnae['codigo'],
                        'codigo_formatado' => $cnae['codigo_formatado'],
                        'descricao' => $cnae['descricao'],
                        'estabelecimento_id' => $estabelecimento->id,
                        'municipio_id' => $estabelecimento->municipio_id,
                        'municipio_nome' => $estabelecimento->municipio->nome ?? ($estabelecimento->municipio ?? '-'),
                        'competencia' => $estabelecimento->isCompetenciaEstadual() ? 'estadual' : 'municipal',
                    ];
                });
            })
            ->groupBy('codigo')
            ->map(function ($itens) {
                $primeiro = $itens->first();

                return [
                    'codigo' => $primeiro['codigo'],
                    'codigo_formatado' => $primeiro['codigo_formatado'],
                    'descricao' => $primeiro['descricao'],
                    'total_estabelecimentos' => $itens->pluck('estabelecimento_id')->unique()->count(),
                    'total_municipios' => $itens->pluck('municipio_id')->filter()->unique()->count(),
                    'competencias' => $itens->pluck('competencia')->unique()->values(),
                    'competencias_label' => $itens->pluck('competencia')->unique()->map(fn($competencia) => ucfirst($competencia))->implode(', '),
                ];
            })
            ->sortByDesc('total_estabelecimentos')
            ->values();

        $cnaeSelecionado = $this->normalizarCodigoCnae($request->input('cnae'));
        $buscaCnae = trim((string) $request->input('busca_cnae'));

        $resumoCnaes = $resumoCompleto
            ->when($cnaeSelecionado, fn($colecao) => $colecao->where('codigo', $cnaeSelecionado))
            ->when($buscaCnae !== '', function ($colecao) use ($buscaCnae) {
                $buscaNormalizada = mb_strtolower($buscaCnae);

                return $colecao->filter(function ($item) use ($buscaNormalizada) {
                    return str_contains(mb_strtolower($item['codigo_formatado']), $buscaNormalizada)
                        || str_contains(mb_strtolower($item['descricao']), $buscaNormalizada);
                });
            })
            ->values();

        $estabelecimentosFiltrados = $estabelecimentosEscopo
            ->when($cnaeSelecionado, function ($colecao) use ($cnaeSelecionado) {
                return $colecao->filter(function ($estabelecimento) use ($cnaeSelecionado) {
                    return $estabelecimento->cnaes_relatorio->contains('codigo', $cnaeSelecionado);
                });
            })
            ->values();

        $totais = [
            'estabelecimentos' => $estabelecimentosEscopo->count(),
            'cnaes' => $resumoCompleto->count(),
            'estadual' => $estabelecimentosEscopo->filter(fn($estabelecimento) => $estabelecimento->isCompetenciaEstadual())->count(),
            'municipal' => $estabelecimentosEscopo->filter(fn($estabelecimento) => $estabelecimento->isCompetenciaMunicipal())->count(),
        ];

        $municipios = $usuario->isAdmin()
            ? Municipio::query()->orderBy('nome')->get(['id', 'nome'])
            : collect();

        $estabelecimentos = $this->paginarColecao($estabelecimentosFiltrados, 15, $request, 'est_page');

        $escopoVisual = $usuario->isAdmin()
            ? 'Todos os municípios e competências'
            : ($usuario->isMunicipal()
                ? 'Município do usuário e competência municipal'
                : 'Competência estadual');

        return view('admin.relatorios.estabelecimentos-cnae', compact(
            'resumoCnaes',
            'estabelecimentos',
            'totais',
            'municipios',
            'escopoVisual',
            'cnaeSelecionado'
        ));
    }

    /**
     * Relatório de Equipamentos de Imagem
     */
    public function equipamentosRadiacao()
    {
        $usuario = auth('interno')->user();

        // Códigos das atividades que exigem equipamentos de radiação (normalizados)
        $codigosAtividadesRadiacao = AtividadeEquipamentoRadiacao::where('ativo', true)
            ->pluck('codigo_atividade')
            ->map(fn($c) => preg_replace('/[^0-9]/', '', $c))
            ->unique()
            ->filter()
            ->toArray();

        // Buscar todos os estabelecimentos e filtrar por atividades em PHP
        $query = Estabelecimento::query()
            ->whereNotNull('atividades_exercidas')
            ->with('municipio') // Carregar relacionamento para o mapa
            ->withCount('equipamentosRadiacao as equipamentos_count');

        // Filtro por município se for usuário municipal
        if ($usuario->isMunicipal()) {
            $query->where('municipio_id', $usuario->municipio_id);
        }

        $todosEstabelecimentos = $query->orderBy('nome_fantasia')->get();

        // Filtrar estabelecimentos que têm atividades de radiação
        // E EXCLUIR os que declararam não ter equipamentos
        $estabelecimentos = $todosEstabelecimentos->filter(function($est) use ($codigosAtividadesRadiacao) {
            // Excluir estabelecimentos que declararam não ter equipamentos
            if ($est->declaracao_sem_equipamentos_imagem) {
                return false;
            }
            
            $atividadesEstabelecimento = $est->getTodasAtividades();
            foreach ($atividadesEstabelecimento as $codigo) {
                if (in_array($codigo, $codigosAtividadesRadiacao)) {
                    return true;
                }
            }
            return false;
        });

        // Adicionar as atividades de radiação encontradas em cada estabelecimento
        $estabelecimentos = $estabelecimentos->map(function($est) use ($codigosAtividadesRadiacao) {
            $atividadesEstabelecimento = $est->getTodasAtividades();
            $codigosRadiacaoDoEst = array_intersect($atividadesEstabelecimento, $codigosAtividadesRadiacao);
            
            // Buscar as atividades de radiação correspondentes
            $est->atividades_radiacao = AtividadeEquipamentoRadiacao::where('ativo', true)
                ->where(function($q) use ($codigosRadiacaoDoEst) {
                    foreach ($codigosRadiacaoDoEst as $codigo) {
                        $q->orWhereRaw("REPLACE(REPLACE(codigo_atividade, '.', ''), '-', '') = ?", [$codigo]);
                    }
                })
                ->get();
            
            return $est;
        })->values();

        // Calcular totais (exclui os que declararam não ter equipamentos)
        $totalDeclaracoesSemEquipamentos = $todosEstabelecimentos->where('declaracao_sem_equipamentos_imagem', true)->count();
        
        $totais = [
            'total' => $estabelecimentos->count(),
            'com_equipamentos' => $estabelecimentos->where('equipamentos_count', '>', 0)->count(),
            'sem_equipamentos' => $estabelecimentos->where('equipamentos_count', 0)->count(),
            'total_equipamentos' => $estabelecimentos->sum('equipamentos_count'),
            'declaracoes_sem_equipamentos' => $totalDeclaracoesSemEquipamentos,
        ];

        // Atividades que exigem equipamentos (para filtro)
        $atividades = AtividadeEquipamentoRadiacao::where('ativo', true)
            ->orderBy('descricao_atividade')
            ->get();

        return view('admin.relatorios.equipamentos-radiacao', compact(
            'estabelecimentos',
            'totais',
            'atividades'
        ));
    }

    /**
     * Exportar relatório de equipamentos de radiação para Excel
     */
    public function equipamentosRadiacaoExport(Request $request)
    {
        $usuario = auth('interno')->user();

        // Códigos das atividades que exigem equipamentos de radiação (normalizados)
        $codigosAtividadesRadiacao = AtividadeEquipamentoRadiacao::where('ativo', true)
            ->pluck('codigo_atividade')
            ->map(fn($c) => preg_replace('/[^0-9]/', '', $c))
            ->unique()
            ->filter()
            ->toArray();

        // Filtro por atividade específica
        if ($request->filled('atividade')) {
            $atividadeFiltro = AtividadeEquipamentoRadiacao::find($request->atividade);
            if ($atividadeFiltro) {
                $codigosAtividadesRadiacao = [preg_replace('/[^0-9]/', '', $atividadeFiltro->codigo_atividade)];
            }
        }

        // Buscar todos os estabelecimentos
        $query = Estabelecimento::query()
            ->whereNotNull('atividades_exercidas')
            ->with('equipamentosRadiacao')
            ->withCount('equipamentosRadiacao as equipamentos_count');

        // Filtro por município
        if ($usuario->isMunicipal()) {
            $query->where('municipio_id', $usuario->municipio_id);
        }

        $todosEstabelecimentos = $query->orderBy('nome_fantasia')->get();

        // Filtrar estabelecimentos que têm atividades de radiação
        $estabelecimentos = $todosEstabelecimentos->filter(function($est) use ($codigosAtividadesRadiacao) {
            $atividadesEstabelecimento = $est->getTodasAtividades();
            foreach ($atividadesEstabelecimento as $codigo) {
                if (in_array($codigo, $codigosAtividadesRadiacao)) {
                    return true;
                }
            }
            return false;
        });

        // Aplicar filtro de status
        if ($request->filled('status')) {
            if ($request->status === 'com') {
                $estabelecimentos = $estabelecimentos->where('equipamentos_count', '>', 0);
            } elseif ($request->status === 'sem') {
                $estabelecimentos = $estabelecimentos->where('equipamentos_count', '=', 0);
            }
        }

        // Adicionar as atividades de radiação encontradas
        $estabelecimentos = $estabelecimentos->map(function($est) use ($codigosAtividadesRadiacao) {
            $atividadesEstabelecimento = $est->getTodasAtividades();
            $codigosRadiacaoDoEst = array_intersect($atividadesEstabelecimento, $codigosAtividadesRadiacao);
            
            $est->atividades_radiacao_nomes = AtividadeEquipamentoRadiacao::where('ativo', true)
                ->where(function($q) use ($codigosRadiacaoDoEst) {
                    foreach ($codigosRadiacaoDoEst as $codigo) {
                        $q->orWhereRaw("REPLACE(REPLACE(codigo_atividade, '.', ''), '-', '') = ?", [$codigo]);
                    }
                })
                ->pluck('descricao_atividade')
                ->implode(', ');
            
            return $est;
        })->values();

        // Gerar CSV
        $filename = 'relatorio-equipamentos-radiacao-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($estabelecimentos) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalho
            fputcsv($file, [
                'Estabelecimento',
                'Razão Social',
                'CNPJ',
                'Atividades com Radiação',
                'Qtd. Equipamentos',
                'Status',
            ], ';');

            foreach ($estabelecimentos as $est) {
                $cnpj = $est->cnpj ? preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $est->cnpj) : '';
                $atividades = $est->atividades_radiacao_nomes ?? '';
                $status = $est->equipamentos_count > 0 ? 'Cadastrado' : 'Pendente';

                fputcsv($file, [
                    $est->nome_fantasia ?? $est->razao_social,
                    $est->razao_social,
                    $cnpj,
                    $atividades,
                    $est->equipamentos_count,
                    $status,
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Obtém estatísticas gerais do sistema
     */
    private function obterEstatisticasGerais($usuario)
    {
        $stats = [];
        
        // Total de estabelecimentos
        $queryEstabelecimentos = Estabelecimento::query();
        if ($usuario->isMunicipal()) {
            $queryEstabelecimentos->where('municipio_id', $usuario->municipio_id);
        }
        $stats['total_estabelecimentos'] = $queryEstabelecimentos->count();
        
        // Total de processos
        $queryProcessos = Processo::query();
        if ($usuario->isMunicipal()) {
            $queryProcessos->whereHas('estabelecimento', function($q) use ($usuario) {
                $q->where('municipio_id', $usuario->municipio_id);
            });
        }
        $stats['total_processos'] = $queryProcessos->count();
        $stats['processos_abertos'] = (clone $queryProcessos)->where('status', 'aberto')->count();
        
        // Total de ordens de serviço
        $stats['total_ordens_servico'] = OrdemServico::count();
        $stats['ordens_em_andamento'] = OrdemServico::where('status', 'em_andamento')->count();
        
        // Total de documentos digitais
        $stats['total_documentos'] = DocumentoDigital::count();
        
        return $stats;
    }

    /**
     * Listar estabelecimentos que declararam não ter equipamentos
     */
    public function declaracoesSemEquipamentos()
    {
        $usuario = auth('interno')->user();

        $query = Estabelecimento::query()
            ->where('declaracao_sem_equipamentos_imagem', true)
            ->with(['municipio', 'declaracaoSemEquipamentosUsuario'])
            ->orderBy('nome_fantasia');

        // Filtro por município se for usuário municipal
        if ($usuario->isMunicipal()) {
            $query->where('municipio_id', $usuario->municipio_id);
        }

        $declaracoes = $query->paginate(15);

        return view('admin.relatorios.declaracoes-sem-equipamentos', compact(
            'declaracoes'
        ));
    }

    /**
     * Relatório de documentos digitais gerados
     *
     * Regras de visibilidade:
     * - Admin, Gestor Estadual e Técnico Estadual: visualizam documentos do estado
     * - Gestor Municipal e Técnico Municipal: visualizam apenas documentos do seu município
     */
    public function documentosGerados(Request $request)
    {
        $usuario = auth('interno')->user();
        $podeVerApagados = $usuario->isAdmin();

        $tiposDocumento = TipoDocumento::orderBy('nome')->get(['id', 'nome']);

        $query = DocumentoDigital::query()
            ->when($podeVerApagados, fn ($q) => $q->withTrashed())
            ->with([
                'tipoDocumento:id,nome',
                'usuarioCriador:id,nome,municipio_id,nivel_acesso',
                'processo' => fn ($q) => $podeVerApagados
                    ? $q->withTrashed()->select(['id', 'numero_processo', 'estabelecimento_id', 'deleted_at'])
                    : $q->select(['id', 'numero_processo', 'estabelecimento_id']),
                'processo.estabelecimento:id,nome_fantasia,razao_social,municipio_id',
                'processo.estabelecimento.municipio:id,nome',
            ])
            ->whereNotNull('numero_documento');

        if ($usuario->isAdmin()) {
            // Admin visualiza tudo, inclusive registros excluídos/orfãos.
        } elseif ($usuario->isEstadual()) {
            $query->whereHas('usuarioCriador', function ($q) {
                $q->whereIn('nivel_acesso', [
                    \App\Enums\NivelAcesso::GestorEstadual->value,
                    \App\Enums\NivelAcesso::TecnicoEstadual->value,
                ]);
            })->whereHas('processo.estabelecimento');
        } elseif ($usuario->isMunicipal()) {
            $query->whereHas('usuarioCriador', function ($q) use ($usuario) {
                $q->where('municipio_id', $usuario->municipio_id);
            })->whereHas('processo.estabelecimento', function ($q) use ($usuario) {
                $q->where('municipio_id', $usuario->municipio_id);
            });
        } else {
            $query->whereRaw('1 = 0');
        }

        // Filtros opcionais
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tipo_documento_id')) {
            $query->where('tipo_documento_id', $request->tipo_documento_id);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        if ($request->filled('busca')) {
            $busca = trim($request->busca);

            $query->where(function ($q) use ($busca) {
                $q->where('numero_documento', 'like', "%{$busca}%")
                    ->orWhere('nome', 'like', "%{$busca}%")
                    ->orWhereHas('tipoDocumento', function ($tipoQ) use ($busca) {
                        $tipoQ->where('nome', 'like', "%{$busca}%");
                    })
                    ->orWhereHas('processo', function ($processoQ) use ($busca) {
                        $processoQ->where('numero_processo', 'like', "%{$busca}%");
                    })
                    ->orWhereHas('processo.estabelecimento', function ($estQ) use ($busca) {
                        $estQ->where('nome_fantasia', 'like', "%{$busca}%")
                            ->orWhere('razao_social', 'like', "%{$busca}%");
                    });
            });
        }

        $documentos = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $totais = [
            'total' => (clone $query)->count(),
            'assinados' => (clone $query)->where('status', 'assinado')->count(),
            'aguardando_assinatura' => (clone $query)->where('status', 'aguardando_assinatura')->count(),
            'rascunhos' => (clone $query)->where('status', 'rascunho')->count(),
        ];

        return view('admin.relatorios.documentos-gerados', compact('documentos', 'totais', 'tiposDocumento', 'podeVerApagados'));
    }

    /**
     * Relatório de Pesquisa de Satisfação com gráficos
     * Suporta seleção de múltiplas pesquisas (pesquisa_ids[])
     */
    public function pesquisaSatisfacao(Request $request)
    {
        $pesquisas = PesquisaSatisfacao::withCount('respostas')->orderBy('titulo')->get();
        $aba = $request->get('aba', 'relatorio');

        // Suporta array de IDs (múltiplas) ou ID único (retrocompatível)
        $pesquisaIds = $request->input('pesquisa_ids', []);
        if (empty($pesquisaIds) && $request->filled('pesquisa_id')) {
            $pesquisaIds = [$request->input('pesquisa_id')];
        }
        $pesquisaIds = array_filter(array_map('intval', (array) $pesquisaIds));

        $pesquisasSelecionadas = collect();
        $dados = null;

        if (!empty($pesquisaIds)) {
            $pesquisasSelecionadas = PesquisaSatisfacao::with('perguntas.opcoes')
                ->whereIn('id', $pesquisaIds)
                ->get();

            if ($pesquisasSelecionadas->isEmpty()) {
                abort(404);
            }

            // Buscar respostas de TODAS as pesquisas selecionadas
            $queryRespostas = PesquisaSatisfacaoResposta::whereIn('pesquisa_id', $pesquisaIds);

            if ($request->filled('data_inicio')) {
                $queryRespostas->whereDate('created_at', '>=', $request->data_inicio);
            }
            if ($request->filled('data_fim')) {
                $queryRespostas->whereDate('created_at', '<=', $request->data_fim);
            }

            $respostas = $queryRespostas->orderByDesc('created_at')->get();

            $dados = [
                'total_respostas' => $respostas->count(),
                'por_tipo_respondente' => [
                    'interno' => $respostas->where('tipo_respondente', 'interno')->count(),
                    'externo' => $respostas->where('tipo_respondente', 'externo')->count()
                        + $respostas->filter(fn($r) => !$r->tipo_respondente && ($r->respondente_nome || $r->respondente_email))->count(),
                    'anonimo' => $respostas->filter(fn($r) => !$r->tipo_respondente && !$r->respondente_nome && !$r->respondente_email)->count(),
                ],
                'por_mes' => [],
                'por_pesquisa' => [],
                'perguntas' => [],
            ];

            // Respostas por mês (últimos 6 meses)
            for ($i = 5; $i >= 0; $i--) {
                $mes = now()->subMonths($i);
                $count = $respostas->filter(function ($r) use ($mes) {
                    return $r->created_at->format('Y-m') === $mes->format('Y-m');
                })->count();
                $dados['por_mes'][] = [
                    'label' => $mes->translatedFormat('M/Y'),
                    'count' => $count,
                ];
            }

            // Respostas por pesquisa (para gráfico comparativo)
            foreach ($pesquisasSelecionadas as $ps) {
                $dados['por_pesquisa'][] = [
                    'titulo' => \Str::limit($ps->titulo, 30),
                    'count' => $respostas->where('pesquisa_id', $ps->id)->count(),
                ];
            }

            // Análise por pergunta (de todas as pesquisas selecionadas)
            foreach ($pesquisasSelecionadas as $ps) {
                $respostasDaPesquisa = $respostas->where('pesquisa_id', $ps->id);
                $prefixo = count($pesquisaIds) > 1 ? '[' . \Str::limit($ps->titulo, 25) . '] ' : '';

                foreach ($ps->perguntas as $pergunta) {
                    $perguntaDados = [
                        'id' => $pergunta->id,
                        'texto' => $prefixo . $pergunta->texto,
                        'tipo' => $pergunta->tipo,
                        'pesquisa' => $ps->titulo,
                        'distribuicao' => [],
                        'media' => null,
                        'textos_livres' => [],
                    ];

                    if ($pergunta->tipo === 'escala_1_5') {
                        $contagem = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                        $soma = 0;
                        $total = 0;
                        foreach ($respostasDaPesquisa as $resp) {
                            $respostasJson = is_array($resp->respostas) ? $resp->respostas : [];
                            foreach ($respostasJson as $r) {
                                if (($r['pergunta_id'] ?? null) == $pergunta->id && isset($r['valor'])) {
                                    $val = (int) $r['valor'];
                                    if ($val >= 1 && $val <= 5) {
                                        $contagem[$val]++;
                                        $soma += $val;
                                        $total++;
                                    }
                                }
                            }
                        }
                        $perguntaDados['distribuicao'] = $contagem;
                        $perguntaDados['media'] = $total > 0 ? round($soma / $total, 1) : 0;
                        $perguntaDados['total'] = $total;
                    } elseif ($pergunta->tipo === 'multipla_escolha') {
                        $contagem = [];
                        foreach ($pergunta->opcoes as $opcao) {
                            $contagem[$opcao->id] = ['texto' => $opcao->texto, 'count' => 0];
                        }
                        foreach ($respostasDaPesquisa as $resp) {
                            $respostasJson = is_array($resp->respostas) ? $resp->respostas : [];
                            foreach ($respostasJson as $r) {
                                if (($r['pergunta_id'] ?? null) == $pergunta->id && isset($r['opcao_id'])) {
                                    $opcaoId = $r['opcao_id'];
                                    if (isset($contagem[$opcaoId])) {
                                        $contagem[$opcaoId]['count']++;
                                    }
                                }
                            }
                        }
                        $perguntaDados['distribuicao'] = array_values($contagem);
                    } elseif ($pergunta->tipo === 'texto_livre') {
                        foreach ($respostasDaPesquisa as $resp) {
                            $respostasJson = is_array($resp->respostas) ? $resp->respostas : [];
                            foreach ($respostasJson as $r) {
                                if (($r['pergunta_id'] ?? null) == $pergunta->id && !empty($r['valor'])) {
                                    $perguntaDados['textos_livres'][] = [
                                        'texto' => $r['valor'],
                                        'respondente' => $resp->nome_respondente,
                                        'data' => $resp->created_at->format('d/m/Y'),
                                    ];
                                }
                            }
                        }
                    }

                    $dados['perguntas'][] = $perguntaDados;
                }
            }
        }

        $respostasQuery = $this->montarQueryRespostasPesquisaSatisfacao($request);
        $respostasFiltradas = (clone $respostasQuery)->get(['id', 'pesquisa_id', 'respostas']);
        $respostas = $respostasQuery->paginate(20)->withQueryString();
        $respostas->getCollection()->transform(function ($resposta) {
            $resposta->media_notas = $this->calcularMediaRespostaPesquisa($resposta);
            return $resposta;
        });

        $totalRespostas = PesquisaSatisfacaoResposta::count();
        $respostasInterno = PesquisaSatisfacaoResposta::where('tipo_respondente', 'interno')->count();
        $respostasExterno = PesquisaSatisfacaoResposta::where('tipo_respondente', 'externo')->count();
        $mediaGeralNotas = $this->calcularMediaGeralNotasPesquisa($respostasFiltradas);

        $iaConfigurada = !empty(ConfiguracaoSistema::obter('ia_api_key')) && !empty(ConfiguracaoSistema::obter('ia_api_url'));
        $iaPesquisaSatisfacaoAtiva = $iaConfigurada;

        return view('admin.relatorios.pesquisa-satisfacao', compact(
            'aba',
            'pesquisas',
            'pesquisasSelecionadas',
            'dados',
            'respostas',
            'totalRespostas',
            'respostasInterno',
            'respostasExterno',
            'mediaGeralNotas',
            'iaPesquisaSatisfacaoAtiva'
        ));
    }

    /**
     * Análise de IA para Pesquisa de Satisfação
     */
    public function pesquisaSatisfacaoAnaliseIA(Request $request)
    {
        $request->validate([
            'dados_relatorio' => 'required|string',
        ]);

        $iaPesquisaSatisfacaoAtiva = ConfiguracaoSistema::obter('ia_pesquisa_satisfacao_ativa', 'true');
        if ($iaPesquisaSatisfacaoAtiva === 'false') {
            return response()->json([
                'success' => false,
                'error' => 'O Assistente de IA para Pesquisa de Satisfação está desativado. Ative-o em Configurações > Sistema.',
            ], 403);
        }

        $apiKey = ConfiguracaoSistema::obter('ia_api_key');
        $apiUrl = ConfiguracaoSistema::obter('ia_api_url');
        $model = ConfiguracaoSistema::obter('ia_model');

        if (empty($apiKey) || empty($apiUrl) || empty($model)) {
            return response()->json([
                'success' => false,
                'error' => 'Configurações da IA não encontradas. Configure a API em Configurações > Sistema.',
            ], 500);
        }

        $dadosRelatorio = $request->input('dados_relatorio');

        // Verifica se há um prompt customizado configurado
        $promptCustomizado = trim((string) ConfiguracaoSistema::obter('ia_pesquisa_satisfacao_prompt', ''));

        $systemPrompt = "Você é um analista especialista em gestão da qualidade e satisfação de clientes do sistema InfoVISA (Vigilância Sanitária).\n\n"
            . "Sua função é analisar os dados de pesquisas de satisfação e fornecer uma análise estratégica completa para tomada de decisão.\n\n"
            . "FORMATO OBRIGATÓRIO DA RESPOSTA (use exatamente estas seções com os títulos em markdown):\n\n"
            . "## 📊 Resumo Executivo\n"
            . "Síntese geral dos resultados em 2-3 frases.\n\n"
            . "## ✅ Pontos Fortes\n"
            . "Liste os aspectos com melhor avaliação e o que está funcionando bem.\n\n"
            . "## ⚠️ Pontos de Atenção\n"
            . "Liste os aspectos com pior avaliação ou que precisam de melhorias urgentes.\n\n"
            . "## 📈 Tendências Identificadas\n"
            . "Analise a evolução mensal das respostas e identifique padrões.\n\n"
            . "## 🎯 Recomendações de Ação\n"
            . "Liste ações concretas e priorizadas que a gestão deve tomar, baseadas nos dados.\n\n"
            . "## 🔮 Projeção\n"
            . "Com base nos dados atuais, projete o cenário caso as ações sejam (ou não) implementadas.\n\n"
            . "REGRAS:\n"
            . "- Responda SEMPRE em português do Brasil\n"
            . "- Baseie-se EXCLUSIVAMENTE nos dados fornecidos, não invente dados\n"
            . "- Seja objetivo e direto, focando em insights acionáveis\n"
            . "- Use linguagem profissional adequada para relatórios de gestão\n"
            . "- Se houver textos livres dos respondentes, extraia os temas mais recorrentes\n"
            . "- Considere que a escala é de 1 a 5 (1=Péssimo, 2=Ruim, 3=Regular, 4=Bom, 5=Ótimo)\n"
            . "- Notas médias abaixo de 3.0 são críticas, entre 3.0 e 3.9 requerem atenção, acima de 4.0 são positivas";

        if (!empty($promptCustomizado)) {
            $systemPrompt .= "\n\nINSTRUÇÕES ADICIONAIS DO ADMINISTRADOR:\n" . $promptCustomizado;
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Analise os seguintes dados da pesquisa de satisfação e forneça insights para tomada de decisão:\n\n" . $dadosRelatorio],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(90)->post($apiUrl, [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 2000,
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $resposta = $data['choices'][0]['message']['content'] ?? null;

                if ($resposta) {
                    return response()->json([
                        'success' => true,
                        'analise' => $resposta,
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'error' => 'A IA não retornou uma resposta válida.',
                ], 500);
            }

            \Log::error('Erro na API da IA - Análise Pesquisa Satisfação', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao comunicar com a IA. Tente novamente.',
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Exceção na análise IA da pesquisa de satisfação', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao processar análise: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Relatório de Processos
     */
    public function processos(Request $request)
    {
        $usuario = auth('interno')->user();

        // Tipos de processo disponíveis
        $tiposProcesso = \App\Models\TipoProcesso::ativos()->paraUsuario($usuario)->ordenado()->get();
        $statusDisponiveis = Processo::statusDisponiveis();
        $anos = Processo::select('ano')->distinct()->orderBy('ano', 'desc')->pluck('ano');
        $municipios = Municipio::where('usa_infovisa', true)->orderBy('nome')->get();

        // Query base
        $query = Processo::with(['estabelecimento', 'tipoProcesso', 'responsavelAtual']);

        // Filtro por escopo do usuário
        if (!$usuario->isAdmin()) {
            if ($usuario->isMunicipal() && $usuario->municipio_id) {
                $query->whereHas('estabelecimento', function ($q) use ($usuario) {
                    $q->where('municipio_id', $usuario->municipio_id);
                });
            }
        }

        // Filtros
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }
        if ($request->filled('municipio_id')) {
            $query->whereHas('estabelecimento', function ($q) use ($request) {
                $q->where('municipio_id', $request->municipio_id);
            });
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $totalProcessos = (clone $query)->count();

        // Contagem por status
        $porStatus = (clone $query)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status')->toArray();

        // Listagem paginada
        $processos = (clone $query)->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('admin.relatorios.processos', compact(
            'processos', 'tiposProcesso', 'statusDisponiveis', 'anos', 'municipios',
            'totalProcessos', 'porStatus'
        ));
    }

    /**
     * Relatório de Ações por Atividade (Ordens de Serviço)
     */
    public function acoesPorAtividade(Request $request)
    {
        $usuario = auth('interno')->user();

        $filtroCompetencia = $request->input('competencia');
        $filtroMunicipio = $request->input('municipio_id');
        $filtroUsuario = $request->input('usuario_id');
        $filtroStatus = $request->input('status');
        $filtroAtividade = $request->input('atividade_status', 'todas'); // todas, finalizada, pendente
        $filtroDataInicio = $request->input('data_inicio');
        $filtroDataFim = $request->input('data_fim');

        $query = OrdemServico::query()->with(['estabelecimento.municipio', 'municipio']);

        // Escopo por perfil
        if ($usuario->isMunicipal() && $usuario->municipio_id) {
            $query->where('municipio_id', $usuario->municipio_id);
        } elseif ($usuario->isEstadual()) {
            $query->where('competencia', 'estadual');
        }

        if ($filtroCompetencia) $query->where('competencia', $filtroCompetencia);
        if ($filtroMunicipio) $query->where('municipio_id', $filtroMunicipio);
        if ($filtroStatus) $query->where('status', $filtroStatus);
        if ($filtroDataInicio) $query->whereDate('data_abertura', '>=', $filtroDataInicio);
        if ($filtroDataFim) $query->whereDate('data_abertura', '<=', $filtroDataFim);

        if ($filtroUsuario) {
            $query->where(function ($q) use ($filtroUsuario) {
                $q->whereJsonContains('tecnicos_ids', (int) $filtroUsuario)
                  ->orWhereJsonContains('tecnicos_ids', (string) $filtroUsuario);
            });
        }

        $ordensServico = $query->orderByDesc('data_abertura')->get();

        // === Extrair TODAS as atividades individuais de todas as OS ===
        $todasAtividades = collect();
        foreach ($ordensServico as $os) {
            // Município: usa o da OS, senão pega do estabelecimento
            $munId = $os->municipio_id ?? $os->estabelecimento?->municipio_id;
            $munNome = $os->municipio->nome 
                ?? $os->estabelecimento?->municipio?->nome 
                ?? $os->estabelecimento?->cidade 
                ?? 'Estado';
            
            foreach ($os->atividades_tecnicos ?? [] as $ativ) {
                $statusAtiv = $ativ['status'] ?? 'pendente';
                $todasAtividades->push([
                    'tipo_acao_id' => $ativ['tipo_acao_id'] ?? null,
                    'nome' => $ativ['nome_atividade'] ?? 'Sem nome',
                    'status' => $statusAtiv,
                    'tecnicos' => $ativ['tecnicos'] ?? [],
                    'responsavel_id' => $ativ['responsavel_id'] ?? null,
                    'finalizada_por' => $ativ['finalizada_por'] ?? null,
                    'finalizada_em' => $ativ['finalizada_em'] ?? null,
                    'os_id' => $os->id,
                    'os_numero' => $os->numero,
                    'os_competencia' => $os->competencia,
                    'municipio_id' => $munId ?? 0,
                    'municipio_nome' => $munNome,
                    'data_abertura' => $os->data_abertura,
                ]);
            }
        }

        // Filtra atividades por status se solicitado
        $atividadesFiltradas = $todasAtividades;
        if ($filtroAtividade === 'finalizada') {
            $atividadesFiltradas = $todasAtividades->where('status', 'finalizada');
        } elseif ($filtroAtividade === 'pendente') {
            $atividadesFiltradas = $todasAtividades->where('status', 'pendente');
        }

        // === KPIs ===
        $totalOS = $ordensServico->count();
        $totalOSConcluidas = $ordensServico->where('status', 'concluida')->count();
        $totalAtividades = $todasAtividades->count();
        $totalAtivFinalizadas = $todasAtividades->where('status', 'finalizada')->count();
        $totalAtivPendentes = $todasAtividades->where('status', 'pendente')->count();
        $totalEstadual = $ordensServico->where('competencia', 'estadual')->count();
        $totalMunicipal = $ordensServico->where('competencia', 'municipal')->count();
        $pctConclusao = $totalAtividades > 0 ? round(($totalAtivFinalizadas / $totalAtividades) * 100) : 0;

        // === Atividades por tipo de ação ===
        $porTipoAcao = $atividadesFiltradas->groupBy('tipo_acao_id')
            ->map(function ($grupo) {
                return [
                    'nome' => $grupo->first()['nome'],
                    'total' => $grupo->count(),
                    'finalizadas' => $grupo->where('status', 'finalizada')->count(),
                    'pendentes' => $grupo->where('status', 'pendente')->count(),
                ];
            })->sortByDesc('total')->values();

        // === Atividades por município ===
        $porMunicipio = $atividadesFiltradas->groupBy('municipio_id')
            ->map(function ($grupo) {
                return [
                    'nome' => $grupo->first()['municipio_nome'],
                    'total' => $grupo->count(),
                    'finalizadas' => $grupo->where('status', 'finalizada')->count(),
                    'pendentes' => $grupo->where('status', 'pendente')->count(),
                    'estadual' => $grupo->where('os_competencia', 'estadual')->count(),
                    'municipal' => $grupo->where('os_competencia', 'municipal')->count(),
                ];
            })->sortByDesc('total')->values();

        // === Regiões de Saúde do Tocantins (PDR 2014) — apenas para escopo estadual/admin ===
        $regioesSaude = [
            'Médio Norte Araguaia' => ['Aragominas','Araguaína','Araguanã','Babaçulândia','Barra do Ouro','Campos Lindos','Carmolândia','Darcinópolis','Filadélfia','Goiatins','Muricilândia','Nova Olinda','Pau D\'Arco','Piraquê','Santa Fé do Araguaia','Wanderlândia','Xambioá'],
            'Bico do Papagaio' => ['Aguiarnópolis','Ananás','Angico','Araguatins','Augustinópolis','Axixá do Tocantins','Buriti do Tocantins','Cachoeirinha','Carrasco Bonito','Esperantina','Itaguatins','Luzinópolis','Maurilândia do Tocantins','Nazaré','Palmeiras do Tocantins','Praia Norte','Riachinho','Sampaio','Santa Terezinha do Tocantins','São Bento do Tocantins','São Miguel do Tocantins','São Sebastião do Tocantins','Sítio Novo do Tocantins','Tocantinópolis'],
            'Capim Dourado' => ['Aparecida do Rio Negro','Fortaleza do Tabocão','Lagoa do Tocantins','Lajeado','Lizarda','Miracema do Tocantins','Miranorte','Novo Acordo','Palmas','Rio dos Bois','Rio Sono','Santa Tereza do Tocantins','São Félix do Tocantins','Tocantínia'],
            'Cerrado Tocantins Araguaia' => ['Arapoema','Bandeirantes do Tocantins','Bernardo Sayão','Bom Jesus do Tocantins','Brasilândia do Tocantins','Centenário','Colinas do Tocantins','Colméia','Couto Magalhães','Goianorte','Guaraí','Itacajá','Itapiratins','Itaporã do Tocantins','Juarina','Palmeirante','Pedro Afonso','Pequizeiro','Presidente Kennedy','Recursolândia','Santa Maria do Tocantins','Tupirama','Tupiratins'],
            'Ilha do Bananal' => ['Aliança do Tocantins','Alvorada','Araguaçu','Cariri do Tocantins','Crixás do Tocantins','Dueré','Figueirópolis','Formoso do Araguaia','Gurupi','Jaú do Tocantins','Palmeirópolis','Peixe','Sandolândia','Santa Rita do Tocantins','São Salvador do Tocantins','São Valério','Sucupira','Talismã'],
            'Cantão' => ['Abreulândia','Araguacema','Barrolândia','Caseara','Chapada de Areia','Cristalândia','Divinópolis do Tocantins','Dois Irmãos do Tocantins','Lagoa da Confusão','Marianópolis do Tocantins','Monte Santo do Tocantins','Nova Rosalândia','Paraíso do Tocantins','Pium','Pugmil'],
            'Amor Perfeito' => ['Brejinho de Nazaré','Chapada da Natividade','Fátima','Ipueiras','Mateiros','Monte do Carmo','Natividade','Oliveira de Fátima','Pindorama do Tocantins','Ponte Alta do Tocantins','Porto Nacional','Santa Rosa do Tocantins','Silvanópolis'],
            'Sudeste' => ['Almas','Arraias','Aurora do Tocantins','Combinado','Conceição do Tocantins','Dianópolis','Lavandeira','Novo Alegre','Novo Jardim','Paranã','Ponte Alta do Bom Jesus','Porto Alegre do Tocantins','Rio da Conceição','Taguatinga','Taipas do Tocantins'],
        ];

        // Monta mapa inverso: município -> região
        $municipioParaRegiao = [];
        foreach ($regioesSaude as $regiao => $municipiosRegiao) {
            foreach ($municipiosRegiao as $nomeMun) {
                $municipioParaRegiao[mb_strtoupper($nomeMun)] = $regiao;
            }
        }

        // Agrupa atividades por região de saúde
        $porRegiao = collect();
        if ($usuario->isAdmin() || $usuario->isEstadual()) {
            $porRegiao = $atividadesFiltradas->groupBy(function ($ativ) use ($municipioParaRegiao) {
                $nomeUpper = mb_strtoupper($ativ['municipio_nome']);
                return $municipioParaRegiao[$nomeUpper] ?? 'Outros';
            })->map(function ($grupo, $regiao) {
                return [
                    'nome' => $regiao,
                    'total' => $grupo->count(),
                    'finalizadas' => $grupo->where('status', 'finalizada')->count(),
                    'pendentes' => $grupo->where('status', 'pendente')->count(),
                ];
            })->sortByDesc('total')->values();
        }

        // === Atividades por técnico ===
        $porUsuario = [];
        foreach ($atividadesFiltradas as $ativ) {
            foreach ($ativ['tecnicos'] ?? [] as $tecId) {
                $tecId = is_array($tecId) ? ($tecId['id'] ?? null) : $tecId;
                if (!$tecId) continue;
                if (!isset($porUsuario[$tecId])) {
                    $porUsuario[$tecId] = ['total' => 0, 'finalizadas' => 0];
                }
                $porUsuario[$tecId]['total']++;
                if ($ativ['status'] === 'finalizada') {
                    $porUsuario[$tecId]['finalizadas']++;
                }
            }
        }
        $usuariosMap = UsuarioInterno::whereIn('id', array_keys($porUsuario))->get()->keyBy('id');
        $porUsuarioFormatado = collect($porUsuario)
            ->map(fn($data, $id) => [
                'id' => $id,
                'nome' => $usuariosMap[$id]->nome ?? "Usuário #$id",
                'nivel' => isset($usuariosMap[$id]) ? $usuariosMap[$id]->nivel_acesso->label() : '',
                'total' => $data['total'],
                'finalizadas' => $data['finalizadas'],
            ])
            ->sortByDesc('total')->values();

        // === Evolução mensal (atividades) ===
        $porMes = $atividadesFiltradas->groupBy(fn($a) => $a['data_abertura']?->format('Y-m'))
            ->map(fn($grupo, $mes) => [
                'mes' => $mes,
                'total' => $grupo->count(),
                'finalizadas' => $grupo->where('status', 'finalizada')->count(),
            ])->sortKeys()->values();

        // === Top 5 ações mais executadas (para destaque) ===
        $topAcoes = $porTipoAcao->take(5);

        // === Dados para filtros (escopo por perfil) ===
        $municipiosQuery = Municipio::where('ativo', true)->orderBy('nome');
        if ($usuario->isMunicipal() && $usuario->municipio_id) {
            $municipiosQuery->where('id', $usuario->municipio_id);
        }
        $municipios = $municipiosQuery->get(['id', 'nome']);

        $usuariosQuery = UsuarioInterno::where('ativo', true)->orderBy('nome');
        if ($usuario->isMunicipal() && $usuario->municipio_id) {
            $usuariosQuery->where('municipio_id', $usuario->municipio_id);
        } elseif ($usuario->isEstadual()) {
            $usuariosQuery->whereIn('nivel_acesso', ['administrador', 'gestor_estadual', 'tecnico_estadual']);
        }
        $usuarios = $usuariosQuery->get(['id', 'nome']);

        $escopoVisual = 'Visão geral — todos os dados';
        if ($usuario->isMunicipal()) {
            $escopoVisual = 'Escopo: ' . ($usuario->municipioRelacionado->nome ?? 'Município');
        } elseif ($usuario->isEstadual()) {
            $escopoVisual = 'Escopo: Competência estadual';
        }

        return view('admin.relatorios.acoes-atividade', compact(
            'totalOS', 'totalOSConcluidas', 'totalAtividades', 'totalAtivFinalizadas', 'totalAtivPendentes',
            'totalEstadual', 'totalMunicipal', 'pctConclusao',
            'porTipoAcao', 'porMunicipio', 'porRegiao', 'porUsuarioFormatado', 'porMes', 'topAcoes',
            'municipios', 'usuarios', 'escopoVisual'
        ));
    }

}

