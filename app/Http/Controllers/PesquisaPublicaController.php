<?php

namespace App\Http\Controllers;

use App\Models\PesquisaSatisfacao;
use App\Models\PesquisaSatisfacaoResposta;
use App\Models\OrdemServico;
use App\Models\Estabelecimento;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PesquisaPublicaController extends Controller
{
    /**
     * Exibe o formulário público da pesquisa pelo slug.
     * Aceita ?os={id}&est={id} opcionalmente para vincular à OS/Estabelecimento.
     */
    public function show(Request $request, string $slug)
    {
        $pesquisa = PesquisaSatisfacao::where('slug', $slug)
            ->where('ativo', true)
            ->with(['perguntas.opcoes'])
            ->firstOrFail();

        // Contexto opcional: Ordem de Serviço e Estabelecimento
        $ordemServico    = null;
        $estabelecimento = null;

        if ($request->filled('os')) {
            $ordemServico = OrdemServico::find($request->input('os'));
        }
        if ($request->filled('est')) {
            $estabelecimento = Estabelecimento::find($request->input('est'));
        } elseif ($ordemServico) {
            // Pega o primeiro estabelecimento da OS
            $estabelecimento = $ordemServico->getTodosEstabelecimentos()->first()
                ?? ($ordemServico->estabelecimento_id ? Estabelecimento::find($ordemServico->estabelecimento_id) : null);
        }

        // Auto-preencher dados se usuário logado
        $respondente = ['nome' => '', 'email' => ''];
        $usuarioInternoId = null;
        $usuarioExternoId = null;

        if (auth('interno')->check()) {
            $u = auth('interno')->user();
            $respondente = ['nome' => $u->nome, 'email' => $u->email];
            $usuarioInternoId = $u->id;
        } elseif (auth('externo')->check()) {
            $u = auth('externo')->user();
            $respondente = ['nome' => $u->nome, 'email' => $u->email];
            $usuarioExternoId = $u->id;
        }

        return view('pesquisa-publica.show', compact(
            'pesquisa', 'ordemServico', 'estabelecimento',
            'respondente', 'usuarioInternoId', 'usuarioExternoId'
        ));
    }

    /**
     * Salva as respostas enviadas pelo respondente.
     */
    public function responder(Request $request, string $slug)
    {
        $pesquisa = PesquisaSatisfacao::where('slug', $slug)
            ->where('ativo', true)
            ->with(['perguntas.opcoes'])
            ->firstOrFail();

        // Validação dinâmica baseada nas perguntas
        $rules = [
            'respondente_nome'   => 'nullable|string|max:255',
            'respondente_email'  => 'nullable|email|max:255',
            'ordem_servico_id'   => 'nullable|integer',
            'estabelecimento_id' => 'nullable|integer',
            'usuario_interno_id' => 'nullable|integer',
            'usuario_externo_id' => 'nullable|integer',
        ];

        foreach ($pesquisa->perguntas as $pergunta) {
            $key = "resp_{$pergunta->id}";
            if ($pergunta->obrigatoria) {
                $rules[$key] = 'required';
            } else {
                $rules[$key] = 'nullable';
            }
            if ($pergunta->tipo === 'escala_1_5') {
                $rules[$key] .= '|integer|min:1|max:5';
            }
        }

        $data = $request->validate($rules);

        // Monta array de respostas
        $respostas = [];
        foreach ($pesquisa->perguntas as $pergunta) {
            $key   = "resp_{$pergunta->id}";
            $valor = $data[$key] ?? null;
            if ($valor === null) {
                continue;
            }

            $respostas[] = [
                'pergunta_id'   => $pergunta->id,
                'pergunta_texto'=> $pergunta->texto,
                'tipo'          => $pergunta->tipo,
                'valor'         => $valor,
            ];
        }

        // Determinar tipo de respondente
        $tipoRespondente = null;
        if (!empty($data['usuario_interno_id'])) {
            $tipoRespondente = 'interno';
        } elseif (!empty($data['usuario_externo_id'])) {
            $tipoRespondente = 'externo';
        } elseif (!empty($data['respondente_nome']) || !empty($data['respondente_email'])) {
            $tipoRespondente = 'externo';
        }

        PesquisaSatisfacaoResposta::create([
            'pesquisa_id'        => $pesquisa->id,
            'ordem_servico_id'   => $data['ordem_servico_id'] ?? null,
            'estabelecimento_id' => $data['estabelecimento_id'] ?? null,
            'usuario_interno_id' => $data['usuario_interno_id'] ?? null,
            'usuario_externo_id' => $data['usuario_externo_id'] ?? null,
            'tipo_respondente'   => $tipoRespondente,
            'respondente_nome'   => $data['respondente_nome'] ?? null,
            'respondente_email'  => $data['respondente_email'] ?? null,
            'ip_address'         => $request->ip(),
            'token'              => Str::random(64),
            'respostas'          => $respostas,
        ]);

        return redirect()
            ->route('pesquisa.obrigado', $slug)
            ->with('success', 'Obrigado! Sua resposta foi registrada com sucesso.');
    }

    /**
     * Tela de agradecimento após responder.
     */
    public function obrigado(string $slug)
    {
        $pesquisa = PesquisaSatisfacao::where('slug', $slug)->firstOrFail();

        return view('pesquisa-publica.obrigado', compact('pesquisa'));
    }

    /**
     * Submissão de pesquisa interna via AJAX (técnico autenticado).
     */
    public function responderInterno(Request $request)
    {
        $request->validate([
            'pesquisa_id'      => 'required|integer|exists:pesquisas_satisfacao,id',
            'ordem_servico_id' => 'required|integer|exists:ordens_servico,id',
            'respostas'        => 'required|array',
        ]);

        $pesquisa = PesquisaSatisfacao::with('perguntas')->findOrFail($request->pesquisa_id);
        $usuario  = auth('interno')->user();

        // Verifica se já respondeu para esta OS
        $jaRespondeu = PesquisaSatisfacaoResposta::where('pesquisa_id', $pesquisa->id)
            ->where('ordem_servico_id', $request->ordem_servico_id)
            ->where('usuario_interno_id', $usuario->id)
            ->exists();

        if ($jaRespondeu) {
            return response()->json(['message' => 'Você já respondeu esta pesquisa para esta OS.'], 422);
        }

        // Validar perguntas obrigatórias
        foreach ($pesquisa->perguntas as $pergunta) {
            if ($pergunta->obrigatoria) {
                $valor = $request->input("respostas.{$pergunta->id}");
                if (empty($valor) && $valor !== '0') {
                    return response()->json([
                        'message' => 'Por favor, responda a pergunta: "' . $pergunta->texto . '"'
                    ], 422);
                }
            }
        }

        // Montar respostas
        $respostas = [];
        foreach ($pesquisa->perguntas as $pergunta) {
            $valor = $request->input("respostas.{$pergunta->id}");
            if ($valor === null) continue;

            $respostas[] = [
                'pergunta_id'    => $pergunta->id,
                'pergunta_texto' => $pergunta->texto,
                'tipo'           => $pergunta->tipo,
                'valor'          => $valor,
            ];
        }

        // Pega o primeiro estabelecimento da OS
        $os    = OrdemServico::find($request->ordem_servico_id);
        $estab = $os ? ($os->getTodosEstabelecimentos()->first() ?? ($os->estabelecimento_id ? Estabelecimento::find($os->estabelecimento_id) : null)) : null;

        PesquisaSatisfacaoResposta::create([
            'pesquisa_id'        => $pesquisa->id,
            'ordem_servico_id'   => $request->ordem_servico_id,
            'estabelecimento_id' => $estab?->id,
            'usuario_interno_id' => $usuario->id,
            'tipo_respondente'   => 'interno',
            'respondente_nome'   => $usuario->nome,
            'respondente_email'  => $usuario->email,
            'ip_address'         => $request->ip(),
            'token'              => Str::random(64),
            'respostas'          => $respostas,
        ]);

        return response()->json(['message' => 'Pesquisa enviada com sucesso!']);
    }
}
