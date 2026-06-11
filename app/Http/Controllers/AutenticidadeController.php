<?php

namespace App\Http\Controllers;

use App\Models\DocumentoDigital;
use App\Models\ProcessoDocumento;
use App\Services\CarimboValidacaoService;
use Illuminate\Http\Request;

class AutenticidadeController extends Controller
{
    /**
     * Exibe formulário de verificação de autenticidade
     */
    public function index()
    {
        return view('public.verificar-autenticidade');
    }

    /**
     * Verifica autenticidade do documento pelo código
     */
    public function verificar(Request $request, $codigo = null)
    {
        // Se o código vier pela URL (QR Code)
        if ($codigo) {
            $codigoVerificar = $codigo;
        } 
        // Se vier pelo formulário
        else {
            $request->validate([
                'codigo' => 'required|string',
            ], [
                'codigo.required' => 'Digite o código do documento',
            ]);
            $codigoVerificar = $request->codigo;
        }

        // Busca o documento
        $documento = DocumentoDigital::where('codigo_autenticidade', $codigoVerificar)
            ->with([
                'tipoDocumento',
                'processo.tipoProcesso',
                'processo.estabelecimento',
                'assinaturas' => function($query) {
                    $query->where('status', 'assinado')->orderBy('ordem');
                },
                'assinaturas.usuarioInterno'
            ])
            ->first();

        if (!$documento) {
            return view('public.verificar-autenticidade', [
                'erro' => 'Código de autenticidade inválido ou documento não encontrado.',
                'codigo' => $codigoVerificar
            ]);
        }

        // Verifica se o documento está assinado
        if ($documento->status !== 'assinado') {
            return view('public.verificar-autenticidade', [
                'erro' => 'Este documento ainda não foi finalizado ou assinado.',
                'codigo' => $codigoVerificar
            ]);
        }

        return view('public.documento-autenticado', compact('documento'));
    }

    /**
     * Valida arquivo aprovado pela Vigilância Sanitária (QR Code do carimbo de validação)
     */
    public function validarArquivo($codigo)
    {
        $documento = ProcessoDocumento::where('codigo_validacao', $codigo)
            ->where('status_aprovacao', 'aprovado')
            ->with([
                'tipoDocumentoObrigatorio',
                'aprovadoPor',
                'processo.tipoProcesso',
                'processo.estabelecimento.municipio',
            ])
            ->first();

        if (!$documento) {
            return view('public.verificar-autenticidade', [
                'erro' => 'Código de validação inválido ou documento não encontrado.',
                'codigo' => $codigo,
            ]);
        }

        return view('public.arquivo-validado', compact('documento'));
    }

    /**
     * Visualiza o PDF carimbado do arquivo validado
     */
    public function visualizarArquivoValidado($codigo)
    {
        $documento = ProcessoDocumento::where('codigo_validacao', $codigo)
            ->where('status_aprovacao', 'aprovado')
            ->firstOrFail();

        $caminho = app(CarimboValidacaoService::class)->resolverCaminhoCarimbado($documento);

        if (!$caminho) {
            abort(404, 'Arquivo não encontrado.');
        }

        return response()->file($caminho, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $documento->nome_original . '"',
        ]);
    }

    /**
     * Visualiza o PDF do documento autenticado
     */
    public function visualizarPdf($codigo)
    {
        $documento = DocumentoDigital::where('codigo_autenticidade', $codigo)
            ->where('status', 'assinado')
            ->firstOrFail();

        if (!$documento->arquivo_pdf) {
            abort(404, 'PDF não encontrado');
        }

        $caminhoPdf = storage_path('app/public/' . $documento->arquivo_pdf);
        
        if (!file_exists($caminhoPdf)) {
            abort(404, 'Arquivo PDF não encontrado');
        }

        return response()->file($caminhoPdf);
    }
}
