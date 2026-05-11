@extends('layouts.admin')

@section('title', 'Relatório de Processos')

@section('content')
@php
    $usuario = auth('interno')->user();
    $mostrarMunicipio = $usuario->isAdmin();
@endphp
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.relatorios.index') }}" class="hover:text-gray-700">Relatórios</a>
                <span>/</span>
                <span class="text-gray-900">Processos</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Relatório de Processos</h1>
            <p class="text-gray-500 mt-1">Clique no número do processo para abrir os detalhes</p>
        </div>
    </div>

    {{-- Cards Resumo --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Total</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalProcessos, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Abertos</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($porStatus['aberto'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Parados</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($porStatus['parado'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Arquivados</p>
            <p class="text-2xl font-bold text-gray-700 mt-1">{{ number_format($porStatus['arquivado'] ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Banner de filtro por técnico --}}
    @if($tecnicoFiltrado)
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 flex items-start gap-3">
            <svg class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-semibold text-indigo-900">
                    Mostrando processos que estão com {{ $tecnicoFiltrado->nome }}
                </p>
                <p class="text-xs text-indigo-700 mt-0.5">
                    Total: {{ number_format($totalProcessos, 0, ',', '.') }} processo(s). A coluna "Com o técnico" mostra há quanto tempo o processo está sob responsabilidade desse técnico.
                </p>
            </div>
            <a href="{{ route('admin.relatorios.processos', array_merge(request()->except(['tecnico_id', 'page']), [])) }}"
               class="text-xs font-medium text-indigo-700 hover:text-indigo-900 underline whitespace-nowrap">
                Remover filtro
            </a>
        </div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-800">Filtros</h2>
            <span class="text-xs text-gray-500">
                @if($mostrarMunicipio)
                    Use tipo, status, município, técnico e período
                @else
                    Use tipo, status, técnico e período
                @endif
            </span>
        </div>
        <form method="GET" action="{{ route('admin.relatorios.processos') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                <select name="tipo" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($tiposProcesso as $tp)
                        <option value="{{ $tp->codigo }}" @selected(request('tipo') === $tp->codigo)>{{ $tp->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($statusDisponiveis as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Ano</label>
                <select name="ano" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($anos as $ano)
                        <option value="{{ $ano }}" @selected(request('ano') == $ano)>{{ $ano }}</option>
                    @endforeach
                </select>
            </div>

            @if($mostrarMunicipio)
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Município</label>
                    <select name="municipio_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        @foreach($municipios as $mun)
                            <option value="{{ $mun->id }}" @selected(request('municipio_id') == $mun->id)>{{ $mun->nome }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Filtro: Processos com o técnico --}}
            <div class="{{ $mostrarMunicipio ? '' : 'md:col-span-2' }}">
                <label class="block text-xs font-medium text-gray-600 mb-1">Com o técnico</label>
                <select name="tecnico_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    @foreach($tecnicosDisponiveis as $tec)
                        <option value="{{ $tec->id }}" @selected((string) request('tecnico_id') === (string) $tec->id)>
                            {{ $tec->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data inicial</label>
                <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data final</label>
                <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="md:col-span-6 flex items-center gap-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Filtrar
                </button>
                <a href="{{ route('admin.relatorios.processos') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Processo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estabelecimento</th>
                        @if($mostrarMunicipio)
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Município</th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Responsável</th>
                        @if($tecnicoFiltrado)
                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider bg-indigo-50">Com o técnico</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider bg-indigo-50">Descrição da tramitação</th>
                        @endif
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Criado em</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($processos as $processo)
                        @php
                            $statusClass = match($processo->status) {
                                'aberto' => 'bg-blue-100 text-blue-700',
                                'parado' => 'bg-amber-100 text-amber-700',
                                'arquivado' => 'bg-gray-100 text-gray-700',
                                default => 'bg-gray-100 text-gray-700',
                            };

                            // Dados da tramitação atual
                            $comTecnicoDesde = $processo->responsavel_desde ?? optional($processo->ultimoEventoAtribuicao)->created_at;
                            $descricaoTramitacao = optional($processo->ultimoEventoAtribuicao)->descricao;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium">
                                <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}"
                                   class="text-blue-600 hover:text-blue-800 hover:underline transition">
                                    {{ $processo->numero_processo }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $processo->tipo_nome }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <span class="truncate block max-w-[200px]" title="{{ $processo->estabelecimento->razao_social ?? '-' }}">
                                    {{ $processo->estabelecimento->razao_social ?? '-' }}
                                </span>
                                @if(!empty($processo->estabelecimento->nome_fantasia) && $processo->estabelecimento->nome_fantasia !== $processo->estabelecimento->razao_social)
                                    <span class="text-xs text-gray-400 truncate block max-w-[200px]">{{ $processo->estabelecimento->nome_fantasia }}</span>
                                @endif
                            </td>
                            @if($mostrarMunicipio)
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $processo->estabelecimento->municipio ?? '-' }}</td>
                            @endif
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ $processo->status_nome }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $processo->responsavelAtual->nome ?? '-' }}</td>
                            @if($tecnicoFiltrado)
                                <td class="px-4 py-3 text-sm text-gray-700 bg-indigo-50/40">
                                    @if($comTecnicoDesde)
                                        <span class="font-medium text-gray-900">{{ $comTecnicoDesde->diffForHumans(null, \Carbon\CarbonInterface::DIFF_ABSOLUTE) }}</span>
                                        <span class="block text-[11px] text-gray-500">desde {{ $comTecnicoDesde->format('d/m/Y H:i') }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 bg-indigo-50/40 max-w-[280px]">
                                    @if($descricaoTramitacao)
                                        <span class="block text-gray-700 whitespace-pre-wrap">{{ \Illuminate\Support\Str::limit($descricaoTramitacao, 160) }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $processo->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        @php
                            $colSpan = 5 + ($mostrarMunicipio ? 1 : 0) + ($tecnicoFiltrado ? 2 : 0) + 1;
                        @endphp
                        <tr>
                            <td colspan="{{ $colSpan }}" class="px-4 py-8 text-center text-sm text-gray-500">Nenhum processo encontrado para os filtros informados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($processos->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $processos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
