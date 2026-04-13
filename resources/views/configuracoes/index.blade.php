@extends('layouts.admin')

@section('title', 'Configurações')
@section('page-title', 'Configurações do Sistema')

@php
    $user = auth('interno')->user();
    $isAdmin = $user->isAdmin();
    $isGestorEstadual = $user->nivel_acesso->value === 'gestor_estadual';
    $isGestorMunicipal = $user->nivel_acesso->value === 'gestor_municipal';
@endphp

@section('content')
<div class="max-w-8xl mx-auto">
    <style>
        .config-card {
            padding: 0.75rem;
            min-height: 96px;
        }
        .config-card .config-icon {
            width: 2.5rem;
            height: 2.5rem;
        }
        .config-card .config-icon svg {
            width: 1rem;
            height: 1rem;
        }
        .config-card h3 {
            font-size: 0.9rem;
            line-height: 1.1rem;
            margin-bottom: 0.125rem;
        }
        .config-card p {
            font-size: 0.68rem;
            line-height: 0.9rem;
        }
    </style>

    <div class="mb-6">
        <p class="text-gray-600">Gerencie as configurações e parâmetros do sistema</p>
    </div>

    {{-- Campo de busca dinâmica --}}
    <div class="mb-6">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text"
                   id="busca-config"
                   placeholder="Buscar configuração... (ex: documento, IA, município, chat, processo)"
                   class="w-full pl-12 pr-10 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm bg-white shadow-sm"
                   autocomplete="off">
            <button type="button" id="limpar-busca" class="absolute inset-y-0 right-0 pr-4 items-center hidden" title="Limpar busca">
                <svg class="w-5 h-5 text-gray-400 hover:text-gray-600 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="busca-sem-resultado" class="hidden mt-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800 text-center">
            Nenhuma configuração encontrada para o termo pesquisado.
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SEÇÃO: Processos e Atividades                                --}}
    {{-- ============================================================ --}}
    @if($isAdmin || $isGestorEstadual)
    <div class="mb-8">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Processos e Atividades
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

            @if($isAdmin)
            <a href="{{ route('admin.configuracoes.tipos-processo.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Tipos de Processo</h3>
                        <p class="text-xs text-gray-600">Configure os tipos de processos disponíveis no sistema</p>
                    </div>
                </div>
            </a>
            @endif

            <a href="{{ route('admin.configuracoes.tipo-acoes.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Tipos de Ações</h3>
                        <p class="text-xs text-gray-600">Configure ações realizadas pela vigilância sanitária</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.configuracoes.pactuacao.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Pactuação</h3>
                        <p class="text-xs text-gray-600">Configure competências municipais e estaduais por atividade (CNAE)</p>
                    </div>
                </div>
            </a>

            @if($isAdmin)
            <a href="{{ route('admin.configuracoes.tipo-setores.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-teal-100 rounded-lg flex items-center justify-center">
                            <svg class="text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Tipos de Setor</h3>
                        <p class="text-xs text-gray-600">Configure setores e vincule a níveis de acesso</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.configuracoes.unidades.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-violet-100 rounded-lg flex items-center justify-center">
                            <svg class="text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Unidades</h3>
                        <p class="text-xs text-gray-600">Cadastre unidades para vincular aos tipos de processo</p>
                    </div>
                </div>
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- SEÇÃO: Documentos                                            --}}
    {{-- ============================================================ --}}
    @if($isAdmin || $isGestorEstadual || $isGestorMunicipal)
    <div class="mb-8">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            Documentos
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

            @if($isAdmin || $isGestorEstadual)
            <a href="{{ route('admin.configuracoes.tipos-documento.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Tipos de Documento</h3>
                        <p class="text-xs text-gray-600">Configure os tipos de documentos disponíveis</p>
                    </div>
                </div>
            </a>
            @endif

            <a href="{{ route('admin.configuracoes.modelos-documento.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Modelos de Documentos</h3>
                        <p class="text-xs text-gray-600">{{ $isGestorMunicipal ? 'Crie e gerencie os modelos de documentos do seu município' : 'Crie e gerencie modelos de documentos digitais' }}</p>
                    </div>
                </div>
            </a>

            @if($isAdmin || $isGestorEstadual)
            <a href="{{ route('admin.configuracoes.listas-documento.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Documentos por Atividade</h3>
                        <p class="text-xs text-gray-600">Configure documentos exigidos por tipo de processo, atividade e escopo</p>
                    </div>
                </div>
            </a>
            @endif

            @if($isAdmin)
            <a href="{{ route('admin.configuracoes.documentos-pops.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-pink-100 rounded-lg flex items-center justify-center">
                            <svg class="text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Documentos IA</h3>
                        <p class="text-xs text-gray-600">Gerencie documentos POPs, categorias e integração com Assistente IA</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.configuracoes.documentos-ajuda.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-emerald-100 rounded-lg flex items-center justify-center">
                            <svg class="text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Documentos de Ajuda</h3>
                        <p class="text-xs text-gray-600">Gerencie documentos de ajuda exibidos nos processos (manuais, guias)</p>
                    </div>
                </div>
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- SEÇÃO: Regras de Negócio                                     --}}
    {{-- ============================================================ --}}
    @if($isAdmin || $isGestorEstadual)
    <div class="mb-8">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Regras de Negócio
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

            <a href="{{ route('admin.configuracoes.equipamentos-radiacao.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Equipamentos de Imagem</h3>
                        <p class="text-xs text-gray-600">Configure atividades que exigem cadastro de equipamentos de imagem</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.configuracoes.responsaveis-tecnicos.index') }}"
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-cyan-100 rounded-lg flex items-center justify-center">
                            <svg class="text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Responsável Técnico</h3>
                        <p class="text-xs text-gray-600">Configure atividades que exigem cadastro de responsável técnico</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- SEÇÃO: Comunicação e Feedback                                --}}
    {{-- ============================================================ --}}
    @if($isAdmin || $isGestorEstadual)
    <div class="mb-8">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            Comunicação e Feedback
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

            <a href="{{ route('admin.configuracoes.avisos.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Avisos do Sistema</h3>
                        <p class="text-xs text-gray-600">Crie avisos para usuários internos por nível de acesso</p>
                    </div>
                </div>
            </a>

            @if($isAdmin)
            <a href="{{ route('admin.configuracoes.chat-broadcast.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Mensagens do Suporte</h3>
                        <p class="text-xs text-gray-600">Envie mensagens de broadcast para usuários por nível de acesso</p>
                    </div>
                </div>
            </a>

            @if(Route::has('admin.configuracoes.pesquisas-satisfacao.index'))
            <a href="{{ route('admin.configuracoes.pesquisas-satisfacao.index') }}"
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-rose-100 rounded-lg flex items-center justify-center">
                            <svg class="text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Pesquisas de Satisfação</h3>
                        <p class="text-xs text-gray-600">Crie questionários para avaliação das inspeções</p>
                    </div>
                </div>
            </a>
            @endif
            @endif
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- SEÇÃO: Sistema e Geral                                       --}}
    {{-- ============================================================ --}}
    @if($isAdmin || $isGestorMunicipal)
    <div class="mb-8">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Sistema e Geral
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

            <a href="{{ route('admin.configuracoes.municipios.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Municípios</h3>
                        <p class="text-xs text-gray-600">{{ $isGestorMunicipal ? 'Atualize a logomarca do seu município' : 'Gerencie o cadastro de municípios do Tocantins' }}</p>
                    </div>
                </div>
            </a>

            @if($isAdmin)
            <a href="{{ route('admin.configuracoes.sistema.index') }}" 
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Configurações do Sistema</h3>
                        <p class="text-xs text-gray-600">Identidade visual, IA, chat interno e parâmetros globais</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.treinamentos.index') }}"
               class="config-card block bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="config-icon bg-sky-100 rounded-lg flex items-center justify-center">
                            <svg class="text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0112 20.055a12.083 12.083 0 01-6.16-9.477L12 14zm0 0v6"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900">Treinamentos</h3>
                        <p class="text-xs text-gray-600">Gerencie eventos, apresentações, perguntas e relatórios</p>
                    </div>
                </div>
            </a>
            @endif
        </div>
    </div>
    @endif
</div>

<script>
(function() {
    const input = document.getElementById('busca-config');
    const btnLimpar = document.getElementById('limpar-busca');
    const semResultado = document.getElementById('busca-sem-resultado');

    function removerAcentos(str) {
        return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function filtrar(termo) {
        const termoNorm = removerAcentos(termo.toLowerCase().trim());
        const sections = document.querySelectorAll('.mb-8'); // seções (Processos, Documentos, etc.)
        let totalVisivel = 0;

        sections.forEach(function(section) {
            const cards = section.querySelectorAll('.config-card');
            let secaoTemVisivel = false;

            cards.forEach(function(card) {
                const texto = removerAcentos(card.textContent.toLowerCase());
                if (!termoNorm || texto.includes(termoNorm)) {
                    card.style.display = '';
                    secaoTemVisivel = true;
                    totalVisivel++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Mostrar/esconder a seção inteira (título + grid)
            if (cards.length > 0) {
                section.style.display = secaoTemVisivel ? '' : 'none';
            }
        });

        semResultado.classList.toggle('hidden', totalVisivel > 0 || !termoNorm);
    }

    input.addEventListener('input', function() {
        const termo = this.value;
        btnLimpar.style.display = termo ? 'flex' : 'none';
        filtrar(termo);
    });

    btnLimpar.addEventListener('click', function() {
        input.value = '';
        this.style.display = 'none';
        filtrar('');
        input.focus();
    });
})();
</script>
@endsection
