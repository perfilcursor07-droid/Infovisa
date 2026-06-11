<?php

namespace App\Services;

use App\Models\ProcessoDocumento;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Fpdi;

/**
 * Carimba PDFs aprovados pela Vigilância Sanitária com uma faixa de validação
 * no rodapé de todas as páginas, contendo dados de verificação e QR Code.
 *
 * O arquivo original é preservado; uma versão carimbada é gerada ao lado dele.
 */
class CarimboValidacaoService
{
    /** Altura da faixa de validação no rodapé (mm) */
    private const ALTURA_FAIXA = 20.0;

    /**
     * Gera a versão carimbada do documento aprovado.
     * Preenche codigo_validacao, hash_arquivo e caminho_carimbado no model.
     *
     * @throws \Exception se o arquivo não for PDF ou não puder ser processado
     */
    public function carimbar(ProcessoDocumento $documento, ?string $verificadoPorNome = null): void
    {
        if (strtolower((string) $documento->extensao) !== 'pdf') {
            throw new \Exception('Apenas arquivos PDF podem ser carimbados.');
        }

        $caminhoOriginal = $this->resolverCaminhoAbsoluto($documento);

        if (!$caminhoOriginal || !file_exists($caminhoOriginal)) {
            throw new \Exception('Arquivo original não encontrado: ' . $documento->caminho);
        }

        if (empty($documento->codigo_validacao)) {
            $documento->codigo_validacao = ProcessoDocumento::gerarCodigoValidacao();
        }

        $documento->hash_arquivo = hash_file('sha256', $caminhoOriginal);

        $urlValidacao = route('validar.arquivo', ['codigo' => $documento->codigo_validacao]);

        // Gera QR Code temporário
        $qrCode = new QrCode($urlValidacao);
        $writer = new PngWriter();
        $qrTemp = storage_path('app/temp_qr_validacao_' . $documento->id . '.png');
        file_put_contents($qrTemp, $writer->write($qrCode)->getString());

        // Versão carimbada fica no mesmo diretório do arquivo original, com sufixo _validado
        $caminhoRelativoCarimbado = $this->montarCaminhoRelativoCarimbado($documento);
        $caminhoAbsolutoCarimbado = dirname($caminhoOriginal)
            . DIRECTORY_SEPARATOR
            . pathinfo($caminhoOriginal, PATHINFO_FILENAME) . '_validado.pdf';

        try {
            $this->gerarPdfCarimbado($documento, $caminhoOriginal, $caminhoAbsolutoCarimbado, $qrTemp, $urlValidacao, $verificadoPorNome);
        } finally {
            @unlink($qrTemp);
        }

        $documento->caminho_carimbado = $caminhoRelativoCarimbado;
        $documento->save();
    }

    /**
     * Resolve o caminho absoluto da versão carimbada (se existir).
     */
    public function resolverCaminhoCarimbado(ProcessoDocumento $documento): ?string
    {
        if (empty($documento->caminho_carimbado)) {
            return null;
        }

        $relativo = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $documento->caminho_carimbado);

        $candidatos = [
            $this->basePathDoDocumento($documento) . DIRECTORY_SEPARATOR . $relativo,
            storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $relativo),
            storage_path('app' . DIRECTORY_SEPARATOR . $relativo),
        ];

        foreach ($candidatos as $caminho) {
            if (file_exists($caminho)) {
                return $caminho;
            }
        }

        return null;
    }

    private function gerarPdfCarimbado(
        ProcessoDocumento $documento,
        string $caminhoOriginal,
        string $caminhoDestino,
        string $qrTemp,
        string $urlValidacao,
        ?string $verificadoPorNome = null
    ): void {
        $fpdi = new Fpdi();

        // Impede que escrever perto da borda inferior crie páginas em branco automaticamente
        $fpdi->SetAutoPageBreak(false);
        $fpdi->SetMargins(0, 0, 0);

        $arquivoFonte = $caminhoOriginal;
        $tempConvertido = null;

        try {
            $totalPaginas = $fpdi->setSourceFile($arquivoFonte);
        } catch (\Exception $e) {
            // PDFs com compressão > 1.4 não são suportados pelo FPDI free; tenta converter com Ghostscript
            $tempConvertido = storage_path('app/temp_gs_validacao_' . $documento->id . '.pdf');
            $cmd = sprintf(
                'gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -sOutputFile=%s %s 2>&1',
                escapeshellarg($tempConvertido),
                escapeshellarg($arquivoFonte)
            );
            exec($cmd, $saida, $codigo);

            if ($codigo !== 0 || !file_exists($tempConvertido) || filesize($tempConvertido) === 0) {
                @unlink($tempConvertido);
                throw $e;
            }

            $arquivoFonte = $tempConvertido;
            $totalPaginas = $fpdi->setSourceFile($arquivoFonte);
        }

        $aprovadoPor = $documento->aprovadoPor->nome ?? $verificadoPorNome ?? 'Vigilância Sanitária';
        $aprovadoEm = $documento->aprovado_em ? $documento->aprovado_em->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s');
        $numeroProcesso = $documento->processo->numero_processo ?? '';

        $linha1 = 'Arquivo verificado por: ' . mb_strtoupper($aprovadoPor) . ' em ' . $aprovadoEm;
        $linha2 = 'Documento aprovado pela Vigilância Sanitária' . ($numeroProcesso ? ' - Processo ' . $numeroProcesso : '');
        $linha3 = 'Valide em: ' . $urlValidacao;

        for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) {
            $template = $fpdi->importPage($pagina);
            $tamanho = $fpdi->getTemplateSize($template);

            $fpdi->AddPage($tamanho['orientation'], [$tamanho['width'], $tamanho['height']]);
            $fpdi->useTemplate($template);

            $this->desenharFaixaValidacao($fpdi, $tamanho['width'], $tamanho['height'], $qrTemp, $linha1, $linha2, $linha3);
        }

        $dirDestino = dirname($caminhoDestino);
        if (!is_dir($dirDestino)) {
            mkdir($dirDestino, 0775, true);
        }

        $fpdi->Output('F', $caminhoDestino);

        if ($tempConvertido) {
            @unlink($tempConvertido);
        }

        Log::info('PDF carimbado com validação gerado', [
            'processo_documento_id' => $documento->id,
            'paginas' => $totalPaginas,
            'destino' => $caminhoDestino,
        ]);
    }

    private function desenharFaixaValidacao(
        Fpdi $pdf,
        float $largura,
        float $altura,
        string $qrTemp,
        string $linha1,
        string $linha2,
        string $linha3
    ): void {
        $alturaFaixa = self::ALTURA_FAIXA;
        $topoFaixa = $altura - $alturaFaixa;

        // Faixa branca de fundo com linha superior verde
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(0, $topoFaixa, $largura, $alturaFaixa, 'F');
        $pdf->SetDrawColor(22, 163, 74);
        $pdf->SetLineWidth(0.4);
        $pdf->Line(0, $topoFaixa, $largura, $topoFaixa);

        // QR Code à direita (mínimo ~18mm para leitura confiável quando impresso)
        $ladoQr = $alturaFaixa - 2.0;
        $xQr = $largura - $ladoQr - 2.0;
        $yQr = $topoFaixa + 1.0;
        $pdf->Image($qrTemp, $xQr, $yQr, $ladoQr, $ladoQr, 'PNG');

        // Textos à esquerda
        $larguraTexto = $xQr - 8.0;
        $pdf->SetTextColor(31, 41, 55);

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY(4, $topoFaixa + 3.0);
        $pdf->Cell($larguraTexto, 4.0, $this->converterTexto($linha1), 0, 0, 'L');

        $pdf->SetFont('Helvetica', '', 8.5);
        $pdf->SetXY(4, $topoFaixa + 8.0);
        $pdf->Cell($larguraTexto, 4.0, $this->converterTexto($linha2), 0, 0, 'L');

        $pdf->SetTextColor(75, 85, 99);
        $pdf->SetFont('Helvetica', '', 7.5);
        $pdf->SetXY(4, $topoFaixa + 13.0);
        $pdf->Cell($larguraTexto, 4.0, $this->converterTexto($linha3), 0, 0, 'L');
    }

    private function converterTexto(string $texto): string
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto) ?: $texto;
    }

    /**
     * Diretório base (storage/app ou storage/app/public) conforme a origem do documento.
     */
    private function basePathDoDocumento(ProcessoDocumento $documento): string
    {
        // Arquivos enviados por usuários externos ou documentos digitais ficam no disco public
        if ($documento->tipo_documento === 'documento_digital' || $documento->tipo_usuario === 'externo') {
            return storage_path('app' . DIRECTORY_SEPARATOR . 'public');
        }

        return storage_path('app');
    }

    private function resolverCaminhoAbsoluto(ProcessoDocumento $documento): ?string
    {
        if (empty($documento->caminho)) {
            return null;
        }

        $relativo = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $documento->caminho);

        $candidatos = [
            $this->basePathDoDocumento($documento) . DIRECTORY_SEPARATOR . $relativo,
            storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $relativo),
            storage_path('app' . DIRECTORY_SEPARATOR . $relativo),
        ];

        foreach ($candidatos as $caminho) {
            if (file_exists($caminho)) {
                return $caminho;
            }
        }

        return null;
    }

    private function montarCaminhoRelativoCarimbado(ProcessoDocumento $documento): string
    {
        $caminho = str_replace('\\', '/', (string) $documento->caminho);
        $dir = dirname($caminho);
        $nomeBase = pathinfo($caminho, PATHINFO_FILENAME);

        $relativo = ($dir !== '.' && $dir !== '' ? $dir . '/' : '') . $nomeBase . '_validado.pdf';

        return $relativo;
    }
}
