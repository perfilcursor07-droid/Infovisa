@extends('layouts.company')

@section('title', 'Meus Processos')
@section('page-title', 'Meus Processos')

@section('content')
<div class="space-y-4">
    {{-- Cabeçalho --}}
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
            <h1 class="text-lg font-bold text-slate-900 tracking-tight leading-tight">Processos</h1>
            <p class="text-xs text-slate-400">Acompanhe os processos dos seus estabelecimentos</p>
        </div>
    </div>

    {{-- Estatísticas --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
        <div class="flex items-center gap-3 bg-white rounded-xl px-3 py-2.5 border border-slate-200/80 shadow-sm">
            <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </div>
            <div class="leading-tight">
                <p class="text-lg font-bold text-slate-900 leading-none">{{ $estatisticas['total'] }}</p>
                <p class="text-[11px] font-medium text-slate-500 mt-0.5">Total</p>
            </div>
        </div>
        <a href="{{ route('company.processos.index', ['status' => 'em_andamento']) }}"
           class="flex items-center gap-3 bg-white rounded-xl px-3 py-2.5 border shadow-sm hover:shadow transition-all {{ request('status') === 'em_andamento' ? 'border-blue-300 ring-2 ring-blue-100' : 'border-slate-200/80 hover:border-blue-200' }}">
            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="leading-tight">
                <p class="text-lg font-bold text-blue-600 leading-none">{{ $estatisticas['em_andamento'] }}</p>
                <p class="text-[11px] font-medium text-slate-500 mt-0.5">Em Andamento</p>
            </div>
        </a>
        <a href="{{ route('company.processos.index', ['status' => 'concluido']) }}"
           class="flex items-center gap-3 bg-white rounded-xl px-3 py-2.5 border shadow-sm hover:shadow transition-all {{ request('status') === 'concluido' ? 'border-green-300 ring-2 ring-green-100' : 'border-slate-200/80 hover:border-green-200' }}">
            <div class="w-9 h-9 rounded-lg bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="leading-tight">
                <p class="text-lg font-bold text-green-600 leading-none">{{ $estatisticas['concluidos'] }}</p>
                <p class="text-[11px] font-medium text-slate-500 mt-0.5">Concluídos</p>
            </div>
        </a>
        <a href="{{ route('company.processos.index', ['status' => 'arquivado']) }}"
           class="flex items-center gap-3 bg-white rounded-xl px-3 py-2.5 border shadow-sm hover:shadow transition-all {{ request('status') === 'arquivado' ? 'border-slate-400 ring-2 ring-slate-100' : 'border-slate-200/80 hover:border-slate-300' }}">
            <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            </div>
            <div class="leading-tight">
                <p class="text-lg font-bold text-slate-600 leading-none">{{ $estatisticas['arquivados'] }}</p>
                <p class="text-[11px] font-medium text-slate-500 mt-0.5">Arquivados</p>
            </div>
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-2">
        <form method="GET" action="{{ route('company.processos.index') }}" class="flex flex-col sm:flex-row gap-2">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Buscar por número do processo..."
                       class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition">
            </div>
            <div>
                <select name="estabelecimento_id" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition">
                    <option value="">Todos os estabelecimentos</option>
                    @foreach($estabelecimentos as $est)
                    <option value="{{ $est->id }}" {{ request('estabelecimento_id') == $est->id ? 'selected' : '' }}>
                        {{ $est->nome_fantasia ?: $est->razao_social ?: $est->nome_completo }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold shadow-sm transition">
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'status', 'estabelecimento_id']))
                <a href="{{ route('company.processos.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 text-sm font-semibold transition">
                    Limpar
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Lista de Processos --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        @if($processos->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Número</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Estabelecimento</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Data</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($processos as $processo)
                    <tr class="group hover:bg-blue-50/40 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-purple-50 text-[13px] font-bold text-purple-700 tabular-nums">{{ $processo->numero_processo }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-[13px] font-medium text-slate-700">{{ $processo->tipoProcesso->nome ?? 'N/A' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-[13px] font-semibold text-slate-800">{{ $processo->estabelecimento->nome_fantasia ?: $processo->estabelecimento->razao_social }}</div>
                            <div class="text-[11px] text-slate-400 tabular-nums">{{ $processo->estabelecimento->documento_formatado }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full
                                @if($processo->status === 'concluido') bg-green-50 text-green-700
                                @elseif($processo->status === 'em_andamento') bg-blue-50 text-blue-700
                                @elseif($processo->status === 'arquivado') bg-slate-100 text-slate-700
                                @else bg-yellow-50 text-yellow-700 @endif">
                                <span class="w-1.5 h-1.5 rounded-full
                                    @if($processo->status === 'concluido') bg-green-500
                                    @elseif($processo->status === 'em_andamento') bg-blue-500
                                    @elseif($processo->status === 'arquivado') bg-slate-400
                                    @else bg-yellow-500 @endif"></span>
                                {{ str_replace('_', ' ', ucfirst($processo->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-[13px] text-slate-500 tabular-nums">
                            {{ $processo->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('company.processos.show', $processo->id) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white transition">
                                Ver detalhes
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $processos->links() }}
        </div>
        @else
        <div class="px-6 py-14 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto">
                <svg class="h-7 w-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="mt-3 text-sm font-semibold text-slate-800">Nenhum processo encontrado</h3>
            <p class="mt-1 text-sm text-slate-500">Os processos serão exibidos aqui quando forem criados para seus estabelecimentos.</p>
        </div>
        @endif
    </div>
</div>
@endsection
