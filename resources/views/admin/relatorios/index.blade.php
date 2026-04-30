@extends('layouts.admin')

@section('title', 'Relatórios')

@section('content')
<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Relatórios</h1>
            <p class="text-gray-500 mt-1">Selecione um relatório para visualizar ou exportar</p>
        </div>
    </div>

    {{-- Grid de Relatórios Disponíveis --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        {{-- Relatório: Estabelecimentos por CNAE --}}
        <a href="{{ route('admin.relatorios.estabelecimentos-cnae') }}"
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md hover:border-cyan-300 transition-all group">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-cyan-100 rounded-lg group-hover:bg-cyan-200 transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M8 7h8M8 11h8M8 15h5"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-cyan-600 transition-colors">
                        Estabelecimentos por CNAE
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Resumo por tipo de estabelecimento com escopo automático por perfil
                    </p>
                    <div class="mt-2 flex items-center text-xs text-cyan-600 font-medium">
                        <span>Ver</span>
                        <svg class="w-3 h-3 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </a>
        
        {{-- Relatório: Equipamentos de Imagem --}}
        <a href="{{ route('admin.relatorios.equipamentos-radiacao') }}" 
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md hover:border-orange-300 transition-all group">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-orange-100 rounded-lg group-hover:bg-orange-200 transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-orange-600 transition-colors">
                        Equipamentos de Imagem
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Status de cadastro por estabelecimento
                    </p>
                    <div class="mt-2 flex items-center text-xs text-orange-600 font-medium">
                        <span>Ver</span>
                        <svg class="w-3 h-3 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </a>

        {{-- Relatório: Documentos Gerados --}}
        <a href="{{ route('admin.relatorios.documentos-gerados') }}"
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md hover:border-blue-300 transition-all group">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                        Documentos Gerados
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Listagem completa com filtros por período e status
                    </p>
                    <div class="mt-2 flex items-center text-xs text-blue-600 font-medium">
                        <span>Ver</span>
                        <svg class="w-3 h-3 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </a>

        {{-- Relatório: Pesquisa de Satisfação (somente admin) --}}
        @if(auth('interno')->user()->isAdmin())
        <a href="{{ route('admin.relatorios.pesquisa-satisfacao') }}"
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md hover:border-emerald-300 transition-all group">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-emerald-100 rounded-lg group-hover:bg-emerald-200 transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-emerald-600 transition-colors">
                        Pesquisa de Satisfação
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Gráficos e análise das respostas por pesquisa
                    </p>
                    <div class="mt-2 flex items-center text-xs text-emerald-600 font-medium">
                        <span>Ver</span>
                        <svg class="w-3 h-3 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </a>
        @endif

        {{-- Relatório: Processos --}}
        <a href="{{ route('admin.relatorios.processos') }}"
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md hover:border-green-300 transition-all group">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-green-100 rounded-lg group-hover:bg-green-200 transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-green-600 transition-colors">Processos</h3>
                    <p class="text-xs text-gray-500 mt-1">Por tipo, status, municipio e periodo</p>
                    <div class="mt-2 flex items-center text-xs text-green-600 font-medium">
                        <span>Ver</span>
                        <svg class="w-3 h-3 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </a>

        {{-- Relatório: Ordens de Serviço / Ações por Atividade --}}
        <a href="{{ route('admin.relatorios.acoes-atividade') }}"
           class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md hover:border-purple-300 transition-all group">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-purple-100 rounded-lg group-hover:bg-purple-200 transition-colors flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-purple-600 transition-colors">
                        Ações e Estabelecimentos por Atividade
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        OS por ação, município, região de saúde, competência e técnico
                    </p>
                    <div class="mt-2 flex items-center text-xs text-purple-600 font-medium">
                        <span>Ver</span>
                        <svg class="w-3 h-3 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </a>

        {{-- Relatório: Estatísticas Gerais --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 opacity-60">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-indigo-100 rounded-lg flex-shrink-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900">
                        Estatísticas
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Gráficos e indicadores
                    </p>
                    <div class="mt-2 flex items-center text-xs text-gray-400 font-medium">
                        <span>Em breve</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
