@extends('layouts.admin')

@section('title', 'Estabelecimentos')
@section('page-title', 'Estabelecimentos')

@section('content')
<div class="space-y-6">
    {{-- Header com botões --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Lista de Estabelecimentos</h2>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.estabelecimentos.create.juridica') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-medium transition-colors">
                + Pessoa Jurídica
            </a>
            <a href="{{ route('admin.estabelecimentos.create.fisica') }}"
               class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-medium transition-colors">
                + Pessoa Física
            </a>
        </div>
    </div>

    {{-- Filtro por Grupo de Risco --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
        <div class="flex flex-col sm:flex-row gap-4">
            {{-- Filtros de Risco --}}
            <div class="flex-shrink-0">
                <label class="block text-xs font-medium text-gray-700 mb-2">Filtrar por Grupo de Risco</label>
                <div class="flex gap-2">
                    <a href="{{ route('admin.estabelecimentos.index', array_merge(request()->except('risco'), [])) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded transition-colors {{ !request('risco') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Todos
                    </a>
                    <a href="{{ route('admin.estabelecimentos.index', array_merge(request()->except('risco'), ['risco' => 'baixo'])) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded transition-colors {{ request('risco') === 'baixo' ? 'text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                       style="{{ request('risco') === 'baixo' ? 'background-color: #34d399;' : '' }}">
                        Baixo
                    </a>
                    <a href="{{ route('admin.estabelecimentos.index', array_merge(request()->except('risco'), ['risco' => 'medio'])) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded transition-colors {{ request('risco') === 'medio' ? 'text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                       style="{{ request('risco') === 'medio' ? 'background-color: #fbbf24;' : '' }}">
                        Médio
                    </a>
                    <a href="{{ route('admin.estabelecimentos.index', array_merge(request()->except('risco'), ['risco' => 'alto'])) }}"
                       class="px-3 py-1.5 text-xs font-medium rounded transition-colors {{ request('risco') === 'alto' ? 'text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                       style="{{ request('risco') === 'alto' ? 'background-color: #ef4444;' : '' }}">
                        Alto
                    </a>
                </div>
            </div>

            {{-- Busca --}}
            <div class="flex-1">
                <form method="GET" action="{{ route('admin.estabelecimentos.index') }}" class="flex gap-2 items-end">
                    <input type="hidden" name="risco" value="{{ request('risco') }}">
                    <div class="flex-1">
                        <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text"
                               id="search"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="CNPJ, CPF, Razão Social..."
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
                        Buscar
                    </button>
                    @if(request('search') || request('risco'))
                    <a href="{{ route('admin.estabelecimentos.index') }}"
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition-colors">
                        Limpar
                    </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Lista de Estabelecimentos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($estabelecimentos->count() > 0)
            {{-- Info de resultados --}}
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <p class="text-sm text-gray-700">
                    Exibindo {{ $estabelecimentos->firstItem() }} a {{ $estabelecimentos->lastItem() }} de {{ $estabelecimentos->total() }} resultado{{ $estabelecimentos->total() !== 1 ? 's' : '' }}.
                </p>
            </div>

            {{-- Tabela --}}
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                CNPJ/CPF
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Razão Social / Nome
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nome Fantasia
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Grupo de Risco
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Município
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Situação
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($estabelecimentos as $estabelecimento)
                        <tr class="hover:bg-blue-50 transition-colors cursor-pointer {{ !$estabelecimento->ativo ? 'bg-red-50' : '' }}" 
                            onclick="window.location='{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}'">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-1.5">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ $estabelecimento->documento_formatado }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'Pessoa Jurídica' : 'Pessoa Física' }}
                                        </div>
                                    </div>
                                    @if(!$estabelecimento->ativo)
                                    <span class="px-1.5 py-0.5 text-xs font-bold rounded bg-red-100 text-red-800">
                                        INATIVO
                                    </span>
                                    @endif
                                    @if($estabelecimento->isCompetenciaEstadual())
                                    <span class="px-1.5 py-0.5 text-xs font-bold rounded" style="background-color: #e9d5ff; color: #7c3aed;" title="Competência Estadual">
                                        EST.
                                    </span>
                                    @else
                                    <span class="px-1.5 py-0.5 text-xs font-bold rounded" style="background-color: #dbeafe; color: #2563eb;" title="Competência Municipal">
                                        MUN.
                                    </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900">
                                    {{ $estabelecimento->nome_razao_social }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if($estabelecimento->nome_fantasia)
                                    {{ $estabelecimento->nome_fantasia }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded transition-all duration-200 cursor-help shadow-sm" 
                                      style="{{ $estabelecimento->grupo_risco_style }}"
                                      title="{{ $estabelecimento->grupo_risco_tooltip }}">
                                    {{ $estabelecimento->grupo_risco_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $estabelecimento->cidade }} - {{ $estabelecimento->estado }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $estabelecimento->situacao_cor }}">
                                    {{ $estabelecimento->situacao_label }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginação --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $estabelecimentos->links('pagination.tailwind-clean') }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum estabelecimento encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'status']))
                        Tente ajustar os filtros de busca.
                    @else
                        Comece cadastrando um novo estabelecimento.
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.estabelecimentos.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Limpar Filtros
                        </a>
                    @else
                        <a href="{{ route('admin.estabelecimentos.create.juridica') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            + Novo Estabelecimento
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
