<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoDocumentoObrigatorio;
use Illuminate\Http\Request;

class TipoDocumentoObrigatorioController extends Controller
{
    public function index(Request $request)
    {
        $query = TipoDocumentoObrigatorio::query();

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'ilike', "%{$busca}%")
                  ->orWhere('descricao', 'ilike', "%{$busca}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        if ($request->filled('documento_comum')) {
            $query->where('documento_comum', $request->documento_comum === '1');
        }

        if ($request->filled('escopo_competencia')) {
            $query->where('escopo_competencia', $request->escopo_competencia);
        }

        if ($request->filled('tipo_setor')) {
            $query->where('tipo_setor', $request->tipo_setor);
        }

        $tipos = $query->ordenado()->paginate(20)->withQueryString();

        return view('configuracoes.tipos-documento-obrigatorio.index', compact('tipos'));
    }

    public function create()
    {
        return view('configuracoes.tipos-documento-obrigatorio.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
            'documento_comum' => 'boolean',
            'tipo_processo_id' => 'nullable|exists:tipo_processos,id',
            'escopo_competencia' => 'required|string|in:todos,estadual,municipal',
            'municipio_id' => 'nullable|required_if:escopo_competencia,municipal|exists:municipios,id',
            'tipo_setor' => 'required|string|in:todos,publico,privado',
            'observacao_publica' => 'nullable|string',
            'observacao_privada' => 'nullable|string',
            'prazo_validade_dias' => 'nullable|integer|min:1',
            'carimbar_aprovacao' => 'boolean',
            'carimbo_modo' => 'nullable|string|in:desativado,automatico,manual',
            'criterio_ia' => 'nullable|string',
            'ia_modelo_visao' => 'nullable|string|max:255',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['documento_comum'] = $request->has('documento_comum');
        $validated['carimbo_modo'] = $validated['carimbo_modo'] ?? 'desativado';
        $validated['carimbar_aprovacao'] = $validated['carimbo_modo'] === 'automatico';
        $validated['carimbo_texto'] = $validated['carimbo_modo'] === 'manual' ? ($request->input('carimbo_texto') ?: null) : null;
        $validated['ordem'] = $validated['ordem'] ?? 0;

        // Campos de IA só podem ser definidos por administradores
        if (!auth('interno')->user()->isAdmin()) {
            unset($validated['criterio_ia'], $validated['ia_modelo_visao']);
        }

        // Se não é documento comum, limpa o tipo_processo_id
        if (!$validated['documento_comum']) {
            $validated['tipo_processo_id'] = null;
        }

        // Se escopo não é municipal, limpa municipio_id
        if ($validated['escopo_competencia'] !== 'municipal') {
            $validated['municipio_id'] = null;
        }

        TipoDocumentoObrigatorio::create($validated);

        $tab = $validated['escopo_competencia'] === 'municipal' ? 'tipos-documento-municipal' : 'tipos-documento';

        return redirect()
            ->route('admin.configuracoes.listas-documento.index', ['tab' => $tab])
            ->with('success', 'Tipo de documento obrigatório criado com sucesso!');
    }

    /**
     * Cria múltiplos tipos de documento de uma vez.
     * Recebe uma lista de nomes (um por linha) e configurações compartilhadas.
     */
    public function storeMultiple(Request $request)
    {
        $validated = $request->validate([
            'nomes' => 'required|string',
            'documento_comum' => 'boolean',
            'tipo_processo_id' => 'nullable|exists:tipo_processos,id',
            'escopo_competencia' => 'required|string|in:todos,estadual,municipal',
            'municipio_id' => 'nullable|required_if:escopo_competencia,municipal|exists:municipios,id',
            'tipo_setor' => 'required|string|in:todos,publico,privado',
            'prazo_validade_dias' => 'nullable|integer|min:1',
        ]);

        // Separa os nomes por linha, remove vazios e duplicados
        $nomes = collect(preg_split('/\r\n|\r|\n/', $validated['nomes']))
            ->map(fn ($nome) => trim($nome))
            ->filter(fn ($nome) => $nome !== '')
            ->unique()
            ->values();

        if ($nomes->isEmpty()) {
            return back()->with('error', 'Informe ao menos um nome de documento.')->withInput();
        }

        // Valida tamanho máximo de cada nome (limite da coluna)
        $nomesLongos = $nomes->filter(fn ($nome) => mb_strlen($nome) > 500);
        if ($nomesLongos->isNotEmpty()) {
            return back()
                ->with('error', 'Os seguintes documentos excedem o limite de 500 caracteres: ' . $nomesLongos->map(fn ($n) => '"' . mb_substr($n, 0, 40) . '..."')->implode(', '))
                ->withInput();
        }

        $documentoComum = $request->has('documento_comum');
        $tipoProcessoId = $documentoComum ? ($validated['tipo_processo_id'] ?? null) : null;
        $municipioId = $validated['escopo_competencia'] === 'municipal' ? ($validated['municipio_id'] ?? null) : null;

        $criados = 0;
        $ignorados = 0;
        $ordem = 0;

        foreach ($nomes as $nome) {
            // Evita duplicar documentos com mesmo nome, escopo e município
            $jaExiste = TipoDocumentoObrigatorio::where('nome', $nome)
                ->where('escopo_competencia', $validated['escopo_competencia'])
                ->where('municipio_id', $municipioId)
                ->exists();

            if ($jaExiste) {
                $ignorados++;
                continue;
            }

            TipoDocumentoObrigatorio::create([
                'nome' => $nome,
                'descricao' => null,
                'ativo' => true,
                'ordem' => $ordem++,
                'documento_comum' => $documentoComum,
                'tipo_processo_id' => $tipoProcessoId,
                'escopo_competencia' => $validated['escopo_competencia'],
                'municipio_id' => $municipioId,
                'tipo_setor' => $validated['tipo_setor'],
                'prazo_validade_dias' => $validated['prazo_validade_dias'] ?? null,
            ]);

            $criados++;
        }

        $mensagem = "{$criados} tipo(s) de documento criado(s) com sucesso!";
        if ($ignorados > 0) {
            $mensagem .= " {$ignorados} ignorado(s) por já existir(em).";
        }

        $tab = $validated['escopo_competencia'] === 'municipal' ? 'tipos-documento-municipal' : 'tipos-documento';

        return redirect()
            ->route('admin.configuracoes.listas-documento.index', ['tab' => $tab])
            ->with('success', $mensagem);
    }

    public function edit(TipoDocumentoObrigatorio $tipos_documento_obrigatorio)
    {
        return view('configuracoes.tipos-documento-obrigatorio.edit', [
            'tipo' => $tipos_documento_obrigatorio
        ]);
    }

    public function update(Request $request, TipoDocumentoObrigatorio $tipos_documento_obrigatorio)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
            'documento_comum' => 'boolean',
            'tipo_processo_id' => 'nullable|exists:tipo_processos,id',
            'escopo_competencia' => 'required|string|in:todos,estadual,municipal',
            'municipio_id' => 'nullable|required_if:escopo_competencia,municipal|exists:municipios,id',
            'tipo_setor' => 'required|string|in:todos,publico,privado',
            'observacao_publica' => 'nullable|string',
            'observacao_privada' => 'nullable|string',
            'prazo_validade_dias' => 'nullable|integer|min:1',
            'carimbar_aprovacao' => 'boolean',
            'carimbo_modo' => 'nullable|string|in:desativado,automatico,manual',
            'criterio_ia' => 'nullable|string',
            'ia_modelo_visao' => 'nullable|string|max:255',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['documento_comum'] = $request->has('documento_comum');
        $validated['carimbo_modo'] = $validated['carimbo_modo'] ?? 'desativado';
        $validated['carimbar_aprovacao'] = $validated['carimbo_modo'] === 'automatico';
        $validated['carimbo_texto'] = $validated['carimbo_modo'] === 'manual' ? ($request->input('carimbo_texto') ?: null) : null;
        $validated['ordem'] = $validated['ordem'] ?? 0;

        // Campos de IA só podem ser alterados por administradores
        if (!auth('interno')->user()->isAdmin()) {
            unset($validated['criterio_ia'], $validated['ia_modelo_visao']);
        }

        // Se não é documento comum, limpa o tipo_processo_id
        if (!$validated['documento_comum']) {
            $validated['tipo_processo_id'] = null;
        }

        // Se escopo não é municipal, limpa municipio_id
        if ($validated['escopo_competencia'] !== 'municipal') {
            $validated['municipio_id'] = null;
        }

        $tipos_documento_obrigatorio->update($validated);

        $tab = $validated['escopo_competencia'] === 'municipal' ? 'tipos-documento-municipal' : 'tipos-documento';

        return redirect()
            ->route('admin.configuracoes.listas-documento.index', ['tab' => $tab])
            ->with('success', 'Tipo de documento obrigatório atualizado com sucesso!');
    }

    public function destroy(Request $request, TipoDocumentoObrigatorio $tipos_documento_obrigatorio)
    {
        // Verifica se está sendo usado em alguma lista
        if ($tipos_documento_obrigatorio->listasDocumento()->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Este tipo de documento está vinculado a listas e não pode ser excluído.'], 422);
            }
            return back()->with('error', 'Este tipo de documento está vinculado a listas e não pode ser excluído.');
        }

        $tipos_documento_obrigatorio->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Tipo de documento excluído com sucesso!']);
        }

        return back()->with('success', 'Tipo de documento obrigatório excluído com sucesso!');
    }

    /**
     * Exclui múltiplos tipos de documento de uma vez.
     */
    public function destroyMultiple(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:tipos_documento_obrigatorio,id',
        ]);

        $excluidos = 0;
        $vinculados = 0;
        $idsExcluidos = [];

        foreach ($validated['ids'] as $id) {
            $tipo = TipoDocumentoObrigatorio::find($id);
            if (!$tipo) {
                continue;
            }
            if ($tipo->listasDocumento()->exists()) {
                $vinculados++;
                continue;
            }
            $tipo->delete();
            $idsExcluidos[] = (int) $id;
            $excluidos++;
        }

        $mensagem = "{$excluidos} documento(s) excluído(s) com sucesso!";
        if ($vinculados > 0) {
            $mensagem .= " {$vinculados} não excluído(s) por estar(em) vinculado(s) a listas.";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'ids_excluidos' => $idsExcluidos,
                'excluidos' => $excluidos,
                'vinculados' => $vinculados,
            ]);
        }

        return back()->with('success', $mensagem);
    }
}
