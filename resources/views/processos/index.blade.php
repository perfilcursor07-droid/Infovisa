@extends('layouts.admin')

@section('title', 'Processos')

@section('content')
@php
    $resumoQuick = $resumoQuick ?? [
        'todos' => $processos->total(),
        'completo' => 0,
        'nao_enviado' => 0,
        'aguardando' => 0,
        'arquivado' => 0,
        'nao_atribuido' => 0,
    ];

    $filtrosAvancadosAtivos = request()->hasAny(['tipo', 'status', 'docs_obrigatorios', 'ano', 'responsavel', 'setor', 'ordenacao', 'monitorando']);
    $temAlgumFiltro = $filtrosAvancadosAtivos || request()->filled('busca') || request()->filled('quick');
@endphp

<div class="max-w-[1400px] mx-auto" x-data="{ filtrosAbertos: {{ $filtrosAvancadosAtivos ? 'true' : 'false' }} }">
    {{-- Header --}}
    <div class="mb-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Processos</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $processos->total() }} processo{{ $processos->total() !== 1 ? 's' : '' }} encontrado{{ $processos->total() !== 1 ? 's' : '' }}</p>
            </div>
        </div>
    </div>

    {{-- Barra de busca + botão filtros --}}
    <form method="GET" action="{{ route('admin.processos.index-geral') }}" id="formProcessos" class="mb-3">
        {{-- Preserva filtros rápidos em hidden --}}
        @if(request('quick'))
            <input type="hidden" name="quick" value="{{ request('quick') }}">
        @endif

        <div class="flex gap-2">
            {{-- Busca --}}
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="busca" value="{{ request('busca') }}"
                       placeholder="Buscar por nº do processo, CNPJ, razão social ou nome fantasia..."
                       class="w-full pl-10 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
            </div>

            {{-- Botão Filtros --}}
            <button type="button" @click="filtrosAbertos = !filtrosAbertos"
                    class="relative inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                    :class="filtrosAbertos ? 'border-blue-500 text-blue-700 bg-blue-50' : 'text-gray-700'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filtros
                @if($filtrosAvancadosAtivos)
                    <span class="inline-flex items-center justify-center w-5 h-5 bg-blue-600 text-white text-[10px] font-bold rounded-full">
                        @php
                            $contFiltros = collect(['tipo', 'status', 'docs_obrigatorios', 'ano', 'responsavel', 'setor', 'monitorando'])
                                ->filter(fn($key) => request()->filled($key))
                                ->count();
                        @endphp
                        {{ $contFiltros }}
                    </span>
                @endif
            </button>

            {{-- Buscar --}}
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                Buscar
            </button>

            {{-- Limpar --}}
            @if($temAlgumFiltro)
                <a href="{{ route('admin.processos.index-geral') }}"
                   class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Limpar
                </a>
            @endif
        </div>

        {{-- Painel de Filtros Avançados (collapsible) --}}
        <div x-show="filtrosAbertos" x-transition class="mt-3 bg-white border border-gray-200 rounded-xl shadow-sm p-4" x-cloak>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                {{-- Tipo de Processo --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo</label>
                    <select name="tipo" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        @foreach($tiposProcesso as $tipo)
                            <option value="{{ $tipo->codigo }}" {{ request('tipo') == $tipo->codigo ? 'selected' : '' }}>{{ $tipo->nome }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        @foreach($statusDisponiveis as $key => $nome)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $nome }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Documentação --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Documentação</label>
                    <select name="docs_obrigatorios" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="completos" {{ request('docs_obrigatorios') == 'completos' ? 'selected' : '' }}>Completos</option>
                        <option value="pendentes" {{ request('docs_obrigatorios') == 'pendentes' ? 'selected' : '' }}>Pendentes</option>
                    </select>
                </div>

                {{-- Responsável --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Responsável</label>
                    <select name="responsavel" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="meus" {{ request('responsavel') == 'meus' ? 'selected' : '' }}>Meus processos</option>
                        @if(auth('interno')->user()->setor)
                            <option value="meu_setor" {{ request('responsavel') == 'meu_setor' ? 'selected' : '' }}>Meu setor</option>
                        @endif
                        <option value="nao_atribuido" {{ request('responsavel') == 'nao_atribuido' ? 'selected' : '' }}>Não atribuídos</option>
                    </select>
                </div>

                {{-- Setor --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Setor</label>
                    <select name="setor" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        @foreach(($setoresDisponiveis ?? collect()) as $codigo => $nome)
                            <option value="{{ $codigo }}" {{ request('setor') == $codigo ? 'selected' : '' }}>{{ $nome }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Ano --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Ano</label>
                    <select name="ano" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        @foreach($anos as $anoItem)
                            <option value="{{ $anoItem }}" {{ request('ano') == $anoItem ? 'selected' : '' }}>{{ $anoItem }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Ordenação --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Ordenar por</label>
                    <select name="ordenacao" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="recentes" {{ request('ordenacao', 'recentes') == 'recentes' ? 'selected' : '' }}>Mais recentes</option>
                        <option value="antigos" {{ request('ordenacao') == 'antigos' ? 'selected' : '' }}>Mais antigos</option>
                        <option value="numero" {{ request('ordenacao') == 'numero' ? 'selected' : '' }}>Número do processo</option>
                        <option value="estabelecimento" {{ request('ordenacao') == 'estabelecimento' ? 'selected' : '' }}>Estabelecimento</option>
                    </select>
                </div>

                {{-- Monitorando --}}
                <div class="flex items-end">
                    <label class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-100 transition w-full">
                        <input type="checkbox" name="monitorando" value="1" {{ request()->boolean('monitorando') ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span>Apenas os que acompanho</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-gray-100">
                <a href="{{ route('admin.processos.index-geral') }}"
                   class="px-4 py-2 text-xs font-medium text-gray-600 hover:text-gray-800 transition">
                    Limpar filtros
                </a>
                <button type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    Aplicar filtros
                </button>
            </div>
        </div>
    </form>

    {{-- Filtros rápidos (chips) --}}
    <div class="flex items-center gap-2 mb-4 flex-wrap">
        <a href="{{ route('admin.processos.index-geral', request()->except('quick')) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ !request('quick') ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-gray-300' }}">
            Todos <span class="ml-1 opacity-75">{{ $resumoQuick['todos'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.processos.index-geral', array_merge(request()->query(), ['quick' => 'completo'])) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('quick') === 'completo' ? 'bg-green-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-green-300' }}">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-green-400 mr-1"></span>Completos <span class="ml-1 opacity-75">{{ $resumoQuick['completo'] ?? '—' }}</span>
        </a>
        <a href="{{ route('admin.processos.index-geral', array_merge(request()->query(), ['quick' => 'nao_enviado'])) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('quick') === 'nao_enviado' ? 'bg-red-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-red-300' }}">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-red-400 mr-1"></span>Incompletos <span class="ml-1 opacity-75">{{ $resumoQuick['nao_enviado'] ?? '—' }}</span>
        </a>
        <a href="{{ route('admin.processos.index-geral', array_merge(request()->query(), ['quick' => 'aguardando'])) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('quick') === 'aguardando' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-amber-300' }}">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-400 mr-1"></span>Aguardando <span class="ml-1 opacity-75">{{ $resumoQuick['aguardando'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.processos.index-geral', array_merge(request()->query(), ['quick' => 'arquivado'])) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('quick') === 'arquivado' ? 'bg-slate-700 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-slate-300' }}">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-400 mr-1"></span>Arquivados <span class="ml-1 opacity-75">{{ $resumoQuick['arquivado'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.processos.index-geral', array_merge(request()->query(), ['quick' => 'nao_atribuido'])) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('quick') === 'nao_atribuido' ? 'bg-gray-700 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:border-gray-400' }}">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400 mr-1"></span>Não atribuídos <span class="ml-1 opacity-75">{{ $resumoQuick['nao_atribuido'] ?? 0 }}</span>
        </a>
    </div>

    {{-- Lista de Processos --}}
    @if($processos->count() > 0)
        <div class="space-y-2">
            @foreach($processos as $processo)
                @php
                    $docs = $statusDocsObrigatorios[$processo->id] ?? null;
                    $prazo = $prazoFilaPublica[$processo->id] ?? null;
                    $temPendenciaAprovacao = ($processosComPendencias ?? collect())->contains($processo->id);
                    $temRespostasPendentes = ($processosComRespostasPendentes ?? collect())->contains($processo->id);

                    $docStatus = 'sem_info';
                    if ($docs) {
                        if ($docs['completo']) {
                            $docStatus = 'completo';
                        } elseif (($docs['nao_enviado'] ?? 0) > 0 || ($docs['rejeitado'] ?? 0) > 0) {
                            $docStatus = 'nao_enviado';
                        } elseif (($docs['pendente'] ?? 0) > 0) {
                            $docStatus = 'aguardando';
                        }
                    }

                    $statusColor = match($processo->status) {
                        'aberto' => 'blue',
                        'em_analise' => 'amber',
                        'pendente' => 'orange',
                        'concluido' => 'green',
                        'arquivado' => 'gray',
                        'parado' => 'red',
                        default => 'gray',
                    };

                    $borderLeft = match($statusColor) {
                        'blue' => 'border-l-blue-400',
                        'amber' => 'border-l-amber-400',
                        'orange' => 'border-l-orange-400',
                        'green' => 'border-l-green-400',
                        'gray' => 'border-l-gray-300',
                        'red' => 'border-l-red-400',
                        default => 'border-l-gray-300',
                    };

                    $statusDot = match($statusColor) {
                        'blue' => 'bg-blue-400',
                        'amber' => 'bg-amber-400',
                        'orange' => 'bg-orange-400',
                        'green' => 'bg-green-400',
                        'gray' => 'bg-gray-400',
                        'red' => 'bg-red-400',
                        default => 'bg-gray-300',
                    };

                    $statusBadge = match($statusColor) {
                        'blue' => 'bg-blue-50 text-blue-700',
                        'amber' => 'bg-amber-50 text-amber-700',
                        'orange' => 'bg-orange-50 text-orange-700',
                        'green' => 'bg-green-50 text-green-700',
                        'gray' => 'bg-gray-100 text-gray-600',
                        'red' => 'bg-red-50 text-red-700',
                        default => 'bg-gray-100 text-gray-600',
                    };

                    $processoUrl = route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]);
                @endphp

                <a href="{{ $processoUrl }}"
                   class="block bg-white rounded-lg border border-gray-200 {{ $borderLeft }} border-l-[3px] hover:shadow-md hover:border-gray-300 transition-all group">
                    <div class="px-4 py-3">
                        {{-- Linha 1: Número + Status + Tipo --}}
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-sm font-bold text-gray-900 group-hover:text-blue-700 transition shrink-0">
                                    {{ $processo->numero_processo }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-medium rounded-full {{ $statusBadge }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>
                                    {{ $processo->status_nome }}
                                </span>
                                <span class="text-xs text-gray-400 truncate hidden sm:inline">{{ $processo->tipo_nome }}</span>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                @if($prazo)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold
                                        {{ $prazo['atrasado'] ? 'bg-red-50 text-red-700' : ($prazo['dias_restantes'] <= 5 ? 'bg-amber-50 text-amber-700' : 'bg-cyan-50 text-cyan-700') }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        @if($prazo['atrasado'])
                                            {{ abs($prazo['dias_restantes']) }}d atraso
                                        @else
                                            {{ $prazo['dias_restantes'] }}d
                                        @endif
                                    </span>
                                @endif

                                @if($temPendenciaAprovacao)
                                    <span class="w-2 h-2 rounded-full bg-amber-400" title="Aguardando aprovação"></span>
                                @endif
                                @if($temRespostasPendentes)
                                    <span class="w-2 h-2 rounded-full bg-purple-400" title="Respostas pendentes"></span>
                                @endif
                            </div>
                        </div>

                        {{-- Linha 2: Estabelecimento + Docs + Gerência/Técnico --}}
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-gray-700 truncate" title="{{ $processo->estabelecimento->razao_social }}">
                                        {{ $processo->estabelecimento->razao_social }}
                                    </p>
                                    @if(!empty($processo->estabelecimento->nome_fantasia) && $processo->estabelecimento->nome_fantasia !== $processo->estabelecimento->razao_social)
                                        <p class="text-[11px] text-gray-500 truncate" title="{{ $processo->estabelecimento->nome_fantasia }}">
                                            {{ $processo->estabelecimento->nome_fantasia }}
                                        </p>
                                    @endif
                                    @if(!empty($processo->estabelecimento->municipio))
                                        <p class="text-[10px] text-gray-400 truncate" title="{{ $processo->estabelecimento->municipio }}">
                                            {{ $processo->estabelecimento->municipio }}
                                            <span class="mx-1">·</span>
                                            Criado em: {{ $processo->created_at->format('d/m/Y') }}
                                        </p>
                                    @else
                                        <p class="text-[10px] text-gray-400">
                                            Criado em: {{ $processo->created_at->format('d/m/Y') }}
                                        </p>
                                    @endif
                                </div>

                                @if($docs)
                                    <span class="shrink-0 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] font-medium
                                        @if($docStatus === 'completo') bg-green-50 text-green-600
                                        @elseif($docStatus === 'nao_enviado') bg-red-50 text-red-600
                                        @elseif($docStatus === 'aguardando') bg-amber-50 text-amber-600
                                        @else bg-gray-50 text-gray-500
                                        @endif
                                    ">
                                        @if($docStatus === 'completo')
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @endif
                                        {{ $docs['ok'] }}/{{ $docs['total'] }}
                                    </span>
                                @endif
                            </div>

                            <div class="shrink-0 flex items-center gap-3 text-[11px]">
                                @if($processo->setor_atual)
                                    <span class="inline-flex items-center gap-1 text-gray-500" title="Gerência: {{ $processo->setor_atual_nome }}">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        {{ $processo->setor_atual_nome }}
                                    </span>
                                @endif
                                @if($processo->responsavelAtual)
                                    <span class="inline-flex items-center gap-1 text-gray-500" title="Técnico: {{ $processo->responsavelAtual->nome }}">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        {{ $processo->responsavelAtual->nome }}
                                    </span>
                                @elseif($processo->setor_atual)
                                    <span class="text-gray-300 italic">Sem técnico</span>
                                @endif
                                @if(!$processo->setor_atual && !$processo->responsavelAtual)
                                    <span class="text-gray-300 italic">Sem atribuição</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Paginação --}}
        @if($processos->hasPages())
            <div class="mt-5">
                {{ $processos->links('pagination.tailwind-clean') }}
            </div>
        @endif
    @else
        {{-- Sem resultados --}}
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-4 text-base font-semibold text-gray-900">Nenhum processo encontrado</h3>
            <p class="mt-1 text-sm text-gray-500">Tente ajustar os filtros de busca</p>
            <a href="{{ route('admin.processos.index-geral') }}" class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                Limpar filtros
            </a>
        </div>
    @endif
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
