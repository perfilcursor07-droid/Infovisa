{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Tipos de Documento Obrigatório</h3>
        <p class="text-sm text-gray-500">Documentos de escopo estadual ou comum a todos. Documentos municipais ficam na aba dedicada.</p>
    </div>
    <div class="flex items-center gap-2">
        <button @click="$dispatch('open-modal-tipo-documento-lote')" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-amber-600 text-amber-700 text-sm font-medium rounded-lg hover:bg-amber-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Adicionar em Lote
        </button>
        <button @click="$dispatch('open-modal-tipo-documento')" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Tipo
        </button>
    </div>
</div>

{{-- Filtros --}}
<div class="bg-gray-50 rounded-lg p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="hidden" name="tab" value="tipos-documento">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="busca" value="{{ request('busca') }}" 
                   placeholder="Buscar por nome ou descrição..."
                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
        </div>
        <div class="w-36">
            <select name="documento_comum" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                <option value="">Todos os tipos</option>
                <option value="1" {{ request('documento_comum') === '1' ? 'selected' : '' }}>Documentos Comuns</option>
                <option value="0" {{ request('documento_comum') === '0' ? 'selected' : '' }}>Documentos Específicos</option>
            </select>
        </div>
        <div class="w-32">
            <select name="escopo_competencia" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                <option value="">Todos escopos</option>
                <option value="estadual" {{ request('escopo_competencia') === 'estadual' ? 'selected' : '' }}>Estadual</option>
                <option value="todos" {{ request('escopo_competencia') === 'todos' ? 'selected' : '' }}>Todos</option>
            </select>
        </div>
        <div class="w-28">
            <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                <option value="">Status</option>
                <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Ativos</option>
                <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Inativos</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-amber-100 text-amber-700 text-sm font-medium rounded-lg hover:bg-amber-200 transition-colors">
            Filtrar
        </button>
        @if(request()->hasAny(['busca', 'documento_comum', 'escopo_competencia', 'status']))
        <a href="{{ route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-documento']) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
            Limpar
        </a>
        @endif
    </form>
</div>

{{-- Conteúdo --}}
@if($tiposDocumento->isEmpty())
<div class="text-center py-8">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <p class="text-gray-500">Nenhum tipo de documento encontrado</p>
</div>
@else

@php
    $filtroEscopo = request('escopo_competencia');
    $tiposEstaduais = $tiposDocumento->filter(fn($t) => $t->escopo_competencia === 'estadual');
    $tiposTodos = $tiposDocumento->filter(fn($t) => $t->escopo_competencia === 'todos');
@endphp

{{-- SEÇÃO ESTADUAL + TODOS --}}
@if(!$filtroEscopo || $filtroEscopo === 'estadual' || $filtroEscopo === 'todos')
@php $estaduaisETodos = $tiposEstaduais->merge($tiposTodos)->sortBy('ordem'); @endphp
@if($estaduaisETodos->count() > 0)
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <span class="text-base">🏛️</span>
        <span class="text-xs font-bold text-blue-800 uppercase tracking-wide">Estadual</span>
        <span class="text-[10px] px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded-full font-bold">{{ $estaduaisETodos->count() }}</span>
        <div class="flex-1 h-px bg-blue-100"></div>
    </div>
    <div class="bg-white rounded-xl border border-blue-200 overflow-hidden">
        @include('configuracoes.listas-documento.partials.tabela-tipos-documento', ['tiposTabela' => $estaduaisETodos])
    </div>
</div>
@endif
@endif

{{-- Paginação --}}
@if($tiposDocumento->hasPages())
<div class="mt-4">
    {{ $tiposDocumento->appends(request()->query())->links('pagination.tailwind-clean') }}
</div>
@endif
@endif

{{-- Modal Novo Tipo de Documento --}}
<div x-data="{ open: false, escopo: 'estadual' }" 
     @open-modal-tipo-documento.window="open = true"
     x-show="open" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-black/50" @click="open = false"></div>
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Novo Tipo de Documento</h3>
            
            <form action="{{ route('admin.configuracoes.tipos-documento-obrigatorio.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                            <input type="text" name="nome" required placeholder="Ex: CNPJ, Contrato Social"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                            <input type="number" name="ordem" value="0" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="descricao" rows="2" placeholder="Descrição do documento..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                    </div>

                    {{-- Documento Comum --}}
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex items-start gap-3 mb-4">
                            <input type="checkbox" name="documento_comum" id="documento_comum_modal" value="1"
                                   class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500 mt-0.5">
                            <div>
                                <label for="documento_comum_modal" class="text-sm font-medium text-gray-700">Documento Comum a Todos os Serviços</label>
                                <p class="text-xs text-gray-500 mt-1">Se marcado, será obrigatório para TODOS os serviços do escopo selecionado</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Escopo de Competência *</label>
                                <select name="escopo_competencia" x-model="escopo"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                    <option value="todos">Todos (Estadual + Municipal)</option>
                                    <option value="estadual" selected>Apenas Estadual</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Para documentos municipais, use a aba "Tipo de Documento Municipal".</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Setor</label>
                                <select name="tipo_setor"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                    <option value="todos">Todos (Público + Privado)</option>
                                    <option value="publico">Apenas Público</option>
                                    <option value="privado">Apenas Privado</option>
                                </select>
                            </div>
                        </div>

                        {{-- Município (só aparece quando escopo = municipal) --}}
                        <div x-show="escopo === 'municipal'" x-cloak class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Município *</label>
                            <select name="municipio_id" :required="escopo === 'municipal'"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Selecione...</option>
                                @foreach(\App\Models\Municipio::orderBy('nome')->get() as $mun)
                                <option value="{{ $mun->id }}">{{ $mun->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prazo de Validade (dias)</label>
                                <input type="number" name="prazo_validade_dias" min="1" placeholder="Ex: 30"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <p class="text-xs text-gray-500 mt-1">Opcional. Ex: CNPJ válido por 30 dias</p>
                            </div>
                            <div class="flex items-center gap-2 pt-6">
                                <input type="checkbox" name="ativo" id="ativo_modal" value="1" checked
                                       class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                                <label for="ativo_modal" class="text-sm text-gray-700">Ativo</label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observação para Estabelecimentos Públicos</label>
                            <textarea name="observacao_publica" rows="2" placeholder="Ex: Isento para estabelecimentos públicos"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observação para Estabelecimentos Privados</label>
                            <textarea name="observacao_privada" rows="2" placeholder="Ex: Apenas para empresas privadas"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Adicionar Tipos de Documento em Lote --}}
<div x-data="{ open: false, escopo: 'estadual', comum: false }" 
     @open-modal-tipo-documento-lote.window="open = true"
     x-show="open" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-black/50" @click="open = false"></div>
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Adicionar Tipos de Documento em Lote</h3>
                    <p class="text-sm text-gray-500">Cole a lista de documentos, um por linha. Todos serão criados com as mesmas configurações.</p>
                </div>
            </div>
            
            <form action="{{ route('admin.configuracoes.tipos-documento-obrigatorio.store-multiple') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    {{-- Lista de nomes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lista de Documentos *</label>
                        <textarea name="nomes" rows="10" required
                                  placeholder="Cole aqui um documento por linha. Exemplo:&#10;Comprovante de pagamento da taxa sanitária municipal (DUAM)&#10;Cópia do CPF, RG ou CNH&#10;Declaração de consumo de abastecimento de água&#10;Cópia do Alvará da Prefeitura"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-mono text-sm"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Cada linha vira um tipo de documento. Linhas vazias e duplicadas são ignoradas automaticamente.</p>
                    </div>

                    {{-- Configurações compartilhadas --}}
                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-3">Configurações aplicadas a todos</p>

                        <div class="flex items-start gap-3 mb-4">
                            <input type="checkbox" name="documento_comum" id="documento_comum_lote" value="1" x-model="comum"
                                   class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500 mt-0.5">
                            <div>
                                <label for="documento_comum_lote" class="text-sm font-medium text-gray-700">Documentos Comuns a Todos os Serviços</label>
                                <p class="text-xs text-gray-500 mt-1">Se marcado, serão obrigatórios para TODOS os serviços do escopo selecionado</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Escopo de Competência *</label>
                                <select name="escopo_competencia" x-model="escopo"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                    <option value="todos">Todos (Estadual + Municipal)</option>
                                    <option value="estadual" selected>Apenas Estadual</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Para documentos municipais, use a aba "Tipo de Documento Municipal".</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Setor</label>
                                <select name="tipo_setor"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                    <option value="todos">Todos (Público + Privado)</option>
                                    <option value="publico">Apenas Público</option>
                                    <option value="privado">Apenas Privado</option>
                                </select>
                            </div>
                        </div>

                        {{-- Município (só aparece quando escopo = municipal) --}}
                        <div x-show="escopo === 'municipal'" x-cloak class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Município *</label>
                            <select name="municipio_id" :required="escopo === 'municipal'"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Selecione...</option>
                                @foreach(\App\Models\Municipio::orderBy('nome')->get() as $mun)
                                <option value="{{ $mun->id }}">{{ $mun->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prazo de Validade (dias)</label>
                            <input type="number" name="prazo_validade_dias" min="1" placeholder="Ex: 30 (opcional)"
                                   class="w-full md:w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            <p class="text-xs text-gray-500 mt-1">Opcional. Aplicado a todos os documentos da lista.</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700">
                        Criar Todos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

