<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ListaDocumento;
use App\Models\Atividade;
use App\Models\TipoDocumentoObrigatorio;
use App\Models\TipoServico;
use App\Models\TipoProcesso;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListaDocumentoController extends Controller
{
    public function index(Request $request)
    {
        // Listas de Documentos
        $queryListas = ListaDocumento::with(['municipio', 'criadoPor', 'tipoProcesso'])
            ->withCount(['atividades', 'tiposDocumentoObrigatorio']);

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $queryListas->where(function($q) use ($busca) {
                $q->where('nome', 'ilike', "%{$busca}%")
                  ->orWhere('descricao', 'ilike', "%{$busca}%");
            });
        }

        if ($request->filled('tipo_processo_id')) {
            $queryListas->where('tipo_processo_id', $request->tipo_processo_id);
        }

        if ($request->filled('escopo')) {
            $queryListas->where('escopo', $request->escopo);
        }

        if ($request->filled('municipio_id')) {
            $queryListas->where('municipio_id', $request->municipio_id);
        }

        if ($request->filled('status')) {
            $queryListas->where('ativo', $request->status === 'ativo');
        }

        $listas = $queryListas->orderBy('nome')->paginate(15)->withQueryString();

        // Tipos de Documento Obrigatório
        $queryTiposDocumento = TipoDocumentoObrigatorio::with('municipio');

        if ($request->filled('busca') && $request->tab === 'tipos-documento') {
            $busca = $request->busca;
            $queryTiposDocumento->where(function($q) use ($busca) {
                $q->where('nome', 'ilike', "%{$busca}%")
                  ->orWhere('descricao', 'ilike', "%{$busca}%");
            });
        }

        if ($request->filled('status') && $request->tab === 'tipos-documento') {
            $queryTiposDocumento->where('ativo', $request->status === 'ativo');
        }

        if ($request->filled('documento_comum') && $request->tab === 'tipos-documento') {
            $queryTiposDocumento->where('documento_comum', $request->documento_comum === '1');
        }

        if ($request->filled('escopo_competencia') && $request->tab === 'tipos-documento') {
            $queryTiposDocumento->where('escopo_competencia', $request->escopo_competencia);
        }

        if ($request->filled('tipo_setor') && $request->tab === 'tipos-documento') {
            $queryTiposDocumento->where('tipo_setor', $request->tipo_setor);
        }

        $tiposDocumento = $queryTiposDocumento->ordenado()->paginate(15)->withQueryString();

        // Tipos de Serviço com contagem de atividades
        $tiposServico = TipoServico::with(['municipio', 'atividades'])->withCount('atividades')->ordenado()->paginate(15, ['*'], 'servico_page')->withQueryString();

        // Atividades
        $queryAtividades = Atividade::with('tipoServico');
        if ($request->filled('tipo_servico_id') && $request->tab === 'atividades') {
            $queryAtividades->where('tipo_servico_id', $request->tipo_servico_id);
        }
        $atividades = $queryAtividades->ordenado()->paginate(15)->withQueryString();

        // Tipos de Processo
        $tiposProcesso = TipoProcesso::where('ativo', true)->orderBy('nome')->get();

        // Municípios
        $municipios = Municipio::orderBy('nome')->get();

        return view('configuracoes.listas-documento.index', compact(
            'listas',
            'tiposDocumento',
            'tiposServico',
            'atividades',
            'tiposProcesso',
            'municipios'
        ));
    }

    public function create()
    {
        $tiposServico = TipoServico::ativos()->with('atividadesAtivas')->ordenado()->get();
        
        // Documentos específicos (podem ser selecionados)
        $tiposDocumento = TipoDocumentoObrigatorio::ativos()
            ->where('documento_comum', false)
            ->ordenado()
            ->get();
        
        // Documentos comuns - na criação mostra todos, pois o tipo de processo ainda não foi selecionado
        // A view pode filtrar via JavaScript quando o usuário selecionar o tipo de processo
        $documentosComuns = TipoDocumentoObrigatorio::ativos()
            ->where('documento_comum', true)
            ->with('tipoProcesso')
            ->ordenado()
            ->get();
            
        $tiposProcesso = TipoProcesso::where('ativo', true)->orderBy('nome')->get();
        $municipios = Municipio::orderBy('nome')->get();

        return view('configuracoes.listas-documento.create', compact('tiposServico', 'tiposDocumento', 'documentosComuns', 'tiposProcesso', 'municipios'));
    }

    public function store(Request $request)
    {
        // Debug: Log the incoming request data
        \Log::info('Lista Documento Store Request', [
            'all_data' => $request->all(),
            'documentos_selecionados' => $request->input('documentos_selecionados'),
            'tipos_servico' => $request->input('tipos_servico'),
        ]);

        // Verifica se é um tipo de processo especial (não requer tipos de serviço)
        $tipoProcessoId = $request->input('tipo_processo_id');
        $tipoProcesso = $tipoProcessoId ? TipoProcesso::find($tipoProcessoId) : null;
        $isProcessoEspecial = $tipoProcesso && in_array($tipoProcesso->codigo, ['projeto_arquitetonico', 'analise_rotulagem']);

        // Regras de validação base
        $rules = [
            'tipo_processo_id' => 'required|exists:tipo_processos,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'escopo' => 'required|in:estadual,municipal',
            'municipio_id' => 'nullable|required_if:escopo,municipal|exists:municipios,id',
            'ativo' => 'boolean',
            'documentos_selecionados' => 'nullable|array',
            'documentos_selecionados.*' => 'exists:tipos_documento_obrigatorio,id',
        ];

        $messages = [];

        // Para processos especiais, tipos_servico é opcional
        if (!$isProcessoEspecial) {
            $rules['tipos_servico'] = 'required|array|min:1';
            $rules['tipos_servico.*'] = 'exists:tipos_servico,id';
            $messages['tipos_servico.required'] = 'Selecione pelo menos um tipo de serviço.';
            $messages['tipos_servico.min'] = 'Selecione pelo menos um tipo de serviço.';
        } else {
            $rules['tipos_servico'] = 'nullable|array';
            $rules['tipos_servico.*'] = 'exists:tipos_servico,id';
        }

        $validated = $request->validate($rules, $messages);

        $validated['ativo'] = $request->has('ativo');
        $validated['criado_por'] = Auth::guard('interno')->user()->id;

        if ($validated['escopo'] === 'estadual') {
            $validated['municipio_id'] = null;
        }

        DB::beginTransaction();
        try {
            $lista = ListaDocumento::create([
                'tipo_processo_id' => $validated['tipo_processo_id'],
                'nome' => $validated['nome'],
                'descricao' => $validated['descricao'],
                'escopo' => $validated['escopo'],
                'municipio_id' => $validated['municipio_id'],
                'ativo' => $validated['ativo'],
                'criado_por' => $validated['criado_por'],
            ]);

            // Busca todas as atividades dos tipos de serviço selecionados (se houver)
            $tiposServicoSelecionados = $validated['tipos_servico'] ?? [];
            if (!empty($tiposServicoSelecionados)) {
                $atividadesIds = \App\Models\Atividade::whereIn('tipo_servico_id', $tiposServicoSelecionados)
                    ->pluck('id')
                    ->toArray();

                // Vincula atividades
                if (!empty($atividadesIds)) {
                    $lista->atividades()->attach($atividadesIds);
                }
            }

            // Vincula documentos com pivot data
            $documentosData = [];
            foreach (($validated['documentos_selecionados'] ?? []) as $index => $docId) {
                $obrigatorio = $request->input("documento_{$docId}_obrigatorio", 1);
                $observacao = $request->input("documento_{$docId}_observacao");
                
                $documentosData[$docId] = [
                    'obrigatorio' => (bool) $obrigatorio,
                    'observacao' => $observacao,
                    'ordem' => $index,
                ];
            }
            if (!empty($documentosData)) {
                $lista->tiposDocumentoObrigatorio()->attach($documentosData);
            }

            DB::commit();

            return redirect()
                ->route('admin.configuracoes.listas-documento.index')
                ->with('success', 'Lista de documentos criada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao criar lista de documentos', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return back()->withInput()->with('error', 'Erro ao criar lista: ' . $e->getMessage());
        }
    }

    public function show(ListaDocumento $listas_documento)
    {
        $listas_documento->load([
            'municipio',
            'criadoPor',
            'tipoProcesso',
            'atividades.tipoServico',
            'tiposDocumentoObrigatorio'
        ]);

        return view('configuracoes.listas-documento.show', [
            'lista' => $listas_documento
        ]);
    }

    public function edit(ListaDocumento $listas_documento)
    {
        $listas_documento->load(['atividades', 'tiposDocumentoObrigatorio', 'tipoProcesso']);
        
        $tiposServico = TipoServico::ativos()->with('atividadesAtivas')->ordenado()->get();
        
        // Documentos específicos (podem ser selecionados)
        $tiposDocumento = TipoDocumentoObrigatorio::ativos()
            ->where('documento_comum', false)
            ->ordenado()
            ->get();
        
        // Documentos comuns filtrados pelo tipo de processo da lista
        // Mostra documentos comuns que são para este tipo de processo OU para todos (tipo_processo_id = null)
        $documentosComuns = TipoDocumentoObrigatorio::ativos()
            ->where('documento_comum', true)
            ->where(function($q) use ($listas_documento) {
                $q->whereNull('tipo_processo_id')
                  ->orWhere('tipo_processo_id', $listas_documento->tipo_processo_id);
            })
            ->ordenado()
            ->get();
            
        $tiposProcesso = TipoProcesso::where('ativo', true)->orderBy('nome')->get();
        $municipios = Municipio::orderBy('nome')->get();

        return view('configuracoes.listas-documento.edit', [
            'lista' => $listas_documento,
            'tiposServico' => $tiposServico,
            'tiposDocumento' => $tiposDocumento,
            'documentosComuns' => $documentosComuns,
            'tiposProcesso' => $tiposProcesso,
            'municipios' => $municipios,
        ]);
    }

    public function update(Request $request, ListaDocumento $listas_documento)
    {
        // Debug: Log the incoming request data
        \Log::info('Lista Documento Update Request', [
            'lista_id' => $listas_documento->id,
            'all_data' => $request->all(),
            'documentos' => $request->input('documentos'),
            'tipos_servico' => $request->input('tipos_servico'),
        ]);

        // Verifica se é um tipo de processo especial (não requer tipos de serviço)
        $tipoProcessoId = $request->input('tipo_processo_id');
        $tipoProcesso = $tipoProcessoId ? TipoProcesso::find($tipoProcessoId) : null;
        $isProcessoEspecial = $tipoProcesso && in_array($tipoProcesso->codigo, ['projeto_arquitetonico', 'analise_rotulagem']);

        // Regras de validação base
        $rules = [
            'tipo_processo_id' => 'required|exists:tipo_processos,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'escopo' => 'required|in:estadual,municipal',
            'municipio_id' => 'nullable|required_if:escopo,municipal|exists:municipios,id',
            'ativo' => 'boolean',
            'documentos' => 'nullable|array',
            'documentos.*.id' => 'required|exists:tipos_documento_obrigatorio,id',
            'documentos.*.obrigatorio' => 'boolean',
            'documentos.*.observacao' => 'nullable|string',
        ];

        $messages = [];

        // Para processos especiais, tipos_servico é opcional
        if (!$isProcessoEspecial) {
            $rules['tipos_servico'] = 'required|array|min:1';
            $rules['tipos_servico.*'] = 'exists:tipos_servico,id';
            $messages['tipos_servico.required'] = 'Selecione pelo menos um tipo de serviço.';
            $messages['tipos_servico.min'] = 'Selecione pelo menos um tipo de serviço.';
        } else {
            $rules['tipos_servico'] = 'nullable|array';
            $rules['tipos_servico.*'] = 'exists:tipos_servico,id';
        }

        $validated = $request->validate($rules, $messages);

        $validated['ativo'] = $request->has('ativo');

        if ($validated['escopo'] === 'estadual') {
            $validated['municipio_id'] = null;
        }

        DB::beginTransaction();
        try {
            $listas_documento->update([
                'tipo_processo_id' => $validated['tipo_processo_id'],
                'nome' => $validated['nome'],
                'descricao' => $validated['descricao'],
                'escopo' => $validated['escopo'],
                'municipio_id' => $validated['municipio_id'],
                'ativo' => $validated['ativo'],
            ]);

            // Busca todas as atividades dos tipos de serviço selecionados (se houver)
            $tiposServicoSelecionados = $validated['tipos_servico'] ?? [];
            if (!empty($tiposServicoSelecionados)) {
                $atividadesIds = \App\Models\Atividade::whereIn('tipo_servico_id', $tiposServicoSelecionados)
                    ->pluck('id')
                    ->toArray();
                $listas_documento->atividades()->sync($atividadesIds);
            } else {
                // Se não tem tipos de serviço (processo especial), remove todas as atividades
                $listas_documento->atividades()->sync([]);
            }

            // Atualiza documentos
            $documentosData = [];
            foreach (($validated['documentos'] ?? []) as $index => $doc) {
                $documentosData[$doc['id']] = [
                    'obrigatorio' => $doc['obrigatorio'] ?? true,
                    'observacao' => $doc['observacao'] ?? null,
                    'ordem' => $index,
                ];
            }
            $listas_documento->tiposDocumentoObrigatorio()->sync($documentosData);

            DB::commit();

            return redirect()
                ->route('admin.configuracoes.listas-documento.index')
                ->with('success', 'Lista de documentos atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao atualizar lista: ' . $e->getMessage());
        }
    }

    public function destroy(ListaDocumento $listas_documento)
    {
        $listas_documento->delete();

        return redirect()
            ->route('admin.configuracoes.listas-documento.index')
            ->with('success', 'Lista de documentos excluída com sucesso!');
    }

    /**
     * Duplica uma lista existente
     */
    public function duplicate(ListaDocumento $listas_documento)
    {
        DB::beginTransaction();
        try {
            $novaLista = $listas_documento->replicate();
            $novaLista->nome = $listas_documento->nome . ' (Cópia)';
            $novaLista->criado_por = Auth::guard('interno')->user()->id;
            $novaLista->save();

            // Copia atividades
            $novaLista->atividades()->attach($listas_documento->atividades->pluck('id'));

            // Copia documentos com pivot data
            foreach ($listas_documento->tiposDocumentoObrigatorio as $doc) {
                $novaLista->tiposDocumentoObrigatorio()->attach($doc->id, [
                    'obrigatorio' => $doc->pivot->obrigatorio,
                    'observacao' => $doc->pivot->observacao,
                    'ordem' => $doc->pivot->ordem,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.configuracoes.listas-documento.edit', $novaLista)
                ->with('success', 'Lista duplicada com sucesso! Edite conforme necessário.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao duplicar lista: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // UNIDADE MÓVEL — Documentos obrigatórios por CNAE
    // =========================================================================

    public function unidadeMovelIndex()
    {
        $documentos = \App\Models\DocumentoUnidadeMovel::with('tipoDocumento', 'cnaesRelation')
            ->orderBy('escopo')
            ->orderBy('ordem')
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'tipo_documento_id' => $doc->tipo_documento_obrigatorio_id,
                    'tipo_documento_nome' => $doc->tipoDocumento->nome ?? '—',
                    'escopo' => $doc->escopo,
                    'obrigatorio' => $doc->obrigatorio,
                    'ordem' => $doc->ordem,
                    'cnaes' => $doc->cnaesRelation->pluck('cnae_codigo')->all(),
                ];
            });

        return response()->json($documentos);
    }

    public function unidadeMovelStore(Request $request)
    {
        $request->validate([
            'tipo_documento_obrigatorio_id' => 'required|exists:tipos_documento_obrigatorio,id',
            'escopo' => 'required|in:geral,por_municipio',
            'obrigatorio' => 'nullable|boolean',
            'cnaes' => 'required|array|min:1',
            'cnaes.*' => 'required|string',
        ]);

        $doc = \App\Models\DocumentoUnidadeMovel::create([
            'tipo_documento_obrigatorio_id' => $request->tipo_documento_obrigatorio_id,
            'escopo' => $request->escopo,
            'obrigatorio' => $request->boolean('obrigatorio', true),
            'ordem' => \App\Models\DocumentoUnidadeMovel::max('ordem') + 1,
        ]);

        $doc->syncCnaes($request->cnaes);

        return response()->json([
            'success' => true,
            'message' => 'Documento adicionado com sucesso!',
            'documento' => [
                'id' => $doc->id,
                'tipo_documento_id' => $doc->tipo_documento_obrigatorio_id,
                'tipo_documento_nome' => $doc->tipoDocumento->nome,
                'escopo' => $doc->escopo,
                'obrigatorio' => $doc->obrigatorio,
                'ordem' => $doc->ordem,
                'cnaes' => $doc->cnaes(),
            ],
        ]);
    }

    public function unidadeMovelDestroy($id)
    {
        $doc = \App\Models\DocumentoUnidadeMovel::findOrFail($id);
        $doc->delete();

        return response()->json([
            'success' => true,
            'message' => 'Documento removido com sucesso!',
        ]);
    }
}
