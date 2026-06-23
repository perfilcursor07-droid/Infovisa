<?php

namespace App\Services;

use App\Mail\DocumentoAssinadoNotificacao;
use App\Models\DocumentoDigital;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\UsuarioExterno;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DocumentoNotificacaoEmailService
{
    /**
     * Notifica por e-mail quando o documento foi totalmente assinado.
     * Envia para e-mail do estabelecimento, criador, usuarios vinculados e responsaveis.
     */
    public function notificarDocumentoAssinado(DocumentoDigital $documento): array
    {
        $resultado = ['total' => 0, 'enviados' => 0, 'erros' => 0, 'ignorados' => 0];

        if ($documento->sigiloso) {
            $resultado['ignorados']++;
            return $resultado;
        }

        if (!$documento->todasAssinaturasCompletas()) {
            $resultado['ignorados']++;
            return $resultado;
        }

        // Notifica documentos com prazo (inclui notificacoes/fiscalizacao)
        if (!$documento->temPrazo()) {
            $resultado['ignorados']++;
            return $resultado;
        }

        $processo = $this->resolverProcesso($documento);
        if (!$processo) {
            Log::warning('Email documento: processo nao encontrado', ['documento_id' => $documento->id]);
            $resultado['ignorados']++;
            return $resultado;
        }

        $estabelecimento = $processo->estabelecimento;
        if (!$estabelecimento) {
            Log::warning('Email documento: estabelecimento nao encontrado', [
                'documento_id' => $documento->id,
                'processo_id' => $processo->id,
            ]);
            $resultado['ignorados']++;
            return $resultado;
        }

        $emails = $this->coletarEmailsDestinatarios($estabelecimento, $processo);
        if ($emails->isEmpty()) {
            Log::info('Email documento: nenhum destinatario', [
                'documento_id' => $documento->id,
                'estabelecimento_id' => $estabelecimento->id,
            ]);
            return $resultado;
        }

        $documento->loadMissing('tipoDocumento');

        $tipoDocumento = $documento->tipoDocumento->nome ?? 'Documento';
        $numeroDocumento = $documento->numero_documento ?? '';
        $numeroProcesso = $processo->numero_processo ?? '';
        $prazoDias = $documento->prazo_dias ?? null;
        $nomeEstabelecimento = $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social ?? '';
        $linkDocumento = url("/company/processos/{$processo->id}");
        $comPrazo = (bool) $documento->temPrazo();

        $resultado['total'] = $emails->count();

        foreach ($emails as $dest) {
            try {
                Mail::to($dest['email'], $dest['nome'])->send(new DocumentoAssinadoNotificacao(
                    nomeDestinatario: $dest['nome'],
                    nomeEstabelecimento: $nomeEstabelecimento,
                    tipoDocumento: $tipoDocumento,
                    numeroDocumento: $numeroDocumento,
                    numeroProcesso: $numeroProcesso,
                    prazoDias: $prazoDias,
                    linkDocumento: $linkDocumento,
                    comPrazo: $comPrazo,
                ));

                $resultado['enviados']++;

                Log::info('Email documento assinado enviado', [
                    'documento_id' => $documento->id,
                    'email' => $dest['email'],
                    'from' => config('mail.from.address'),
                ]);
            } catch (\Throwable $e) {
                $resultado['erros']++;
                Log::error('Erro ao enviar email de documento assinado', [
                    'documento_id' => $documento->id,
                    'email' => $dest['email'],
                    'from' => config('mail.from.address'),
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        return $resultado;
    }

    /**
     * Coleta e-mails unicos do estabelecimento e usuarios vinculados.
     *
     * @return Collection<int, array{email: string, nome: string}>
     */
    public function coletarEmailsDestinatarios(Estabelecimento $estabelecimento, ?Processo $processo = null): Collection
    {
        $emails = collect();

        $this->adicionarEmail($emails, $estabelecimento->email, $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social ?? 'Estabelecimento');

        if ($estabelecimento->usuario_externo_id) {
            $criador = UsuarioExterno::find($estabelecimento->usuario_externo_id);
            if ($criador) {
                $this->adicionarEmail($emails, $criador->email, $criador->nome);
            }
        }

        foreach ($estabelecimento->usuariosVinculados()->get() as $usuario) {
            $this->adicionarEmail($emails, $usuario->email, $usuario->nome);
        }

        foreach ($estabelecimento->responsaveis()->wherePivot('ativo', true)->get() as $responsavel) {
            $this->adicionarEmail($emails, $responsavel->email, $responsavel->nome);
        }

        if ($processo?->usuarioExterno) {
            $this->adicionarEmail($emails, $processo->usuarioExterno->email, $processo->usuarioExterno->nome);
        }

        return $emails->values();
    }

    private function adicionarEmail(Collection $emails, ?string $email, ?string $nome): void
    {
        $email = is_string($email) ? trim(strtolower($email)) : '';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        if ($emails->contains(fn ($item) => strtolower($item['email']) === $email)) {
            return;
        }

        $emails->push([
            'email' => $email,
            'nome' => $nome ?: 'Destinatario',
        ]);
    }

    private function resolverProcesso(DocumentoDigital $documento): ?Processo
    {
        if ($documento->processo_id) {
            return $documento->processo()->with('estabelecimento')->first();
        }

        $processosIds = $documento->processos_ids ?? [];
        if (empty($processosIds)) {
            return null;
        }

        return Processo::with('estabelecimento')->find($processosIds[0]);
    }
}
