<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CnpjService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CnpjController extends Controller
{
    private CnpjService $cnpjService;

    public function __construct(CnpjService $cnpjService)
    {
        $this->cnpjService = $cnpjService;
    }

    /**
     * Consulta dados de CNPJ na API Minha Receita
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function consultar(Request $request): JsonResponse
    {
        try {
            \Log::info('=== CONSULTA CNPJ INICIADA ===', [
                'request_data' => $request->all(),
                'cnpj_recebido' => $request->input('cnpj')
            ]);

            $request->validate([
                'cnpj' => 'required|string|min:14|max:18'
            ]);

            $cnpj = $request->input('cnpj');
            
            // Remove formatação
            $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);
            
            \Log::info('CNPJ após limpeza', [
                'cnpj_original' => $cnpj,
                'cnpj_limpo' => $cnpjLimpo,
                'tamanho' => strlen($cnpjLimpo)
            ]);
            
            // Valida CNPJ
            if (!CnpjService::validarCnpj($cnpjLimpo)) {
                \Log::warning('CNPJ inválido', ['cnpj' => $cnpjLimpo]);
                return response()->json([
                    'success' => false,
                    'message' => 'CNPJ inválido'
                ], 400);
            }

            // Consulta na API
            $dados = $this->cnpjService->consultarCnpj($cnpjLimpo);

            if (!$dados) {
                return response()->json([
                    'success' => false,
                    'message' => 'CNPJ não encontrado em nenhuma base de dados da Receita Federal. Você pode preencher os dados manualmente.'
                ], 404);
            }

            // Identifica qual API retornou os dados
            $apiSource = $dados['api_source'] ?? 'desconhecida';
            $apiNames = [
                'minha_receita' => 'Receita Federal',
                'brasil_api' => 'BrasilAPI',
                'receita_ws' => 'ReceitaWS',
                'publica_cnpj_ws' => 'Publica CNPJ WS',
                'cnpja_commercial' => 'CNPJa (Receita em tempo real)',
            ];

            return response()->json([
                'success' => true,
                'message' => 'Dados encontrados com sucesso',
                'api_source' => $apiNames[$apiSource] ?? 'Desconhecida',
                'data' => $dados
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro na consulta CNPJ', [
                'error' => $e->getMessage(),
                'cnpj' => $request->input('cnpj')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Verifica se um CNPJ já existe no sistema
     * Retorna lista de estabelecimentos se existirem
     */
    public function verificarExistente($cnpj)
    {
        try {
            // Remove formatação do CNPJ
            $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

            // Busca estabelecimentos com este CNPJ
            $estabelecimentos = \App\Models\Estabelecimento::where('cnpj', $cnpjLimpo)
                ->select('id', 'nome_fantasia', 'tipo_setor')
                ->get();

            $existe = $estabelecimentos->count() > 0;

            return response()->json([
                'existe' => $existe,
                'cnpj' => $cnpjLimpo,
                'estabelecimentos' => $estabelecimentos->map(function($est) {
                    return [
                        'id' => $est->id,
                        'nome_fantasia' => $est->nome_fantasia,
                        'tipo_setor' => $est->tipo_setor
                    ];
                })
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao verificar CNPJ existente', [
                'cnpj' => $cnpj,
                'erro' => $e->getMessage()
            ]);

            return response()->json([
                'existe' => false,
                'erro' => 'Erro ao verificar CNPJ',
                'estabelecimentos' => []
            ], 500);
        }
    }

    /**
     * Verifica se as atividades são de competência estadual ou municipal
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verificarCompetencia(Request $request): JsonResponse
    {
        try {
            $atividades = $request->input('atividades', []);
            $municipio = $request->input('municipio', null);
            $respostasQuestionario = $request->input('respostas_questionario', []);
            $respostasQuestionario2 = $request->input('respostas_questionario2', []); // Segunda pergunta (localização)
            
            \Log::info('=== VERIFICAÇÃO DE COMPETÊNCIA INICIADA ===', [
                'atividades_recebidas' => $atividades,
                'municipio_recebido' => $municipio,
                'respostas_recebidas' => $respostasQuestionario,
                'respostas2_recebidas' => $respostasQuestionario2
            ]);
            
            // Valida se tem atividades
            if (empty($atividades) || !is_array($atividades)) {
                return response()->json([
                    'competencia' => 'municipal',
                    'atividades_verificadas' => 0,
                    'erro' => 'Nenhuma atividade fornecida'
                ]);
            }
            
            // Remove " - TO" ou "/TO" do nome do município
            if ($municipio) {
                $municipio = preg_replace('/\s*[-\/]\s*TO\s*$/i', '', $municipio);
                $municipio = trim($municipio);
            }

            // Normaliza as chaves das respostas para garantir compatibilidade
            $respostasNormalizadas = [];
            if (is_array($respostasQuestionario)) {
                foreach ($respostasQuestionario as $key => $val) {
                    $keyLimpa = preg_replace('/[^0-9]/', '', $key);
                    $respostasNormalizadas[$keyLimpa] = $val;
                    if ($key !== $keyLimpa) {
                        $respostasNormalizadas[$key] = $val;
                    }
                }
            }
            
            // Normaliza respostas da segunda pergunta
            $respostas2Normalizadas = [];
            if (is_array($respostasQuestionario2)) {
                foreach ($respostasQuestionario2 as $key => $val) {
                    $keyLimpa = preg_replace('/[^0-9]/', '', $key);
                    $respostas2Normalizadas[$keyLimpa] = $val;
                    if ($key !== $keyLimpa) {
                        $respostas2Normalizadas[$key] = $val;
                    }
                }
            }

            // Verifica competência usando o método avançado
            $temAtividadeEstadual = false;
            $temAtividadeNaoSujeitaVisa = false;
            $atividadesVerificadas = [];
            $riscoMaisAlto = 'baixo';
            $ordemRisco = ['baixo' => 1, 'medio' => 2, 'alto' => 3];
            
            foreach ($atividades as $cnae) {
                try {
                    $cnaeOriginal = $cnae;
                    
                    // Atividades especiais (PROJ_ARQ, ANAL_ROT) não devem ser limpas
                    if (in_array($cnae, ['PROJ_ARQ', 'ANAL_ROT'])) {
                        $cnaeLimpo = $cnae;
                    } else {
                        $cnaeLimpo = preg_replace('/[^0-9]/', '', $cnae);
                    }
                    
                    // Busca as respostas para este CNAE
                    $resposta1 = $respostasNormalizadas[$cnaeLimpo] ?? $respostasNormalizadas[$cnaeOriginal] ?? null;
                    $resposta2 = $respostas2Normalizadas[$cnaeLimpo] ?? $respostas2Normalizadas[$cnaeOriginal] ?? null;
                    
                    // Usa o método avançado de verificação
                    $resultado = \App\Models\Pactuacao::verificarCompetenciaAvancada(
                        $cnaeLimpo, 
                        $municipio, 
                        $resposta1, 
                        $resposta2
                    );
                    
                    \Log::info('Resultado verificação avançada', [
                        'cnae' => $cnaeLimpo,
                        'resultado' => $resultado
                    ]);
                    
                    // Processa o resultado
                    $isEstadual = $resultado['competencia'] === 'estadual';
                    $naoSujeitoVisa = $resultado['competencia'] === 'nao_sujeito_visa';
                    
                    if ($isEstadual) {
                        $temAtividadeEstadual = true;
                    }
                    if ($naoSujeitoVisa) {
                        $temAtividadeNaoSujeitaVisa = true;
                    }
                    
                    // Atualiza o risco mais alto
                    $riscoAtividade = $resultado['risco'] ?? 'baixo';
                    if (($ordemRisco[$riscoAtividade] ?? 0) > ($ordemRisco[$riscoMaisAlto] ?? 0)) {
                        $riscoMaisAlto = $riscoAtividade;
                    }
                    
                    $atividadesVerificadas[] = [
                        'cnae' => $cnaeLimpo,
                        'estadual' => $isEstadual,
                        'nao_sujeito_visa' => $naoSujeitoVisa,
                        'competencia' => $resultado['competencia'],
                        'risco' => $resultado['risco'],
                        'detalhes' => $resultado['detalhes'] ?? []
                    ];
                    
                } catch (\Exception $e) {
                    \Log::error('Erro ao verificar CNAE individual', [
                        'cnae' => $cnae,
                        'erro' => $e->getMessage()
                    ]);
                }
            }
            
            // Determina a competência final
            $competenciaFinal = 'municipal';
            
            if ($temAtividadeNaoSujeitaVisa && !$temAtividadeEstadual) {
                // Verifica se TODAS as atividades são não sujeitas à VISA
                $todasNaoSujeitas = true;
                foreach ($atividadesVerificadas as $av) {
                    if ($av['competencia'] !== 'nao_sujeito_visa') {
                        $todasNaoSujeitas = false;
                        break;
                    }
                }
                
                if ($todasNaoSujeitas) {
                    $competenciaFinal = 'nao_sujeito_visa';
                }
            } elseif ($temAtividadeEstadual) {
                $competenciaFinal = 'estadual';
            }
            
            $resultado = [
                'competencia' => $competenciaFinal,
                'risco' => $riscoMaisAlto,
                'atividades_verificadas' => count($atividades),
                'detalhes' => $atividadesVerificadas,
                'municipio' => $municipio,
                'tem_atividade_nao_sujeita_visa' => $temAtividadeNaoSujeitaVisa
            ];
            
            \Log::info('=== RESULTADO FINAL ===', $resultado);

            return response()->json($resultado);

        } catch (\Exception $e) {
            \Log::error('ERRO GERAL ao verificar competência', [
                'request_all' => $request->all(),
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'competencia' => 'municipal',
                'erro' => $e->getMessage(),
                'atividades_verificadas' => 0
            ], 200);
        }
    }
}
