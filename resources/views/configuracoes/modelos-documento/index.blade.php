@extends('layouts.admin')

@section('title', 'Modelos de Documentos')
@section('page-title', 'Modelos de Documentos')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Breadcrumb --}}
    @php
        $usuario = auth('interno')->user();
        $podeAcessarConfiguracoes = $usuario->isAdmin() || $usuario->isGestor();
    @endphp

    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-600">
            @if($podeAcessarConfiguracoes)
                <a href="{{ route('admin.configuracoes.index') }}" class="hover:text-blue-600">Configurações</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            @endif
            <span class="text-gray-900 font-medium">Modelos de Documentos</span>
        </nav>
    </div>

    {{-- Header com botão de adicionar --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-gray-600">Gerencie os modelos de documentos digitais estaduais e municipais.</p>
        </div>
        <a href="{{ route('admin.configuracoes.modelos-documento.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Modelo
        </a>
    </div>

    {{-- Busca --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <label for="busca" class="sr-only">Buscar modelos</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.1-5.15a6.25 6.25 0 11-12.5 0 6.25 6.25 0 0112.5 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           id="busca"
                           name="busca"
                           value="{{ request('busca') }}"
                           placeholder="Buscar por tipo, código, descrição, município ou subcategoria..."
                           class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Buscar
                </button>
                @if(request()->filled('busca'))
                    <a href="{{ route('admin.configuracoes.modelos-documento.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                        Limpar
                    </a>
                @endif
            </div>
        </form>
        @if(request()->filled('busca'))
            <p class="mt-3 text-xs text-gray-500">
                {{ $modelos->total() }} resultado(s) para "{{ request('busca') }}".
            </p>
        @endif
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Lista de Modelos --}}
    @if($modelos->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Documento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Escopo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordem</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($modelos as $modelo)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $modelo->tipoDocumento->nome }}</div>
                                @if($modelo->subcategoria)
                                    <div class="text-xs text-gray-500">Subcategoria: {{ $modelo->subcategoria->nome }}</div>
                                @endif
                                @if($modelo->descricao)
                                    <div class="text-sm text-gray-500">{{ Str::limit($modelo->descricao, 60) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <code class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $modelo->codigo }}</code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $modelo->escopo_label }}</div>
                                @if($modelo->isMunicipal())
                                    <div class="text-xs text-gray-400">{{ $modelo->municipio?->nome ?? 'Município não informado' }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($modelo->ativo)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Ativo</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">Inativo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $modelo->ordem }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.configuracoes.modelos-documento.edit', $modelo->id) }}" 
                                       class="text-blue-600 hover:text-blue-900">Editar</a>
                                    <form action="{{ route('admin.configuracoes.modelos-documento.destroy', $modelo->id) }}" 
                                          method="POST"
                                          onsubmit="return confirm('Tem certeza que deseja remover este modelo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Remover</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($modelos->hasPages())
            <div class="mt-6">
                {{ $modelos->links() }}
            </div>
        @endif
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            @if(request()->filled('busca'))
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum modelo encontrado</h3>
                <p class="text-gray-600 mb-6">Tente buscar por outro termo ou limpe a busca.</p>
                <a href="{{ route('admin.configuracoes.modelos-documento.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Limpar busca
                </a>
            @else
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum modelo cadastrado</h3>
                <p class="text-gray-600 mb-6">Comece criando seu primeiro modelo de documento</p>
                <a href="{{ route('admin.configuracoes.modelos-documento.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Criar Primeiro Modelo
                </a>
            @endif
        </div>
    @endif
</div>
@endsection
