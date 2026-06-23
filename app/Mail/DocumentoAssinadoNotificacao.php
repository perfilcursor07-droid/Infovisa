<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentoAssinadoNotificacao extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nomeDestinatario,
        public string $nomeEstabelecimento,
        public string $tipoDocumento,
        public string $numeroDocumento,
        public string $numeroProcesso,
        public ?int $prazoDias,
        public string $linkDocumento,
        public bool $comPrazo,
    ) {}

    public function envelope(): Envelope
    {
        $assunto = $this->comPrazo
            ? "InfoVISA - Novo documento com prazo: {$this->tipoDocumento}"
            : "InfoVISA - Novo documento: {$this->tipoDocumento}";

        $replyTo = config('mail.reply_to.address') ?: config('mail.from.address');

        return new Envelope(
            subject: $assunto,
            replyTo: array_filter([$replyTo]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.documento-prazo-criado',
            text: 'emails.documento-prazo-criado-text',
        );
    }
}
