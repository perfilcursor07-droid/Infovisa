@extends('layouts.admin')

@section('title', 'Estabelecimentos')
@section('page-title', 'Estabelecimentos')

@section('content')
<div class="space-y-4 [&_.text-sm]:text-[13px]">
    {{-- Header com botões --}}
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900 tracking-tight leading-tight">Lista de Estabelecimentos</h2>
                <p class="text-xs text-slate-400">Consulte, filtre e acesse os estabelecimentos cadastrados</p>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.estabelecimentos.create.juridica') }}"
               class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-3.5 py-2 rounded-lg hover:bg-blue-700 text-sm font-semibold shadow-sm shadow-blue-600/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                Pessoa Jurídica
            </a>
            <a href="{{ route('admin.estabelecimentos.create.fisica') }}"
               class="inline-flex items-center gap-1.5 bg-emerald-600 text-white px-3.5 py-2 rounded-lg hover:bg-emerald-700 text-sm font-semibold shadow-sm shadow-emerald-600/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                Pessoa Física
            </a>
        </div>
    </div>

    {{-- Atalhos: Pendentes, Rejeitados e Desativados --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
        <a href="{{ route('admin.estabelecimentos.pendentes') }}"
           class="group flex items-center gap-2.5 bg-white border border-slate-200/80 rounded-xl shadow-sm px-3 py-2.5 hover:shadow hover:border-amber-300 transition-all">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center {{ ($estatisticas['pendentes'] ?? 0) > 0 ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-600' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1 leading-tight">
                <p class="text-sm font-semibold text-slate-800">Pendentes</p>
                <p class="text-[11px] text-slate-500">
                    @if(($estatisticas['pendentes'] ?? 0) > 0)
                        <span class="text-amber-600 font-bold">{{ $estatisticas['pendentes'] }}</span> aguardando aprovação
                    @else
                        Nenhum pendente
                    @endif
                </p>
            </div>
            <svg class="w-4 h-4 text-slate-300 group-hover:text-amber-500 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>

        <a href="{{ route('admin.estabelecimentos.rejeitados') }}"
           class="group flex items-center gap-2.5 bg-white border border-slate-200/80 rounded-xl shadow-sm px-3 py-2.5 hover:shadow hover:border-red-300 transition-all">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center {{ ($estatisticas['rejeitados'] ?? 0) > 0 ? 'bg-red-500 text-white' : 'bg-red-50 text-red-600' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1 leading-tight">
                <p class="text-sm font-semibold text-slate-800">Rejeitados</p>
                <p class="text-[11px] text-slate-500">
                    @if(($estatisticas['rejeitados'] ?? 0) > 0)
                        <span class="text-red-600 font-bold">{{ $estatisticas['rejeitados'] }}</span> para revalidar
                    @else
                        Nenhum rejeitado
                    @endif
                </p>
            </div>
            <svg class="w-4 h-4 text-slate-300 group-hover:text-red-500 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>

        <a href="{{ route('admin.estabelecimentos.desativados') }}"
           class="group flex items-center gap-2.5 bg-white border border-slate-200/80 rounded-xl shadow-sm px-3 py-2.5 hover:shadow hover:border-slate-400 transition-all">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center {{ ($estatisticas['desativados'] ?? 0) > 0 ? 'bg-slate-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1 leading-tight">
                <p class="text-sm font-semibold text-slate-800">Desativados</p>
                <p class="text-[11px] text-slate-500">
                    @if(($estatisticas['desativados'] ?? 0) > 0)
                        <span class="text-slate-700 font-bold">{{ $estatisticas['desativados'] }}</span> desativado{{ ($estatisticas['desativados'] ?? 0) !== 1 ? 's' : '' }}
                    @else
                        Nenhum desativado
                    @endif
                </p>
            </div>
            <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-600 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    {{-- Filtro por Grupo de Risco + Busca --}}
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-3">
        <div class="flex flex-col lg:flex-row gap-3 lg:items-end">
            {{-- Filtros de Risco --}}
            <div class="flex-shrink-0">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Grupo de Risco</label>
                <div class="inline-flex gap-1 p-1 bg-slate-100/80 rounded-xl">
                    <a href="{{ route('admin.estabelecimentos.index', array_merge(request()->except('risco'), [])) }}"
                       class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ !request('risco') ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        Todos
                    </a>
                    <a href="{{ route('admin.estabelecimentos.index', array_merge(request()->except('risco'), ['risco' => 'baixo'])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ request('risco') === 'baixo' ? 'text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}"
                       style="{{ request('risco') === 'baixo' ? 'background-color: #34d399;' : '' }}">
                        <span class="w-2 h-2 rounded-full {{ request('risco') === 'baixo' ? 'bg-white' : '' }}" style="{{ request('risco') === 'baixo' ? '' : 'background-color: #34d399;' }}"></span>
                        Baixo
                    </a>
                    <a href="{{ route('admin.estabelecimentos.index', array_merge(request()->except('risco'), ['risco' => 'medio'])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ request('risco') === 'medio' ? 'text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}"
                       style="{{ request('risco') === 'medio' ? 'background-color: #fbbf24;' : '' }}">
                        <span class="w-2 h-2 rounded-full {{ request('risco') === 'medio' ? 'bg-white' : '' }}" style="{{ request('risco') === 'medio' ? '' : 'background-color: #fbbf24;' }}"></span>
                        Médio
                    </a>
                    <a href="{{ route('admin.estabelecimentos.index', array_merge(request()->except('risco'), ['risco' => 'alto'])) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ request('risco') === 'alto' ? 'text-white shadow-sm' : 'text-slate-500 hover:text-slate-800' }}"
                       style="{{ request('risco') === 'alto' ? 'background-color: #ef4444;' : '' }}">
                        <span class="w-2 h-2 rounded-full {{ request('risco') === 'alto' ? 'bg-white' : '' }}" style="{{ request('risco') === 'alto' ? '' : 'background-color: #ef4444;' }}"></span>
                        Alto
                    </a>
                </div>
            </div>

            {{-- Busca --}}
            <div class="flex-1">
                <form method="GET" action="{{ route('admin.estabelecimentos.index') }}" class="flex gap-2 items-end">
                    <input type="hidden" name="risco" value="{{ request('risco') }}">
                    <div class="flex-1">
                        <label for="search" class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Buscar</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="CNPJ, CPF, Razão Social..."
                                   class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition">
                        </div>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold shadow-sm transition-colors">
                        Buscar
                    </button>
                    @if(request('search') || request('risco'))
                    <a href="{{ route('admin.estabelecimentos.index') }}"
                       class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 text-sm font-semibold transition-colors">
                        Limpar
                    </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Lista de Estabelecimentos --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        @if($estabelecimentos->count() > 0)
            {{-- Info de resultados --}}
            <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
                <p class="text-xs text-slate-500">
                    Exibindo <span class="font-semibold text-slate-700">{{ $estabelecimentos->firstItem() }}</span> a <span class="font-semibold text-slate-700">{{ $estabelecimentos->lastItem() }}</span> de <span class="font-semibold text-slate-700">{{ $estabelecimentos->total() }}</span> resultado{{ $estabelecimentos->total() !== 1 ? 's' : '' }}.
                </p>
            </div>

            {{-- Tabela --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/80 border-b border-slate-100">
                        <tr>
                            <th scope="col" class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                CNPJ/CPF
                            </th>
                            <th scope="col" class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                Razão Social / Nome
                            </th>
                            <th scope="col" class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                Nome Fantasia
                            </th>
                            <th scope="col" class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                Grupo de Risco
                            </th>
                            <th scope="col" class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                Município
                            </th>
                            <th scope="col" class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                Situação
                            </th>
                            <th scope="col" class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($estabelecimentos as $estabelecimento)
                        <tr class="group hover:bg-blue-50/50 transition-colors cursor-pointer {{ !$estabelecimento->ativo ? 'bg-red-50/60' : '' }}"
                            onclick="window.location='{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}'">
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-1.5">
                                    <div>
                                        <div class="font-semibold text-slate-800 tabular-nums">
                                            {{ $estabelecimento->documento_formatado }}
                                        </div>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'Pessoa Jurídica' : 'Pessoa Física' }}
                                        </div>
                                    </div>
                                    @if(!$estabelecimento->ativo)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md bg-red-100 text-red-700">
                                        INATIVO
                                    </span>
                                    @endif
                                    @if($estabelecimento->isCompetenciaEstadual())
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md" style="background-color: #ede9fe; color: #7c3aed;" title="Competência Estadual">
                                        EST.
                                    </span>
                                    @else
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-md" style="background-color: #dbeafe; color: #2563eb;" title="Competência Municipal">
                                        MUN.
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-semibold text-slate-800 group-hover:text-blue-700 transition-colors">
                                    {{ $estabelecimento->nome_razao_social }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                @if($estabelecimento->nome_fantasia)
                                    {{ $estabelecimento->nome_fantasia }}
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="inline-block px-2 py-0.5 text-[11px] font-semibold rounded-md cursor-help"
                                      style="{{ $estabelecimento->grupo_risco_style }}"
                                      title="{{ $estabelecimento->grupo_risco_tooltip }}">
                                    {{ $estabelecimento->grupo_risco_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">
                                {{ $estabelecimento->cidade }} - {{ $estabelecimento->estado }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="px-2 py-0.5 inline-flex text-[11px] leading-5 font-semibold rounded-full {{ $estabelecimento->situacao_cor }}">
                                    {{ $estabelecimento->situacao_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
                                   onclick="event.stopPropagation()"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white transition"
                                   title="Abrir estabelecimento">
                                    Abrir
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginação --}}
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $estabelecimentos->links('pagination.tailwind-clean') }}
            </div>
        @else
            <div class="px-6 py-14 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto">
                    <svg class="h-7 w-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="mt-3 text-sm font-semibold text-slate-800">Nenhum estabelecimento encontrado</h3>
                <p class="mt-1 text-sm text-slate-500">
                    @if(request()->hasAny(['search', 'status']))
                        Tente ajustar os filtros de busca.
                    @else
                        Comece cadastrando um novo estabelecimento.
                    @endif
                </p>
                <div class="mt-5">
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.estabelecimentos.index') }}"
                           class="inline-flex items-center px-4 py-2 shadow-sm text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                            Limpar Filtros
                        </a>
                    @else
                        <a href="{{ route('admin.estabelecimentos.create.juridica') }}"
                           class="inline-flex items-center px-4 py-2 shadow-sm text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                            + Novo Estabelecimento
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
