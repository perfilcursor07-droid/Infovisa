<?php

namespace App\Http\Controllers;

use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TipoDocumentoController extends Controller
{
    /**
     * Lista todos os tipos de documentos
     */
    public function index()
    {
        $tipos = TipoDocumento::ordenado()->get();
        
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
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:255|unique:tipo_documentos,codigo',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'visibilidade' => 'required|in:todos,estadual,municipal',
            'ordem' => 'integer|min:0',
            'tem_prazo' => 'boolean',
            'prazo_padrao_dias' => 'nullable|integer|min:1',
            'prazo_notificacao' => 'boolean',
            'permite_resposta' => 'boolean',
            'abrir_processo_automaticamente' => 'boolean',
            'tipo_processo_codigo' => 'nullable|string|max:255',
        ]);

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

        TipoDocumento::create($validated);

        return redirect()
            ->route('admin.configuracoes.tipos-documento.index')
            ->with('success', 'Tipo de documento criado com sucesso!');
    }

    /**
     * Exibe o formulário de edição
     */
    public function edit(TipoDocumento $tipoDocumento)
    {
        $tipoDocumento->load('tiposDocumentoResposta');
        $tiposResposta = \App\Models\TipoDocumentoResposta::ativo()->ordenado()->get();
        return view('configuracoes.tipos-documento.edit', compact('tipoDocumento', 'tiposResposta'));
    }

    /**
     * Atualiza um tipo existente
     */
    public function update(Request $request, TipoDocumento $tipoDocumento)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:255|unique:tipo_documentos,codigo,' . $tipoDocumento->id,
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'visibilidade' => 'required|in:todos,estadual,municipal',
            'ordem' => 'integer|min:0',
            'tem_prazo' => 'boolean',
            'prazo_padrao_dias' => 'nullable|integer|min:1',
            'prazo_notificacao' => 'boolean',
            'permite_resposta' => 'boolean',
            'abrir_processo_automaticamente' => 'boolean',
            'tipo_processo_codigo' => 'nullable|string|max:255',
        ]);

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

        $tipoDocumento->update($validated);

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
