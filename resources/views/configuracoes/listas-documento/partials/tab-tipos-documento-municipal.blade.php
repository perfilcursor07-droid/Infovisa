{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Tipos de Documento Municipal</h3>
        <p class="text-sm text-gray-500">Documentos obrigatórios com escopo municipal, organizados por município</p>
    </div>
    <div class="flex items-center gap-2">
        <button @click="$dispatch('open-modal-tipo-documento-lote-municipal')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-green-600 text-green-700 text-sm font-medium rounded-lg hover:bg-green-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Adicionar em Lote
        </button>
        <button @click="$dispatch('open-modal-tipo-documento-municipal')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Tipo Municipal
        </button>
    </div>
</div>

{{-- Filtros --}}
<div class="bg-gray-50 rounded-lg p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="hidden" name="tab" value="tipos-documento-municipal">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="busca" value="{{ request('busca') }}"
                   placeholder="Buscar por nome ou descrição..."
                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <div class="w-48">
            <select name="municipio_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Todos os municípios</option>
                @foreach($municipios as $mun)
                <option value="{{ $mun->id }}" {{ (string) request('municipio_id') === (string) $mun->id ? 'selected' : '' }}>{{ $mun->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-36">
            <select name="documento_comum" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Todos os tipos</option>
                <option value="1" {{ request('documento_comum') === '1' ? 'selected' : '' }}>Documentos Comuns</option>
                <option value="0" {{ request('documento_comum') === '0' ? 'selected' : '' }}>Documentos Específicos</option>
            </select>
        </div>
        <div class="w-28">
            <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Status</option>
                <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Ativos</option>
                <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Inativos</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-green-100 text-green-700 text-sm font-medium rounded-lg hover:bg-green-200 transition-colors">
            Filtrar
        </button>
        @if(request()->hasAny(['busca', 'municipio_id', 'documento_comum', 'status']) && request('tab') === 'tipos-documento-municipal')
        <a href="{{ route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-documento-municipal']) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
            Limpar
        </a>
        @endif
    </form>
</div>

{{-- Conteúdo agrupado por município --}}
@if($tiposDocumentoMunicipaisAgrupados->isEmpty())
<div class="text-center py-8 bg-green-50 rounded-xl border border-green-200">
    <svg class="w-12 h-12 text-green-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    <p class="text-sm text-green-700">Nenhum tipo de documento municipal encontrado</p>
</div>
@else
<div class="mb-4">
    <div class="flex items-center gap-2 mb-4">
        <span class="text-base">🏘️</span>
        <span class="text-xs font-bold text-green-800 uppercase tracking-wide">Municipal</span>
        <span class="text-[10px] px-1.5 py-0.5 bg-green-100 text-green-700 rounded-full font-bold">{{ $tiposDocumentoMunicipais->count() }}</span>
        <div class="flex-1 h-px bg-green-100"></div>
    </div>

    @foreach($tiposDocumentoMunicipaisAgrupados as $munId => $tiposDoMun)
    @php $munNome = $tiposDoMun->first()->municipio->nome ?? 'Município não informado'; @endphp
    <div class="mb-4">
        <div class="flex items-center gap-2 mb-2 ml-2">
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="text-sm font-semibold text-green-700">{{ $munNome }}</span>
            <span class="text-[10px] px-1.5 py-0.5 bg-green-50 text-green-600 rounded-full font-medium">{{ $tiposDoMun->count() }}</span>
        </div>
        <div class="bg-white rounded-xl border border-green-200 overflow-hidden">
            @include('configuracoes.listas-documento.partials.tabela-tipos-documento', ['tiposTabela' => $tiposDoMun])
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Modal Novo Tipo Municipal --}}
<div x-data="{ open: false }"
     @open-modal-tipo-documento-municipal.window="open = true"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-black/50" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Novo Tipo de Documento Municipal</h3>

            <form action="{{ route('admin.configuracoes.tipos-documento-obrigatorio.store') }}" method="POST">
                @csrf
                <input type="hidden" name="escopo_competencia" value="municipal">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                            <input type="text" name="nome" required placeholder="Ex: DUAM, Alvará da Prefeitura"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                            <input type="number" name="ordem" value="0" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="descricao" rows="2" placeholder="Descrição do documento..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex items-start gap-3 mb-4">
                            <input type="checkbox" name="documento_comum" id="documento_comum_modal_mun" value="1"
                                   class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 mt-0.5">
                            <div>
                                <label for="documento_comum_modal_mun" class="text-sm font-medium text-gray-700">Documento Comum a Todos os Serviços</label>
                                <p class="text-xs text-gray-500 mt-1">Se marcado, será obrigatório para TODOS os serviços do município</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Município *</label>
                                <select name="municipio_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">Selecione...</option>
                                    @foreach($municipios as $mun)
                                    <option value="{{ $mun->id }}">{{ $mun->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Setor</label>
                                <select name="tipo_setor"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="todos">Todos (Público + Privado)</option>
                                    <option value="publico">Apenas Público</option>
                                    <option value="privado">Apenas Privado</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prazo de Validade (dias)</label>
                                <input type="number" name="prazo_validade_dias" min="1" placeholder="Ex: 30"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div class="flex items-center gap-2 pt-6">
                                <input type="checkbox" name="ativo" id="ativo_modal_mun" value="1" checked
                                       class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <label for="ativo_modal_mun" class="text-sm text-gray-700">Ativo</label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observação para Estabelecimentos Públicos</label>
                            <textarea name="observacao_publica" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observação para Estabelecimentos Privados</label>
                            <textarea name="observacao_privada" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Adicionar em Lote (Municipal) --}}
<div x-data="{ open: false }"
     @open-modal-tipo-documento-lote-municipal.window="open = true"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-black/50" @click="open = false"></div>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Adicionar Documentos Municipais em Lote</h3>
                    <p class="text-sm text-gray-500">Cole a lista de documentos, um por linha. Todos serão vinculados ao município selecionado.</p>
                </div>
            </div>

            <form action="{{ route('admin.configuracoes.tipos-documento-obrigatorio.store-multiple') }}" method="POST">
                @csrf
                <input type="hidden" name="escopo_competencia" value="municipal">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lista de Documentos *</label>
                        <textarea name="nomes" rows="10" required
                                  placeholder="Cole aqui um documento por linha..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 font-mono text-sm"></textarea>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex items-start gap-3 mb-4">
                            <input type="checkbox" name="documento_comum" id="documento_comum_lote_mun" value="1"
                                   class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 mt-0.5">
                            <div>
                                <label for="documento_comum_lote_mun" class="text-sm font-medium text-gray-700">Documentos Comuns a Todos os Serviços</label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Município *</label>
                                <select name="municipio_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">Selecione...</option>
                                    @foreach($municipios as $mun)
                                    <option value="{{ $mun->id }}">{{ $mun->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Setor</label>
                                <select name="tipo_setor"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="todos">Todos (Público + Privado)</option>
                                    <option value="publico">Apenas Público</option>
                                    <option value="privado">Apenas Privado</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prazo de Validade (dias)</label>
                            <input type="number" name="prazo_validade_dias" min="1" placeholder="Opcional"
                                   class="w-full md:w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Criar Todos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
