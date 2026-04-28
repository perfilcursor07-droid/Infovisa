@extends('layouts.admin')

@section('title', 'Nova Ordem de Serviço')
@section('page-title', 'Nova Ordem de Serviço')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.ordens-servico.index') }}" 
               class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all"
               title="Voltar para lista">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 leading-tight">Nova Ordem de Serviço</h1>
                <p class="text-sm text-gray-500">Preencha os dados abaixo para gerar uma nova OS.</p>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.ordens-servico.store') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Main Column (Left) --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- 1. Vinculação --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 relative z-20">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 rounded-t-xl">
                        <h2 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold ring-2 ring-white">1</span>
                            Vinculação
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Tipo de Abertura</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="relative flex flex-col p-3 bg-white border-2 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50/30 transition-all group">
                                    <input type="radio" name="tipo_vinculacao" value="com_estabelecimento" id="com_estabelecimento" 
                                           {{ old('tipo_vinculacao', 'com_estabelecimento') == 'com_estabelecimento' ? 'checked' : '' }}
                                           class="absolute top-3 right-3 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 group-hover:text-blue-700">Com Estabelecimento</span>
                                    <span class="text-xs text-gray-500 mt-0.5">Vinculado a uma empresa e processo existente.</span>
                                </label>

                                <label class="relative flex flex-col p-3 bg-white border-2 rounded-xl cursor-pointer hover:border-blue-500 hover:bg-blue-50/30 transition-all group">
                                    <input type="radio" name="tipo_vinculacao" value="sem_estabelecimento" id="sem_estabelecimento" 
                                           {{ old('tipo_vinculacao', 'com_estabelecimento') == 'sem_estabelecimento' ? 'checked' : '' }}
                                           class="absolute top-3 right-3 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 group-hover:text-blue-700">Sem Estabelecimento (Avulsa / Fiscalização)</span>
                                    <span class="text-xs text-gray-500 mt-0.5">Sem estabelecimento no inicio; depois voce pode vincular quando tiver os dados.</span>
                                </label>
                            </div>
                        </div>

                        <div id="estabelecimento-container" style="display: none;" class="space-y-5 pt-5 border-t border-gray-100">
                            <div>
                                <label for="estabelecimento_busca" class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-3">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Buscar Estabelecimentos <span class="text-red-500">*</span>
                                </label>
                                <div class="flex gap-2">
                                    <div class="flex-1 relative group">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                        </div>
                                        <select id="estabelecimento_busca" class="w-full pl-12 pr-4 py-3 text-sm border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white hover:border-gray-300 transition-all appearance-none cursor-pointer">
                                            <option value="">Digite nome ou CNPJ</option>
                                        </select>
                                    </div>
                                    <button type="button" onclick="adicionarEstabelecimento()" 
                                            class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium text-sm transition-colors flex items-center gap-1 whitespace-nowrap">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Adicionar
                                    </button>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Ao selecionar um estabelecimento na busca ele será adicionado automaticamente. O botão "Adicionar" continua disponível como apoio.
                                </p>
                                @error('estabelecimentos_ids')
                                    <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Lista de Estabelecimentos Adicionados --}}
                            <div id="estabelecimentos-adicionados" class="hidden">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Estabelecimentos Vinculados</label>
                                <div id="estabelecimentos-lista" class="space-y-2"></div>
                                <div id="estabelecimentos-hidden-inputs"></div>
                            </div>

                            {{-- Hidden input para processo_id (compatibilidade - preenchido automaticamente) --}}
                            <input type="hidden" name="processo_id" id="processo_id" value="">

                            @if(isset($pastasProcesso) && $pastasProcesso->isNotEmpty())
                            <div class="mt-4 bg-purple-50 border border-purple-200 rounded-lg p-3">
                                <label for="pasta_id" class="flex items-center gap-1.5 text-xs font-semibold text-purple-700 mb-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                    Pasta do Processo (Opcional)
                                </label>
                                <select name="pasta_id" id="pasta_id"
                                        class="w-full text-sm border-purple-200 rounded-lg focus:ring-purple-500 focus:border-purple-500 bg-white shadow-sm">
                                    <option value="">Todos (sem pasta)</option>
                                    @foreach($pastasProcesso as $pasta)
                                        <option value="{{ $pasta->id }}" {{ (string) old('pasta_id') === (string) $pasta->id ? 'selected' : '' }}>
                                            {{ $pasta->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-xs text-purple-700">Se selecionada, a OS será criada diretamente nesta pasta do processo.</p>
                            </div>
                            @endif

                            {{-- Aviso múltiplos estabelecimentos --}}
                            <div id="aviso-multiplos-estabelecimentos" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800 flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <p class="font-bold">Múltiplos Estabelecimentos</p>
                                    <p>Cada estabelecimento deve ter um processo aberto vinculado. Selecione o processo em cada card abaixo.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Escopo da OS --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 relative z-10">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 rounded-t-xl">
                        <h2 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold ring-2 ring-white">2</span>
                            Escopo da Ordem de Serviço
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        {{-- Tipos de Ação - Botão para abrir modal --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Tipos de Ação <span class="text-red-500">*</span>
                            </label>
                            <button type="button" onclick="abrirModalTiposAcao()" 
                                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg hover:bg-gray-100 hover:border-blue-400 transition-all text-left">
                                <span id="tipos-acao-display" class="text-gray-500">Clique para selecionar tipos de ação...</span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div id="tipos-acao-tags" class="flex flex-wrap gap-2 mt-2"></div>
                            {{-- Hidden inputs para enviar os valores --}}
                            <div id="tipos-acao-hidden-inputs"></div>
                            @error('tipos_acao_ids')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Atribuição de Técnicos por Atividade --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Atribuição de Técnicos por Atividade <span class="text-red-500">*</span>
                            </label>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="text-sm text-blue-800">
                                        <p class="font-medium">Nova estrutura de atribuição</p>
                                        <p class="mt-1">Primeiro selecione as atividades acima, depois atribua técnicos específicos para cada atividade selecionada.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="atividades-tecnicos-container" class="space-y-4">
                                <p class="text-gray-500 text-sm italic">Selecione primeiro os tipos de ação para configurar os técnicos.</p>
                            </div>
                            
                            {{-- Hidden inputs para enviar a estrutura --}}
                            <div id="atividades-tecnicos-hidden-inputs"></div>
                            @error('atividades_tecnicos')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Modal Tipos de Ação --}}
                <div id="modal-tipos-acao" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="fecharModalTiposAcao()"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-2xl sm:w-full mx-auto overflow-hidden">
                            {{-- Header com Gradient --}}
                            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-white">Selecionar Tipos de Ação</h3>
                                </div>
                                <button type="button" onclick="fecharModalTiposAcao()" class="text-white/70 hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            {{-- Campo de Pesquisa Melhorado --}}
                            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-b from-gray-50 to-white">
                                <div class="relative group">
                                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" id="pesquisa-tipos-acao" placeholder="Pesquise por ação ou subação..." 
                                           class="w-full pl-12 pr-4 py-3 text-sm border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white hover:border-gray-300 transition-all"
                                           onkeyup="filtrarTiposAcao()">
                                </div>
                                <p class="text-xs text-gray-500 mt-2 ml-1">💡 Dica: Digite o nome da ação ou subação para filtrar</p>
                            </div>
                            
                            {{-- Lista de Tipos de Ação --}}
                            <div class="px-6 py-4 max-h-[60vh] overflow-y-auto" id="lista-tipos-acao">
                                <div class="space-y-3">
                                    @foreach($tiposAcao as $tipoAcao)
                                    @php
                                        $subAcoesTexto = $tipoAcao->subAcoesAtivas->pluck('descricao')->map(fn($d) => strtolower($d))->implode(' ');
                                    @endphp
                                    <div class="tipo-acao-item bg-gradient-to-r from-gray-50 to-white rounded-xl border-2 border-gray-200 hover:border-blue-400 hover:shadow-md transition-all" 
                                         data-nome="{{ strtolower($tipoAcao->descricao) }}" 
                                         data-subacoes="{{ $subAcoesTexto }}">
                                        @if($tipoAcao->subAcoesAtivas->count() > 0)
                                            {{-- Ação com subações --}}
                                            <div class="p-4">
                                                <div class="flex items-center justify-between mb-3">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                            </svg>
                                                        </div>
                                                        <span class="text-sm font-semibold text-gray-900">{{ $tipoAcao->descricao }}</span>
                                                    </div>
                                                    <span class="text-xs font-bold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full">{{ $tipoAcao->subAcoesAtivas->count() }} subações</span>
                                                </div>
                                                <div class="pl-4 space-y-2 border-l-3 border-indigo-300">
                                                    {{-- Opção para selecionar APENAS a ação principal --}}
                                                    <label class="flex items-center p-2.5 bg-blue-50 rounded-lg hover:bg-blue-100 cursor-pointer border border-blue-200 hover:border-blue-300 transition-all group">
                                                        <input type="checkbox" class="tipo-acao-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4" 
                                                               value="{{ $tipoAcao->id }}" 
                                                               data-label="{{ $tipoAcao->descricao }}"
                                                               data-acao-label="{{ $tipoAcao->descricao }}"
                                                               data-is-acao-principal="true">
                                                        <span class="ml-3 text-sm text-blue-700 group-hover:text-blue-800 transition-colors font-medium">{{ $tipoAcao->descricao }}</span>
                                                        <span class="ml-auto text-xs bg-blue-200 text-blue-700 px-2 py-0.5 rounded-full">Ação Principal</span>
                                                    </label>
                                                    {{-- Subações --}}
                                                    @foreach($tipoAcao->subAcoesAtivas as $subAcao)
                                                    <label class="flex items-center p-2.5 bg-white rounded-lg hover:bg-indigo-50 cursor-pointer border border-transparent hover:border-indigo-200 transition-all group">
                                                        <input type="checkbox" class="tipo-acao-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" 
                                                               value="{{ $tipoAcao->id }}" 
                                                               data-label="{{ $subAcao->descricao }}"
                                                               data-acao-label="{{ $tipoAcao->descricao }}"
                                                               data-sub-acao-id="{{ $subAcao->id }}"
                                                               data-sub-acao-label="{{ $subAcao->descricao }}">
                                                        <span class="ml-3 text-sm text-gray-700 group-hover:text-indigo-700 transition-colors">{{ $subAcao->descricao }}</span>
                                                    </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            {{-- Ação sem subações --}}
                                            <label class="flex items-center p-4 hover:bg-blue-50 cursor-pointer group">
                                                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center group-hover:scale-110 transition-transform">
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                    </svg>
                                                </div>
                                                <input type="checkbox" class="tipo-acao-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 ml-3" 
                                                       value="{{ $tipoAcao->id }}" data-label="{{ $tipoAcao->descricao }}"
                                                       {{ in_array($tipoAcao->id, old('tipos_acao_ids', [])) ? 'checked' : '' }}>
                                                <span class="ml-3 text-sm text-gray-700 group-hover:text-blue-700 transition-colors font-medium">{{ $tipoAcao->descricao }}</span>
                                            </label>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                <p id="sem-resultados-tipos" class="hidden text-center text-gray-500 py-8">
                                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Nenhum tipo de ação encontrado
                                </p>
                            </div>
                            
                            {{-- Footer com Botões --}}
                            <div class="px-6 py-4 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-white flex justify-end gap-3">
                                <button type="button" onclick="fecharModalTiposAcao()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all">
                                    Cancelar
                                </button>
                                <button type="button" onclick="confirmarTiposAcao()" class="px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-sm hover:shadow-md transition-all flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Confirmar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Técnicos por Atividade --}}
                <div id="modal-tecnicos-atividade" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="fecharModalTecnicosAtividade()"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-2xl sm:w-full mx-auto overflow-hidden">
                            {{-- Header com Gradient --}}
                            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-green-600 to-green-700 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-white" id="modal-atividade-titulo">Atribuir Técnicos</h3>
                                </div>
                                <button type="button" onclick="fecharModalTecnicosAtividade()" class="text-white/70 hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="px-6 py-5">
                                {{-- Instrução com Ícone --}}
                                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-4 mb-5">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div class="text-sm text-green-800">
                                            <p class="font-semibold mb-1">Como funciona:</p>
                                            <p>Marque os técnicos que participarão desta atividade. O primeiro marcado será automaticamente definido como responsável.</p>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Lista de Técnicos com Checkboxes --}}
                                <div class="mb-5">
                                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-3">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"/>
                                        </svg>
                                        Selecione os Técnicos <span class="text-red-500">*</span>
                                    </label>
                                    
                                    {{-- Campo de Busca --}}
                                    <div class="relative mb-3">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                        </div>
                                        <input type="text" id="busca-tecnicos" 
                                               placeholder="Buscar técnico por nome..." 
                                               class="w-full pl-10 pr-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white"
                                               oninput="filtrarTecnicos(this.value)">
                                    </div>
                                    
                                    <div id="lista-tecnicos-container" class="max-h-64 overflow-y-auto border-2 border-gray-200 rounded-xl bg-gradient-to-b from-gray-50 to-white">
                                        @foreach($tecnicos as $tecnico)
                                        <label class="flex items-center p-4 hover:bg-green-50 cursor-pointer border-b border-gray-100 last:border-b-0 tecnico-item-label transition-colors group" data-tecnico-id="{{ $tecnico->id }}" data-tecnico-nome="{{ strtolower($tecnico->nome) }}">
                                            <input type="checkbox" class="tecnico-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500 w-5 h-5" 
                                                   value="{{ $tecnico->id }}" data-nome="{{ $tecnico->nome }}"
                                                   onchange="atualizarResponsavelAutomatico()">
                                            <span class="ml-3 text-sm text-gray-700 group-hover:text-green-700 transition-colors flex-1 font-medium">{{ $tecnico->nome }}</span>
                                            <span class="responsavel-badge hidden ml-2 px-3 py-1 text-xs font-bold bg-green-100 text-green-700 rounded-full flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></path></svg>
                                                Responsável
                                            </span>
                                        </label>
                                        @endforeach
                                    </div>
                                    <p id="nenhum-tecnico-encontrado" class="hidden text-sm text-gray-500 text-center py-4">Nenhum técnico encontrado.</p>
                                </div>
                                
                                {{-- Seleção do Responsável (aparece quando há mais de 1 técnico) --}}
                                <div id="responsavel-container" class="hidden mb-5">
                                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-3">
                                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        Técnico Responsável <span class="text-red-500">*</span>
                                    </label>
                                    <select id="responsavel-select" class="w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white hover:border-gray-300 transition-all"
                                            onchange="atualizarBadgeResponsavel()">
                                        <option value="">Selecione o responsável...</option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-2 ml-1">Escolha quem será o técnico responsável principal por esta atividade.</p>
                                </div>
                            </div>
                            
                            {{-- Footer com Botões --}}
                            <div class="px-6 py-4 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-white flex justify-end gap-3">
                                <button type="button" onclick="fecharModalTecnicosAtividade()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all">
                                    Cancelar
                                </button>
                                <button type="button" onclick="confirmarTecnicosAtividade()" class="px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-700 rounded-lg hover:from-green-700 hover:to-green-800 shadow-sm hover:shadow-md transition-all flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Confirmar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sidebar (Right) --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- 3. Detalhes e Prazos --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-t-xl">
                        <h2 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-sm">3</span>
                            <span class="text-base">Prazos e Detalhes</span>
                        </h2>
                    </div>
                    <div class="p-6 space-y-6">
                        
                        {{-- Datas em Grid --}}
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Data Início --}}
                            <div class="relative">
                                <label for="data_inicio" class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Início <span class="text-red-500">*</span>
                                </label>
                                    <input type="date" id="data_inicio" name="data_inicio" value="{{ old('data_inicio') }}" @if(empty($permiteDatasRetroativas)) min="{{ now()->toDateString() }}" @endif required
                                       class="w-full px-4 py-3 text-sm font-medium rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white hover:border-gray-300 transition-all">
                                @error('data_inicio') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Data Fim --}}
                            <div class="relative">
                                <label for="data_fim" class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Término <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="data_fim" name="data_fim" value="{{ old('data_fim') }}" @if(empty($permiteDatasRetroativas)) min="{{ now()->toDateString() }}" @endif required
                                       class="w-full px-4 py-3 text-sm font-medium rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white hover:border-gray-300 transition-all">
                                @if(!empty($permiteDatasRetroativas))
                                    <p class="mt-1 text-xs text-gray-500">💡 Datas retroativas são permitidas para administradores</p>
                                @else
                                    <p class="mt-1 text-xs text-gray-500">💡 Datas retroativas não são permitidas</p>
                                @endif
                                @error('data_fim') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Observações --}}
                        <div>
                            <label for="observacoes" class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Observações
                            </label>
                            <textarea id="observacoes" name="observacoes" rows="4" placeholder="Descreva detalhes adicionais sobre a ordem de serviço..."
                                      class="w-full px-4 py-3 text-sm rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white hover:border-gray-300 transition-all resize-none">{{ old('observacoes') }}</textarea>
                            @error('observacoes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Upload --}}
                        <div>
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                Anexo (PDF)
                            </label>
                            <div class="flex items-center justify-center w-full">
                                <label for="documento_anexo" class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gradient-to-b from-gray-50 to-white hover:from-blue-50 hover:to-white hover:border-blue-400 transition-all group">
                                    <div class="flex flex-col items-center justify-center py-4">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 group-hover:bg-blue-100 flex items-center justify-center mb-2 transition-colors">
                                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-500 group-hover:text-blue-600">Clique para anexar</p>
                                        <p class="text-xs text-gray-400">PDF até 10MB</p>
                                    </div>
                                    <input id="documento_anexo" name="documento_anexo" type="file" accept=".pdf,application/pdf" class="hidden" />
                                </label>
                            </div>
                            {{-- Arquivo Selecionado Feedback --}}
                            <div id="arquivo-selecionado" class="hidden mt-3 p-3 bg-green-50 rounded-xl border border-green-200 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <span id="nome-arquivo" class="text-sm font-medium text-green-700 truncate max-w-[140px]"></span>
                                </div>
                                <button type="button" onclick="removerArquivo()" class="text-green-600 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            @error('documento_anexo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                    </div>
                </div>

                {{-- Ações Sticky --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <button type="submit" 
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm hover:shadow-md transition-all mb-3 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Criar Ordem de Serviço
                    </button>
                    <a href="{{ route('admin.ordens-servico.index') }}" 
                       class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors text-sm">
                        Cancelar
                    </a>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    /* ===== CUSTOMIZAÇÃO SELECT2 ===== */
    
    /* Container Principal */
    .select2-container--default .select2-selection--single {
        min-height: 48px !important;
        border-radius: 0.75rem !important;
        border: 2px solid #e5e7eb !important;
        background-color: #ffffff !important;
        padding-top: 4px !important;
        transition: all 0.2s ease !important;
    }
    
    .select2-container--default .select2-selection--single:hover {
        border-color: #d1d5db !important;
    }
    
    .select2-container--focus .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 0 0 2px rgba(59, 130, 246, 0.5) !important;
    }
    
    /* Arrow */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 8px !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #6b7280 transparent transparent transparent !important;
        margin-top: -6px !important;
    }
    
    /* Texto Selecionado */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 46px !important;
        padding-left: 12px !important;
        color: #374151 !important;
        font-weight: 500 !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af !important;
    }
    
    /* ===== DROPDOWN ===== */
    .select2-dropdown {
        border: 2px solid #e5e7eb !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        margin-top: 4px !important;
    }
    
    .select2-dropdown--below {
        border-top: none !important;
        border-top-left-radius: 0 !important;
        border-top-right-radius: 0 !important;
    }
    
    /* ===== SEARCH BOX ===== */
    .select2-search--dropdown {
        padding: 8px !important;
        background: linear-gradient(to bottom, #f9fafb, #ffffff) !important;
        border-bottom: 1px solid #e5e7eb !important;
    }
    
    .select2-search--dropdown .select2-search__field {
        border: 2px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        padding: 10px 12px !important;
        font-size: 14px !important;
        transition: all 0.2s ease !important;
        background-color: #ffffff !important;
    }
    
    .select2-search--dropdown .select2-search__field:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        outline: none !important;
    }
    
    .select2-search--dropdown .select2-search__field::placeholder {
        color: #d1d5db !important;
    }
    
    /* ===== RESULTADOS ===== */
    .select2-results {
        max-height: 300px !important;
    }
    
    .select2-results__options {
        padding: 8px !important;
    }
    
    /* Item de Resultado */
    .select2-results__option {
        padding: 0 !important;
        margin-bottom: 6px !important;
        border-radius: 0.5rem !important;
        transition: all 0.15s ease !important;
    }
    
    .select2-results__option--highlighted {
        background-color: #eff6ff !important;
        color: #1e40af !important;
    }
    
    .select2-results__option--selected {
        background-color: #dbeafe !important;
        color: #1e40af !important;
    }
    
    /* Custom Result Template */
    .select2-result-custom {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
        padding: 12px !important;
        border-radius: 0.5rem !important;
        transition: all 0.15s ease !important;
    }
    
    .select2-results__option--highlighted .select2-result-custom {
        background-color: #eff6ff !important;
    }
    
    .select2-result-icon {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 32px !important;
        height: 32px !important;
        border-radius: 0.375rem !important;
        background-color: #dbeafe !important;
        flex-shrink: 0 !important;
    }
    
    .select2-result-icon svg {
        width: 18px !important;
        height: 18px !important;
        color: #0284c7 !important;
        stroke-width: 2 !important;
    }
    
    .select2-results__option--highlighted .select2-result-icon {
        background-color: #bfdbfe !important;
    }
    
    .select2-result-content {
        flex: 1 !important;
        min-width: 0 !important;
    }
    
    .select2-result-title {
        font-weight: 600 !important;
        color: #111827 !important;
        font-size: 14px !important;
        margin-bottom: 2px !important;
    }
    
    .select2-results__option--highlighted .select2-result-title {
        color: #1e40af !important;
    }
    
    .select2-result-cnpj {
        font-size: 12px !important;
        color: #6b7280 !important;
        font-family: 'Courier New', monospace !important;
    }
    
    .select2-results__option--highlighted .select2-result-cnpj {
        color: #1e40af !important;
        opacity: 0.8 !important;
    }
    
    /* Mensagem de Erro/Info */
    .select2-results__message {
        padding: 16px 12px !important;
        text-align: center !important;
        color: #6b7280 !important;
        font-size: 13px !important;
        background-color: #f9fafb !important;
    }
    
    /* ===== SELEÇÃO CUSTOMIZADA ===== */
    .select2-selection-custom {
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
    }
    
    .select2-selection-custom svg {
        width: 16px !important;
        height: 16px !important;
        color: #3b82f6 !important;
        flex-shrink: 0 !important;
    }
    
    .select2-selection-custom span {
        font-weight: 500 !important;
        color: #111827 !important;
    }
    
    /* Esconder inputs originais de radio para custom styling */
    input[type="radio"]:focus { outline: none; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const comEstabelecimentoRadio = document.getElementById('com_estabelecimento');
        const semEstabelecimentoRadio = document.getElementById('sem_estabelecimento');
        const estabelecimentoContainer = document.getElementById('estabelecimento-container');
        const documentoInput = document.getElementById('documento_anexo');

        // ==========================================
        // Múltiplos Estabelecimentos
        // ==========================================
        let estabelecimentosSelecionados = []; // [{id, text, cnpj, nome, processo_id, continuar_sem_processo}]

        function toggleEstabelecimentoField() {
            if (comEstabelecimentoRadio.checked) {
                estabelecimentoContainer.style.display = 'block';
            } else {
                estabelecimentoContainer.style.display = 'none';
                estabelecimentosSelecionados = [];
                atualizarListaEstabelecimentos();
            }
        }

        comEstabelecimentoRadio.addEventListener('change', toggleEstabelecimentoField);
        semEstabelecimentoRadio.addEventListener('change', toggleEstabelecimentoField);
        toggleEstabelecimentoField();

        documentoInput.addEventListener('change', function(e) {
            const arquivo = e.target.files[0];
            const arquivoContainer = document.getElementById('arquivo-selecionado');
            if (arquivo) {
                if (arquivo.size > 10 * 1024 * 1024) { alert('Máximo 10MB'); this.value = ''; return; }
                if (arquivo.type !== 'application/pdf') { alert('Apenas PDF'); this.value = ''; return; }
                document.getElementById('nome-arquivo').textContent = arquivo.name;
                arquivoContainer.classList.remove('hidden');
            } else {
                arquivoContainer.classList.add('hidden');
            }
        });

        window.removerArquivo = function() {
            documentoInput.value = '';
            document.getElementById('arquivo-selecionado').classList.add('hidden');
        };

        // Select2 para buscar estabelecimentos
        $('#estabelecimento_busca').select2({
            ajax: {
                url: '{{ url('/admin/ordens-servico/api/buscar-estabelecimentos') }}',
                dataType: 'json',
                delay: 250,
                data: (params) => ({ q: params.term, page: params.page || 1 }),
                processResults: (data, params) => ({ 
                    results: data.results.map(item => ({
                        id: item.id,
                        text: item.text,
                        cnpj: item.cnpj,
                        nome: item.nome_fantasia || item.text
                    })), 
                    pagination: { more: data.pagination.more } 
                }),
                cache: true
            },
            placeholder: 'Digite nome ou CNPJ',
            minimumInputLength: 2,
            width: '100%',
            allowClear: true,
            templateResult: function(data) {
                if (!data.id) return data.text;
                return $('<div class="select2-result-custom">' +
                    '<div class="select2-result-icon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>' +
                    '<div class="select2-result-content">' +
                    '<div class="select2-result-title">' + (data.nome || data.text) + '</div>' +
                    '<div class="select2-result-cnpj">' + (data.cnpj || '') + '</div>' +
                    '</div>' +
                    '</div>');
            },
            templateSelection: function(data) {
                if (!data.id) return data.text;
                return $('<div class="select2-selection-custom">' +
                    '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>' +
                    '<span>' + (data.nome || data.text) + '</span>' +
                    '</div>');
            }
        });

        // UX: ao clicar na área, já abre e foca para digitação
        const estabelecimentoBuscaSelect = $('#estabelecimento_busca');
        estabelecimentoBuscaSelect.on('select2:open', function () {
            const searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) {
                searchField.placeholder = 'Digite nome ou CNPJ';
                searchField.focus();
            }
        });

        const estabelecimentoBuscaWrapper = document.querySelector('#estabelecimento_busca')?.closest('.group');
        if (estabelecimentoBuscaWrapper) {
            estabelecimentoBuscaWrapper.addEventListener('click', function () {
                estabelecimentoBuscaSelect.select2('open');
            });
        }

        estabelecimentoBuscaSelect.on('select2:select', function () {
            setTimeout(() => adicionarEstabelecimento(), 0);
        });

        // Adicionar estabelecimento à lista
        window.adicionarEstabelecimento = function() {
            const select = $('#estabelecimento_busca');
            const data = select.select2('data')[0];
            if (!data || !data.id) {
                alert('Selecione um estabelecimento para adicionar.');
                return;
            }
            // Verifica se já está na lista
            if (estabelecimentosSelecionados.find(e => e.id == data.id)) {
                alert('Este estabelecimento já foi adicionado.');
                return;
            }
            estabelecimentosSelecionados.push({
                id: data.id,
                text: data.text,
                cnpj: data.cnpj || '',
                nome: data.nome || data.text,
                processo_id: null,
                continuar_sem_processo: false
            });
            select.val(null).trigger('change');
            atualizarListaEstabelecimentos();
        };

        window.definirContinuarSemProcesso = function(estId, continuar) {
            const estabelecimento = estabelecimentosSelecionados.find(e => e.id == estId);
            if (!estabelecimento) {
                return;
            }

            estabelecimento.continuar_sem_processo = !!continuar;
            const hiddenInput = document.getElementById(`continuar-sem-processo-est-${estId}`);
            if (hiddenInput) {
                hiddenInput.value = continuar ? '1' : '0';
            }
        };

        window.confirmarContinuarSemProcesso = function(estId) {
            const confirmou = window.confirm('Este estabelecimento não possui processos abertos. Deseja continuar e cadastrar a OS sem processo?');
            definirContinuarSemProcesso(estId, confirmou);
            return confirmou;
        };

        // Remover estabelecimento da lista
        window.removerEstabelecimentoDaLista = function(id) {
            estabelecimentosSelecionados = estabelecimentosSelecionados.filter(e => e.id != id);
            atualizarListaEstabelecimentos();
            // Atualiza dropdown de estabelecimentos nas atividades
            if (atividadesSelecionadas.length > 0) {
                atualizarInterfaceTecnicos();
            }
        };

        // Atualizar a lista visual e hidden inputs
        function atualizarListaEstabelecimentos() {
            const container = document.getElementById('estabelecimentos-adicionados');
            const lista = document.getElementById('estabelecimentos-lista');
            const hiddenContainer = document.getElementById('estabelecimentos-hidden-inputs');
            const avisoMultiplos = document.getElementById('aviso-multiplos-estabelecimentos');
            
            lista.innerHTML = '';
            hiddenContainer.innerHTML = '';

            if (estabelecimentosSelecionados.length === 0) {
                container.classList.add('hidden');
                avisoMultiplos.classList.add('hidden');
                return;
            }

            container.classList.remove('hidden');

            estabelecimentosSelecionados.forEach((est, index) => {
                // Card visual com processo dropdown
                const card = document.createElement('div');
                card.className = 'bg-white border border-gray-200 rounded-xl hover:border-blue-400 hover:shadow-md transition-all duration-200 overflow-hidden';
                card.style.borderLeft = '4px solid #3b82f6';
                card.id = `estab-card-${est.id}`;
                card.innerHTML = `
                    <div class="flex items-start gap-3 px-4 pt-4 pb-3">
                        <div class="relative flex-shrink-0">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-blue-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow">${index + 1}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 leading-snug">${est.nome}</p>
                            ${est.cnpj ? `<span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-md bg-gray-100 text-xs font-mono text-gray-500">${est.cnpj}</span>` : ''}
                        </div>
                        <button type="button" onclick="removerEstabelecimentoDaLista(${est.id})"
                                title="Remover estabelecimento"
                                class="flex-shrink-0 p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="px-4 pb-4">
                        <div class="bg-blue-50/50 border border-blue-100 rounded-lg p-3">
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-blue-700 mb-2">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Processo Vinculado <span class="text-red-500 ml-0.5">*</span>
                            </label>
                            <select id="processo-est-${est.id}" onchange="selecionarProcessoEstabelecimento(${est.id}, this.value)"
                                    class="w-full text-sm border-blue-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm">
                                <option value="">Carregando processos...</option>
                            </select>
                        </div>
                    </div>
                `;
                lista.appendChild(card);

                // Hidden inputs
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'estabelecimentos_ids[]';
                input.value = est.id;
                hiddenContainer.appendChild(input);

                // Hidden input para processo do estabelecimento
                const inputProcesso = document.createElement('input');
                inputProcesso.type = 'hidden';
                inputProcesso.name = `processos_estabelecimentos[${est.id}]`;
                inputProcesso.id = `processo-hidden-est-${est.id}`;
                inputProcesso.value = est.processo_id || '';
                hiddenContainer.appendChild(inputProcesso);

                const inputContinuarSemProcesso = document.createElement('input');
                inputContinuarSemProcesso.type = 'hidden';
                inputContinuarSemProcesso.name = `continuar_sem_processo_estabelecimentos[${est.id}]`;
                inputContinuarSemProcesso.id = `continuar-sem-processo-est-${est.id}`;
                inputContinuarSemProcesso.value = est.continuar_sem_processo ? '1' : '0';
                hiddenContainer.appendChild(inputContinuarSemProcesso);

                // Carrega processos para este estabelecimento
                carregarProcessosEstabelecimento(est.id, est.processo_id || null);
            });

            // Aviso múltiplos
            if (estabelecimentosSelecionados.length > 1) {
                avisoMultiplos.classList.remove('hidden');
            } else {
                avisoMultiplos.classList.add('hidden');
            }

            // Também envia o primeiro como estabelecimento_id para compatibilidade
            let inputPrincipal = document.getElementById('estabelecimento_id_hidden');
            if (!inputPrincipal) {
                inputPrincipal = document.createElement('input');
                inputPrincipal.type = 'hidden';
                inputPrincipal.name = 'estabelecimento_id';
                inputPrincipal.id = 'estabelecimento_id_hidden';
                hiddenContainer.appendChild(inputPrincipal);
            }
            inputPrincipal.value = estabelecimentosSelecionados[0].id;
        }

        // Selecionar processo para um estabelecimento específico
        window.selecionarProcessoEstabelecimento = function(estId, processoId) {
            const hiddenInput = document.getElementById(`processo-hidden-est-${estId}`);
            if (hiddenInput) {
                hiddenInput.value = processoId;
            }
            // Atualiza no array
            const est = estabelecimentosSelecionados.find(e => e.id == estId);
            if (est) {
                est.processo_id = processoId;
                est.continuar_sem_processo = false;
                const continuarInput = document.getElementById(`continuar-sem-processo-est-${estId}`);
                if (continuarInput) {
                    continuarInput.value = '0';
                }
            }
            // Atualiza processo_id principal (compatibilidade) = processo do primeiro estabelecimento
            if (estabelecimentosSelecionados.length > 0 && estabelecimentosSelecionados[0].id == estId) {
                document.getElementById('processo_id').value = processoId;
            }
        };

        // Carregar processos de um estabelecimento específico (por card)
        function carregarProcessosEstabelecimento(estId, processoIdPreSelecionado) {
            const processoSelect = document.getElementById(`processo-est-${estId}`);
            
            if (!processoSelect) return;

            processoSelect.innerHTML = '<option value="">Carregando...</option>';
            processoSelect.disabled = true;

            fetch(`{{ url('/admin/ordens-servico/api/processos-estabelecimento') }}/${estId}`)
                .then(r => r.json())
                .then(data => {
                    if(data.success && data.processos.length > 0) {
                        processoSelect.innerHTML = '<option value="">Selecione um processo</option>';
                        data.processos.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.id;
                            opt.textContent = `${p.numero_processo} - ${p.tipo_label}`;
                            if (processoIdPreSelecionado && p.id == processoIdPreSelecionado) {
                                opt.selected = true;
                            }
                            processoSelect.appendChild(opt);
                        });
                        processoSelect.disabled = false;
                        
                        // Se tinha pré-seleção e deu match, atualiza hidden
                        if (processoIdPreSelecionado && processoSelect.value) {
                            selecionarProcessoEstabelecimento(estId, processoSelect.value);
                        }
                        // Se só tem 1 processo, auto-seleciona
                        if (data.processos.length === 1) {
                            processoSelect.value = data.processos[0].id;
                            selecionarProcessoEstabelecimento(estId, data.processos[0].id);
                        }
                    } else {
                        processoSelect.innerHTML = '<option value="">Sem processos abertos</option>';
                        processoSelect.disabled = true;
                        selecionarProcessoEstabelecimento(estId, '');

                        const estabelecimento = estabelecimentosSelecionados.find(e => e.id == estId);
                        if (estabelecimento && !estabelecimento.popupSemProcessoExibido && !estabelecimento.continuar_sem_processo) {
                            estabelecimento.popupSemProcessoExibido = true;
                            confirmarContinuarSemProcesso(estId);
                        }
                    }
                })
                .catch(() => {
                    processoSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                    processoSelect.disabled = true;
                });
        }

        // Variáveis globais para controle
        let atividadesSelecionadas = [];
        let atividadesTecnicos = {};
        let atividadeAtualModal = null;

        // Funções para Modal de Tipos de Ação
        window.abrirModalTiposAcao = function() {
            document.getElementById('modal-tipos-acao').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        window.fecharModalTiposAcao = function() {
            document.getElementById('modal-tipos-acao').classList.add('hidden');
            document.body.style.overflow = '';
        };

        window.confirmarTiposAcao = function() {
            const checkboxes = document.querySelectorAll('.tipo-acao-checkbox:checked');
            const tagsContainer = document.getElementById('tipos-acao-tags');
            const hiddenContainer = document.getElementById('tipos-acao-hidden-inputs');
            const display = document.getElementById('tipos-acao-display');
            
            tagsContainer.innerHTML = '';
            hiddenContainer.innerHTML = '';
            atividadesSelecionadas = [];
            
            if (checkboxes.length === 0) {
                display.textContent = 'Clique para selecionar tipos de ação...';
                display.classList.add('text-gray-500');
                display.classList.remove('text-gray-700');
            } else {
                display.textContent = checkboxes.length + ' atividade(s) selecionada(s)';
                display.classList.remove('text-gray-500');
                display.classList.add('text-gray-700');
                
                checkboxes.forEach(cb => {
                    const tipoAcaoId = cb.value;
                    const subAcaoId = cb.dataset.subAcaoId || null;
                    const subAcaoLabel = cb.dataset.subAcaoLabel || null;
                    const acaoLabel = cb.dataset.acaoLabel || cb.dataset.label;
                    const isAcaoPrincipal = cb.dataset.isAcaoPrincipal === 'true';
                    
                    // Se tem subação, usa o label da subação; senão, usa o label da ação
                    const displayLabel = subAcaoLabel || cb.dataset.label;
                    
                    // Cria um ID único para a atividade (ação + subação se existir, ou ação_principal)
                    const atividadeUniqueId = isAcaoPrincipal ? `${tipoAcaoId}_principal` : (subAcaoId ? `${tipoAcaoId}_${subAcaoId}` : tipoAcaoId);
                    
                    atividadesSelecionadas.push({
                        id: atividadeUniqueId,
                        tipo_acao_id: tipoAcaoId,
                        sub_acao_id: subAcaoId,
                        nome: displayLabel,
                        acao_nome: acaoLabel,
                        is_acao_principal: isAcaoPrincipal
                    });
                    
                    // Tag visual - cor diferente para ação principal vs subação
                    const tag = document.createElement('span');
                    const tagClass = subAcaoId && !isAcaoPrincipal ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700';
                    const principalLabel = isAcaoPrincipal ? ' <span class="text-xs opacity-70">(Principal)</span>' : '';
                    tag.className = `inline-flex items-center gap-1 px-2 py-1 ${tagClass} text-xs font-medium rounded-full`;
                    tag.innerHTML = displayLabel + principalLabel + '<button type="button" onclick="removerTipoAcao(\'' + atividadeUniqueId + '\')" class="hover:opacity-70"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>';
                    tagsContainer.appendChild(tag);
                    
                    // Hidden input para tipo_acao_id
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'tipos_acao_ids[]';
                    input.value = tipoAcaoId;
                    hiddenContainer.appendChild(input);
                });
            }
            
            // Atualiza a interface de técnicos por atividade
            atualizarInterfaceTecnicos();
            fecharModalTiposAcao();
        };

        window.removerTipoAcao = function(id) {
            // ID pode ser "tipoAcaoId", "tipoAcaoId_subAcaoId" ou "tipoAcaoId_principal"
            const parts = String(id).split('_');
            const tipoAcaoId = parts[0];
            const secondPart = parts[1] || null;
            const isAcaoPrincipal = secondPart === 'principal';
            const subAcaoId = isAcaoPrincipal ? null : secondPart;
            
            // Encontra o checkbox correto
            const checkboxes = document.querySelectorAll('.tipo-acao-checkbox[value="' + tipoAcaoId + '"]');
            checkboxes.forEach(cb => {
                const cbSubAcaoId = cb.dataset.subAcaoId || null;
                const cbIsAcaoPrincipal = cb.dataset.isAcaoPrincipal === 'true';
                
                if (isAcaoPrincipal && cbIsAcaoPrincipal) {
                    cb.checked = false;
                } else if (subAcaoId && cbSubAcaoId === subAcaoId) {
                    cb.checked = false;
                } else if (!isAcaoPrincipal && !subAcaoId && !cbSubAcaoId && !cbIsAcaoPrincipal) {
                    cb.checked = false;
                }
            });
            
            // Remove da estrutura de técnicos
            delete atividadesTecnicos[id];
            
            confirmarTiposAcao();
        };

        // Função para atualizar a interface de técnicos por atividade
        function atualizarInterfaceTecnicos() {
            const container = document.getElementById('atividades-tecnicos-container');
            
            if (atividadesSelecionadas.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm italic">Selecione primeiro os tipos de ação para configurar os técnicos.</p>';
                return;
            }
            
            container.innerHTML = '';
            
            atividadesSelecionadas.forEach(atividade => {
                const atividadeDiv = document.createElement('div');
                atividadeDiv.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50';
                
                const tecnicosAtribuidos = atividadesTecnicos[atividade.id] || { responsavel: null, tecnicos: [], estabelecimento_id: null };
                const responsavelNome = tecnicosAtribuidos.responsavel ? 
                    (document.querySelector(`.tecnico-checkbox[value="${tecnicosAtribuidos.responsavel}"]`)?.dataset.nome || 'Técnico não encontrado') : 
                    'Não definido';
                
                const tecnicosAdicionais = tecnicosAtribuidos.tecnicos.length > 1 ? 
                    tecnicosAtribuidos.tecnicos
                        .filter(id => id !== tecnicosAtribuidos.responsavel)
                        .map(id => {
                            const cb = document.querySelector(`.tecnico-checkbox[value="${id}"]`);
                            return cb ? cb.dataset.nome : 'Técnico não encontrado';
                        }).join(', ') : 'Nenhum';
                
                // Se tem subação, mostra a subação como título principal; se é ação principal, mostra badge
                let tituloAtividade;
                if (atividade.sub_acao_id && !atividade.is_acao_principal) {
                    tituloAtividade = `<span class="text-indigo-600">${atividade.nome}</span> <span class="text-xs text-gray-500">(${atividade.acao_nome})</span>`;
                } else if (atividade.is_acao_principal) {
                    tituloAtividade = `${atividade.nome} <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-2">Principal</span>`;
                } else {
                    tituloAtividade = atividade.nome;
                }

                // Dropdown de estabelecimento para a atividade (só se tiver múltiplos)
                let estabelecimentoDropdownHtml = '';
                if (estabelecimentosSelecionados.length > 1) {
                    const selectedEstId = tecnicosAtribuidos.estabelecimento_id || '';
                    let opcoesEst = '<option value="">-- Todos / Geral --</option>';
                    estabelecimentosSelecionados.forEach(est => {
                        const sel = (est.id == selectedEstId) ? 'selected' : '';
                        opcoesEst += `<option value="${est.id}" ${sel}>${est.nome} (${est.cnpj})</option>`;
                    });
                    estabelecimentoDropdownHtml = `
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <label class="flex items-center gap-1 text-xs font-semibold text-gray-600 mb-1">
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                Estabelecimento vinculado a esta ação
                            </label>
                            <select onchange="definirEstabelecimentoAtividade('${atividade.id}', this.value)" 
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                ${opcoesEst}
                            </select>
                        </div>
                    `;
                }
                
                atividadeDiv.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-900">${tituloAtividade}</h4>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="abrirModalTecnicosAtividade('${atividade.id}', '${atividade.nome.replace(/'/g, "\\'")}')" 
                                    class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full hover:bg-blue-200 transition-colors">
                                ${tecnicosAtribuidos.responsavel ? 'Editar' : 'Atribuir'} Técnicos
                            </button>
                            <button type="button" onclick="if(confirm('Remover este tipo de ação?')) removerTipoAcao('${atividade.id}')"
                                    class="px-3 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-full hover:bg-red-200 transition-colors">
                                Remover
                            </button>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p><span class="font-medium">Responsável:</span> ${responsavelNome}</p>
                        <p><span class="font-medium">Técnicos adicionais:</span> ${tecnicosAdicionais}</p>
                    </div>
                    ${estabelecimentoDropdownHtml}
                `;
                
                container.appendChild(atividadeDiv);
            });
            
            // Atualiza os hidden inputs
            atualizarHiddenInputsTecnicos();
        }

        // Define estabelecimento para uma atividade específica
        window.definirEstabelecimentoAtividade = function(atividadeId, estabelecimentoId) {
            if (!atividadesTecnicos[atividadeId]) {
                atividadesTecnicos[atividadeId] = { responsavel: null, tecnicos: [], estabelecimento_id: null };
            }
            atividadesTecnicos[atividadeId].estabelecimento_id = estabelecimentoId ? parseInt(estabelecimentoId) : null;
            atualizarHiddenInputsTecnicos();
        };

        // Funções para Modal de Técnicos por Atividade
        window.abrirModalTecnicosAtividade = function(atividadeId, atividadeNome) {
            atividadeAtualModal = atividadeId;
            document.getElementById('modal-atividade-titulo').textContent = `Atribuir Técnicos - ${atividadeNome}`;
            
            // Limpa o campo de busca
            document.getElementById('busca-tecnicos').value = '';
            filtrarTecnicos('');
            
            // Carrega dados existentes
            const tecnicosAtribuidos = atividadesTecnicos[atividadeId] || { responsavel: null, tecnicos: [] };
            
            // Limpa e configura checkboxes
            document.querySelectorAll('.tecnico-checkbox').forEach(cb => {
                cb.checked = tecnicosAtribuidos.tecnicos.includes(parseInt(cb.value));
            });
            
            // Atualiza o select de responsável e badges
            atualizarResponsavelAutomatico();
            
            // Se já tinha responsável definido, seleciona ele
            if (tecnicosAtribuidos.responsavel) {
                document.getElementById('responsavel-select').value = tecnicosAtribuidos.responsavel;
                atualizarBadgeResponsavel();
            }
            
            document.getElementById('modal-tecnicos-atividade').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        // Função para filtrar técnicos por nome
        window.filtrarTecnicos = function(termo) {
            const termoLower = termo.toLowerCase().trim();
            const labels = document.querySelectorAll('.tecnico-item-label');
            const container = document.getElementById('lista-tecnicos-container');
            const nenhumEncontrado = document.getElementById('nenhum-tecnico-encontrado');
            let encontrados = 0;
            
            labels.forEach(label => {
                const nome = label.dataset.tecnicoNome || '';
                if (termoLower === '' || nome.includes(termoLower)) {
                    label.style.display = 'flex';
                    encontrados++;
                } else {
                    label.style.display = 'none';
                }
            });
            
            // Mostra mensagem se nenhum técnico foi encontrado
            if (encontrados === 0 && termoLower !== '') {
                container.style.display = 'none';
                nenhumEncontrado.classList.remove('hidden');
            } else {
                container.style.display = 'block';
                nenhumEncontrado.classList.add('hidden');
            }
        };

        // Função para atualizar automaticamente o responsável quando técnicos são marcados
        window.atualizarResponsavelAutomatico = function() {
            const checkboxesMarcados = Array.from(document.querySelectorAll('.tecnico-checkbox:checked'));
            const responsavelSelect = document.getElementById('responsavel-select');
            const responsavelContainer = document.getElementById('responsavel-container');
            
            // Limpa o select
            responsavelSelect.innerHTML = '<option value="">Selecione o responsável...</option>';
            
            // Esconde todos os badges
            document.querySelectorAll('.responsavel-badge').forEach(badge => {
                badge.classList.add('hidden');
            });
            
            if (checkboxesMarcados.length === 0) {
                responsavelContainer.classList.add('hidden');
                return;
            }
            
            // Adiciona opções ao select
            checkboxesMarcados.forEach(cb => {
                const option = document.createElement('option');
                option.value = cb.value;
                option.textContent = cb.dataset.nome;
                responsavelSelect.appendChild(option);
            });
            
            // Se só tem 1 técnico, ele é automaticamente o responsável
            if (checkboxesMarcados.length === 1) {
                responsavelSelect.value = checkboxesMarcados[0].value;
                responsavelContainer.classList.add('hidden');
                
                // Mostra badge no único técnico
                const label = document.querySelector(`.tecnico-item-label[data-tecnico-id="${checkboxesMarcados[0].value}"]`);
                if (label) {
                    label.querySelector('.responsavel-badge').classList.remove('hidden');
                }
            } else {
                // Se tem mais de 1, mostra o select para escolher
                responsavelContainer.classList.remove('hidden');
                
                // Se não tinha responsável definido, seleciona o primeiro
                const responsavelAtual = responsavelSelect.value;
                if (!responsavelAtual && checkboxesMarcados.length > 0) {
                    responsavelSelect.value = checkboxesMarcados[0].value;
                }
                
                atualizarBadgeResponsavel();
            }
        };

        // Função para atualizar o badge de responsável
        window.atualizarBadgeResponsavel = function() {
            const responsavelId = document.getElementById('responsavel-select').value;
            
            // Esconde todos os badges
            document.querySelectorAll('.responsavel-badge').forEach(badge => {
                badge.classList.add('hidden');
            });
            
            // Mostra badge no responsável selecionado
            if (responsavelId) {
                const label = document.querySelector(`.tecnico-item-label[data-tecnico-id="${responsavelId}"]`);
                if (label) {
                    label.querySelector('.responsavel-badge').classList.remove('hidden');
                }
            }
        };

        window.fecharModalTecnicosAtividade = function() {
            document.getElementById('modal-tecnicos-atividade').classList.add('hidden');
            document.body.style.overflow = '';
            atividadeAtualModal = null;
        };

        window.confirmarTecnicosAtividade = function() {
            if (!atividadeAtualModal) return;
            
            const checkboxesMarcados = Array.from(document.querySelectorAll('.tecnico-checkbox:checked'));
            
            if (checkboxesMarcados.length === 0) {
                alert('Selecione pelo menos um técnico.');
                return;
            }
            
            const responsavelId = document.getElementById('responsavel-select').value;
            if (!responsavelId) {
                alert('Selecione um técnico responsável.');
                return;
            }
            
            const tecnicosIds = checkboxesMarcados.map(cb => parseInt(cb.value));
            
            // Salva na estrutura (preserva estabelecimento_id existente)
            const existente = atividadesTecnicos[atividadeAtualModal] || {};
            atividadesTecnicos[atividadeAtualModal] = {
                responsavel: parseInt(responsavelId),
                tecnicos: tecnicosIds,
                estabelecimento_id: existente.estabelecimento_id || null
            };
            
            // Atualiza interface
            atualizarInterfaceTecnicos();
            fecharModalTecnicosAtividade();
        };

        // Função para atualizar os hidden inputs da estrutura de técnicos
        function atualizarHiddenInputsTecnicos() {
            const container = document.getElementById('atividades-tecnicos-hidden-inputs');
            container.innerHTML = '';
            
            // Cria a estrutura atividades_tecnicos
            const estrutura = atividadesSelecionadas.map(atividade => {
                const tecnicosAtribuidos = atividadesTecnicos[atividade.id];
                if (!tecnicosAtribuidos || !tecnicosAtribuidos.responsavel) {
                    return null; // Pula atividades sem técnicos atribuídos
                }
                
                return {
                    tipo_acao_id: parseInt(atividade.tipo_acao_id || atividade.id),
                    sub_acao_id: atividade.sub_acao_id ? parseInt(atividade.sub_acao_id) : null,
                    nome_atividade: atividade.nome, // Nome que será exibido para o técnico
                    tecnicos: tecnicosAtribuidos.tecnicos,
                    responsavel_id: tecnicosAtribuidos.responsavel,
                    estabelecimento_id: tecnicosAtribuidos.estabelecimento_id || null,
                    status: 'pendente'
                };
            }).filter(item => item !== null);
            
            // Cria hidden input com JSON
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'atividades_tecnicos';
            input.value = JSON.stringify(estrutura);
            container.appendChild(input);
        }

        // Funções de Filtro/Pesquisa - Pesquisa em ações E subações
        window.filtrarTiposAcao = function() {
            const termo = document.getElementById('pesquisa-tipos-acao').value.toLowerCase().trim();
            const items = document.querySelectorAll('.tipo-acao-item');
            let encontrados = 0;
            
            items.forEach(item => {
                const nomeAcao = item.dataset.nome || '';
                const subacoes = item.dataset.subacoes || '';
                
                // Pesquisa no nome da ação OU nas subações
                const matchAcao = nomeAcao.includes(termo);
                const matchSubacao = subacoes.includes(termo);
                
                if (matchAcao || matchSubacao) {
                    item.style.display = 'block';
                    encontrados++;
                    
                    // Se pesquisou e encontrou em subação, destaca as subações que correspondem
                    if (termo && matchSubacao && !matchAcao) {
                        const subacaoLabels = item.querySelectorAll('.pl-4 label');
                        subacaoLabels.forEach(label => {
                            const textoSubacao = label.textContent.toLowerCase();
                            if (textoSubacao.includes(termo)) {
                                label.classList.add('bg-yellow-50', 'ring-1', 'ring-yellow-300');
                            } else {
                                label.classList.remove('bg-yellow-50', 'ring-1', 'ring-yellow-300');
                            }
                        });
                    } else {
                        // Remove destaque se não está pesquisando
                        const subacaoLabels = item.querySelectorAll('.pl-4 label');
                        subacaoLabels.forEach(label => {
                            label.classList.remove('bg-yellow-50', 'ring-1', 'ring-yellow-300');
                        });
                    }
                } else {
                    item.style.display = 'none';
                }
            });
            
            document.getElementById('sem-resultados-tipos').classList.toggle('hidden', encontrados > 0);
        };

        // Limpar pesquisa ao abrir modal
        const originalAbrirTiposAcao = window.abrirModalTiposAcao;
        window.abrirModalTiposAcao = function() {
            document.getElementById('pesquisa-tipos-acao').value = '';
            filtrarTiposAcao();
            originalAbrirTiposAcao();
        };

        // Inicializar com valores old() se existirem
        confirmarTiposAcao();

        document.querySelector('form').addEventListener('submit', function(e) {
            // Validação explícita de período
            const dataInicio = document.getElementById('data_inicio');
            const dataFim = document.getElementById('data_fim');
            if (!dataInicio?.value) {
                e.preventDefault();
                alert('Informe a Data de Início da Ordem de Serviço.');
                dataInicio?.focus();
                dataInicio?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            if (!dataFim?.value) {
                e.preventDefault();
                alert('Informe a Data de Término da Ordem de Serviço.');
                dataFim?.focus();
                dataFim?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Validação explícita de tipos de ação
            if (atividadesSelecionadas.length === 0) {
                e.preventDefault();
                alert('Selecione pelo menos um Tipo de Ação para criar a Ordem de Serviço.');
                document.getElementById('tipos-acao-display')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Validação: cada atividade deve ter pelo menos um técnico atribuído
            let atividadesSemTecnico = [];
            atividadesSelecionadas.forEach(atividade => {
                const tecnicosAtribuidos = atividadesTecnicos[atividade.id];
                if (!tecnicosAtribuidos || !tecnicosAtribuidos.tecnicos || tecnicosAtribuidos.tecnicos.length === 0) {
                    atividadesSemTecnico.push(atividade.nome);
                }
            });
            if (atividadesSemTecnico.length > 0) {
                e.preventDefault();
                alert('Atribua pelo menos um técnico para cada atividade:\n\n' + atividadesSemTecnico.join('\n'));
                document.getElementById('tipos-acao-display')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Validação: pelo menos 1 estabelecimento selecionado quando "com estabelecimento"
            if (comEstabelecimentoRadio.checked && estabelecimentosSelecionados.length === 0) {
                e.preventDefault();
                alert('Adicione pelo menos um estabelecimento à Ordem de Serviço.');
                return;
            }

            // Validação de processo por estabelecimento (obrigatório para todos)
            if (comEstabelecimentoRadio.checked && estabelecimentosSelecionados.length > 0) {
                let confirmacoesPendentes = [];
                let estabelecimentosSemProcesso = [];
                estabelecimentosSelecionados.forEach(est => {
                    const hiddenInput = document.getElementById(`processo-hidden-est-${est.id}`);
                    const continuarSemProcesso = document.getElementById(`continuar-sem-processo-est-${est.id}`)?.value === '1';
                    const processoSelect = document.getElementById(`processo-est-${est.id}`);
                    const semProcessosDisponiveis = !!processoSelect && processoSelect.disabled;

                    if ((!hiddenInput || !hiddenInput.value) && semProcessosDisponiveis && !continuarSemProcesso) {
                        confirmacoesPendentes.push(est.id);
                    } else if ((!hiddenInput || !hiddenInput.value) && !continuarSemProcesso) {
                        estabelecimentosSemProcesso.push(est.nome);
                    }
                });

                for (const estId of confirmacoesPendentes) {
                    if (!confirmarContinuarSemProcesso(estId)) {
                        e.preventDefault();
                        return;
                    }
                }

                if (estabelecimentosSemProcesso.length > 0) {
                    e.preventDefault();
                    alert('Revise os estabelecimentos abaixo:\n\n' + estabelecimentosSemProcesso.join('\n') + '\n\nSelecione um processo quando houver processo disponível.');
                    return;
                }
            }
            
        });

        // Pré-seleção de estabelecimento e processo (quando vindo de um processo)
        @if(isset($estabelecimentoPreSelecionado) && $estabelecimentoPreSelecionado)
        (function() {
            // Garante que está com estabelecimento selecionado
            comEstabelecimentoRadio.checked = true;
            toggleEstabelecimentoField();
            
            // Adiciona o estabelecimento pré-selecionado à lista
            estabelecimentosSelecionados.push({
                id: {{ $estabelecimentoPreSelecionado->id }},
                text: '{{ $estabelecimentoPreSelecionado->cnpj }} - {{ $estabelecimentoPreSelecionado->nome_fantasia }}',
                cnpj: '{{ $estabelecimentoPreSelecionado->cnpj }}',
                nome: '{{ $estabelecimentoPreSelecionado->nome_fantasia }}',
                processo_id: @json($processoPreSelecionado?->id),
                continuar_sem_processo: false
            });
            atualizarListaEstabelecimentos();
        })();
        @endif
    });
</script>
@endpush
