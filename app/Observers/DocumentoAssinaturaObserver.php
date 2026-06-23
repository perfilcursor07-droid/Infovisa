<?php

namespace App\Observers;

use App\Models\DocumentoAssinatura;
use App\Models\DocumentoDigital;
use App\Services\DocumentoNotificacaoEmailService;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Log;

class DocumentoAssinaturaObserver
{
    /**
     * Quando uma assinatura é criada já com status assinado,
     * também processa notificação via WhatsApp.
     */
    public function created(DocumentoAssinatura $assinatura): void
    {
        if ($assinatura->status !== 'assinado') {
            return;
        }

        $this->processarNotificacao($assinatura);
    }

    /**
     * Quando uma assinatura é atualizada (assinada),
     * verifica se todas as assinaturas obrigatórias estão completas
     * e envia notificação via WhatsApp
     */
    public function updated(DocumentoAssinatura $assinatura): void
    {
        // Só processa se o status mudou para 'assinado'
        if (!$assinatura->wasChanged('status') || $assinatura->status !== 'assinado') {
            return;
        }

        $this->processarNotificacao($assinatura);
    }

    /**
     * Processa notificação quando assinatura chega em estado assinado
     */
    private function processarNotificacao(DocumentoAssinatura $assinatura): void
    {
        $documento = $assinatura->documentoDigital;
        if (!$documento) {
            return;
        }

        // Verifica se TODAS as assinaturas obrigatórias estão completas
        if (!$documento->todasAssinaturasCompletas()) {
            return;
        }

        // Todas assinadas! Enviar WhatsApp e e-mail
        try {
            $service = new WhatsappService();

            $resultados = $service->notificarDocumentoAssinado($documento);

            Log::info('WhatsApp Observer: Notificação processada', [
                'documento_id' => $documento->id,
                'total' => $resultados['total'],
                'enviados' => $resultados['enviados'],
                'erros' => $resultados['erros'],
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp Observer: Erro ao enviar notificação', [
                'documento_id' => $documento->id,
                'erro' => $e->getMessage(),
            ]);
        }

        defer(function () use ($documento) {
            try {
                $documento->refresh();
                $emailService = new DocumentoNotificacaoEmailService();
                $resultadosEmail = $emailService->notificarDocumentoAssinado($documento);

                Log::info('Email Observer: Notificação processada', [
                    'documento_id' => $documento->id,
                    'total' => $resultadosEmail['total'],
                    'enviados' => $resultadosEmail['enviados'],
                    'erros' => $resultadosEmail['erros'],
                    'ignorados' => $resultadosEmail['ignorados'] ?? 0,
                    'motivo' => $resultadosEmail['motivo'] ?? null,
                ]);
            } catch (\Throwable $e) {
                Log::error('Email Observer: Erro ao enviar notificação', [
                    'documento_id' => $documento->id,
                    'erro' => $e->getMessage(),
                ]);
            }
        });
    }
}
