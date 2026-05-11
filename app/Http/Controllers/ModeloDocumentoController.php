<?php

namespace App\Http\Controllers;

use App\Enums\NivelAcesso;
use App\Models\Municipio;
use App\Models\ModeloDocumento;
use App\Models\TipoDocumento;
use App\Models\UsuarioInterno;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ModeloDocumentoController extends Controller
{
    /**
     * Lista todos os modelos de documentos
     */
    public function index()
    {
        $usuario = auth('interno')->user();

        $modelos = ModeloDocumento::with(['tipoDocumento', 'municipio'])
            ->visiveisParaUsuario($usuario)
            ->ordenado()
            ->paginate(15);
        
        return view('configuracoes.modelos-documento.index', compact('modelos'));
    }

    /**
     * Exibe o formulário de criação
     */
    public function create()
    {
        $usuario = auth('interno')->user();
        $tiposDocumento = TipoDocumento::ativo()
            ->visivelParaUsuario($usuario)
            ->with('subcategoriasAtivas')
            ->ordenado()
            ->get();
        $municipios = $this->getMunicipiosDisponiveis($usuario);

        return view('configuracoes.modelos-documento.create', compact('tiposDocumento', 'municipios'));
    }

    /**
     * Salva um novo modelo
     */
    public function store(Request $request)
    {
        $usuario = auth('interno')->user();

        $validated = $request->validate([
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'subcategoria_id' => 'nullable|exists:tipo_documento_subcategorias,id',
            'descricao' => 'nullable|string',
            'conteudo' => 'required|string',
            'variaveis' => 'nullable|array',
            'escopo' => 'required|in:estadual,municipal',
            'municipio_id' => 'nullable|exists:municipios,id',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $validated = $this->normalizarEscopo($validated, $usuario);
        $validated = $this->normalizarSubcategoria($validated);

        // Gera código automaticamente baseado no tipo + timestamp
        $tipoDocumento = TipoDocumento::find($validated['tipo_documento_id']);
        $validated['codigo'] = $tipoDocumento->codigo . '_' . time();
        $validated['ativo'] = $request->has('ativo');

        ModeloDocumento::create($validated);

        return redirect()
            ->route('admin.configuracoes.modelos-documento.index')
            ->with('success', 'Modelo de documento criado com sucesso!');
    }

    /**
     * Exibe o formulário de edição
     */
    public function edit(ModeloDocumento $modeloDocumento)
    {
        $usuario = auth('interno')->user();
        $this->autorizarGerenciamento($modeloDocumento, $usuario);

        $tiposDocumento = TipoDocumento::ativo()
            ->visivelParaUsuario($usuario)
            ->with('subcategoriasAtivas')
            ->ordenado()
            ->get();
        $municipios = $this->getMunicipiosDisponiveis($usuario);

        return view('configuracoes.modelos-documento.edit', compact('modeloDocumento', 'tiposDocumento', 'municipios'));
    }

    /**
     * Atualiza um modelo existente
     */
    public function update(Request $request, ModeloDocumento $modeloDocumento)
    {
        $usuario = auth('interno')->user();
        $this->autorizarGerenciamento($modeloDocumento, $usuario);

        $validated = $request->validate([
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'subcategoria_id' => 'nullable|exists:tipo_documento_subcategorias,id',
            'codigo' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
            'conteudo' => 'required|string',
            'variaveis' => 'nullable|array',
            'escopo' => 'required|in:estadual,municipal',
            'municipio_id' => 'nullable|exists:municipios,id',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $validated = $this->normalizarEscopo($validated, $usuario);
        $validated = $this->normalizarSubcategoria($validated);
        // Converte checkbox ativo
        $validated['ativo'] = $request->has('ativo') ? true : false;

        $modeloDocumento->update($validated);

        return redirect()
            ->route('admin.configuracoes.modelos-documento.index')
            ->with('success', 'Modelo de documento atualizado com sucesso!');
    }

    /**
     * Remove um modelo
     */
    public function destroy(ModeloDocumento $modeloDocumento)
    {
        $usuario = auth('interno')->user();
        $this->autorizarGerenciamento($modeloDocumento, $usuario);

        $modeloDocumento->delete();

        return redirect()
            ->route('admin.configuracoes.modelos-documento.index')
            ->with('success', 'Modelo de documento removido com sucesso!');
    }

    private function getMunicipiosDisponiveis(UsuarioInterno $usuario)
    {
        if ($usuario->nivel_acesso === NivelAcesso::GestorMunicipal && $usuario->municipio_id) {
            return Municipio::where('id', $usuario->municipio_id)->orderBy('nome')->get();
        }

        return Municipio::orderBy('nome')->get();
    }

    /**
     * Garante que a subcategoria informada pertença ao tipo selecionado.
     * Se não pertencer ou for inválida, limpa o campo.
     */
    private function normalizarSubcategoria(array $validated): array
    {
        $subcategoriaId = $validated['subcategoria_id'] ?? null;

        if (!$subcategoriaId) {
            $validated['subcategoria_id'] = null;
            return $validated;
        }

        $pertence = \App\Models\TipoDocumentoSubcategoria::where('id', $subcategoriaId)
            ->where('tipo_documento_id', $validated['tipo_documento_id'])
            ->exists();

        if (!$pertence) {
            $validated['subcategoria_id'] = null;
        }

        return $validated;
    }

    private function normalizarEscopo(array $validated, UsuarioInterno $usuario): array
    {
        if ($usuario->nivel_acesso === NivelAcesso::GestorEstadual) {
            $validated['escopo'] = 'estadual';
            $validated['municipio_id'] = null;

            return $validated;
        }

        if ($usuario->nivel_acesso === NivelAcesso::GestorMunicipal) {
            if (!$usuario->municipio_id) {
                abort(403, 'O gestor municipal precisa estar vinculado a um município.');
            }

            $validated['escopo'] = 'municipal';
            $validated['municipio_id'] = $usuario->municipio_id;

            return $validated;
        }

        if (($validated['escopo'] ?? 'estadual') === 'estadual') {
            $validated['municipio_id'] = null;
        }

        if (($validated['escopo'] ?? null) === 'municipal' && empty($validated['municipio_id'])) {
            throw ValidationException::withMessages([
                'municipio_id' => 'Selecione o município do modelo municipal.',
            ]);
        }

        return $validated;
    }

    private function autorizarGerenciamento(ModeloDocumento $modeloDocumento, UsuarioInterno $usuario): void
    {
        if ($usuario->nivel_acesso === NivelAcesso::Administrador) {
            return;
        }

        if ($usuario->nivel_acesso === NivelAcesso::GestorEstadual && $modeloDocumento->isEstadual()) {
            return;
        }

        if (
            $usuario->nivel_acesso === NivelAcesso::GestorMunicipal
            && $modeloDocumento->isMunicipal()
            && (int) $modeloDocumento->municipio_id === (int) $usuario->municipio_id
        ) {
            return;
        }

        abort(403, 'Você não tem permissão para gerenciar este modelo de documento.');
    }
}
