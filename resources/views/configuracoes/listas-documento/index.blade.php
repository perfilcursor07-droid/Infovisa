@extends('layouts.admin')

@section('title', 'Lista de Documentos por Atividade')
@section('page-title', 'Lista de Documentos por Atividade')

@section('content')
<div class="max-w-8xl mx-auto" x-data="{ activeTab: '{{ request('tab', 'listas') }}' }">
    {{-- Voltar --}}
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Configurações
        </a>
    </div>

    {{-- Descrição --}}
    <div class="mb-6">
        <p class="text-sm text-gray-600">Configure quais documentos são exigidos para cada tipo de processo, atividade e escopo (Estado ou Município específico)</p>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <p class="text-sm text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <p class="text-sm text-red-800">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Abas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200 bg-gray-50">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'listas'" 
                        :class="activeTab === 'listas' ? 'border-blue-600 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Listas de Documentos
                </button>
                <button @click="activeTab = 'tipos-documento'" 
                        :class="activeTab === 'tipos-documento' ? 'border-amber-500 text-amber-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Tipos de Documento
                </button>
                <button @click="activeTab = 'tipos-servico'" 
                        :class="activeTab === 'tipos-servico' ? 'border-violet-500 text-violet-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Tipos de Serviço & Atividades
                </button>
                <button @click="activeTab = 'unidade-movel'" 
                        :class="activeTab === 'unidade-movel' ? 'border-fuchsia-500 text-fuchsia-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/>
                    </svg>
                    Unidade Móvel
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            {{-- Tab: Listas de Documentos --}}
            <div x-show="activeTab === 'listas'" x-transition>
                @include('configuracoes.listas-documento.partials.tab-listas')
            </div>

            {{-- Tab: Tipos de Documento --}}
            <div x-show="activeTab === 'tipos-documento'" x-transition>
                @include('configuracoes.listas-documento.partials.tab-tipos-documento')
            </div>

            {{-- Tab: Tipos de Serviço & Atividades --}}
            <div x-show="activeTab === 'tipos-servico'" x-transition>
                @include('configuracoes.listas-documento.partials.tab-tipos-servico')
            </div>

            {{-- Tab: Unidade Móvel --}}
            <div x-show="activeTab === 'unidade-movel'" x-transition>
                @include('configuracoes.listas-documento.partials.tab-unidade-movel')
            </div>
        </div>
    </div>
</div>
@endsection
