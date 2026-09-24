@extends('layouts.company')

@section('title', 'Meus Estabelecimentos')
@section('page-title', 'Meus Estabelecimentos')

@section('content')
<div class="space-y-4">
    {{-- Mensagem de Sucesso --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200/80 rounded-xl px-4 py-3">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-emerald-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    {{-- Cabeçalho --}}
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-900 tracking-tight leading-tight">Estabelecimentos</h1>
                <p class="text-xs text-slate-400">Gerencie seus estabelecimentos cadastrados</p>
            </div>
        </div>
        <a href="{{ route('company.estabelecimentos.create') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm shadow-blue-600/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Estabelecimento
        </a>
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
        <a href="{{ route('company.estabelecimentos.index', ['status' => 'pendente']) }}"
           class="group flex items-center gap-3 bg-white rounded-xl px-3 py-2.5 border shadow-sm hover:shadow transition-all {{ request('status') === 'pendente' ? 'border-yellow-300 ring-2 ring-yellow-100' : 'border-slate-200/80 hover:border-yellow-200' }}">
            <div class="w-9 h-9 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="leading-tight">
                <p class="text-lg font-bold text-yellow-600 leading-none">{{ $estatisticas['pendentes'] }}</p>
                <p class="text-[11px] font-medium text-slate-500 mt-0.5">Pendentes</p>
            </div>
        </a>
        <a href="{{ route('company.estabelecimentos.index', ['status' => 'aprovado']) }}"
           class="group flex items-center gap-3 bg-white rounded-xl px-3 py-2.5 border shadow-sm hover:shadow transition-all {{ request('status') === 'aprovado' ? 'border-green-300 ring-2 ring-green-100' : 'border-slate-200/80 hover:border-green-200' }}">
            <div class="w-9 h-9 rounded-lg bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="leading-tight">
                <p class="text-lg font-bold text-green-600 leading-none">{{ $estatisticas['aprovados'] }}</p>
                <p class="text-[11px] font-medium text-slate-500 mt-0.5">Aprovados</p>
            </div>
        </a>
        <a href="{{ route('company.estabelecimentos.index', ['status' => 'rejeitado']) }}"
           class="group flex items-center gap-3 bg-white rounded-xl px-3 py-2.5 border shadow-sm hover:shadow transition-all {{ request('status') === 'rejeitado' ? 'border-red-300 ring-2 ring-red-100' : 'border-slate-200/80 hover:border-red-200' }}">
            <div class="w-9 h-9 rounded-lg bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="leading-tight">
                <p class="text-lg font-bold text-red-600 leading-none">{{ $estatisticas['rejeitados'] }}</p>
                <p class="text-[11px] font-medium text-slate-500 mt-0.5">Rejeitados</p>
            </div>
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-2">
        <form method="GET" action="{{ route('company.estabelecimentos.index') }}" class="flex flex-col sm:flex-row gap-2">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Buscar por nome, CNPJ ou CPF..."
                       class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold shadow-sm transition">
                    Buscar
                </button>
                @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('company.estabelecimentos.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 text-sm font-semibold transition">
                    Limpar
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Lista de Estabelecimentos --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        @if($estabelecimentos->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Documento</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Nome/Razão Social</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Cidade</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($estabelecimentos as $estabelecimento)
                    <tr class="group hover:bg-blue-50/40 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-[13px] font-semibold text-slate-800 tabular-nums">{{ $estabelecimento->documento_formatado }}</div>
                            <div class="text-[11px] text-slate-400">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'PJ' : 'PF' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-[13px] font-semibold text-slate-800">{{ $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social ?: $estabelecimento->nome_completo }}</div>
                            @if($estabelecimento->nome_fantasia && $estabelecimento->razao_social)
                            <div class="text-[11px] text-slate-400">{{ $estabelecimento->razao_social }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-[13px] text-slate-600">
                            {{ $estabelecimento->cidade }} - {{ $estabelecimento->estado }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full
                                @if($estabelecimento->status === 'aprovado') bg-green-50 text-green-700
                                @elseif($estabelecimento->status === 'pendente') bg-yellow-50 text-yellow-700
                                @else bg-red-50 text-red-700 @endif">
                                <span class="w-1.5 h-1.5 rounded-full
                                    @if($estabelecimento->status === 'aprovado') bg-green-500
                                    @elseif($estabelecimento->status === 'pendente') bg-yellow-500
                                    @else bg-red-500 @endif"></span>
                                {{ ucfirst($estabelecimento->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}"
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
            {{ $estabelecimentos->links() }}
        </div>
        @else
        <div class="px-6 py-14 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto">
                <svg class="h-7 w-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h3 class="mt-3 text-sm font-semibold text-slate-800">Nenhum estabelecimento encontrado</h3>
            <p class="mt-1 text-sm text-slate-500">Comece cadastrando seu primeiro estabelecimento.</p>
            <div class="mt-5">
                <a href="{{ route('company.estabelecimentos.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Novo Estabelecimento
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
