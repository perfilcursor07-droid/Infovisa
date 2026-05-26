<?php

namespace App\Http\Controllers;

use App\Models\ProcessoPasta;
use App\Models\Processo;
use Illuminate\Http\Request;

class ProcessoPastaController extends Controller
{
    /**
     * Listar pastas de um processo
     */
    public function index($estabelecimentoId, $processoId)
    {
        $processo = Processo::findOrFail($processoId);
        $pastas = $processo->pastas()->orderBy('ordem')->get();

        return response()->json($pastas);
    }

    /**
     * Criar nova pasta
     */
    public function store(Request $request, $estabelecimentoId, $processoId)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string|max:7',
        ]);

        $processo = Processo::findOrFail($processoId);

        // Pega a última ordem
        $ultimaOrdem = $processo->pastas()->max('ordem') ?? 0;

        $pasta = ProcessoPasta::create([
            'processo_id' => $processoId,
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'] ?? null,
            'cor' => $validated['cor'] ?? '#3B82F6',
            'ordem' => $ultimaOrdem + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pasta criada com sucesso!',
            'pasta' => $pasta,
        ]);
    }

    /**
     * Atualizar pasta
     */
    public function update(Request $request, $estabelecimentoId, $processoId, $pastaId)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string|max:7',
        ]);

        $pasta = ProcessoPasta::where('processo_id', $processoId)
            ->findOrFail($pastaId);

        $pasta->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pasta atualizada com sucesso!',
            'pasta' => $pasta,
        ]);
    }

    /**
     * Vincula uma pasta existente a uma unidade do tipo de processo.
     * Útil quando técnicos criaram pastas avulsas que deveriam ser unidades.
     */
    public function vincularUnidade(Request $request, $estabelecimentoId, $processoId, $pastaId)
    {
        $request->validate([
            'unidade_id' => 'nullable|exists:unidades,id',
        ]);

        $processo = Processo::with('tipoProcesso')->findOrFail($processoId);
        $pasta = ProcessoPasta::where('processo_id', $processoId)->findOrFail($pastaId);

        $unidadeId = $request->unidade_id ? (int) $request->unidade_id : null;

        if ($unidadeId) {
            // Verifica se a unidade pertence ao tipo de processo
            $unidadesDoTipo = $processo->tipoProcesso->unidades()->ativas()->pluck('unidades.id')->toArray();
            if (!in_array($unidadeId, $unidadesDoTipo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta unidade não está disponível para este tipo de processo.',
                ], 422);
            }

            // Vincula a unidade ao processo (caso ainda não esteja)
            $processo->unidades()->syncWithoutDetaching([$unidadeId]);
        }

        $pasta->update([
            'unidade_id' => $unidadeId,
            'protegida' => $unidadeId ? true : $pasta->protegida,
        ]);

        return response()->json([
            'success' => true,
            'message' => $unidadeId
                ? 'Pasta vinculada à unidade com sucesso!'
                : 'Vínculo de unidade removido.',
            'pasta' => $pasta->fresh(),
        ]);
    }

    /**
     * Excluir pasta
     */
    public function destroy($estabelecimentoId, $processoId, $pastaId)
    {
        $pasta = ProcessoPasta::where('processo_id', $processoId)
            ->findOrFail($pastaId);

        // Não permite excluir pastas protegidas (criadas automaticamente por unidades)
        if ($pasta->protegida) {
            return response()->json([
                'success' => false,
                'message' => 'Esta pasta não pode ser excluída pois está vinculada a uma unidade do processo.',
            ], 422);
        }

        // Move todos os documentos e arquivos para "Todos" (pasta_id = null)
        $pasta->documentos()->update(['pasta_id' => null]);
        $pasta->documentosDigitais()->update(['pasta_id' => null]);
        \App\Models\OrdemServico::where('pasta_id', $pasta->id)
            ->where(function ($query) use ($processoId) {
                $query->where('processo_id', $processoId)
                    ->orWhereHas('estabelecimentos', function ($subQuery) use ($processoId) {
                        $subQuery->where('ordem_servico_estabelecimentos.processo_id', $processoId);
                    });
            })
            ->update(['pasta_id' => null]);

        $pasta->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pasta excluída com sucesso! Os documentos foram movidos para "Todos".',
        ]);
    }

    /**
     * Mover documento/arquivo para pasta
     */
    public function moverItem(Request $request, $estabelecimentoId, $processoId)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:documento,arquivo,ordem_servico',
            'item_id' => 'required|integer',
            'pasta_id' => 'nullable|integer',
        ]);

        if (!empty($validated['pasta_id'])) {
            $pastaValida = ProcessoPasta::where('processo_id', $processoId)
                ->where('id', $validated['pasta_id'])
                ->exists();

            if (!$pastaValida) {
                return response()->json([
                    'success' => false,
                    'message' => 'A pasta selecionada não pertence a este processo.',
                ], 422);
            }
        }

        if ($validated['tipo'] === 'documento') {
            $item = \App\Models\DocumentoDigital::where('processo_id', $processoId)
                ->findOrFail($validated['item_id']);
        } elseif ($validated['tipo'] === 'arquivo') {
            $item = \App\Models\ProcessoDocumento::where('processo_id', $processoId)
                ->findOrFail($validated['item_id']);
        } else {
            $item = \App\Models\OrdemServico::where('id', $validated['item_id'])
                ->where(function ($query) use ($processoId) {
                    $query->where('processo_id', $processoId)
                        ->orWhereHas('estabelecimentos', function ($subQuery) use ($processoId) {
                            $subQuery->where('ordem_servico_estabelecimentos.processo_id', $processoId);
                        });
                })
                ->firstOrFail();
        }

        $item->update(['pasta_id' => $validated['pasta_id']]);

        return response()->json([
            'success' => true,
            'message' => 'Item movido com sucesso!',
        ]);
    }
}
