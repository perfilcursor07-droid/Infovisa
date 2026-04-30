@extends('layouts.admin')

@section('title', 'Relatório de Ações por Atividade')

@section('content')
<div class="space-y-5 max-w-[1440px] mx-auto" x-data="{ tab: 'visao-geral' }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-1">
                <a href="{{ route('admin.relatorios.index') }}" class="hover:text-gray-600 transition">Relatórios</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-700 font-medium">Ações por Atividade</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 tracking-tight">Relatório de Ações por Atividade</h1>
            <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                {{ $escopoVisual }}
            </p>
        </div>
        {{-- Tabs de navegação --}}
        <div class="flex bg-gray-100 rounded-lg p-0.5 text-xs font-semibold">
            <button @click="tab = 'visao-geral'" :class="tab === 'visao-geral' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 rounded-md transition">Visão Geral</button>
            @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual())
            <button @click="tab = 'regioes'" :class="tab === 'regioes' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 rounded-md transition">Regiões de Saúde</button>
            @endif
            <button @click="tab = 'municipios'" :class="tab === 'municipios' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 rounded-md transition">Municípios</button>
            <button @click="tab = 'tecnicos'" :class="tab === 'tecnicos' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 rounded-md transition">Técnicos</button>
        </div>
    </div>

    {{-- KPI Hero --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- Card principal: Taxa de conclusão --}}
        <div class="lg:col-span-1 bg-gradient-to-br from-indigo-600 to-violet-700 rounded-xl p-5 text-white shadow-lg shadow-indigo-200/50 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-8 translate-x-8"></div>
            <p class="text-[10px] font-semibold uppercase tracking-widest text-indigo-200">Taxa de Conclusão</p>
            <p class="text-4xl font-black mt-2 tracking-tight">{{ $pctConclusao }}%</p>
            <p class="text-xs text-indigo-200 mt-1">{{ $totalAtivFinalizadas }} de {{ $totalAtividades }} atividades</p>
            <div class="mt-3 bg-white/20 rounded-full h-1.5 overflow-hidden">
                <div class="h-full bg-white rounded-full" style="width: {{ $pctConclusao }}%"></div>
            </div>
        </div>
        {{-- Cards secundários --}}
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm flex flex-col justify-between">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Atividades Realizadas</p>
            <div>
                <p class="text-3xl font-black text-emerald-600 mt-1">{{ number_format($totalAtivFinalizadas, 0, ',', '.') }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">de {{ $totalAtividades }} total</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm flex flex-col justify-between">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Pendentes</p>
            <div>
                <p class="text-3xl font-black text-amber-500 mt-1">{{ number_format($totalAtivPendentes, 0, ',', '.') }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">aguardando execução</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Ordens de Serviço</p>
            <p class="text-3xl font-black text-gray-900 mt-1">{{ number_format($totalOS, 0, ',', '.') }}</p>
            <div class="flex items-center gap-3 mt-2 text-[10px]">
                <span class="flex items-center gap-1 text-indigo-600 font-bold"><span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>{{ $totalEstadual }} est.</span>
                <span class="flex items-center gap-1 text-teal-600 font-bold"><span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>{{ $totalMunicipal }} mun.</span>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <details class="bg-white rounded-xl border border-gray-100 shadow-sm group" {{ request()->hasAny(['competencia','municipio_id','usuario_id','status','atividade_status','data_inicio','data_fim','regiao']) ? 'open' : '' }}>
        <summary class="px-5 py-3 cursor-pointer flex items-center justify-between text-sm font-semibold text-gray-600 select-none">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filtros
                @if(request()->hasAny(['competencia','municipio_id','usuario_id','status','data_inicio','data_fim','regiao']) || request('atividade_status', 'todas') !== 'todas')
                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-indigo-100 text-indigo-700 rounded-full">Ativos</span>
                @endif
            </span>
            <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <div class="px-5 pb-4 pt-2 border-t border-gray-50">
            <form method="GET" action="{{ route('admin.relatorios.acoes-atividade') }}" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-8 gap-3 items-end">
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase mb-1">Atividades</label>
                    <select name="atividade_status" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        <option value="todas" @selected(request('atividade_status', 'todas') === 'todas')>Todas</option>
                        <option value="finalizada" @selected(request('atividade_status') === 'finalizada')>✓ Concluídas</option>
                        <option value="pendente" @selected(request('atividade_status') === 'pendente')>⏳ Pendentes</option>
                    </select>
                </div>
                @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual())
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase mb-1">Competência</label>
                    <select name="competencia" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        <option value="">Todas</option>
                        <option value="estadual" @selected(request('competencia') === 'estadual')>Estadual</option>
                        <option value="municipal" @selected(request('competencia') === 'municipal')>Municipal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase mb-1">Município</label>
                    <select name="municipio_id" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        <option value="">Todos</option>
                        @foreach($municipios as $mun)<option value="{{ $mun->id }}" @selected((string) request('municipio_id') === (string) $mun->id)>{{ $mun->nome }}</option>@endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase mb-1">Técnico</label>
                    <select name="usuario_id" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        <option value="">Todos</option>
                        @foreach($usuarios as $usr)<option value="{{ $usr->id }}" @selected((string) request('usuario_id') === (string) $usr->id)>{{ $usr->nome }}</option>@endforeach
                    </select>
                </div>
                @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual())
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase mb-1">Região de Saúde</label>
                    <select name="regiao" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        <option value="">Todas</option>
                        @foreach($regioesSaudeNomes as $reg)<option value="{{ $reg }}" @selected(request('regiao') === $reg)>{{ $reg }}</option>@endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase mb-1">Status OS</label>
                    <select name="status" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        <option value="">Todos</option>
                        <option value="aberta" @selected(request('status') === 'aberta')>Aberta</option>
                        <option value="em_andamento" @selected(request('status') === 'em_andamento')>Em Andamento</option>
                        <option value="concluida" @selected(request('status') === 'concluida')>Concluída</option>
                        <option value="cancelada" @selected(request('status') === 'cancelada')>Cancelada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase mb-1">De</label>
                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase mb-1">Até</label>
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm shadow-indigo-200">Aplicar</button>
                    <a href="{{ route('admin.relatorios.acoes-atividade') }}" class="px-3 py-2 bg-gray-100 text-gray-400 text-sm rounded-lg hover:bg-gray-200 hover:text-gray-600 transition" title="Limpar filtros">✕</a>
                </div>
            </form>
        </div>
    </details>

    {{-- ===== TAB: VISÃO GERAL ===== --}}
    <div x-show="tab === 'visao-geral'" x-transition>
        {{-- Gráficos Row 1 --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-5">
            <div class="lg:col-span-3 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-4">Evolução Mensal de Atividades</h3>
                <div style="height: 280px;"><canvas id="chartMensal"></canvas></div>
            </div>
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-4">Atividades por Tipo de Ação</h3>
                <div style="height: {{ max(300, count($porTipoAcao) * 40 + 60) }}px;"><canvas id="chartAcoesTipo"></canvas></div>
            </div>
        </div>

        {{-- Top Ações + Tabela detalhada --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Top 5 ações --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-4">Top 5 Ações Mais Executadas</h3>
                <div class="space-y-3">
                    @foreach($topAcoes as $i => $acao)
                    @php $pctAcao = $porTipoAcao->max('total') > 0 ? round(($acao['total'] / $porTipoAcao->max('total')) * 100) : 0; @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-gray-700 font-medium truncate pr-2" title="{{ $acao['nome'] }}">{{ Str::limit($acao['nome'], 45) }}</span>
                            <span class="text-xs font-bold text-gray-900 flex-shrink-0">{{ $acao['total'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500 transition-all" style="width: {{ $pctAcao }}%"></div>
                            </div>
                            <div class="flex gap-1 text-[9px] font-bold flex-shrink-0">
                                <span class="text-emerald-600">{{ $acao['finalizadas'] }}✓</span>
                                @if($acao['pendentes'] > 0)<span class="text-amber-500">{{ $acao['pendentes'] }}⏳</span>@endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($topAcoes->isEmpty())
                    <p class="text-xs text-gray-300 text-center py-6">Nenhuma atividade encontrada</p>
                    @endif
                </div>
            </div>

            {{-- Tabela completa de ações --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Todas as Ações</h3>
                    <span class="text-[10px] text-gray-400">{{ $porTipoAcao->count() }} tipo(s)</span>
                </div>
                <div class="overflow-x-auto max-h-[360px] overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50/80 sticky top-0 z-10">
                            <tr>
                                <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase">Ação</th>
                                <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase w-16">Total</th>
                                <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-emerald-500 uppercase w-16">Conc.</th>
                                <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-amber-500 uppercase w-16">Pend.</th>
                                <th class="px-5 py-2.5 text-[10px] font-semibold text-gray-400 uppercase w-28">Progresso</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($porTipoAcao as $acao)
                            @php $pct = $acao['total'] > 0 ? round(($acao['finalizadas'] / $acao['total']) * 100) : 0; @endphp
                            <tr class="hover:bg-indigo-50/30 transition-colors">
                                <td class="px-5 py-2.5 text-gray-800 font-medium text-xs">{{ $acao['nome'] }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-gray-900">{{ $acao['total'] }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-emerald-600">{{ $acao['finalizadas'] }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-amber-500">{{ $acao['pendentes'] }}</td>
                                <td class="px-5 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-full rounded-full {{ $pct === 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-bold w-8 text-right {{ $pct === 100 ? 'text-emerald-600' : 'text-gray-500' }}">{{ $pct }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-gray-300 text-xs">Nenhuma ação encontrada</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TAB: REGIÕES DE SAÚDE ===== --}}
    @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual())
    <div x-show="tab === 'regioes'" x-transition x-cloak>
        {{-- Gráficos lado a lado: Atividades por Região + Estabelecimentos por Região --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-4">Atividades por Região de Saúde</h3>
                <div style="height: {{ max(300, count($porRegiao) * 44 + 60) }}px;" class="relative">
                    <div id="loadingRegiao" class="absolute inset-0 flex items-center justify-center">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="animate-spin h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span class="text-xs text-gray-400">Carregando...</span>
                        </div>
                    </div>
                    <canvas id="chartRegiao"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-4">Estabelecimentos por Região de Saúde</h3>
                <div style="height: {{ max(300, count($estabelecimentosPorRegiao) * 44 + 60) }}px;" class="relative">
                    <div id="loadingEstabRegiao" class="absolute inset-0 flex items-center justify-center">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="animate-spin h-6 w-6 text-teal-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span class="text-xs text-gray-400">Carregando...</span>
                        </div>
                    </div>
                    <canvas id="chartEstabRegiao"></canvas>
                </div>
            </div>
        </div>

        {{-- Tabela consolidada --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-50">
                <h3 class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Consolidado por Região de Saúde</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase">Região</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase w-24">Estabelec.</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase w-20">Atividades</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-emerald-500 uppercase w-20">Concluídas</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-amber-500 uppercase w-20">Pendentes</th>
                            <th class="px-5 py-2.5 text-[10px] font-semibold text-gray-400 uppercase w-36">Conclusão</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @php
                            $estabMap = collect($estabelecimentosPorRegiao ?? [])->keyBy('nome');
                        @endphp
                        @forelse($porRegiao as $reg)
                        @php
                            $pctReg = $reg['total'] > 0 ? round(($reg['finalizadas'] / $reg['total']) * 100) : 0;
                            $qtdEstab = $estabMap[$reg['nome']]['total'] ?? 0;
                        @endphp
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-5 py-3 text-gray-800 font-semibold">{{ $reg['nome'] }}</td>
                            <td class="px-3 py-3 text-right font-bold text-teal-600">{{ $qtdEstab }}</td>
                            <td class="px-3 py-3 text-right font-bold text-gray-900">{{ $reg['total'] }}</td>
                            <td class="px-3 py-3 text-right font-bold text-emerald-600">{{ $reg['finalizadas'] }}</td>
                            <td class="px-3 py-3 text-right font-bold text-amber-500">{{ $reg['pendentes'] }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                        <div class="h-full rounded-full {{ $pctReg === 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $pctReg }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold w-10 text-right {{ $pctReg === 100 ? 'text-emerald-600' : 'text-gray-600' }}">{{ $pctReg }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-300 text-xs">Nenhum dado encontrado</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== TAB: MUNICÍPIOS ===== --}}
    <div x-show="tab === 'municipios'" x-transition x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-5">
            <div class="lg:col-span-3 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-4">Atividades por Município — Estadual vs Municipal</h3>
                <div style="height: {{ max(240, min(count($porMunicipio), 12) * 32) }}px;" class="relative">
                    <div id="loadingMunicipio" class="absolute inset-0 flex items-center justify-center">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="animate-spin h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span class="text-xs text-gray-400">Carregando gráfico...</span>
                        </div>
                    </div>
                    <canvas id="chartMunicipio"></canvas>
                </div>
            </div>
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-4">Competência</h3>
                <div style="height: 240px;" class="relative">
                    <div id="loadingCompetencia" class="absolute inset-0 flex items-center justify-center">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="animate-spin h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span class="text-xs text-gray-400">Carregando gráfico...</span>
                        </div>
                    </div>
                    <canvas id="chartCompetencia"></canvas>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-50">
                <h3 class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Detalhamento por Município</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase">Município</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase w-16">Total</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-emerald-500 uppercase w-16">Conc.</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-amber-500 uppercase w-16">Pend.</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-indigo-500 uppercase w-16">Est.</th>
                            <th class="px-3 py-2.5 text-right text-[10px] font-semibold text-teal-500 uppercase w-16">Mun.</th>
                            <th class="px-5 py-2.5 text-[10px] font-semibold text-gray-400 uppercase w-28">Conclusão</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($porMunicipio as $mun)
                        @php $pct = $mun['total'] > 0 ? round(($mun['finalizadas'] / $mun['total']) * 100) : 0; @endphp
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-5 py-2.5 text-gray-800 font-medium">{{ $mun['nome'] }}</td>
                            <td class="px-3 py-2.5 text-right font-bold text-gray-900">{{ $mun['total'] }}</td>
                            <td class="px-3 py-2.5 text-right font-bold text-emerald-600">{{ $mun['finalizadas'] }}</td>
                            <td class="px-3 py-2.5 text-right font-bold text-amber-500">{{ $mun['pendentes'] }}</td>
                            <td class="px-3 py-2.5 text-right font-semibold text-indigo-600">{{ $mun['estadual'] }}</td>
                            <td class="px-3 py-2.5 text-right font-semibold text-teal-600">{{ $mun['municipal'] }}</td>
                            <td class="px-5 py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-full rounded-full {{ $pct === 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-bold w-8 text-right {{ $pct === 100 ? 'text-emerald-600' : 'text-gray-500' }}">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-gray-300 text-xs">Nenhum dado encontrado</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== TAB: TÉCNICOS ===== --}}
    <div x-show="tab === 'tecnicos'" x-transition x-cloak>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between">
                <h3 class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Produtividade por Técnico</h3>
                <span class="text-[10px] text-gray-400">{{ $porUsuarioFormatado->count() }} técnico(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-[10px] font-semibold text-gray-400 uppercase">Técnico</th>
                            <th class="px-3 py-3 text-left text-[10px] font-semibold text-gray-400 uppercase w-36">Perfil</th>
                            <th class="px-3 py-3 text-right text-[10px] font-semibold text-gray-400 uppercase w-24">Atribuídas</th>
                            <th class="px-3 py-3 text-right text-[10px] font-semibold text-emerald-500 uppercase w-24">Concluídas</th>
                            <th class="px-3 py-3 text-right text-[10px] font-semibold text-amber-500 uppercase w-24">Pendentes</th>
                            <th class="px-5 py-3 text-[10px] font-semibold text-gray-400 uppercase w-44">Desempenho</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($porUsuarioFormatado as $usr)
                        @php
                            $pct = $usr['total'] > 0 ? round(($usr['finalizadas'] / $usr['total']) * 100) : 0;
                            $pendentes = $usr['total'] - $usr['finalizadas'];
                        @endphp
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                                        {{ strtoupper(substr($usr['nome'], 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-gray-800 text-xs">{{ $usr['nome'] }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-xs text-gray-400">{{ $usr['nivel'] }}</td>
                            <td class="px-3 py-3 text-right font-bold text-gray-900">{{ $usr['total'] }}</td>
                            <td class="px-3 py-3 text-right font-bold text-emerald-600">{{ $usr['finalizadas'] }}</td>
                            <td class="px-3 py-3 text-right font-bold text-amber-500">{{ $pendentes }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                        @if($usr['finalizadas'] > 0)
                                        <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $pct }}%"></div>
                                        @endif
                                    </div>
                                    <span class="text-xs font-bold w-10 text-right {{ $pct === 100 ? 'text-emerald-600' : ($pct >= 50 ? 'text-indigo-600' : 'text-amber-600') }}">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-300 text-xs">Nenhum técnico encontrado</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const P = { indigo:'#6366f1', emerald:'#10b981', amber:'#f59e0b', rose:'#f43f5e', teal:'#14b8a6', violet:'#8b5cf6', sky:'#0ea5e9', orange:'#f97316', cyan:'#06b6d4', lime:'#84cc16', fuchsia:'#d946ef', slate:'#94a3b8' };
    const palette = [P.indigo, P.teal, P.amber, P.rose, P.sky, P.emerald, P.violet, P.orange, P.cyan, P.lime, P.fuchsia, P.slate];

    Chart.defaults.font.family = "'Inter','Segoe UI',system-ui,sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#94a3b8';

    // Evolução Mensal
    const mesData = @json($porMes);
    if (mesData.length > 0) {
        new Chart(document.getElementById('chartMensal'), {
            type: 'bar',
            data: {
                labels: mesData.map(m => { const [y,mo]=m.mes.split('-'); return ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'][parseInt(mo)-1]+'/'+y.slice(2); }),
                datasets: [
                    { label:'Concluídas', data:mesData.map(m=>m.finalizadas), backgroundColor:P.emerald+'cc', borderColor:P.emerald, borderWidth:1, borderRadius:4, barPercentage:0.7 },
                    { label:'Pendentes', data:mesData.map(m=>m.total-m.finalizadas), backgroundColor:P.amber+'88', borderColor:P.amber, borderWidth:1, borderRadius:4, barPercentage:0.7 },
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                interaction:{intersect:false,mode:'index'},
                plugins:{legend:{position:'top',labels:{boxWidth:10,padding:14,font:{size:11}}},tooltip:{backgroundColor:'#1e293b',cornerRadius:8,padding:10}},
                scales:{x:{stacked:true,grid:{display:false}},y:{stacked:true,beginAtZero:true,ticks:{precision:0},grid:{color:'#f1f5f9'}}}
            }
        });
    }

    // Ações por Tipo (horizontal)
    const acoesTipoData = @json($porTipoAcao);
    if (acoesTipoData.length > 0) {
        new Chart(document.getElementById('chartAcoesTipo'), {
            type: 'bar',
            data: {
                labels: acoesTipoData.map(a => a.nome.length>40?a.nome.substring(0,40)+'…':a.nome),
                datasets: [
                    { label:'Concluídas', data:acoesTipoData.map(a=>a.finalizadas), backgroundColor:P.emerald+'cc', borderColor:P.emerald, borderWidth:1, borderRadius:4, barThickness: Math.max(14, Math.min(28, 400 / acoesTipoData.length)) },
                    { label:'Pendentes', data:acoesTipoData.map(a=>a.pendentes), backgroundColor:P.amber+'88', borderColor:P.amber, borderWidth:1, borderRadius:4, barThickness: Math.max(14, Math.min(28, 400 / acoesTipoData.length)) },
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:false, indexAxis:'y',
                plugins:{legend:{position:'top',labels:{boxWidth:10,padding:14,font:{size:11}}},tooltip:{backgroundColor:'#1e293b',cornerRadius:8,padding:10}},
                scales:{x:{stacked:true,beginAtZero:true,ticks:{precision:0},grid:{color:'#f1f5f9'}},y:{stacked:true,grid:{display:false},ticks:{font:{size:11},padding:4}}}
            }
        });
    }

    // Município, Competência e Regiões (init quando aba muda)
    function initMunicipioCharts() {
        const el = document.getElementById('chartMunicipio');
        const compEl = document.getElementById('chartCompetencia');
        if (el && el._chartInit) return;
        if (el) el._chartInit = true;

        // Município
        const munData = @json($porMunicipio->take(15));
        const loadMun = document.getElementById('loadingMunicipio');
        if (loadMun) loadMun.style.display = 'none';
        if (munData.length > 0) {
            new Chart(el, {
                type:'bar',
                data:{
                    labels:munData.map(m=>m.nome),
                    datasets:[
                        {label:'Estadual',data:munData.map(m=>m.estadual),backgroundColor:P.indigo+'cc',borderColor:P.indigo,borderWidth:1,borderRadius:4,barThickness:18},
                        {label:'Municipal',data:munData.map(m=>m.municipal),backgroundColor:P.teal+'cc',borderColor:P.teal,borderWidth:1,borderRadius:4,barThickness:18},
                    ]
                },
                options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{position:'top',labels:{boxWidth:10,padding:14}},tooltip:{backgroundColor:'#1e293b',cornerRadius:8,padding:10}},scales:{x:{stacked:true,beginAtZero:true,ticks:{precision:0},grid:{color:'#f1f5f9'}},y:{stacked:true,grid:{display:false},ticks:{font:{size:10}}}}}
            });
        }

        const loadComp = document.getElementById('loadingCompetencia');
        if (loadComp) loadComp.style.display = 'none';
        if (compEl) {
            new Chart(compEl, {
                type:'doughnut',
                data:{labels:['Estadual','Municipal'],datasets:[{data:[{{ $totalEstadual }},{{ $totalMunicipal }}],backgroundColor:[P.indigo+'cc',P.teal+'cc'],borderWidth:0,hoverOffset:6}]},
                options:{responsive:true,maintainAspectRatio:false,cutout:'68%',plugins:{legend:{position:'bottom',labels:{font:{size:12},padding:16}},tooltip:{backgroundColor:'#1e293b',cornerRadius:8,padding:10}}}
            });
        }
    }

    // Watch para tab de municípios
    const tabButtons = document.querySelectorAll('[\\@click*="municipios"]');
    tabButtons.forEach(btn => btn.addEventListener('click', () => setTimeout(initMunicipioCharts, 100)));
    setTimeout(() => { if (document.getElementById('chartMunicipio')?.offsetParent) initMunicipioCharts(); }, 200);

    // Regiões de Saúde
    function initRegioesCharts() {
        const regEl = document.getElementById('chartRegiao');
        const estRegEl = document.getElementById('chartEstabRegiao');
        if (regEl && regEl._chartInit) return;
        if (regEl) regEl._chartInit = true;

        const regCores = [P.indigo, P.teal, P.emerald, P.amber, P.rose, P.sky, P.violet, P.orange, P.cyan];
        const regData = @json($porRegiao ?? collect());
        const estRegData = @json($estabelecimentosPorRegiao ?? collect());

        // Atividades por Região
        const loadReg = document.getElementById('loadingRegiao');
        if (loadReg) loadReg.style.display = 'none';
        if (regEl && regData.length > 0) {
            new Chart(regEl, {
                type:'bar',
                data:{
                    labels:regData.map(r=>r.nome),
                    datasets:[
                        {label:'Concluídas',data:regData.map(r=>r.finalizadas),backgroundColor:P.emerald+'cc',borderColor:P.emerald,borderWidth:1,borderRadius:4,barThickness:Math.max(18,Math.min(32,300/regData.length))},
                        {label:'Pendentes',data:regData.map(r=>r.pendentes),backgroundColor:P.amber+'88',borderColor:P.amber,borderWidth:1,borderRadius:4,barThickness:Math.max(18,Math.min(32,300/regData.length))},
                    ]
                },
                options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{position:'top',labels:{boxWidth:10,padding:14,font:{size:11}}},tooltip:{backgroundColor:'#1e293b',cornerRadius:8,padding:10}},scales:{x:{stacked:true,beginAtZero:true,ticks:{precision:0},grid:{color:'#f1f5f9'}},y:{stacked:true,grid:{display:false},ticks:{font:{size:11},padding:4}}}}
            });
        }

        // Estabelecimentos por Região
        const loadEstReg = document.getElementById('loadingEstabRegiao');
        if (loadEstReg) loadEstReg.style.display = 'none';
        if (estRegEl && estRegData.length > 0) {
            new Chart(estRegEl, {
                type:'bar',
                data:{
                    labels:estRegData.map(r=>r.nome),
                    datasets:[{
                        label:'Estabelecimentos',
                        data:estRegData.map(r=>r.total),
                        backgroundColor:estRegData.map((_,i)=>regCores[i%regCores.length]+'cc'),
                        borderColor:estRegData.map((_,i)=>regCores[i%regCores.length]),
                        borderWidth:1,borderRadius:4,
                        barThickness:Math.max(18,Math.min(32,300/estRegData.length))
                    }]
                },
                options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:false},tooltip:{backgroundColor:'#1e293b',cornerRadius:8,padding:10}},scales:{x:{beginAtZero:true,ticks:{precision:0},grid:{color:'#f1f5f9'}},y:{grid:{display:false},ticks:{font:{size:11},padding:4}}}}
            });
        }
    }

    const regTabButtons = document.querySelectorAll('[\\@click*="regioes"]');
    regTabButtons.forEach(btn => btn.addEventListener('click', () => setTimeout(initRegioesCharts, 100)));
    setTimeout(() => { if (document.getElementById('chartRegiao')?.offsetParent) initRegioesCharts(); }, 200);
});
</script>
@endsection
