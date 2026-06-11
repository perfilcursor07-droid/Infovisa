@extends('layouts.admin')

@section('title', 'Nova Lista de Documentos')
@section('page-title', 'Nova Lista de Documentos')

@section('content')
<div class="max-w-8xl mx-auto" x-data="listaDocumentoForm()">
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.configuracoes.listas-documento.index') }}" 
           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Listas
        </a>
        <div class="text-sm text-gray-500">
            <span class="text-red-500">*</span> Campos obrigatórios
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-red-800 mb-1">Corrija os seguintes erros:</h4>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.configuracoes.listas-documento.store') }}" method="POST">
        @csrf

        {{-- Passo 1: Tipo de Processo e Escopo --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Tipo de Processo e Escopo</h3>
                        <p class="text-xs text-gray-600 mt-0.5">Defina o tipo de processo e abrangência da lista</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="tipo_processo_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Processo <span class="text-red-500">*</span>
                        </label>
                        <select name="tipo_processo_id" id="tipo_processo_id" required
                                x-model="tipoProcessoId"
                                @change="onTipoProcessoChange($event)"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="">Selecione o tipo de processo...</option>
                            @foreach($tiposProcesso as $tp)
                            <option value="{{ $tp->id }}" data-codigo="{{ $tp->codigo }}" {{ old('tipo_processo_id') == $tp->id ? 'selected' : '' }}>
                                {{ $tp->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="escopo" class="block text-sm font-medium text-gray-700 mb-2">
                            Escopo <span class="text-red-500">*</span>
                        </label>
                        <select name="escopo" id="escopo" x-model="escopo" required
                                @change="updateNomeLista()"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="estadual">Estadual (Todos os municípios)</option>
                            <option value="municipal">Municipal (Específico)</option>
                        </select>
                    </div>

                    <div x-show="escopo === 'municipal'" x-transition class="lg:col-span-2">
                        <label for="municipio_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Município <span class="text-red-500">*</span>
                        </label>
                        <select name="municipio_id" id="municipio_id"
                                @change="updateNomeLista()"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="">Selecione o município...</option>
                            @foreach($municipios as $municipio)
                            <option value="{{ $municipio->id }}" {{ old('municipio_id') == $municipio->id ? 'selected' : '' }}>
                                {{ $municipio->nome }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Passo 2: Tipos de Serviço (escondido para processos especiais) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6" 
             x-show="!isProcessoEspecial" x-transition>
            <div class="bg-gradient-to-r from-violet-50 to-purple-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-violet-600 text-white rounded-full flex items-center justify-center text-sm font-bold">2</div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Tipos de Serviço</h3>
                        <p class="text-xs text-gray-600 mt-0.5">Selecione os tipos de serviço que exigirão esta lista</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                @if($tiposServico->isEmpty())
                <div class="flex items-start gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">Nenhum tipo de serviço cadastrado</p>
                        <p class="text-xs text-yellow-700 mt-1">
                            <a href="{{ route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-servico']) }}" class="underline hover:no-underline">Cadastre tipos de serviço primeiro</a>
                        </p>
                    </div>
                </div>
                @else
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-blue-800">Todas as atividades (CNAEs) dentro dos tipos selecionados serão incluídas automaticamente. O nome da lista será gerado com base no primeiro tipo selecionado.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach($tiposServico as $tipoServico)
                    <label class="group relative flex items-start gap-3 p-4 border-2 border-gray-200 rounded-xl hover:border-violet-300 hover:bg-violet-50/50 cursor-pointer transition-all duration-200"
                           :class="tiposServicoSelecionados.includes({{ $tipoServico->id }}) ? 'border-violet-500 bg-violet-50' : ''">
                        <input type="checkbox" name="tipos_servico[]" value="{{ $tipoServico->id }}"
                               {{ in_array($tipoServico->id, old('tipos_servico', [])) ? 'checked' : '' }}
                               @change="toggleTipoServico({{ $tipoServico->id }}, '{{ $tipoServico->nome }}')"
                               class="w-5 h-5 mt-0.5 text-violet-600 border-gray-300 rounded focus:ring-violet-500 focus:ring-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <svg class="w-4 h-4 text-violet-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-900 truncate">{{ $tipoServico->nome }}</span>
                                </div>
                                <span class="px-2 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-700 rounded-full whitespace-nowrap flex-shrink-0">
                                    {{ $tipoServico->atividades_count ?? $tipoServico->atividades->count() }} CNAEs
                                </span>
                            </div>
                            @if($tipoServico->descricao)
                            <p class="text-xs text-gray-600 mb-2 line-clamp-2">{{ $tipoServico->descricao }}</p>
                            @endif
                            @if($tipoServico->atividades->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($tipoServico->atividades->take(3) as $atividade)
                                <span class="px-2 py-0.5 text-xs bg-white border border-gray-200 text-gray-700 rounded">
                                    {{ $atividade->codigo_cnae ?: Str::limit($atividade->nome, 20) }}
                                </span>
                                @endforeach
                                @if($tipoServico->atividades->count() > 3)
                                <span class="px-2 py-0.5 text-xs text-gray-500">
                                    +{{ $tipoServico->atividades->count() - 3 }}
                                </span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Aviso para Processos Especiais (Projeto Arquitetônico / Análise de Rotulagem) --}}
        <div class="bg-white rounded-xl shadow-sm border border-purple-200 overflow-hidden mb-6" 
             x-show="isProcessoEspecial" x-transition>
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-4 border-b border-purple-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Processo Especial Selecionado</h3>
                        <p class="text-xs text-gray-600 mt-0.5">Esta lista será vinculada diretamente ao tipo de processo</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="flex items-start gap-3 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                    <svg class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-purple-800 mb-2">
                            Para <strong>Projeto Arquitetônico</strong> e <strong>Análise de Rotulagem</strong>, a lista de documentos é vinculada diretamente ao tipo de processo.
                        </p>
                        <ul class="text-xs text-purple-700 space-y-1 list-disc list-inside">
                            <li>Não é necessário selecionar tipos de serviço ou atividades (CNAEs)</li>
                            <li>Os documentos serão exigidos para todos os estabelecimentos que abrirem este tipo de processo</li>
                            <li>Esta configuração é independente das listas de documentos por atividade do Licenciamento Sanitário</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Passo 3: Nome e Descrição --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-600 text-white rounded-full flex items-center justify-center text-sm font-bold" x-text="isProcessoEspecial ? '2' : '3'"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Nome e Descrição</h3>
                        <p class="text-xs text-gray-600 mt-0.5">Identifique a lista (gerado automaticamente ou personalize)</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="space-y-5">
                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome da Lista <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nome" id="nome" x-model="nomeLista" value="{{ old('nome') }}" required
                               placeholder="Será gerado automaticamente ao selecionar tipo de serviço..."
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                        <p class="mt-1.5 text-xs text-gray-500">
                            <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            O nome é gerado automaticamente baseado no tipo de serviço e escopo. Você pode editá-lo se desejar.
                        </p>
                    </div>

                    <div>
                        <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                            Descrição <span class="text-gray-400 text-xs">(opcional)</span>
                        </label>
                        <textarea name="descricao" id="descricao" rows="3"
                                  placeholder="Descreva o propósito desta lista, quando deve ser usada, etc..."
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors resize-none">{{ old('descricao') }}</textarea>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <input type="checkbox" name="ativo" id="ativo" value="1" {{ old('ativo', true) ? 'checked' : '' }}
                               class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2">
                        <label for="ativo" class="text-sm font-medium text-gray-700 cursor-pointer">
                            Lista ativa (disponível para uso imediato)
                        </label>
                    </div>
                </div>
            </div>
        </div>
        {{-- Passo 4: Documentos Exigidos --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-amber-600 text-white rounded-full flex items-center justify-center text-sm font-bold" x-text="isProcessoEspecial ? '3' : '4'"></div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Documentos Exigidos</h3>
                        <p class="text-xs text-gray-600 mt-0.5">Selecione os documentos específicos e veja os documentos comuns aplicados automaticamente</p>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                {{-- Documentos Comuns (Informativo) --}}
                @if($documentosComuns->isNotEmpty())
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Documentos Comuns (Aplicados Automaticamente)
                        </h4>
                        <span class="px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">
                            {{ $documentosComuns->count() }} documentos
                        </span>
                    </div>
                    
                    <div class="p-4 bg-green-50 border-2 border-green-200 rounded-lg mb-4">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-green-800">
                                <strong>Estes documentos são obrigatórios para todos os serviços</strong> e serão aplicados automaticamente. Você não precisa selecioná-los, apenas visualize quais são.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-6" id="documentos-comuns-container">
                        @foreach($documentosComuns as $doc)
                        <div class="documento-comum-item border-2 border-green-200 bg-green-50/30 rounded-lg p-4" data-tipo-processo-id="{{ $doc->tipo_processo_id ?? '' }}">
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 bg-green-500 text-white rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-sm font-semibold text-gray-900 mb-1">{{ $doc->nome }}</h5>
                                    @if($doc->descricao)
                                    <p class="text-xs text-gray-600 line-clamp-2">{{ $doc->descricao }}</p>
                                    @endif
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded">
                                            Comum
                                        </span>
                                        @if($doc->tipoProcesso)
                                        <span class="px-2 py-0.5 text-xs bg-indigo-100 text-indigo-700 rounded">
                                            {{ $doc->tipoProcesso->nome }}
                                        </span>
                                        @else
                                        <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded">
                                            Todos os processos
                                        </span>
                                        @endif
                                        @if($doc->escopo_competencia)
                                        <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded">
                                            {{ ucfirst($doc->escopo_competencia) }}
                                        </span>
                                        @endif
                                        @if($doc->tipo_setor)
                                        <span class="px-2 py-0.5 text-xs bg-purple-100 text-purple-700 rounded">
                                            {{ ucfirst($doc->tipo_setor) }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="border-t-2 border-gray-200 pt-4 mb-4"></div>
                </div>
                @endif

                {{-- Documentos Específicos (Selecionáveis) --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Documentos Específicos (Selecione os Necessários)
                        </h4>
                    </div>
                    
                    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-amber-800">
                                Selecione apenas os documentos <strong>específicos</strong> para este tipo de serviço. Os documentos comuns já serão aplicados automaticamente.
                            </p>
                        </div>
                    </div>
                    
                    @if($tiposDocumento->isEmpty())
                    <div class="flex items-start gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Nenhum tipo de documento específico cadastrado</p>
                            <p class="text-xs text-yellow-700 mt-1">
                                <a href="{{ route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-documento']) }}" class="underline hover:no-underline">Cadastre tipos de documento primeiro</a>
                            </p>
                        </div>
                    </div>
                    @else
                    <div class="space-y-3 max-h-[600px] overflow-y-auto pr-2">
                        @foreach($tiposDocumento as $doc)
                        <div class="border-2 border-gray-200 rounded-lg hover:border-amber-300 transition-all duration-200" 
                             x-data="{ selecionado: {{ in_array($doc->id, old('documentos_selecionados', [])) ? 'true' : 'false' }} }"
                             :class="selecionado ? 'border-amber-400 bg-amber-50/30' : 'bg-white'">
                            <div class="p-4">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" 
                                           x-model="selecionado"
                                           name="documentos_selecionados[]"
                                           value="{{ $doc->id }}"
                                           {{ in_array($doc->id, old('documentos_selecionados', [])) ? 'checked' : '' }}
                                           @change="if(!selecionado) { $refs.obrigatorio_{{ $doc->id }}.checked = true; $refs.observacao_{{ $doc->id }}.value = ''; }"
                                           class="w-5 h-5 mt-1 text-amber-600 border-gray-300 rounded focus:ring-amber-500 focus:ring-2">
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between gap-4 mb-2">
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-gray-900">{{ $doc->nome }}</h4>
                                                @if($doc->descricao)
                                                <p class="text-xs text-gray-600 mt-1">{{ $doc->descricao }}</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-4 flex-shrink-0" x-show="selecionado" x-transition>
                                                <label class="flex items-center gap-2 cursor-pointer group">
                                                    <input type="radio" name="documento_{{ $doc->id }}_obrigatorio" value="1" checked
                                                           x-ref="obrigatorio_{{ $doc->id }}"
                                                           class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                                                    <span class="text-xs font-medium text-gray-700 group-hover:text-red-600">Obrigatório</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer group">
                                                    <input type="radio" name="documento_{{ $doc->id }}_obrigatorio" value="0"
                                                           class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                                    <span class="text-xs font-medium text-gray-700 group-hover:text-blue-600">Opcional</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div x-show="selecionado" x-transition class="mt-3">
                                            <input type="text" name="documento_{{ $doc->id }}_observacao" 
                                                   x-ref="observacao_{{ $doc->id }}"
                                                   value="{{ old('documento_'.$doc->id.'_observacao') }}"
                                                   placeholder="Observação específica para este documento (opcional)"
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Botões de Ação --}}
        <div class="flex items-center justify-between gap-4 p-6 bg-gray-50 rounded-xl border border-gray-200">
            <div class="text-sm text-gray-600">
                <svg class="w-4 h-4 inline mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Revise todas as informações antes de criar
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.configuracoes.listas-documento.index') }}" 
                   class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Criar Lista de Documentos
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function listaDocumentoForm() {
    return {
        escopo: '{{ old('escopo', 'estadual') }}',
        nomeLista: '{{ old('nome', '') }}',
        tiposServicoSelecionados: @json(old('tipos_servico', [])),
        tiposServicoNomes: {},
        tipoProcessoId: '{{ old('tipo_processo_id', '') }}',
        tipoProcessoCodigo: '',
        isProcessoEspecial: false,
        
        init() {
            // Verifica se já tem um tipo de processo selecionado (old value)
            if (this.tipoProcessoId) {
                const select = document.getElementById('tipo_processo_id');
                const option = select.querySelector(`option[value="${this.tipoProcessoId}"]`);
                if (option) {
                    this.tipoProcessoCodigo = option.dataset.codigo || '';
                    this.isProcessoEspecial = ['projeto_arquitetonico', 'analise_rotulagem'].includes(this.tipoProcessoCodigo);
                }
            }
        },
        
        onTipoProcessoChange(event) {
            const select = event.target;
            const selectedOption = select.options[select.selectedIndex];
            this.tipoProcessoCodigo = selectedOption.dataset.codigo || '';
            this.isProcessoEspecial = ['projeto_arquitetonico', 'analise_rotulagem'].includes(this.tipoProcessoCodigo);
            
            // Se for processo especial, limpa os tipos de serviço selecionados
            if (this.isProcessoEspecial) {
                this.tiposServicoSelecionados = [];
                this.tiposServicoNomes = {};
                // Desmarca todos os checkboxes de tipos de serviço
                document.querySelectorAll('input[name="tipos_servico[]"]').forEach(cb => cb.checked = false);
            }
            
            // Atualiza nome da lista
            this.updateNomeListaProcessoEspecial();
            
            // Filtra documentos comuns
            filtrarDocumentosComuns(this.tipoProcessoId);
        },
        
        updateNomeListaProcessoEspecial() {
            if (this.isProcessoEspecial) {
                const select = document.getElementById('tipo_processo_id');
                const selectedOption = select.options[select.selectedIndex];
                const nomeProcesso = selectedOption.text;
                
                let sufixo = '';
                if (this.escopo === 'estadual') {
                    sufixo = ' - Estado';
                } else if (this.escopo === 'municipal') {
                    const municipioSelect = document.getElementById('municipio_id');
                    const municipioNome = municipioSelect.options[municipioSelect.selectedIndex]?.text;
                    if (municipioNome && municipioNome !== 'Selecione o município...') {
                        sufixo = ' - ' + municipioNome;
                    } else {
                        sufixo = ' - Municipal';
                    }
                }
                
                this.nomeLista = nomeProcesso + sufixo;
            }
        },
        
        toggleTipoServico(id, nome) {
            const index = this.tiposServicoSelecionados.indexOf(id);
            if (index > -1) {
                this.tiposServicoSelecionados.splice(index, 1);
                delete this.tiposServicoNomes[id];
            } else {
                this.tiposServicoSelecionados.push(id);
                this.tiposServicoNomes[id] = nome;
            }
            this.updateNomeLista();
        },
        
        updateNomeLista() {
            // Se for processo especial, usa lógica diferente
            if (this.isProcessoEspecial) {
                this.updateNomeListaProcessoEspecial();
                return;
            }
            
            // Só atualiza se o usuário não tiver digitado um nome personalizado
            const nomeInput = document.getElementById('nome');
            const nomeAtual = nomeInput.value.trim();
            
            // Se já tem um nome e não parece ser auto-gerado, não sobrescreve
            if (nomeAtual && !nomeAtual.includes(' - Estado') && !nomeAtual.includes(' - Municipal')) {
                return;
            }
            
            if (this.tiposServicoSelecionados.length === 0) {
                this.nomeLista = '';
                return;
            }
            
            // Pega o nome do primeiro tipo de serviço selecionado
            const primeiroTipoId = this.tiposServicoSelecionados[0];
            const primeiroTipoNome = this.tiposServicoNomes[primeiroTipoId];
            
            if (!primeiroTipoNome) return;
            
            // Gera o nome baseado no escopo
            let sufixo = '';
            if (this.escopo === 'estadual') {
                sufixo = ' - Estado';
            } else if (this.escopo === 'municipal') {
                const municipioSelect = document.getElementById('municipio_id');
                const municipioNome = municipioSelect.options[municipioSelect.selectedIndex]?.text;
                if (municipioNome && municipioNome !== 'Selecione o município...') {
                    sufixo = ' - ' + municipioNome;
                } else {
                    sufixo = ' - Municipal';
                }
            }
            
            this.nomeLista = primeiroTipoNome + sufixo;
        }
    }
}

// Validação antes do submit
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const formData = new FormData(form);
            const documentos = formData.getAll('documentos_selecionados[]');
            const tiposServico = formData.getAll('tipos_servico[]');
            const tipoProcessoSelect = document.getElementById('tipo_processo_id');
            const selectedOption = tipoProcessoSelect.options[tipoProcessoSelect.selectedIndex];
            const tipoProcessoCodigo = selectedOption?.dataset?.codigo || '';
            const isProcessoEspecial = ['projeto_arquitetonico', 'analise_rotulagem'].includes(tipoProcessoCodigo);
            
            // Só valida tipos de serviço se NÃO for processo especial
            if (!isProcessoEspecial && tiposServico.length === 0) {
                e.preventDefault();
                alert('⚠️ Selecione pelo menos um tipo de serviço!');
                document.querySelector('[name="tipos_servico[]"]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            // Documentos específicos são opcionais: a lista pode usar apenas os
            // documentos comuns (estaduais ou municipais) aplicados automaticamente.
        });
    }
    
    // Filtrar documentos comuns quando o tipo de processo for selecionado
    const tipoProcessoSelect = document.getElementById('tipo_processo_id');
    if (tipoProcessoSelect) {
        tipoProcessoSelect.addEventListener('change', function() {
            filtrarDocumentosComuns(this.value);
        });
        
        // Aplica filtro inicial se já houver um valor selecionado
        if (tipoProcessoSelect.value) {
            filtrarDocumentosComuns(tipoProcessoSelect.value);
        }
    }
});

// Função para filtrar documentos comuns por tipo de processo
function filtrarDocumentosComuns(tipoProcessoId) {
    const container = document.getElementById('documentos-comuns-container');
    if (!container) return;
    
    const documentos = container.querySelectorAll('.documento-comum-item');
    let visiveis = 0;
    
    documentos.forEach(function(doc) {
        const docTipoProcessoId = doc.getAttribute('data-tipo-processo-id');
        
        // Mostra se: não tem tipo_processo_id (aplica a todos) OU se o tipo_processo_id é igual ao selecionado
        if (!docTipoProcessoId || docTipoProcessoId === '' || docTipoProcessoId === tipoProcessoId) {
            doc.style.display = '';
            visiveis++;
        } else {
            doc.style.display = 'none';
        }
    });
    
    // Atualiza o contador de documentos comuns
    const contadorSpan = container.closest('.mb-6')?.querySelector('.bg-green-100.text-green-700');
    if (contadorSpan) {
        contadorSpan.textContent = visiveis + ' documento' + (visiveis !== 1 ? 's' : '');
    }
}
</script>
@endsection
