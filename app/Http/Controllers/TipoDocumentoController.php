<?php

namespace App\Http\Controllers;

use App\Models\TipoDocumento;
use App\Models\TipoDocumentoSubcategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TipoDocumentoController extends Controller
{
    /**
     * Lista todos os tipos de documentos
     *
     * Visibilidade:
     * - Administrador: vê todos os tipos (todos, estadual, municipal)
     * - Gestor/Técnico Estadual: vê apenas tipos com visibilidade "todos" ou "estadual"
     * - Gestor/Técnico Municipal: vê apenas tipos com visibilidade "todos" ou "municipal"
     */
    public function index()
    {
        $tipos = TipoDocumento::visivelParaUsuario()
            ->with('subcategorias')
            ->ordenado()
            ->get();

        return view('configuracoes.tipos-documento.index', compact('tipos'));
    }

    /**
     * Reordena tipos via drag-and-drop (AJAX)
     */
    public function reordenar(Request $request)
    {
        $request->validate(['ordem' => 'required|array', 'ordem.*' => 'integer']);

        foreach ($request->ordem as $posicao => $id) {
            TipoDocumento::where('id', $id)->update(['ordem' => $posicao + 1]);
        }

        return response()->json(['success' => true, 'message' => 'Ordem atualizada!']);
    }

    /**
     * Aborta com 403 caso o gestor estadual tente manipular um tipo municipal.
     * Administrador tem acesso completo.
     */
    private function autorizarManipulacao(TipoDocumento $tipoDocumento): void
    {
        $usuario = auth('interno')->user();
        if (!$usuario || $usuario->isAdmin()) {
            return;
        }
        if ($usuario->isEstadual() && $tipoDocumento->visibilidade === 'municipal') {
            abort(403, 'Acesso negado: este tipo de documento é de uso municipal.');
        }
    }

    /**
     * Retorna a lista de visibilidades permitidas para o usuário logado.
     */
    private function visibilidadesPermitidas(): array
    {
        $usuario = auth('interno')->user();
        if (!$usuario || $usuario->isAdmin()) {
            return ['todos', 'estadual', 'municipal'];
        }
        if ($usuario->isEstadual()) {
            return ['todos', 'estadual'];
        }
        return ['todos', 'municipal'];
    }

    /**
     * Regras de validação comuns para subcategorias enviadas no form (repeater).
     */
    private function regrasSubcategorias(): array
    {
        return [
            'subcategorias' => 'nullable|array',
            'subcategorias.*.id' => 'nullable|integer|exists:tipo_documento_subcategorias,id',
            'subcategorias.*.nome' => 'nullable|string|max:255',
            'subcategorias.*.codigo' => 'nullable|string|max:255',
            'subcategorias.*.ordem' => 'nullable|integer|min:0',
            'subcategorias.*.ativo' => 'nullable|boolean',
        ];
    }

    /**
     * Sincroniza o repeater de subcategorias com o tipo de documento.
     * Linhas sem nome são ignoradas. Subcategorias existentes omitidas do payload são removidas.
     */
    private function sincronizarSubcategorias(TipoDocumento $tipoDocumento, array $itens): void
    {
        $idsMantidos = [];
        $ordem = 0;

        foreach ($itens as $item) {
            $nome = trim($item['nome'] ?? '');
            if ($nome === '') {
                continue;
            }

            $codigo = !empty($item['codigo']) ? Str::slug($item['codigo']) : Str::slug($nome);
            $ativo = array_key_exists('ativo', $item)
                ? (bool) $item['ativo']
                : true;
            $ordemItem = isset($item['ordem']) && $item['ordem'] !== ''
                ? (int) $item['ordem']
                : $ordem;

            $dados = [
                'tipo_documento_id' => $tipoDocumento->id,
                'nome' => $nome,
                'codigo' => $codigo,
                'ordem' => $ordemItem,
                'ativo' => $ativo,
            ];

            if (!empty($item['id'])) {
                $sub = TipoDocumentoSubcategoria::where('tipo_documento_id', $tipoDocumento->id)
                    ->where('id', $item['id'])
                    ->first();
                if ($sub) {
                    $sub->update($dados);
                    $idsMantidos[] = $sub->id;
                }
            } else {
                $sub = TipoDocumentoSubcategoria::create($dados);
                $idsMantidos[] = $sub->id;
            }

            $ordem++;
        }

        // Remove subcategorias que existiam antes e foram omitidas do payload
        $query = TipoDocumentoSubcategoria::where('tipo_documento_id', $tipoDocumento->id);
        if (!empty($idsMantidos)) {
            $query->whereNotIn('id', $idsMantidos);
        }
        $query->delete();
    }

    /**
     * Exibe o formulário de criação
     */
    public function create()
    {
        return view('configuracoes.tipos-documento.create');
    }

    /**
     * Salva um novo tipo
     */
    public function store(Request $request)
    {
        $visibilidadesPermitidas = $this->visibilidadesPermitidas();

        $validated = $request->validate(array_merge([
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:255|unique:tipo_documentos,codigo',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'visibilidade' => 'required|in:' . implode(',', $visibilidadesPermitidas),
            'ordem' => 'integer|min:0',
            'tem_prazo' => 'boolean',
            'prazo_padrao_dias' => 'nullable|integer|min:1',
            'prazo_notificacao' => 'boolean',
            'permite_resposta' => 'boolean',
            'abrir_processo_automaticamente' => 'boolean',
            'tipo_processo_codigo' => 'nullable|string|max:255',
        ], $this->regrasSubcategorias()));

        // Se tem_prazo está desmarcado, limpa o prazo_padrao_dias e prazo_notificacao
        if (!$request->has('tem_prazo')) {
            $validated['tem_prazo'] = false;
            $validated['prazo_padrao_dias'] = null;
            $validated['prazo_notificacao'] = false;
        } else {
            // Se tem_prazo está marcado, verifica prazo_notificacao
            $validated['prazo_notificacao'] = $request->has('prazo_notificacao');
        }

        // Permite resposta do estabelecimento
        $validated['permite_resposta'] = $request->has('permite_resposta');

        // Abrir processo automaticamente
        $validated['abrir_processo_automaticamente'] = $request->has('abrir_processo_automaticamente');
        if (!$validated['abrir_processo_automaticamente']) {
            $validated['tipo_processo_codigo'] = null;
        }

        // Gera código automaticamente se não fornecido
        if (empty($validated['codigo'])) {
            $validated['codigo'] = Str::slug($validated['nome']);
        }

        $subcategorias = $validated['subcategorias'] ?? [];
        unset($validated['subcategorias']);

        $tipoDocumento = TipoDocumento::create($validated);

        $this->sincronizarSubcategorias($tipoDocumento, $subcategorias);

        return redirect()
            ->route('admin.configuracoes.tipos-documento.index')
            ->with('success', 'Tipo de documento criado com sucesso!');
    }

    /**
     * Exibe o formulário de edição
     */
    public function edit(TipoDocumento $tipoDocumento)
    {
        $this->autorizarManipulacao($tipoDocumento);

        $tipoDocumento->load(['tiposDocumentoResposta', 'subcategorias']);
        $tiposResposta = \App\Models\TipoDocumentoResposta::ativo()->ordenado()->get();
        return view('configuracoes.tipos-documento.edit', compact('tipoDocumento', 'tiposResposta'));
    }

    /**
     * Atualiza um tipo existente
     */
    public function update(Request $request, TipoDocumento $tipoDocumento)
    {
        $this->autorizarManipulacao($tipoDocumento);

        $visibilidadesPermitidas = $this->visibilidadesPermitidas();

        $validated = $request->validate(array_merge([
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:255|unique:tipo_documentos,codigo,' . $tipoDocumento->id,
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'visibilidade' => 'required|in:' . implode(',', $visibilidadesPermitidas),
            'ordem' => 'integer|min:0',
            'tem_prazo' => 'boolean',
            'prazo_padrao_dias' => 'nullable|integer|min:1',
            'prazo_notificacao' => 'boolean',
            'permite_resposta' => 'boolean',
            'abrir_processo_automaticamente' => 'boolean',
            'tipo_processo_codigo' => 'nullable|string|max:255',
        ], $this->regrasSubcategorias()));

        // Se tem_prazo está desmarcado, limpa o prazo_padrao_dias e prazo_notificacao
        if (!$request->has('tem_prazo')) {
            $validated['tem_prazo'] = false;
            $validated['prazo_padrao_dias'] = null;
            $validated['prazo_notificacao'] = false;
        } else {
            // Se tem_prazo está marcado, verifica prazo_notificacao
            $validated['prazo_notificacao'] = $request->has('prazo_notificacao');
        }

        // Permite resposta do estabelecimento
        $validated['permite_resposta'] = $request->has('permite_resposta');

        // Abrir processo automaticamente
        $validated['abrir_processo_automaticamente'] = $request->has('abrir_processo_automaticamente');
        if (!$validated['abrir_processo_automaticamente']) {
            $validated['tipo_processo_codigo'] = null;
        }

        // Gera código automaticamente se não fornecido
        if (empty($validated['codigo'])) {
            $validated['codigo'] = Str::slug($validated['nome']);
        }

        $subcategorias = $validated['subcategorias'] ?? [];
        unset($validated['subcategorias']);

        $tipoDocumento->update($validated);

        $this->sincronizarSubcategorias($tipoDocumento, $subcategorias);

        // Sincroniza tipos de documento resposta (se permite_resposta)
        if ($validated['permite_resposta'] && $request->has('tipos_resposta')) {
            $sync = [];
            foreach ($request->input('tipos_resposta', []) as $ordem => $id) {
                $sync[$id] = ['obrigatorio' => true, 'ordem' => $ordem];
            }
            $tipoDocumento->tiposDocumentoResposta()->sync($sync);
        } elseif (!$validated['permite_resposta']) {
            $tipoDocumento->tiposDocumentoResposta()->detach();
        }

        return redirect()
            ->route('admin.configuracoes.tipos-documento.index')
            ->with('success', 'Tipo de documento atualizado com sucesso!');
    }

    /**
     * Remove um tipo
     */
    public function destroy(TipoDocumento $tipoDocumento)
    {
        $this->autorizarManipulacao($tipoDocumento);

        $tipoDocumento->delete();

        return redirect()
            ->route('admin.configuracoes.tipos-documento.index')
            ->with('success', 'Tipo de documento removido com sucesso!');
    }

    /**
     * Vincula tipos de documento resposta a um tipo de documento
     */
    public function vincularRespostas(Request $request, TipoDocumento $tipoDocumento)
    {
        $this->autorizarManipulacao($tipoDocumento);

        $request->validate([
            'tipos_resposta' => 'nullable|array',
            'tipos_resposta.*' => 'exists:tipo_documento_respostas,id',
        ]);

        $sync = [];
        foreach ($request->input('tipos_resposta', []) as $ordem => $id) {
            $sync[$id] = ['obrigatorio' => true, 'ordem' => $ordem];
        }

        $tipoDocumento->tiposDocumentoResposta()->sync($sync);

        return redirect()
            ->route('admin.configuracoes.tipos-documento.edit', $tipoDocumento)
            ->with('success', 'Documentos de resposta vinculados com sucesso!');
    }
}
