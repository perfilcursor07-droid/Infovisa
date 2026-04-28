@extends('layouts.admin')

@section('title', 'Editar Ordem de Serviço')

@section('content')
<div class="max-w-8xl mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-4 mb-3">
            <a href="{{ route('admin.ordens-servico.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border-2 border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-blue-300 transition-all shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Editar Ordem de Serviço</h1>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $ordemServico->numero }} - Atualize as informações</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulário --}}
    <form method="POST" action="{{ route('admin.ordens-servico.update', $ordemServico) }}" class="space-y-5">
            @csrf
            @method('PUT')

        @if($somentVincularEstabelecimento)
        {{-- Aviso para Técnico Estadual --}}
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-blue-800">
                <p class="font-semibold">Acesso restrito — Vincular Estabelecimento</p>
                <p class="mt-1">Como Técnico Estadual, você pode apenas vincular ou alterar o estabelecimento desta Ordem de Serviço. Os demais campos são gerenciados pelo gestor responsável.</p>
            </div>
        </div>
        @endif

        {{-- Card: Dados Principais --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-3.5 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <h2 class="text-base font-semibold text-gray-900">Dados Principais</h2>
                </div>
            </div>
            <div class="p-5">
                
                @if(!$ordemServico->estabelecimento_id && $ordemServico->estabelecimentos->isEmpty())
                <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-amber-800">
                            <p class="font-medium">Esta OS não possui estabelecimento vinculado</p>
                            <p class="mt-1">Você pode vincular um ou mais estabelecimentos agora.</p>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Estabelecimentos (Múltiplos) --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Estabelecimentos <span class="text-gray-500">(Opcional)</span>
                        </label>
                        <div class="mb-2">
                            <select id="estabelecimento_busca_edit" class="w-full">
                                <option value="">Buscar estabelecimento...</option>
                            </select>
                        </div>
                        @error('estabelecimentos_ids')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-600 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Ao selecionar, o estabelecimento é vinculado automaticamente. Busque por <strong>CNPJ</strong>, <strong>CPF</strong>, <strong>Nome Fantasia</strong> ou <strong>Razão Social</strong>. Você pode adicionar vários.
                        </p>

                        {{-- Lista de estabelecimentos adicionados --}}
                        <div id="estabelecimentos-adicionados-edit" class="mt-3">
                            <div id="estabelecimentos-lista-edit" class="space-y-2"></div>
                            <div id="estabelecimentos-hidden-inputs-edit"></div>
                        </div>

                        {{-- Aviso de múltiplos estabelecimentos --}}
                        <div id="aviso-multiplos-estabelecimentos-edit" class="hidden mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium">Vários estabelecimentos selecionados</p>
                                    <p class="mt-1">Nas ações abaixo, você poderá vincular cada ação a um estabelecimento específico.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden input para processo_id (compatibilidade) --}}
                    <input type="hidden" name="processo_id" id="processo_id" value="{{ $ordemServico->processo_id }}">

                    @if(!$somentVincularEstabelecimento && isset($pastasProcesso) && $pastasProcesso->isNotEmpty())
                    <div class="md:col-span-2">
                        <label for="pasta_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Pasta do Processo <span class="text-gray-500">(Opcional)</span>
                        </label>
                        <select name="pasta_id" id="pasta_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos (sem pasta)</option>
                            @foreach($pastasProcesso as $pasta)
                                <option value="{{ $pasta->id }}" {{ (string) old('pasta_id', $ordemServico->pasta_id) === (string) $pasta->id ? 'selected' : '' }}>
                                    {{ $pasta->nome }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-600">Use esta opção para mover a OS para uma pasta específica do processo.</p>
                    </div>
                    @endif

                    @if(!$somentVincularEstabelecimento)
                    {{-- Tipos de Ação (Múltiplos) --}}
                    <div class="md:col-span-2">
                        <label for="tipos_acao_ids" class="block text-sm font-medium text-gray-700 mb-1">
                            Tipos de Ação <span class="text-red-500">*</span>
                        </label>
                        
                        {{-- Campo visual para abrir modal --}}
                        <div id="tipos-acao-selecionados-edit" 
                             onclick="abrirModalTiposAcaoEdit()"
                             class="w-full min-h-[42px] px-3 py-2 border border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition-all">
                            <div id="tipos-acao-placeholder-edit" class="text-gray-400 text-sm">
                                Clique para selecionar tipos de ação...
                            </div>
                            <div id="tipos-acao-badges-edit" class="flex flex-wrap gap-2 hidden">
                                <!-- Badges serão inseridas via JavaScript -->
                            </div>
                        </div>
                        
                        {{-- Hidden inputs para enviar os IDs --}}
                        <div id="tipos-acao-hidden-inputs-edit"></div>
                        
                        @error('tipos_acao_ids')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Atribuição de Técnicos por Atividade --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Atribuição de Técnicos por Atividade <span class="text-red-500">*</span>
                        </label>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium">Nova estrutura de atribuição</p>
                                    <p class="mt-1">Cada atividade possui seus próprios técnicos atribuídos com um responsável designado.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div id="atividades-tecnicos-container-edit" class="space-y-4">
                            <!-- Será preenchido via JavaScript -->
                        </div>
                        
                        {{-- Hidden inputs para enviar a estrutura --}}
                        <div id="atividades-tecnicos-hidden-inputs-edit"></div>
                        @error('atividades_tecnicos')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Data de Início --}}
                    <div>
                        <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                            Data de Início <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="data_inicio" 
                               name="data_inicio" 
                               value="{{ old('data_inicio', $ordemServico->data_inicio?->format('Y-m-d')) }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('data_inicio') border-red-500 @enderror">
                        @error('data_inicio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Data Fim --}}
                    <div>
                        <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-1">
                            Data de Término <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="data_fim" 
                               name="data_fim" 
                               value="{{ old('data_fim', $ordemServico->data_fim?->format('Y-m-d')) }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('data_fim') border-red-500 @enderror">
                        @error('data_fim')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Observação sobre datas --}}
                    <div class="md:col-span-2">
                        <p class="text-xs text-gray-600 flex items-center gap-1">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            A data de término não pode ser anterior à data de início. Datas retroativas são permitidas.
                        </p>
                    </div>

                    {{-- Observações --}}
                    <div class="md:col-span-2">
                        <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">
                            Observações
                        </label>
                        <textarea id="observacoes" 
                                  name="observacoes" 
                                  rows="3"
                                  placeholder="Observações adicionais..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('observacoes') border-red-500 @enderror">{{ old('observacoes', $ordemServico->observacoes) }}</textarea>
                        @error('observacoes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif {{-- fim @if(!$somentVincularEstabelecimento) --}}
                </div>
            </div>

            {{-- Botões --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.ordens-servico.show', $ordemServico) }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                    Voltar
                </a>
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition">
                    {{ $somentVincularEstabelecimento ? 'Vincular Estabelecimento' : 'Atualizar Ordem de Serviço' }}
                </button>
            </div>
        </div>
    </form>

    {{-- Modal Tipos de Ação com SubAções --}}
    <div id="modal-tipos-acao-edit" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="fecharModalTiposAcaoEdit()"></div>
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
                    <button type="button" onclick="fecharModalTiposAcaoEdit()" class="text-white/70 hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                {{-- Campo de Pesquisa --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-b from-gray-50 to-white">
                    <div class="relative group">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" id="pesquisa-tipos-acao-edit" placeholder="Pesquise por ação ou subação..." 
                               class="w-full pl-12 pr-4 py-3 text-sm border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white hover:border-gray-300 transition-all"
                               onkeyup="filtrarTiposAcaoEdit()">
                    </div>
                    <p class="text-xs text-gray-500 mt-2 ml-1">💡 Dica: Digite o nome da ação ou subação para filtrar</p>
                </div>
                
                {{-- Lista de Tipos de Ação --}}
                <div class="px-6 py-4 max-h-[60vh] overflow-y-auto" id="lista-tipos-acao-edit">
                    <div class="space-y-3">
                        @foreach($tiposAcao as $tipoAcao)
                        @php
                            $subAcoesTexto = $tipoAcao->subAcoesAtivas->pluck('descricao')->map(fn($d) => strtolower($d))->implode(' ');
                        @endphp
                        <div class="tipo-acao-item-edit bg-gradient-to-r from-gray-50 to-white rounded-xl border-2 border-gray-200 hover:border-blue-400 hover:shadow-md transition-all" 
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
                                            <input type="checkbox" class="tipo-acao-checkbox-edit rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4" 
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
                                            <input type="checkbox" class="tipo-acao-checkbox-edit rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4" 
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
                                    <input type="checkbox" class="tipo-acao-checkbox-edit rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 ml-3" 
                                           value="{{ $tipoAcao->id }}" data-label="{{ $tipoAcao->descricao }}">
                                    <span class="ml-3 text-sm text-gray-700 group-hover:text-blue-700 transition-colors font-medium">{{ $tipoAcao->descricao }}</span>
                                </label>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <p id="sem-resultados-tipos-edit" class="hidden text-center text-gray-500 py-8">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Nenhum tipo de ação encontrado
                    </p>
                </div>
                
                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-white flex justify-end gap-3">
                    <button type="button" onclick="fecharModalTiposAcaoEdit()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all">
                        Cancelar
                    </button>
                    <button type="button" onclick="confirmarTiposAcaoEdit()" class="px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-sm hover:shadow-md transition-all flex items-center gap-2">
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
    <div id="modal-tecnicos-atividade-edit" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="fecharModalTecnicosAtividadeEdit()"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-2xl sm:w-full mx-auto overflow-hidden">
                {{-- Header com Gradient --}}
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-green-600 to-green-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white" id="modal-atividade-titulo-edit">Atribuir Técnicos</h3>
                    </div>
                    <button type="button" onclick="fecharModalTecnicosAtividadeEdit()" class="text-white/70 hover:text-white transition-colors p-2 hover:bg-white/10 rounded-lg">
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
                            <input type="text" id="busca-tecnicos-edit" 
                                   placeholder="Buscar técnico por nome..." 
                                   class="w-full pl-10 pr-4 py-2.5 text-sm border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white"
                                   oninput="filtrarTecnicosEdit(this.value)">
                        </div>
                        
                        <div id="lista-tecnicos-container-edit" class="max-h-64 overflow-y-auto border-2 border-gray-200 rounded-xl bg-gradient-to-b from-gray-50 to-white">
                            @foreach($tecnicos as $tecnico)
                            <label class="flex items-center p-4 hover:bg-green-50 cursor-pointer border-b border-gray-100 last:border-b-0 tecnico-item-label-edit transition-colors group" data-tecnico-id="{{ $tecnico->id }}" data-tecnico-nome="{{ strtolower($tecnico->nome) }}">
                                <input type="checkbox" class="tecnico-checkbox-edit rounded border-gray-300 text-green-600 focus:ring-green-500 w-5 h-5" 
                                       value="{{ $tecnico->id }}" data-nome="{{ $tecnico->nome }}"
                                       onchange="atualizarResponsavelAutomaticoEdit()">
                                <span class="ml-3 text-sm text-gray-700 group-hover:text-green-700 transition-colors flex-1 font-medium">{{ $tecnico->nome }}</span>
                                <span class="responsavel-badge-edit hidden ml-2 px-3 py-1 text-xs font-bold bg-green-100 text-green-700 rounded-full flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></path></svg>
                                    Responsável
                                </span>
                            </label>
                            @endforeach
                        </div>
                        <p id="nenhum-tecnico-encontrado-edit" class="hidden text-sm text-gray-500 text-center py-4">Nenhum técnico encontrado.</p>
                    </div>
                    
                    {{-- Seleção do Responsável (aparece quando há mais de 1 técnico) --}}
                    <div id="responsavel-container-edit" class="hidden mb-5">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-3">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Técnico Responsável <span class="text-red-500">*</span>
                        </label>
                        <select id="responsavel-select-edit" class="w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white hover:border-gray-300 transition-all"
                                onchange="atualizarBadgeResponsavelEdit()">
                            <option value="">Selecione o responsável...</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-2 ml-1">Escolha quem será o técnico responsável principal por esta atividade.</p>
                    </div>
                </div>
                
                {{-- Footer com Botões --}}
                <div class="px-6 py-4 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-white flex justify-end gap-3">
                    <button type="button" onclick="fecharModalTecnicosAtividadeEdit()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all">
                        Cancelar
                    </button>
                    <button type="button" onclick="confirmarTecnicosAtividadeEdit()" class="px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-700 rounded-lg hover:from-green-700 hover:to-green-800 shadow-sm hover:shadow-md transition-all flex items-center gap-2">
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
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Customização do Select2 */
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px;
        padding-left: 0;
        color: #111827;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 8px;
    }
    
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .select2-dropdown {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem;
    }
    
    .select2-results__option {
        padding: 0.75rem 1rem;
    }
    
    .select2-results__option--highlighted {
        background-color: #3b82f6 !important;
    }
    
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #eff6ff;
        color: #1e40af;
    }
    
    .select2-container {
        width: 100% !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ==========================================
        // Múltiplos Estabelecimentos - Edit
        // ==========================================
        let estabelecimentosSelecionadosEdit = []; // [{id, text, cnpj, nome, processo_id, continuar_sem_processo}]

        // Inicializa Select2 para busca de Estabelecimento
        $('#estabelecimento_busca_edit').select2({
            placeholder: 'Digite para buscar estabelecimento...',
            allowClear: true,
            language: {
                inputTooShort: function() {
                    return 'Digite pelo menos 2 caracteres para buscar';
                },
                searching: function() {
                    return 'Buscando...';
                },
                noResults: function() {
                    return 'Nenhum estabelecimento encontrado';
                },
                errorLoading: function() {
                    return 'Erro ao carregar resultados';
                }
            },
            ajax: {
                url: '{{ route("admin.ordens-servico.api.buscar-estabelecimentos") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            templateResult: function(estabelecimento) {
                if (estabelecimento.loading) {
                    return estabelecimento.text;
                }
                return $('<span>' + estabelecimento.text + '</span>');
            },
            templateSelection: function(estabelecimento) {
                return estabelecimento.text;
            }
        });

        $('#estabelecimento_busca_edit').on('select2:select', function() {
            adicionarEstabelecimentoEdit();
        });

        // Adicionar estabelecimento à lista
        window.adicionarEstabelecimentoEdit = function() {
            const select = $('#estabelecimento_busca_edit');
            const data = select.select2('data')[0];
            
            if (!data || !data.id) {
                alert('Selecione um estabelecimento para adicionar.');
                return;
            }

            // Evita duplicatas
            if (estabelecimentosSelecionadosEdit.find(e => e.id == data.id)) {
                alert('Este estabelecimento já foi adicionado.');
                return;
            }

            // Extrai CNPJ e nome do text
            const partes = data.text.split(' - ');
            const cnpj = partes[0] || '';
            const nome = partes.slice(1).join(' - ') || data.text;

            estabelecimentosSelecionadosEdit.push({
                id: parseInt(data.id),
                text: data.text,
                cnpj: cnpj.trim(),
                nome: nome.trim(),
                processo_id: null,
                continuar_sem_processo: false
            });

            // Limpa select
            select.val(null).trigger('change');

            atualizarListaEstabelecimentosEdit();
            atualizarInterfaceTecnicosEdit();
        };

        // Remover estabelecimento da lista
        window.removerEstabelecimentoDaListaEdit = function(id) {
            estabelecimentosSelecionadosEdit = estabelecimentosSelecionadosEdit.filter(e => e.id != id);
            atualizarListaEstabelecimentosEdit();
            atualizarInterfaceTecnicosEdit();
        };

        window.definirContinuarSemProcessoEdit = function(estId, continuar) {
            const estabelecimento = estabelecimentosSelecionadosEdit.find(e => e.id == estId);
            if (!estabelecimento) {
                return;
            }

            estabelecimento.continuar_sem_processo = !!continuar;

            const hiddenInput = document.getElementById(`continuar-sem-processo-est-edit-${estId}`);
            if (hiddenInput) {
                hiddenInput.value = continuar ? '1' : '0';
            }
        };

        window.confirmarContinuarSemProcessoEdit = function(estId) {
            const confirmou = window.confirm('Este estabelecimento não possui processos abertos. Deseja continuar e cadastrar a OS sem processo?');
            definirContinuarSemProcessoEdit(estId, confirmou);
            return confirmou;
        };

        // Atualizar lista visual e hidden inputs
        function atualizarListaEstabelecimentosEdit() {
            const listaContainer = document.getElementById('estabelecimentos-lista-edit');
            const hiddenContainer = document.getElementById('estabelecimentos-hidden-inputs-edit');
            const avisoMultiplos = document.getElementById('aviso-multiplos-estabelecimentos-edit');
            
            listaContainer.innerHTML = '';
            hiddenContainer.innerHTML = '';

            if (estabelecimentosSelecionadosEdit.length === 0) {
                listaContainer.innerHTML = '<p class="text-gray-400 text-sm italic">Nenhum estabelecimento adicionado</p>';
                avisoMultiplos.classList.add('hidden');
                return;
            }

            estabelecimentosSelecionadosEdit.forEach((est, index) => {
                // Card visual com processo dropdown
                const card = document.createElement('div');
                card.className = 'bg-white border border-gray-200 rounded-xl hover:border-blue-400 hover:shadow-md transition-all duration-200 overflow-hidden';
                card.style.borderLeft = '4px solid #3b82f6';
                card.id = `estab-card-edit-${est.id}`;
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
                        <button type="button" onclick="removerEstabelecimentoDaListaEdit(${est.id})"
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
                            <select id="processo-est-edit-${est.id}" onchange="selecionarProcessoEstabelecimentoEdit(${est.id}, this.value)"
                                    class="w-full text-sm border-blue-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm">
                                <option value="">Carregando processos...</option>
                            </select>
                            <div id="processo-alerta-edit-${est.id}" class="hidden mt-2 text-xs text-blue-800 bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <p id="processo-alerta-titulo-edit-${est.id}" class="font-semibold">Nenhum processo vinculado</p>
                                <p id="processo-alerta-texto-edit-${est.id}">Este estabelecimento possui processos disponíveis. Selecione um processo acima para vincular à OS.</p>
                            </div>
                        </div>
                    </div>
                `;
                listaContainer.appendChild(card);

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
                inputProcesso.id = `processo-hidden-est-edit-${est.id}`;
                inputProcesso.value = est.processo_id || '';
                hiddenContainer.appendChild(inputProcesso);

                const inputContinuarSemProcesso = document.createElement('input');
                inputContinuarSemProcesso.type = 'hidden';
                inputContinuarSemProcesso.name = `continuar_sem_processo_estabelecimentos[${est.id}]`;
                inputContinuarSemProcesso.id = `continuar-sem-processo-est-edit-${est.id}`;
                inputContinuarSemProcesso.value = est.continuar_sem_processo ? '1' : '0';
                hiddenContainer.appendChild(inputContinuarSemProcesso);

                // Carrega processos para este estabelecimento
                carregarProcessosEstabelecimentoEdit(est.id, est.processo_id || null);
            });

            // Também envia o primeiro como estabelecimento_id para compatibilidade
            let inputPrincipal = document.getElementById('estabelecimento_id_hidden_edit');
            if (!inputPrincipal) {
                inputPrincipal = document.createElement('input');
                inputPrincipal.type = 'hidden';
                inputPrincipal.name = 'estabelecimento_id';
                inputPrincipal.id = 'estabelecimento_id_hidden_edit';
                hiddenContainer.appendChild(inputPrincipal);
            }
            inputPrincipal.value = estabelecimentosSelecionadosEdit[0].id;

            // Aviso múltiplos
            if (estabelecimentosSelecionadosEdit.length > 1) {
                avisoMultiplos.classList.remove('hidden');
            } else {
                avisoMultiplos.classList.add('hidden');
            }
        }

        // Selecionar processo para um estabelecimento específico (edit)
        window.selecionarProcessoEstabelecimentoEdit = function(estId, processoId) {
            const hiddenInput = document.getElementById(`processo-hidden-est-edit-${estId}`);
            if (hiddenInput) {
                hiddenInput.value = processoId;
            }
            // Atualiza no array
            const est = estabelecimentosSelecionadosEdit.find(e => e.id == estId);
            if (est) {
                est.processo_id = processoId;
                est.continuar_sem_processo = false;
                const continuarInput = document.getElementById(`continuar-sem-processo-est-edit-${estId}`);
                if (continuarInput) {
                    continuarInput.value = '0';
                }
            }
            // Atualiza processo_id principal (compatibilidade) = processo do primeiro estabelecimento
            if (estabelecimentosSelecionadosEdit.length > 0 && estabelecimentosSelecionadosEdit[0].id == estId) {
                document.getElementById('processo_id').value = processoId;
            }
        }

        // Carrega processos de um estabelecimento específico (edit)
        function carregarProcessosEstabelecimentoEdit(estId, processoIdPreSelecionado) {
            const processoSelect = document.getElementById(`processo-est-edit-${estId}`);
            const alertaDiv = document.getElementById(`processo-alerta-edit-${estId}`);
            
            if (!processoSelect) return;

            processoSelect.innerHTML = '<option value="">Carregando...</option>';
            processoSelect.disabled = true;
            if (alertaDiv) alertaDiv.classList.add('hidden');

            let url = `${window.APP_URL}/admin/ordens-servico/estabelecimento/${estId}/processos`;
            if (processoIdPreSelecionado) {
                url += `?processo_atual_id=${processoIdPreSelecionado}`;
            }

            fetch(url)
                .then(r => r.json())
                .then(data => {
                    if(data.processos && data.processos.length > 0) {
                        processoSelect.innerHTML = '<option value="">Selecione um processo</option>';
                        data.processos.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.id;
                            opt.textContent = p.texto_completo || `${p.numero} - ${p.tipo}`;
                            if (processoIdPreSelecionado && p.id == processoIdPreSelecionado) {
                                opt.selected = true;
                            }
                            processoSelect.appendChild(opt);
                        });
                        processoSelect.disabled = false;
                        if (alertaDiv) {
                            if (processoSelect.value) {
                                alertaDiv.classList.add('hidden');
                            } else {
                                alertaDiv.classList.remove('hidden');
                                const titulo = document.getElementById(`processo-alerta-titulo-edit-${estId}`);
                                const texto = document.getElementById(`processo-alerta-texto-edit-${estId}`);
                                if (titulo) titulo.textContent = 'Nenhum processo vinculado';
                                if (texto) texto.textContent = 'Este estabelecimento possui processos disponíveis. Selecione um processo acima para vincular à OS.';
                            }
                        }
                        
                        // Se tinha pré-seleção e deu match, atualiza hidden
                        if (processoIdPreSelecionado && processoSelect.value) {
                            selecionarProcessoEstabelecimentoEdit(estId, processoSelect.value);
                        }
                        // Se só tem 1 processo, auto-seleciona
                        if (data.processos.length === 1) {
                            processoSelect.value = data.processos[0].id;
                            selecionarProcessoEstabelecimentoEdit(estId, data.processos[0].id);
                            if (alertaDiv) alertaDiv.classList.add('hidden');
                        }
                    } else {
                        processoSelect.innerHTML = '<option value="">Sem processos abertos</option>';
                        processoSelect.disabled = true;
                        selecionarProcessoEstabelecimentoEdit(estId, '');
                        if (alertaDiv) {
                            alertaDiv.classList.add('hidden');
                        }

                        const estabelecimento = estabelecimentosSelecionadosEdit.find(e => e.id == estId);
                        if (estabelecimento && !estabelecimento.popupSemProcessoExibido && !estabelecimento.continuar_sem_processo) {
                            estabelecimento.popupSemProcessoExibido = true;
                            confirmarContinuarSemProcessoEdit(estId);
                        }
                    }
                })
                .catch(() => {
                    processoSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                    processoSelect.disabled = true;
                });
        }

        // Sincroniza mínimo da data fim com data início
        const dataInicioInput = document.getElementById('data_inicio');
        const dataFimInput = document.getElementById('data_fim');
        
        if (dataInicioInput && dataFimInput) {
            dataInicioInput.addEventListener('change', function() {
                const dataInicio = this.value;
                if (dataInicio) {
                    dataFimInput.min = dataInicio;
                    // Se data fim for menor que data início, limpa
                    if (dataFimInput.value && dataFimInput.value < dataInicio) {
                        dataFimInput.value = dataInicio;
                    }
                }
            });
        }

        // Variáveis globais para controle da nova estrutura
        let atividadesSelecionadasEdit = [];
        let atividadesTecnicosEdit = {}; // Chave: índice único da atividade
        let atividadeAtualModalEdit = null;

        // Carrega dados existentes da OS
        const osAtividadesTecnicos = @json($ordemServico->atividades_tecnicos ?? []);
        const osTiposAcaoIds = @json($ordemServico->tipos_acao_ids ?? []);
        const tiposAcaoDisponiveis = @json($tiposAcao->keyBy('id')->toArray());
        
        // Mapa de técnicos disponíveis para busca de nomes
        const tecnicosDisponiveis = @json($tecnicos->keyBy('id')->map(fn($t) => $t->nome)->toArray());

        // ========================================
        // Funções do Modal de Tipos de Ação
        // ========================================
        
        window.abrirModalTiposAcaoEdit = function() {
            document.getElementById('modal-tipos-acao-edit').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        window.fecharModalTiposAcaoEdit = function() {
            document.getElementById('modal-tipos-acao-edit').classList.add('hidden');
            document.body.style.overflow = '';
        };

        window.filtrarTiposAcaoEdit = function() {
            const termo = document.getElementById('pesquisa-tipos-acao-edit').value.toLowerCase().trim();
            const items = document.querySelectorAll('.tipo-acao-item-edit');
            let encontrados = 0;
            
            items.forEach(item => {
                const nomeAcao = item.dataset.nome || '';
                const subacoes = item.dataset.subacoes || '';
                
                const matchAcao = nomeAcao.includes(termo);
                const matchSubacao = subacoes.includes(termo);
                
                if (matchAcao || matchSubacao) {
                    item.style.display = '';
                    encontrados++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            document.getElementById('sem-resultados-tipos-edit').classList.toggle('hidden', encontrados > 0);
        };

        window.confirmarTiposAcaoEdit = function() {
            const checkboxes = document.querySelectorAll('.tipo-acao-checkbox-edit:checked');
            
            // Guarda mapa de técnicos existentes por tipo_acao + subacao
            const tecnicosExistentes = {};
            atividadesSelecionadasEdit.forEach(a => {
                if (a.uniqueKey && atividadesTecnicosEdit[a.uniqueKey]) {
                    const key = `${a.id}_${a.subAcaoId || 'main'}`;
                    tecnicosExistentes[key] = atividadesTecnicosEdit[a.uniqueKey];
                }
            });
            
            // Limpa seleções anteriores
            atividadesSelecionadasEdit = [];
            atividadesTecnicosEdit = {};
            
            // Processa cada checkbox marcado
            let index = 0;
            checkboxes.forEach(cb => {
                const tipoAcaoId = parseInt(cb.value);
                const subAcaoId = cb.dataset.subAcaoId;
                const isAcaoPrincipal = cb.dataset.isAcaoPrincipal === 'true';
                const label = cb.dataset.subAcaoLabel || cb.dataset.label;
                
                const uniqueKey = `${tipoAcaoId}_${subAcaoId || 'main'}_${index}`;
                const lookupKey = `${tipoAcaoId}_${subAcaoId || 'main'}`;
                
                atividadesSelecionadasEdit.push({
                    id: tipoAcaoId,
                    nome: label,
                    subAcaoId: subAcaoId ? parseInt(subAcaoId) : null,
                    isAcaoPrincipal: isAcaoPrincipal || !subAcaoId,
                    uniqueKey: uniqueKey
                });
                
                // Restaura técnicos existentes se houver
                if (tecnicosExistentes[lookupKey]) {
                    atividadesTecnicosEdit[uniqueKey] = tecnicosExistentes[lookupKey];
                }
                
                index++;
            });
            
            atualizarBadgesTiposAcaoEdit();
            atualizarInterfaceTecnicosEdit();
            fecharModalTiposAcaoEdit();
        };

        function atualizarBadgesTiposAcaoEdit() {
            const placeholder = document.getElementById('tipos-acao-placeholder-edit');
            const badgesContainer = document.getElementById('tipos-acao-badges-edit');
            const hiddenInputsContainer = document.getElementById('tipos-acao-hidden-inputs-edit');
            
            // Limpa containers
            badgesContainer.innerHTML = '';
            hiddenInputsContainer.innerHTML = '';
            
            if (atividadesSelecionadasEdit.length === 0) {
                placeholder.classList.remove('hidden');
                badgesContainer.classList.add('hidden');
                return;
            }
            
            placeholder.classList.add('hidden');
            badgesContainer.classList.remove('hidden');
            
            atividadesSelecionadasEdit.forEach(atividade => {
                // Cria badge - cor diferente para ação principal vs subação
                const badge = document.createElement('span');
                const isSubAcao = atividade.subAcaoId && !atividade.isAcaoPrincipal;
                const badgeClass = isSubAcao ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800';
                badge.className = `inline-flex items-center gap-1 px-2 py-1 ${badgeClass} text-xs font-medium rounded-full`;
                badge.innerHTML = `
                    ${atividade.nome}
                    ${atividade.isAcaoPrincipal ? '<span class="text-xs opacity-70">(Principal)</span>' : ''}
                    <button type="button" onclick="removerTipoAcaoEdit(${atividade.id}, ${atividade.subAcaoId || 'null'}, ${atividade.isAcaoPrincipal || false})" class="text-current hover:opacity-70">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                `;
                badgesContainer.appendChild(badge);
                
                // Cria hidden input
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'tipos_acao_ids[]';
                input.value = atividade.id;
                hiddenInputsContainer.appendChild(input);
            });
        }

        window.removerTipoAcaoEdit = function(tipoAcaoId, subAcaoId, isAcaoPrincipal) {
            // Encontra a atividade a ser removida e sua chave única
            const atividadeRemovida = atividadesSelecionadasEdit.find(a => {
                if (isAcaoPrincipal) {
                    return a.id === tipoAcaoId && a.isAcaoPrincipal;
                }
                if (subAcaoId) {
                    return a.id === tipoAcaoId && a.subAcaoId === subAcaoId;
                }
                return a.id === tipoAcaoId;
            });
            
            // Remove técnicos da atividade removida
            if (atividadeRemovida && atividadeRemovida.uniqueKey) {
                delete atividadesTecnicosEdit[atividadeRemovida.uniqueKey];
            }
            
            // Remove da lista
            atividadesSelecionadasEdit = atividadesSelecionadasEdit.filter(a => {
                if (isAcaoPrincipal) {
                    return !(a.id === tipoAcaoId && a.isAcaoPrincipal);
                }
                if (subAcaoId) {
                    return !(a.id === tipoAcaoId && a.subAcaoId === subAcaoId);
                }
                return a.id !== tipoAcaoId;
            });
            
            // Desmarca checkbox
            const checkboxes = document.querySelectorAll(`.tipo-acao-checkbox-edit[value="${tipoAcaoId}"]`);
            checkboxes.forEach(cb => {
                const cbIsAcaoPrincipal = cb.dataset.isAcaoPrincipal === 'true';
                const cbSubAcaoId = cb.dataset.subAcaoId;
                
                if (isAcaoPrincipal && cbIsAcaoPrincipal) {
                    cb.checked = false;
                } else if (subAcaoId && cbSubAcaoId == subAcaoId) {
                    cb.checked = false;
                } else if (!isAcaoPrincipal && !subAcaoId && !cbSubAcaoId && !cbIsAcaoPrincipal) {
                    cb.checked = false;
                }
            });
            
            atualizarBadgesTiposAcaoEdit();
            atualizarInterfaceTecnicosEdit();
        };

        // Inicializa estrutura com dados existentes e marca checkboxes
        function inicializarDadosExistentes() {
            // Carrega estabelecimentos existentes (com processo_id do pivot)
            const estabelecimentosExistentes = @json($ordemServico->estabelecimentos ?? collect());
            if (estabelecimentosExistentes.length > 0) {
                estabelecimentosExistentes.forEach(est => {
                    estabelecimentosSelecionadosEdit.push({
                        id: est.id,
                        text: `${est.cnpj || est.cpf} - ${est.nome_fantasia}`,
                        cnpj: est.cnpj || est.cpf || '',
                        nome: est.nome_fantasia || est.razao_social || '',
                        processo_id: est.pivot ? est.pivot.processo_id : null,
                        continuar_sem_processo: !(est.pivot && est.pivot.processo_id)
                    });
                });
            } else if ({{ $ordemServico->estabelecimento_id ?? 'null' }}) {
                // Fallback: usa o estabelecimento_id legacy
                @if($ordemServico->estabelecimento_id && $ordemServico->estabelecimento)
                estabelecimentosSelecionadosEdit.push({
                    id: {{ $ordemServico->estabelecimento->id }},
                    text: '{{ ($ordemServico->estabelecimento->cnpj ?? $ordemServico->estabelecimento->cpf) }} - {{ $ordemServico->estabelecimento->nome_fantasia }}',
                    cnpj: '{{ $ordemServico->estabelecimento->cnpj ?? $ordemServico->estabelecimento->cpf }}',
                    nome: '{{ $ordemServico->estabelecimento->nome_fantasia }}',
                    processo_id: {{ $ordemServico->processo_id ?? 'null' }},
                    continuar_sem_processo: {{ $ordemServico->processo_id ? 'false' : 'true' }}
                });
                @endif
            }
            atualizarListaEstabelecimentosEdit();

            if (osAtividadesTecnicos && osAtividadesTecnicos.length > 0) {
                // Usa nova estrutura - cada atividade tem seu índice único
                osAtividadesTecnicos.forEach((atividade, index) => {
                    if (tiposAcaoDisponiveis[atividade.tipo_acao_id]) {
                        const tipoAcao = tiposAcaoDisponiveis[atividade.tipo_acao_id];
                        const nomeAtividade = atividade.nome_atividade || tipoAcao.descricao;
                        
                        // Cria chave única para cada atividade
                        const uniqueKey = `${atividade.tipo_acao_id}_${atividade.sub_acao_id || 'main'}_${index}`;
                        
                        atividadesSelecionadasEdit.push({
                            id: atividade.tipo_acao_id,
                            nome: nomeAtividade,
                            subAcaoId: atividade.sub_acao_id || null,
                            isAcaoPrincipal: !atividade.sub_acao_id,
                            uniqueKey: uniqueKey,
                            status: atividade.status || 'pendente'
                        });
                        
                        // Salva técnicos usando a chave única (inclui estabelecimento_id)
                        atividadesTecnicosEdit[uniqueKey] = {
                            responsavel: atividade.responsavel_id,
                            tecnicos: atividade.tecnicos || [],
                            estabelecimento_id: atividade.estabelecimento_id || null
                        };
                        
                        // Marca o checkbox correspondente
                        const checkboxes = document.querySelectorAll(`.tipo-acao-checkbox-edit[value="${atividade.tipo_acao_id}"]`);
                        checkboxes.forEach(cb => {
                            const subAcaoId = cb.dataset.subAcaoId;
                            const isAcaoPrincipal = cb.dataset.isAcaoPrincipal === 'true';
                            
                            if (atividade.sub_acao_id) {
                                if (subAcaoId == atividade.sub_acao_id) {
                                    cb.checked = true;
                                }
                            } else {
                                if (isAcaoPrincipal || !subAcaoId) {
                                    cb.checked = true;
                                }
                            }
                        });
                    }
                });
            } else if (osTiposAcaoIds && osTiposAcaoIds.length > 0) {
                // Migra da estrutura antiga
                const osTecnicosIds = @json($ordemServico->tecnicos_ids ?? []);
                
                osTiposAcaoIds.forEach((tipoAcaoId, index) => {
                    if (tiposAcaoDisponiveis[tipoAcaoId]) {
                        const uniqueKey = `${tipoAcaoId}_main_${index}`;
                        
                        atividadesSelecionadasEdit.push({
                            id: tipoAcaoId,
                            nome: tiposAcaoDisponiveis[tipoAcaoId].descricao,
                            isAcaoPrincipal: true,
                            uniqueKey: uniqueKey
                        });
                        
                        atividadesTecnicosEdit[uniqueKey] = {
                            responsavel: osTecnicosIds.length > 0 ? osTecnicosIds[0] : null,
                            tecnicos: osTecnicosIds || [],
                            estabelecimento_id: null
                        };
                        
                        const checkboxPrincipal = document.querySelector(`.tipo-acao-checkbox-edit[value="${tipoAcaoId}"][data-is-acao-principal="true"]`);
                        const checkboxSimples = document.querySelector(`.tipo-acao-checkbox-edit[value="${tipoAcaoId}"]:not([data-sub-acao-id])`);
                        
                        if (checkboxPrincipal) {
                            checkboxPrincipal.checked = true;
                        } else if (checkboxSimples) {
                            checkboxSimples.checked = true;
                        }
                    }
                });
            }
            
            atualizarBadgesTiposAcaoEdit();
            atualizarInterfaceTecnicosEdit();
        }

        // Inicializa dados existentes
        inicializarDadosExistentes();

        // Função para atualizar a interface de técnicos por atividade
        function atualizarInterfaceTecnicosEdit() {
            const container = document.getElementById('atividades-tecnicos-container-edit');
            
            if (atividadesSelecionadasEdit.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm italic">Selecione os tipos de ação para configurar os técnicos.</p>';
                return;
            }
            
            container.innerHTML = '';
            
            atividadesSelecionadasEdit.forEach((atividade, index) => {
                const atividadeDiv = document.createElement('div');
                atividadeDiv.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50';
                
                // Usa índice único da atividade
                const atividadeKey = atividade.uniqueKey || `${atividade.id}_${atividade.subAcaoId || 'main'}_${index}`;
                const tecnicosAtribuidos = atividadesTecnicosEdit[atividadeKey] || { responsavel: null, tecnicos: [], estabelecimento_id: null };
                
                // Busca nome do responsável no mapa de técnicos
                const responsavelNome = tecnicosAtribuidos.responsavel ? 
                    (tecnicosDisponiveis[tecnicosAtribuidos.responsavel] || 'Técnico não encontrado') : 
                    'Não definido';
                
                // Busca nomes dos técnicos adicionais (excluindo o responsável)
                const tecnicosAdicionaisIds = tecnicosAtribuidos.tecnicos.filter(id => id !== tecnicosAtribuidos.responsavel);
                const tecnicosAdicionais = tecnicosAdicionaisIds.length > 0 ? 
                    tecnicosAdicionaisIds.map(id => tecnicosDisponiveis[id] || 'Técnico não encontrado').join(', ') : 
                    'Nenhum';
                
                // Se tem subação, mostra a subação como título principal; se é ação principal, mostra badge
                let tituloAtividade;
                if (atividade.subAcaoId && !atividade.isAcaoPrincipal) {
                    tituloAtividade = `<span class="text-indigo-600">${atividade.nome}</span>`;
                } else if (atividade.isAcaoPrincipal) {
                    tituloAtividade = `${atividade.nome} <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ml-2">Principal</span>`;
                } else {
                    tituloAtividade = atividade.nome;
                }
                
                // Salva a chave única na atividade para referência
                atividade.uniqueKey = atividadeKey;

                // Dropdown de estabelecimento para a atividade (só se tiver múltiplos)
                let estabelecimentoDropdownHtml = '';
                if (estabelecimentosSelecionadosEdit.length > 1) {
                    const selectedEstId = tecnicosAtribuidos.estabelecimento_id || '';
                    let opcoesEst = '<option value="">-- Todos / Geral --</option>';
                    estabelecimentosSelecionadosEdit.forEach(est => {
                        const sel = (est.id == selectedEstId) ? 'selected' : '';
                        opcoesEst += `<option value="${est.id}" ${sel}>${est.nome} (${est.cnpj})</option>`;
                    });
                    estabelecimentoDropdownHtml = `
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <label class="flex items-center gap-1 text-xs font-semibold text-gray-600 mb-1">
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                Estabelecimento vinculado a esta ação
                            </label>
                            <select onchange="definirEstabelecimentoAtividadeEdit('${atividadeKey}', this.value)" 
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                ${opcoesEst}
                            </select>
                        </div>
                    `;
                }
                
                atividadeDiv.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-900">${tituloAtividade}</h4>
                        <button type="button" onclick="abrirModalTecnicosAtividadeEdit('${atividadeKey}', '${atividade.nome.replace(/'/g, "\\'")}')" 
                                class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full hover:bg-blue-200 transition-colors">
                            ${tecnicosAtribuidos.responsavel ? 'Editar' : 'Atribuir'} Técnicos
                        </button>
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
            atualizarHiddenInputsTecnicosEdit();
        }

        // Define estabelecimento para uma atividade específica (edit)
        window.definirEstabelecimentoAtividadeEdit = function(atividadeKey, estabelecimentoId) {
            if (!atividadesTecnicosEdit[atividadeKey]) {
                atividadesTecnicosEdit[atividadeKey] = { responsavel: null, tecnicos: [], estabelecimento_id: null };
            }
            atividadesTecnicosEdit[atividadeKey].estabelecimento_id = estabelecimentoId ? parseInt(estabelecimentoId) : null;
            atualizarHiddenInputsTecnicosEdit();
        };

        // Funções para Modal de Técnicos por Atividade
        window.abrirModalTecnicosAtividadeEdit = function(atividadeId, atividadeNome) {
            atividadeAtualModalEdit = atividadeId;
            document.getElementById('modal-atividade-titulo-edit').textContent = `Atribuir Técnicos - ${atividadeNome}`;
            
            // Limpa busca
            document.getElementById('busca-tecnicos-edit').value = '';
            filtrarTecnicosEdit('');
            
            // Carrega dados existentes
            const tecnicosAtribuidos = atividadesTecnicosEdit[atividadeId] || { responsavel: null, tecnicos: [] };
            
            // Desmarca todos os checkboxes primeiro
            document.querySelectorAll('.tecnico-checkbox-edit').forEach(cb => {
                cb.checked = false;
            });
            
            // Marca os técnicos atribuídos
            tecnicosAtribuidos.tecnicos.forEach(tecnicoId => {
                const cb = document.querySelector(`.tecnico-checkbox-edit[value="${tecnicoId}"]`);
                if (cb) cb.checked = true;
            });
            
            // Atualiza o select de responsável e badges
            atualizarResponsavelAutomaticoEdit();
            
            // Se já tinha responsável definido, seleciona ele
            if (tecnicosAtribuidos.responsavel) {
                document.getElementById('responsavel-select-edit').value = tecnicosAtribuidos.responsavel;
                atualizarBadgeResponsavelEdit();
            }
            
            document.getElementById('modal-tecnicos-atividade-edit').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };

        // Função para filtrar técnicos por nome
        window.filtrarTecnicosEdit = function(termo) {
            const termoLower = termo.toLowerCase().trim();
            const labels = document.querySelectorAll('.tecnico-item-label-edit');
            const container = document.getElementById('lista-tecnicos-container-edit');
            const nenhumEncontrado = document.getElementById('nenhum-tecnico-encontrado-edit');
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
        window.atualizarResponsavelAutomaticoEdit = function() {
            const checkboxesMarcados = Array.from(document.querySelectorAll('.tecnico-checkbox-edit:checked'));
            const responsavelSelect = document.getElementById('responsavel-select-edit');
            const responsavelContainer = document.getElementById('responsavel-container-edit');
            
            // Limpa o select
            responsavelSelect.innerHTML = '<option value="">Selecione o responsável...</option>';
            
            // Esconde todos os badges
            document.querySelectorAll('.responsavel-badge-edit').forEach(badge => {
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
                const label = document.querySelector(`.tecnico-item-label-edit[data-tecnico-id="${checkboxesMarcados[0].value}"]`);
                if (label) {
                    label.querySelector('.responsavel-badge-edit').classList.remove('hidden');
                }
            } else {
                // Se tem mais de 1, mostra o select para escolher
                responsavelContainer.classList.remove('hidden');
                
                // Se não tinha responsável definido, seleciona o primeiro
                const responsavelAtual = responsavelSelect.value;
                if (!responsavelAtual && checkboxesMarcados.length > 0) {
                    responsavelSelect.value = checkboxesMarcados[0].value;
                }
                
                atualizarBadgeResponsavelEdit();
            }
        };

        // Função para atualizar o badge de responsável
        window.atualizarBadgeResponsavelEdit = function() {
            const responsavelId = document.getElementById('responsavel-select-edit').value;
            
            // Esconde todos os badges
            document.querySelectorAll('.responsavel-badge-edit').forEach(badge => {
                badge.classList.add('hidden');
            });
            
            // Mostra badge no responsável selecionado
            if (responsavelId) {
                const label = document.querySelector(`.tecnico-item-label-edit[data-tecnico-id="${responsavelId}"]`);
                if (label) {
                    label.querySelector('.responsavel-badge-edit').classList.remove('hidden');
                }
            }
        };

        window.fecharModalTecnicosAtividadeEdit = function() {
            document.getElementById('modal-tecnicos-atividade-edit').classList.add('hidden');
            document.body.style.overflow = '';
            atividadeAtualModalEdit = null;
        };

        window.confirmarTecnicosAtividadeEdit = function() {
            if (!atividadeAtualModalEdit) return;
            
            const checkboxesMarcados = Array.from(document.querySelectorAll('.tecnico-checkbox-edit:checked'));
            
            if (checkboxesMarcados.length === 0) {
                alert('Selecione pelo menos um técnico.');
                return;
            }
            
            const responsavelId = document.getElementById('responsavel-select-edit').value;
            if (!responsavelId) {
                alert('Selecione um técnico responsável.');
                return;
            }
            
            const tecnicosIds = checkboxesMarcados.map(cb => parseInt(cb.value));
            
            // Salva na estrutura (preserva estabelecimento_id existente)
            const existente = atividadesTecnicosEdit[atividadeAtualModalEdit] || {};
            atividadesTecnicosEdit[atividadeAtualModalEdit] = {
                responsavel: parseInt(responsavelId),
                tecnicos: tecnicosIds,
                estabelecimento_id: existente.estabelecimento_id || null
            };
            
            // Atualiza interface
            atualizarInterfaceTecnicosEdit();
            fecharModalTecnicosAtividadeEdit();
        };

        // Função para atualizar os hidden inputs da estrutura de técnicos
        function atualizarHiddenInputsTecnicosEdit() {
            const container = document.getElementById('atividades-tecnicos-hidden-inputs-edit');
            container.innerHTML = '';
            
            // Cria a estrutura atividades_tecnicos
            const estrutura = atividadesSelecionadasEdit.map(atividade => {
                const atividadeKey = atividade.uniqueKey;
                const tecnicosAtribuidos = atividadesTecnicosEdit[atividadeKey];
                if (!tecnicosAtribuidos || !tecnicosAtribuidos.responsavel) {
                    return null; // Pula atividades sem técnicos atribuídos
                }
                
                return {
                    tipo_acao_id: parseInt(atividade.id),
                    sub_acao_id: atividade.subAcaoId ? parseInt(atividade.subAcaoId) : null,
                    nome_atividade: atividade.nome,
                    tecnicos: tecnicosAtribuidos.tecnicos,
                    responsavel_id: tecnicosAtribuidos.responsavel,
                    estabelecimento_id: tecnicosAtribuidos.estabelecimento_id || null,
                    status: atividade.status || 'pendente'
                };
            }).filter(item => item !== null);
            
            // Cria hidden input com JSON
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'atividades_tecnicos';
            input.value = JSON.stringify(estrutura);
            container.appendChild(input);
            
            // Mantém compatibilidade com tecnicos_ids
            const tecnicosIds = [];
            estrutura.forEach(atividade => {
                tecnicosIds.push(...atividade.tecnicos);
            });
            const tecnicosUnicos = [...new Set(tecnicosIds)];
            
            tecnicosUnicos.forEach(tecnicoId => {
                const inputTecnico = document.createElement('input');
                inputTecnico.type = 'hidden';
                inputTecnico.name = 'tecnicos_ids[]';
                inputTecnico.value = tecnicoId;
                container.appendChild(inputTecnico);
            });
        }

        // Inicializa a interface
        atualizarInterfaceTecnicosEdit();

        // Validação de formulário: processo obrigatório por estabelecimento
        document.querySelector('form').addEventListener('submit', function(e) {
            // Validação: cada atividade deve ter pelo menos um técnico atribuído
            let atividadesSemTecnicoEdit = [];
            atividadesSelecionadasEdit.forEach(atividade => {
                const atividadeKey = atividade.uniqueKey || `${atividade.id}_${atividade.subAcaoId || 'main'}`;
                const tecnicosAtribuidos = atividadesTecnicosEdit[atividadeKey];
                if (!tecnicosAtribuidos || !tecnicosAtribuidos.tecnicos || tecnicosAtribuidos.tecnicos.length === 0) {
                    atividadesSemTecnicoEdit.push(atividade.nome);
                }
            });
            if (atividadesSemTecnicoEdit.length > 0) {
                e.preventDefault();
                alert('Atribua pelo menos um técnico para cada atividade:\n\n' + atividadesSemTecnicoEdit.join('\n'));
                document.getElementById('atividades-tecnicos-container-edit')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            if (estabelecimentosSelecionadosEdit.length > 0) {
                let confirmacoesPendentes = [];
                let estabelecimentosSemProcesso = [];
                estabelecimentosSelecionadosEdit.forEach(est => {
                    const hiddenInput = document.getElementById(`processo-hidden-est-edit-${est.id}`);
                    const continuarSemProcesso = document.getElementById(`continuar-sem-processo-est-edit-${est.id}`)?.value === '1';
                    const processoSelect = document.getElementById(`processo-est-edit-${est.id}`);
                    const semProcessosDisponiveis = !!processoSelect && processoSelect.disabled;
                    if ((!hiddenInput || !hiddenInput.value) && semProcessosDisponiveis && !continuarSemProcesso) {
                        confirmacoesPendentes.push(est.id);
                    } else if ((!hiddenInput || !hiddenInput.value) && !continuarSemProcesso) {
                        estabelecimentosSemProcesso.push(est.nome);
                    }
                });

                for (const estId of confirmacoesPendentes) {
                    if (!confirmarContinuarSemProcessoEdit(estId)) {
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
    });
</script>
@endpush
