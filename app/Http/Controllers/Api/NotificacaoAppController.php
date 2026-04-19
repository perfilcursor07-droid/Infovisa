<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\DocumentoDigital;
use Illuminate\Http\Request;

class NotificacaoAppController extends Controller
{
    /**
     * Retorna notificações pendentes para o usuário externo logado.
     * Usado pelo app Android para disparar push notifications locais.
     */
    public function index(Request $request)
    {
        $usuario = auth('externo')->user();

        if (!$usuario) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }

        $notificacoes = [];

        // IDs dos estabelecimentos do usuário
        $estabelecimentoIds = Estabelecimento::where('usuario_externo_id', $usuario->id)
            ->orWhereHas('usuariosVinculados', fn($q) => $q->where('usuario_externo_id', $usuario->id))
            ->pluck('id');

        // 1. Estabelecimentos pendentes de aprovação
        $pendentes = Estabelecimento::whereIn('id', $estabelecimentoIds)
            ->where('status', 'pendente')
            ->get(['id', 'nome_fantasia', 'razao_social', 'status']);

        foreach ($pendentes as $est) {
            $notificacoes[] = [
                'tipo' => 'estabelecimento_pendente',
                'titulo' => 'Estabelecimento em análise',
                'mensagem' => ($est->nome_fantasia ?: $est->razao_social) . ' está aguardando aprovação.',
                'url' => '/company/estabelecimentos/' . $est->id,
                'id' => 'est_pend_' . $est->id,
            ];
        }

        // 2. Estabelecimentos aprovados recentemente (últimas 48h)
        $aprovados = Estabelecimento::whereIn('id', $estabelecimentoIds)
            ->where('status', 'aprovado')
            ->where('aprovado_em', '>=', now()->subHours(48))
            ->get(['id', 'nome_fantasia', 'razao_social', 'aprovado_em']);

        foreach ($aprovados as $est) {
            $notificacoes[] = [
                'tipo' => 'estabelecimento_aprovado',
                'titulo' => 'Estabelecimento aprovado!',
                'mensagem' => ($est->nome_fantasia ?: $est->razao_social) . ' foi aprovado.',
                'url' => '/company/estabelecimentos/' . $est->id,
                'id' => 'est_aprov_' . $est->id,
            ];
        }

        // 3. Estabelecimentos rejeitados recentemente (últimas 48h)
        $rejeitados = Estabelecimento::whereIn('id', $estabelecimentoIds)
            ->where('status', 'rejeitado')
            ->where('updated_at', '>=', now()->subHours(48))
            ->get(['id', 'nome_fantasia', 'razao_social', 'motivo_rejeicao']);

        foreach ($rejeitados as $est) {
            $notificacoes[] = [
                'tipo' => 'estabelecimento_rejeitado',
                'titulo' => 'Estabelecimento rejeitado',
                'mensagem' => ($est->nome_fantasia ?: $est->razao_social) . ' foi rejeitado. Verifique o motivo.',
                'url' => '/company/estabelecimentos/' . $est->id,
                'id' => 'est_rej_' . $est->id,
            ];
        }

        // IDs dos processos
        $processoIds = Processo::whereIn('estabelecimento_id', $estabelecimentoIds)
            ->whereHas('tipoProcesso', fn($q) => $q->where('usuario_externo_pode_visualizar', true))
            ->pluck('id');

        // 4. Documentos pendentes de visualização (assinados pela vigilância)
        $docsPendentesVis = DocumentoDigital::whereIn('processo_id', $processoIds)
            ->where('status', 'assinado')
            ->where('sigiloso', false)
            ->whereDoesntHave('visualizacoes')
            ->with(['processo:id,numero_processo,estabelecimento_id', 'processo.estabelecimento:id,nome_fantasia,razao_social', 'tipoDocumento:id,nome'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($docsPendentesVis as $doc) {
            if (!$doc->todasAssinaturasCompletas()) continue;
            $nomeEst = $doc->processo?->estabelecimento?->nome_fantasia ?: $doc->processo?->estabelecimento?->razao_social ?: '';
            $notificacoes[] = [
                'tipo' => 'documento_novo',
                'titulo' => 'Novo documento da vigilância',
                'mensagem' => ($doc->tipoDocumento?->nome ?: 'Documento') . ' - ' . $nomeEst,
                'url' => '/company/processos/' . $doc->processo_id,
                'id' => 'doc_vis_' . $doc->id,
            ];
        }

        // 5. Documentos rejeitados (precisam correção)
        $docsRejeitados = ProcessoDocumento::whereIn('processo_id', $processoIds)
            ->where('status_aprovacao', 'rejeitado')
            ->with(['processo:id,numero_processo,estabelecimento_id', 'processo.estabelecimento:id,nome_fantasia,razao_social'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($docsRejeitados as $doc) {
            $nomeEst = $doc->processo?->estabelecimento?->nome_fantasia ?: $doc->processo?->estabelecimento?->razao_social ?: '';
            $notificacoes[] = [
                'tipo' => 'documento_rejeitado',
                'titulo' => 'Documento precisa de correção',
                'mensagem' => ($doc->nome_original ?: 'Documento') . ' - ' . $nomeEst,
                'url' => '/company/processos/' . $doc->processo_id,
                'id' => 'doc_rej_' . $doc->id,
            ];
        }

        // 6. Documentos com prazo (notificações pendentes de resposta)
        $docsComPrazo = DocumentoDigital::whereIn('processo_id', $processoIds)
            ->where('status', 'assinado')
            ->where('sigiloso', false)
            ->where('prazo_notificacao', true)
            ->whereNotNull('prazo_iniciado_em')
            ->whereNull('prazo_finalizado_em')
            ->with(['processo:id,numero_processo,estabelecimento_id', 'processo.estabelecimento:id,nome_fantasia,razao_social', 'tipoDocumento:id,nome'])
            ->orderBy('data_vencimento', 'asc')
            ->limit(10)
            ->get();

        foreach ($docsComPrazo as $doc) {
            if (!$doc->todasAssinaturasCompletas()) continue;
            $nomeEst = $doc->processo?->estabelecimento?->nome_fantasia ?: $doc->processo?->estabelecimento?->razao_social ?: '';
            $diasRestantes = $doc->data_vencimento ? (int) round(now()->diffInDays($doc->data_vencimento, false)) : null;
            $urgencia = $diasRestantes !== null && $diasRestantes <= 0 ? 'VENCIDO' : ($diasRestantes <= 3 ? 'URGENTE' : '');

            $notificacoes[] = [
                'tipo' => 'documento_prazo',
                'titulo' => $urgencia ? "⚠️ {$urgencia}: Prazo de resposta" : 'Prazo de resposta pendente',
                'mensagem' => ($doc->tipoDocumento?->nome ?: 'Documento') . ' - ' . $nomeEst .
                    ($diasRestantes !== null ? " ({$diasRestantes} dias)" : ''),
                'url' => '/company/processos/' . $doc->processo_id,
                'id' => 'doc_prazo_' . $doc->id,
                'dias_restantes' => $diasRestantes,
            ];
        }

        return response()->json([
            'total' => count($notificacoes),
            'notificacoes' => $notificacoes,
        ]);
    }
}
