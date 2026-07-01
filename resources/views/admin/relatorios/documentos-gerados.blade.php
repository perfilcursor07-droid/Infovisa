@php
    $layout = ($modoImpressao ?? false) ? 'layouts.print' : 'layouts.admin';
    $totalResultados = $documentos instanceof \Illuminate\Pagination\AbstractPaginator
        ? $documentos->total()
        : $totais['total'];
@endphp
@extends($layout)

@section('title', 'Relatório de Documentos Gerados')

@section('content')
<div class="space-y-5" id="relatorio-documentos-gerados">
    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between">
        <div>
            @unless($modoImpressao ?? false)
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1 no-print">
                <a href="{{ route('admin.relatorios.index') }}" class="hover:text-gray-700">Relatórios</a>
                <span>/</span>
                <span class="text-gray-900">Documentos Gerados</span>
            </div>
            @endunless
            <h1 class="text-2xl font-bold text-gray-900">Documentos Gerados</h1>
            <p class="text-gray-500 text-sm mt-1 flex items-center gap-1.5">
                @unless($modoImpressao ?? false)
                <svg class="w-3.5 h-3.5 shrink-0 no-print" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                @endunless
                {{ $escopoVisual }}
            </p>
            @if($modoImpressao ?? false)
            <p class="text-xs text-gray-500 mt-1">Emitido em {{ now()->format('d/m/Y H:i') }} · {{ number_format($totalResultados, 0, ',', '.') }} registro{{ $totalResultados !== 1 ? 's' : '' }}</p>
            @endif
        </div>
        <div class="flex items-center gap-2 no-print">
            @if($totais['total'] > 0 && !($modoImpressao ?? false))
            <a href="{{ route('admin.relatorios.documentos-gerados', array_merge(request()->except('page'), ['imprimir' => 1])) }}"
               target="_blank"
               rel="noopener"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir
            </a>
            @endif
            @if(($modoImpressao ?? false) && $totais['total'] > 0)
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir
            </button>
            @endif
            @if($totalResultados > 0 && !($modoImpressao ?? false))
            <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-xs font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                {{ number_format($totalResultados, 0, ',', '.') }} resultado{{ $totalResultados > 1 ? 's' : '' }}
            </span>
            @endif
        </div>
    </div>

    @if($modoImpressao ?? false)
    @php
        $filtrosAtivosImpressao = collect([
            request('busca') ? 'Busca: "' . request('busca') . '"' : null,
            request('status') ? 'Status: ' . str_replace('_', ' ', ucfirst(request('status'))) : null,
            $competenciaAtual ? 'Competência: ' . ucfirst($competenciaAtual) : null,
            request('tipo_documento_id') ? 'Tipo: ' . ($tiposDocumento->firstWhere('id', request('tipo_documento_id'))?->nome ?? '-') : null,
            request('subcategoria_id') ? 'Subcategoria: ' . ($subcategorias->firstWhere('id', request('subcategoria_id'))?->nome ?? '-') : null,
            (request('data_inicio') || request('data_fim'))
                ? 'Período: ' . (request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio'))->format('d/m/Y') : '...')
                  . ' a ' . (request('data_fim') ? \Carbon\Carbon::parse(request('data_fim'))->format('d/m/Y') : '...')
                : null,
        ])->filter()->values();
    @endphp
    @if($filtrosAtivosImpressao->isNotEmpty())
    <div class="text-xs text-gray-600 border border-gray-200 rounded-lg px-4 py-2 bg-gray-50">
        <span class="font-semibold text-gray-700">Filtros aplicados:</span>
        {{ $filtrosAtivosImpressao->implode(' · ') }}
    </div>
    @endif
    @endif

    {{-- Cards de resumo --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 no-print">
        <a href="{{ route('admin.relatorios.documentos-gerados', array_merge(request()->except('status', 'page'), [])) }}"
           class="bg-white rounded-xl border-2 transition-all p-4 hover:shadow-md {{ !request('status') ? 'border-blue-500 shadow-md ring-1 ring-blue-200' : 'border-gray-200' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ number_format($totais['total'], 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.relatorios.documentos-gerados', array_merge(request()->except('page'), ['status' => 'assinado'])) }}"
           class="bg-white rounded-xl border-2 transition-all p-4 hover:shadow-md {{ request('status') === 'assinado' ? 'border-green-500 shadow-md ring-1 ring-green-200' : 'border-gray-200' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Assinados</p>
                    <p class="text-2xl font-bold text-green-600 mt-0.5">{{ number_format($totais['assinados'], 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.relatorios.documentos-gerados', array_merge(request()->except('page'), ['status' => 'aguardando_assinatura'])) }}"
           class="bg-white rounded-xl border-2 transition-all p-4 hover:shadow-md {{ request('status') === 'aguardando_assinatura' ? 'border-amber-500 shadow-md ring-1 ring-amber-200' : 'border-gray-200' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Aguardando</p>
                    <p class="text-2xl font-bold text-amber-600 mt-0.5">{{ number_format($totais['aguardando_assinatura'], 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.relatorios.documentos-gerados', array_merge(request()->except('page'), ['status' => 'rascunho'])) }}"
           class="bg-white rounded-xl border-2 transition-all p-4 hover:shadow-md {{ request('status') === 'rascunho' ? 'border-gray-500 shadow-md ring-1 ring-gray-300' : 'border-gray-200' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Rascunhos</p>
                    <p class="text-2xl font-bold text-gray-600 mt-0.5">{{ number_format($totais['rascunhos'], 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
            </div>
        </a>
    </div>

    @if($modoImpressao ?? false)
    <div class="grid grid-cols-4 gap-3 print-summary">
        <div class="border border-gray-200 rounded-lg p-3 text-center">
            <p class="text-[10px] uppercase text-gray-500 font-semibold">Total</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($totais['total'], 0, ',', '.') }}</p>
        </div>
        <div class="border border-gray-200 rounded-lg p-3 text-center">
            <p class="text-[10px] uppercase text-gray-500 font-semibold">Assinados</p>
            <p class="text-xl font-bold text-green-700">{{ number_format($totais['assinados'], 0, ',', '.') }}</p>
        </div>
        <div class="border border-gray-200 rounded-lg p-3 text-center">
            <p class="text-[10px] uppercase text-gray-500 font-semibold">Aguardando</p>
            <p class="text-xl font-bold text-amber-700">{{ number_format($totais['aguardando_assinatura'], 0, ',', '.') }}</p>
        </div>
        <div class="border border-gray-200 rounded-lg p-3 text-center">
            <p class="text-[10px] uppercase text-gray-500 font-semibold">Rascunhos</p>
            <p class="text-xl font-bold text-gray-700">{{ number_format($totais['rascunhos'], 0, ',', '.') }}</p>
        </div>
    </div>
    @endif

    {{-- Barra de busca + filtros avançados colapsáveis --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm no-print" x-data="{ filtrosAbertos: {{ request()->hasAny(['tipo_documento_id', 'subcategoria_id', 'data_inicio', 'data_fim', 'competencia']) ? 'true' : 'false' }} }">
        <form method="GET" action="{{ route('admin.relatorios.documentos-gerados') }}">
            {{-- Manter o status selecionado via cards --}}
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            {{-- Busca principal --}}
            <div class="p-4 flex items-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input
                        type="text"
                        name="busca"
                        value="{{ request('busca') }}"
                        placeholder="Buscar por número, tipo, processo, estabelecimento..."
                        class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                    >
                </div>

                <button type="button"
                        @click="filtrosAbertos = !filtrosAbertos"
                        class="inline-flex items-center gap-1.5 px-3 py-2.5 text-sm font-medium border rounded-lg transition-colors"
                        :class="filtrosAbertos ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filtros
                    @if(request()->hasAny(['tipo_documento_id', 'subcategoria_id', 'data_inicio', 'data_fim', 'competencia']))
                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                    @endif
                </button>

                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Buscar
                </button>

                @if(request()->hasAny(['busca', 'status', 'tipo_documento_id', 'subcategoria_id', 'data_inicio', 'data_fim', 'competencia']))
                    <a href="{{ route('admin.relatorios.documentos-gerados') }}" class="inline-flex items-center gap-1 px-3 py-2.5 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors" title="Limpar filtros">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>

            {{-- Filtros avançados colapsáveis --}}
            <div x-show="filtrosAbertos" x-collapse class="border-t border-gray-100 px-4 pb-4 pt-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    @if($podeEscolherCompetencia)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Competência</label>
                        <select name="competencia" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todas</option>
                            <option value="estadual" @selected($competenciaAtual === 'estadual')>Estadual</option>
                            <option value="municipal" @selected($competenciaAtual === 'municipal')>Municipal</option>
                        </select>
                    </div>
                    @else
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Competência</label>
                        <div class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-medium">
                            {{ $competenciaAtual === 'municipal' ? 'Municipal' : 'Estadual' }}
                        </div>
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tipo de documento</label>
                        <select name="tipo_documento_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                            <option value="">Todos os tipos</option>
                            @foreach($tiposDocumento as $tipoDocumento)
                                <option value="{{ $tipoDocumento->id }}" @selected((string) request('tipo_documento_id') === (string) $tipoDocumento->id)>
                                    {{ $tipoDocumento->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if($subcategorias->count() > 0)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Subcategoria</label>
                        <select name="subcategoria_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todas as subcategorias</option>
                            @foreach($subcategorias as $subcategoria)
                                <option value="{{ $subcategoria->id }}" @selected((string) request('subcategoria_id') === (string) $subcategoria->id)>
                                    {{ $subcategoria->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Data inicial</label>
                        <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Data final</label>
                        <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Filtros ativos (tags) --}}
    @if(request()->hasAny(['busca', 'status', 'tipo_documento_id', 'subcategoria_id', 'data_inicio', 'data_fim', 'competencia']) && !($modoImpressao ?? false))
        <div class="flex flex-wrap items-center gap-2 no-print">
            <span class="text-xs text-gray-500 font-medium">Filtros ativos:</span>
            @if(request('busca'))
                <a href="{{ route('admin.relatorios.documentos-gerados', request()->except(['busca', 'page'])) }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium hover:bg-blue-100 transition-colors">
                    "{{ Str::limit(request('busca'), 20) }}"
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
            @if(request('status'))
                <a href="{{ route('admin.relatorios.documentos-gerados', request()->except(['status', 'page'])) }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium hover:bg-blue-100 transition-colors">
                    {{ str_replace('_', ' ', ucfirst(request('status'))) }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
            @if($podeEscolherCompetencia && request('competencia'))
                <a href="{{ route('admin.relatorios.documentos-gerados', request()->except(['competencia', 'page'])) }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 {{ request('competencia') === 'estadual' ? 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100' : 'bg-teal-50 text-teal-700 hover:bg-teal-100' }} rounded-full text-xs font-medium transition-colors">
                    {{ request('competencia') === 'estadual' ? 'Estadual' : 'Municipal' }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
            @if(request('tipo_documento_id'))
                @php $tipoNome = $tiposDocumento->firstWhere('id', request('tipo_documento_id'))?->nome ?? 'Tipo'; @endphp
                <a href="{{ route('admin.relatorios.documentos-gerados', request()->except(['tipo_documento_id', 'subcategoria_id', 'page'])) }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium hover:bg-blue-100 transition-colors">
                    {{ $tipoNome }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
            @if(request('subcategoria_id'))
                @php $subcatNome = $subcategorias->firstWhere('id', request('subcategoria_id'))?->nome ?? 'Subcategoria'; @endphp
                <a href="{{ route('admin.relatorios.documentos-gerados', request()->except(['subcategoria_id', 'page'])) }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 bg-purple-50 text-purple-700 rounded-full text-xs font-medium hover:bg-purple-100 transition-colors">
                    {{ $subcatNome }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
            @if(request('data_inicio') || request('data_fim'))
                <a href="{{ route('admin.relatorios.documentos-gerados', request()->except(['data_inicio', 'data_fim', 'page'])) }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium hover:bg-blue-100 transition-colors">
                    {{ request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio'))->format('d/m/Y') : '...' }}
                    →
                    {{ request('data_fim') ? \Carbon\Carbon::parse(request('data_fim'))->format('d/m/Y') : '...' }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </div>
    @endif

    {{-- Tabela de resultados --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden print:border-0 print:shadow-none print:rounded-none">
        @if(($modoImpressao ?? false) && $documentos->count() >= 5000)
        <div class="px-4 py-2 bg-amber-50 border-b border-amber-100 text-xs text-amber-800">
            Exibindo os primeiros 5.000 registros. Refine os filtros para um relatório completo.
        </div>
        @endif
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 print:text-xs {{ ($modoImpressao ?? false) ? 'modo-impressao-table' : '' }}">
                <thead>
                    <tr class="bg-gray-50/80 print:bg-gray-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider print:px-2 print:py-2">Documento</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider print:px-2 print:py-2">Processo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell print:table-cell print:px-2 print:py-2">Estabelecimento</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden xl:table-cell print:table-cell print:px-2 print:py-2">Município</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell print:table-cell print:px-2 print:py-2">Competência</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell print:table-cell print:px-2 print:py-2">Criado por</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider print:px-2 print:py-2">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider print:px-2 print:py-2">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($documentos as $documento)
                        @php
                            $processo = $documento->processo;
                            $estabelecimento = $processo?->estabelecimento;
                            $municipio = $estabelecimento?->municipio;
                            $registroExcluido = method_exists($documento, 'trashed') && $documento->trashed();
                            $processoApagado = $processo && method_exists($processo, 'trashed') && $processo->trashed();
                            $registroApagado = !$registroExcluido && (!$processo || !$estabelecimento || $processoApagado);
                            $criador = $documento->usuarioCriador;
                            $competenciaDoc = match (true) {
                                $criador?->isEstadual() => ['label' => 'Estadual', 'class' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20'],
                                $criador?->isMunicipal() => ['label' => 'Municipal', 'class' => 'bg-teal-50 text-teal-700 ring-teal-600/20'],
                                default => ['label' => '-', 'class' => 'bg-gray-50 text-gray-500 ring-gray-500/20'],
                            };
                            $status = $documento->status;
                            $statusConfig = match (true) {
                                $registroExcluido, $registroApagado => ['class' => 'bg-red-50 text-red-700 ring-red-600/20', 'dot' => 'bg-red-500', 'label' => $registroExcluido ? 'Excluído' : 'Apagado'],
                                $status === 'assinado' => ['class' => 'bg-green-50 text-green-700 ring-green-600/20', 'dot' => 'bg-green-500', 'label' => 'Assinado'],
                                $status === 'aguardando_assinatura' => ['class' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'dot' => 'bg-amber-500', 'label' => 'Aguardando'],
                                default => ['class' => 'bg-gray-50 text-gray-600 ring-gray-500/20', 'dot' => 'bg-gray-400', 'label' => 'Rascunho'],
                            };
                        @endphp
                        <tr class="hover:bg-blue-50/30 transition-colors print:hover:bg-transparent">
                            {{-- Documento (número + tipo agrupados) --}}
                            <td class="px-4 py-3 print:px-2 print:py-1.5">
                                <div class="flex flex-col">
                                    @if(!$registroExcluido && !($modoImpressao ?? false))
                                        <a href="{{ route('admin.documentos.show', $documento->id) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline transition">
                                            {{ $documento->numero_documento ?? '-' }}
                                        </a>
                                    @else
                                        <span class="text-sm font-semibold {{ ($modoImpressao ?? false) ? 'text-gray-900' : 'text-gray-400' }}">{{ $documento->numero_documento ?? '-' }}</span>
                                    @endif
                                    <span class="text-xs text-gray-500 mt-0.5 print:text-[10px]">{{ $documento->tipoDocumento->nome ?? $documento->nome ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- Processo --}}
                            <td class="px-4 py-3 text-sm print:px-2 print:py-1.5">
                                @if($processo && $estabelecimento && !($modoImpressao ?? false))
                                    <a href="{{ route('admin.estabelecimentos.processos.show', [$estabelecimento->id, $processo->id]) }}" class="text-blue-600 hover:text-blue-800 hover:underline transition font-medium">
                                        {{ $processo->numero_processo }}
                                    </a>
                                @elseif($processo)
                                    <span class="font-medium text-gray-900">{{ $processo->numero_processo }}</span>
                                @elseif($podeVerApagados)
                                    <span class="text-gray-400 italic text-xs">Apagado</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            {{-- Estabelecimento --}}
                            <td class="px-4 py-3 text-sm text-gray-700 hidden lg:table-cell print:table-cell print:px-2 print:py-1.5">
                                <span class="line-clamp-1" title="{{ $estabelecimento?->nome_fantasia ?? $estabelecimento?->razao_social ?? '' }}">
                                    {{ $estabelecimento?->nome_fantasia ?? $estabelecimento?->razao_social ?? ($podeVerApagados ? 'Apagado' : '-') }}
                                </span>
                            </td>

                            {{-- Município --}}
                            <td class="px-4 py-3 text-sm text-gray-600 hidden xl:table-cell print:table-cell print:px-2 print:py-1.5">
                                {{ $municipio?->nome ?? ($podeVerApagados ? 'Apagado' : '-') }}
                            </td>

                            {{-- Competência --}}
                            <td class="px-4 py-3 hidden lg:table-cell print:table-cell print:px-2 print:py-1.5">
                                @if($competenciaDoc['label'] !== '-')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $competenciaDoc['class'] }}">
                                        {{ $competenciaDoc['label'] }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>

                            {{-- Criado por --}}
                            <td class="px-4 py-3 text-sm text-gray-600 hidden md:table-cell print:table-cell print:px-2 print:py-1.5">
                                <span class="line-clamp-1">{{ $criador?->nome ?? '-' }}</span>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3 print:px-2 print:py-1.5">
                                @if($modoImpressao ?? false)
                                    <span class="text-xs text-gray-700">{{ $statusConfig['label'] }}</span>
                                @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 ring-inset {{ $statusConfig['class'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                                    {{ $statusConfig['label'] }}
                                </span>
                                @endif
                            </td>

                            {{-- Data --}}
                            <td class="px-4 py-3 text-right print:px-2 print:py-1.5">
                                <div class="flex flex-col items-end">
                                    <span class="text-sm text-gray-700 print:text-xs">{{ optional($documento->created_at)->format('d/m/Y') }}</span>
                                    @unless($modoImpressao ?? false)
                                    <span class="text-xs text-gray-400">{{ optional($documento->created_at)->format('H:i') }}</span>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500">Nenhum documento encontrado</p>
                                    <p class="text-xs text-gray-400 mt-1">Tente ajustar os filtros para ver mais resultados</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documentos instanceof \Illuminate\Pagination\AbstractPaginator && $documentos->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 no-print">
                {{ $documentos->links('pagination.tailwind-clean') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .modo-impressao-table th,
    .modo-impressao-table td {
        display: table-cell !important;
    }
    @media print {
        @page { margin: 1cm; size: landscape; }
        .no-print { display: none !important; }
        #relatorio-documentos-gerados { font-size: 11px; }
        #relatorio-documentos-gerados table { page-break-inside: auto; }
        #relatorio-documentos-gerados tr { page-break-inside: avoid; page-break-after: auto; }
        #relatorio-documentos-gerados thead { display: table-header-group; }
    }
</style>
@endpush

@if($modoImpressao ?? false)
@push('scripts')
<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 300);
    });
</script>
@endpush
@endif
