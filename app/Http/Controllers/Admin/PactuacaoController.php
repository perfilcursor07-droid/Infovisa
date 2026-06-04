<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pactuacao;
use App\Models\Estabelecimento;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PactuacaoController extends Controller
{
    /**
     * Lista todas as pactuações (municipais e estaduais)
     */
    public function index()
    {
        // Busca todos os municípios cadastrados no sistema (para dropdown de exceções)
        $todosMunicipios = Municipio::orderBy('nome')->get();
        
        // Busca pactuações por tabela
        $tabelaI = Pactuacao::where('tabela', 'I')->orderBy('cnae_codigo')->get();
        $tabelaII = Pactuacao::where('tabela', 'II')->orderBy('cnae_codigo')->get();
        $tabelaIII = Pactuacao::where('tabela', 'III')->orderBy('cnae_codigo')->get();
        $tabelaIV = Pactuacao::where('tabela', 'IV')->orderBy('cnae_codigo')->get();
        $tabelaV = Pactuacao::where('tabela', 'V')->orderBy('cnae_codigo')->get();
        $tabelaVI = Pactuacao::where('tabela', 'VI')->orderBy('cnae_codigo')->get();
        
        // Busca pactuações estaduais (todas exceto Tabela I)
        $pactuacoesEstaduais = Pactuacao::where('tipo', 'estadual')
            ->orderBy('tabela')
            ->orderBy('cnae_codigo')
            ->get();

        // Atividades marcadas como contempladas para PJ Unidade Móvel
        $pactuacoesUnidadeMovel = Pactuacao::where('unidade_movel', true)
            ->orderBy('cnae_codigo')
            ->get();

        // Atividades permitidas para cadastro de Pessoa Física
        $pactuacoesPessoaFisica = Pactuacao::where('pessoa_fisica', true)
            ->orderBy('cnae_codigo')
            ->get();

        return view('admin.pactuacoes.index', compact(
            'todosMunicipios',
            'tabelaI',
            'tabelaII',
            'tabelaIII',
            'tabelaIV',
            'tabelaV',
            'tabelaVI',
            'pactuacoesEstaduais',
            'pactuacoesUnidadeMovel',
            'pactuacoesPessoaFisica'
        ));
    }

    /**
     * Retorna dados de uma pactuação específica
     */
    public function show($id)
    {
        $pactuacao = Pactuacao::findOrFail($id);
        return response()->json($pactuacao);
    }

    /**
     * Busca questionários para uma lista de CNAEs
     */
    public function buscarQuestionarios(Request $request)
    {
        $cnaes = $request->input('cnaes', []);
        
        if (empty($cnaes)) {
            return response()->json([]);
        }

        // Normaliza os CNAEs (remove formatação)
        $cnaesNormalizados = array_map(function($cnae) {
            return preg_replace('/[^0-9]/', '', $cnae);
        }, $cnaes);

        // Busca pactuações que requerem questionário
        $questionarios = Pactuacao::whereIn('cnae_codigo', $cnaesNormalizados)
            ->where('requer_questionario', true)
            ->where('ativo', true)
            ->get()
            ->map(function($pactuacao) {
                return [
                    'cnae' => $pactuacao->cnae_codigo,
                    'cnae_formatado' => $pactuacao->cnae_codigo,
                    'descricao' => $pactuacao->cnae_descricao,
                    'pergunta' => $pactuacao->pergunta,
                    'pergunta2' => $pactuacao->pergunta2,
                    'tabela' => $pactuacao->tabela,
                    'tipo_questionario' => $pactuacao->tipo_questionario,
                    'risco_sim' => $pactuacao->risco_sim,
                    'risco_nao' => $pactuacao->risco_nao,
                    'municipios_excecao' => $pactuacao->municipios_excecao ?? [],
                    'municipios_excecao_hospitalar' => $pactuacao->municipios_excecao_hospitalar ?? [],
                ];
            });

        return response()->json($questionarios);
    }

    /**
     * Adiciona uma atividade à pactuação
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:municipal,estadual',
            'municipio' => 'required_if:tipo,municipal',
            'cnae_codigo' => 'required|string',
            'cnae_descricao' => 'required|string',
            'municipios_excecao' => 'nullable|array',
            'observacao' => 'nullable|string',
        ]);
        
        try {
            Pactuacao::create([
                'tipo' => $request->tipo,
                'municipio' => $request->tipo === 'municipal' ? $request->municipio : null,
                'cnae_codigo' => $request->cnae_codigo,
                'cnae_descricao' => $request->cnae_descricao,
                'municipios_excecao' => $request->tipo === 'estadual' ? $request->municipios_excecao : null,
                'observacao' => $request->observacao,
                'ativo' => true,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Atividade adicionada com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar atividade: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Adiciona múltiplas atividades de uma vez
     */
    public function storeMultiple(Request $request)
    {
        // Debug: log dos dados recebidos
        \Log::info('storeMultiple chamado', [
            'dados' => $request->all()
        ]);
        
        $request->validate([
            'tipo' => 'required|in:municipal,estadual',
            'municipio' => 'nullable|string',
            'tabela' => 'required|in:I,II,III,IV,V',
            'classificacao_risco' => 'nullable|in:baixo,medio,alto',
            'pergunta' => 'nullable|string',
            'atividades' => 'required|array',
            'atividades.*.codigo' => 'required|string',
            'atividades.*.descricao' => 'required|string',
            'municipios_excecao' => 'nullable|array',
            'observacao' => 'nullable|string',
            // Novos campos avançados
            'tipo_questionario' => 'nullable|string',
            'pergunta2' => 'nullable|string',
            'risco_sim' => 'nullable|in:baixo,medio,alto',
            'risco_nao' => 'nullable|in:baixo,medio,alto',
            'municipios_excecao_hospitalar' => 'nullable|array',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Determina se requer questionário
            $requerQuestionario = !empty($request->tipo_questionario) || !empty($request->pergunta);
            
            foreach ($request->atividades as $atividade) {
                Pactuacao::updateOrCreate(
                    [
                        'tipo' => $request->tipo,
                        'municipio' => $request->tipo === 'municipal' ? $request->municipio : null,
                        'cnae_codigo' => $atividade['codigo'],
                    ],
                    [
                        'cnae_descricao' => $atividade['descricao'],
                        'tabela' => $request->tabela,
                        'classificacao_risco' => $request->classificacao_risco,
                        'pergunta' => $request->pergunta,
                        'municipios_excecao' => $request->tipo === 'estadual' ? $request->municipios_excecao : null,
                        'observacao' => $request->observacao,
                        'ativo' => true,
                        // Novos campos avançados
                        'requer_questionario' => $requerQuestionario,
                        'tipo_questionario' => $request->tipo_questionario,
                        'pergunta2' => $request->pergunta2,
                        'risco_sim' => $request->risco_sim,
                        'risco_nao' => $request->risco_nao,
                        'municipios_excecao_hospitalar' => $request->municipios_excecao_hospitalar,
                    ]
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => count($request->atividades) . ' atividade(s) adicionada(s) com sucesso!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar atividades: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Ativa/Desativa uma pactuação
     */
    public function toggleStatus($id)
    {
        try {
            $pactuacao = Pactuacao::findOrFail($id);
            $pactuacao->ativo = !$pactuacao->ativo;
            $pactuacao->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso!',
                'ativo' => $pactuacao->ativo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Marca/desmarca uma atividade da pactuação como contemplada para PJ Unidade Móvel
     */
    public function toggleUnidadeMovel(Request $request, $id)
    {
        try {
            $pactuacao = Pactuacao::findOrFail($id);

            // Se enviado explicitamente, usa o valor; senão inverte
            if ($request->has('unidade_movel')) {
                $pactuacao->unidade_movel = $request->boolean('unidade_movel');
            } else {
                $pactuacao->unidade_movel = !$pactuacao->unidade_movel;
            }

            $pactuacao->save();

            return response()->json([
                'success' => true,
                'message' => $pactuacao->unidade_movel
                    ? 'Atividade marcada para Unidade Móvel!'
                    : 'Atividade removida da lista de Unidade Móvel!',
                'unidade_movel' => $pactuacao->unidade_movel,
                'pactuacao' => [
                    'id' => $pactuacao->id,
                    'cnae_codigo' => $pactuacao->cnae_codigo,
                    'cnae_descricao' => $pactuacao->cnae_descricao,
                    'tabela' => $pactuacao->tabela,
                    'tipo' => $pactuacao->tipo,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Marca/desmarca uma atividade da pactuação como permitida para Pessoa Física
     */
    public function togglePessoaFisica(Request $request, $id)
    {
        try {
            $pactuacao = Pactuacao::findOrFail($id);

            if ($request->has('pessoa_fisica')) {
                $pactuacao->pessoa_fisica = $request->boolean('pessoa_fisica');
            } else {
                $pactuacao->pessoa_fisica = !$pactuacao->pessoa_fisica;
            }

            $pactuacao->save();

            return response()->json([
                'success' => true,
                'message' => $pactuacao->pessoa_fisica
                    ? 'Atividade liberada para Pessoa Física!'
                    : 'Atividade removida da lista de Pessoa Física!',
                'pessoa_fisica' => $pactuacao->pessoa_fisica,
                'pactuacao' => [
                    'id' => $pactuacao->id,
                    'cnae_codigo' => $pactuacao->cnae_codigo,
                    'cnae_descricao' => $pactuacao->cnae_descricao,
                    'tabela' => $pactuacao->tabela,
                    'tipo' => $pactuacao->tipo,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove uma pactuação
     */
    public function destroy($id)
    {
        try {
            $pactuacao = Pactuacao::findOrFail($id);
            $pactuacao->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Atividade removida com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover atividade: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Busca CNAEs disponíveis (para autocomplete)
     */
    public function buscarCnaes(Request $request)
    {
        $termo = $request->get('termo', '');
        
        if (empty($termo)) {
            return response()->json([]);
        }
        
        // Remove TODOS os caracteres não numéricos (aceita com ou sem formatação)
        $termoLimpo = preg_replace('/[^0-9]/', '', $termo);
        
        \Log::info('Buscando CNAE', ['termo_original' => $termo, 'termo_limpo' => $termoLimpo]);
        
        try {
            // API OFICIAL DO IBGE - Subclasses (7 dígitos)
            if (strlen($termoLimpo) === 7) {
                $url = "https://servicodados.ibge.gov.br/api/v2/cnae/subclasses/{$termoLimpo}";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200 && $response) {
                    $data = json_decode($response, true);
                    
                    // Se retornou um array, pega o primeiro
                    if (is_array($data) && !isset($data['id'])) {
                        $data = $data[0] ?? null;
                    }
                    
                    if ($data && isset($data['id']) && isset($data['descricao'])) {
                        \Log::info('CNAE encontrado na API IBGE (subclasse)', ['cnae' => $data]);
                        return response()->json([
                            [
                                'codigo' => $termoLimpo,
                                'descricao' => $data['descricao']
                            ]
                        ]);
                    }
                }
            }
            
            // API OFICIAL DO IBGE - Classes (5 dígitos)
            if (strlen($termoLimpo) === 5) {
                $url = "https://servicodados.ibge.gov.br/api/v2/cnae/classes/{$termoLimpo}";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200 && $response) {
                    $data = json_decode($response, true);
                    
                    // Se retornou um array, pega o primeiro
                    if (is_array($data) && !isset($data['id'])) {
                        $data = $data[0] ?? null;
                    }
                    
                    if ($data && isset($data['id']) && isset($data['descricao'])) {
                        \Log::info('CNAE encontrado na API IBGE (classe)', ['cnae' => $data]);
                        return response()->json([
                            [
                                'codigo' => $termoLimpo,
                                'descricao' => $data['descricao']
                            ]
                        ]);
                    }
                }
            }
            
            // Se não encontrou nas APIs do IBGE, busca nos estabelecimentos cadastrados
            $cnaes = Estabelecimento::select('cnae_fiscal as codigo', 'cnae_fiscal_descricao as descricao')
                ->whereNotNull('cnae_fiscal')
                ->where(function($q) use ($termoLimpo) {
                    $q->where('cnae_fiscal', 'like', "%{$termoLimpo}%")
                      ->orWhere('cnae_fiscal_descricao', 'like', "%{$termoLimpo}%");
                })
                ->distinct()
                ->limit(20)
                ->get();
            
            \Log::info('CNAEs encontrados nos estabelecimentos', ['count' => $cnaes->count()]);
            
            if ($cnaes->isNotEmpty()) {
                return response()->json($cnaes);
            }
            
            // Se não encontrou em lugar nenhum, retorna o código com descrição genérica
            \Log::info('CNAE não encontrado, retornando descrição genérica');
            return response()->json([
                [
                    'codigo' => $termoLimpo,
                    'descricao' => "CNAE {$termoLimpo} - Descrição não disponível (adicione manualmente)"
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar CNAE: ' . $e->getMessage());
            
            // Em caso de erro, retorna o código com descrição genérica
            return response()->json([
                [
                    'codigo' => $termoLimpo,
                    'descricao' => "CNAE {$termoLimpo} - Descrição não disponível (adicione manualmente)"
                ]
            ]);
        }
    }
    
    /**
     * Adiciona um município à lista de exceções de uma pactuação estadual
     */
    public function adicionarExcecao(Request $request, $id)
    {
        $request->validate([
            'municipio' => 'required|string',
        ]);
        
        try {
            $pactuacao = Pactuacao::findOrFail($id);
            
            if ($pactuacao->tipo !== 'estadual') {
                return response()->json([
                    'success' => false,
                    'message' => 'Exceções só podem ser adicionadas a pactuações estaduais'
                ], 422);
            }
            
            $pactuacao->adicionarMunicipioExcecao($request->municipio);
            
            return response()->json([
                'success' => true,
                'message' => 'Município adicionado às exceções com sucesso!',
                'municipios_excecao' => $pactuacao->municipios_excecao
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar exceção: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Remove um município da lista de exceções de uma pactuação estadual
     */
    public function removerExcecao(Request $request, $id)
    {
        $request->validate([
            'municipio' => 'required|string',
        ]);
        
        try {
            $pactuacao = Pactuacao::findOrFail($id);
            
            if ($pactuacao->tipo !== 'estadual') {
                return response()->json([
                    'success' => false,
                    'message' => 'Exceções só podem ser removidas de pactuações estaduais'
                ], 422);
            }
            
            $pactuacao->removerMunicipioExcecao($request->municipio);
            
            return response()->json([
                'success' => true,
                'message' => 'Município removido das exceções com sucesso!',
                'municipios_excecao' => $pactuacao->municipios_excecao
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover exceção: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Atualiza observação e exceções de uma pactuação
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tabela' => 'nullable|in:I,II,III,IV,V',
            'classificacao_risco' => 'nullable|in:baixo,medio,alto',
            'pergunta' => 'nullable|string',
            'observacao' => 'nullable|string',
            'municipios_excecao' => 'nullable|array',
            // Novos campos avançados
            'tipo_questionario' => 'nullable|string',
            'pergunta2' => 'nullable|string',
            'risco_sim' => 'nullable|in:baixo,medio,alto',
            'risco_nao' => 'nullable|in:baixo,medio,alto',
            'municipios_excecao_hospitalar' => 'nullable|array',
        ]);
        
        try {
            $pactuacao = Pactuacao::findOrFail($id);
            
            // Atualiza todos os campos se fornecidos
            if ($request->has('tabela')) {
                $pactuacao->tabela = $request->tabela;
            }
            
            if ($request->has('classificacao_risco')) {
                $pactuacao->classificacao_risco = $request->classificacao_risco;
            }
            
            if ($request->has('pergunta')) {
                $pactuacao->pergunta = $request->pergunta;
            }
            
            if ($request->has('observacao')) {
                $pactuacao->observacao = $request->observacao;
            }
            
            if ($request->has('municipios_excecao') && $pactuacao->tipo === 'estadual') {
                $pactuacao->municipios_excecao = $request->municipios_excecao;
            }
            
            // Novos campos avançados
            if ($request->has('tipo_questionario')) {
                $pactuacao->tipo_questionario = $request->tipo_questionario;
                $pactuacao->requer_questionario = !empty($request->tipo_questionario) || !empty($request->pergunta);
            }
            
            if ($request->has('pergunta2')) {
                $pactuacao->pergunta2 = $request->pergunta2;
            }
            
            if ($request->has('risco_sim')) {
                $pactuacao->risco_sim = $request->risco_sim;
            }
            
            if ($request->has('risco_nao')) {
                $pactuacao->risco_nao = $request->risco_nao;
            }
            
            if ($request->has('municipios_excecao_hospitalar')) {
                $pactuacao->municipios_excecao_hospitalar = $request->municipios_excecao_hospitalar;
            }
            
            $pactuacao->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Pactuação atualizada com sucesso!',
                'pactuacao' => $pactuacao
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar pactuação: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Pesquisa atividades por código CNAE ou descrição
     */
    public function pesquisar(Request $request)
    {
        $termo = $request->input('termo', '');
        
        if (strlen($termo) < 2) {
            return response()->json([]);
        }
        
        // Busca por código CNAE ou descrição
        $resultados = Pactuacao::where(function($query) use ($termo) {
                $query->where('cnae_codigo', 'LIKE', '%' . $termo . '%')
                      ->orWhere('cnae_descricao', 'ILIKE', '%' . $termo . '%');
            })
            ->where('ativo', true)
            ->orderBy('tabela')
            ->orderBy('cnae_codigo')
            ->limit(50)
            ->get()
            ->map(function($pactuacao) {
                return [
                    'id' => $pactuacao->id,
                    'cnae_codigo' => $pactuacao->cnae_codigo,
                    'cnae_descricao' => $pactuacao->cnae_descricao,
                    'tabela' => $pactuacao->tabela,
                    'tipo' => $pactuacao->tipo,
                    'observacao' => $pactuacao->observacao,
                    'classificacao_risco' => $pactuacao->classificacao_risco,
                    'requer_questionario' => $pactuacao->requer_questionario,
                    'unidade_movel' => (bool) $pactuacao->unidade_movel,
                    'pessoa_fisica' => (bool) $pactuacao->pessoa_fisica,
                ];
            });
        
        return response()->json($resultados);
    }
}
