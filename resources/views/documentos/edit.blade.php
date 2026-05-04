@extends('layouts.admin')

@php
    $permiteSalvarRascunho = $documento->status === 'rascunho';
@endphp

@section('title', $permiteSalvarRascunho ? 'Editar Rascunho' : 'Editar Documento')

@php
    // Desativa o assistente IA principal nesta página (já tem o assistente de redação)
    $desativarAssistenteIA = true;
@endphp

@push('styles')
<style>
    /* Variáveis dinâmicas no editor: mesma aparência do texto normal */
    .variavel-dinamica {
        background: transparent !important;
        color: inherit !important;
        font-family: inherit !important;
        font-size: inherit !important;
        padding: 0 !important;
        border-radius: 0 !important;
    }

    /* TinyMCE responsivo */
    .tox-tinymce {
        border-radius: 0 0 0.5rem 0.5rem !important;
    }
    /* Forçar cursor de texto visível dentro do editor */
    .tox-tinymce .tox-edit-area__iframe {
        cursor: text !important;
    }
    .tox .tox-edit-area {
        cursor: text !important;
    }
    .tox .tox-edit-area__iframe {
        cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='18' viewBox='0 0 12 18'%3E%3Cline x1='6' y1='1' x2='6' y2='17' stroke='white' stroke-width='3' stroke-linecap='round'/%3E%3Cline x1='3' y1='1' x2='9' y2='1' stroke='white' stroke-width='3' stroke-linecap='round'/%3E%3Cline x1='3' y1='17' x2='9' y2='17' stroke='white' stroke-width='3' stroke-linecap='round'/%3E%3Cline x1='6' y1='1' x2='6' y2='17' stroke='%23000000' stroke-width='2.5' stroke-linecap='round'/%3E%3Cline x1='3' y1='1' x2='9' y2='1' stroke='%23000000' stroke-width='2.5' stroke-linecap='round'/%3E%3Cline x1='3' y1='17' x2='9' y2='17' stroke='%23000000' stroke-width='2.5' stroke-linecap='round'/%3E%3C/svg%3E") 6 9, text !important;
    }
    .tox .tox-edit-area__iframe {
        background: #fff !important;
    }

    .documento-conteudo-preservado div,
    .documento-conteudo-preservado li,
    .documento-conteudo-preservado td,
    .documento-conteudo-preservado th,
    .documento-conteudo-preservado h1,
    .documento-conteudo-preservado h2,
    .documento-conteudo-preservado h3,
    .documento-conteudo-preservado h4,
    .documento-conteudo-preservado h5,
    .documento-conteudo-preservado h6 {
        white-space: pre-wrap;
        white-space: break-spaces;
        word-break: break-word;
    }

    .documento-conteudo-preservado p,
    .documento-conteudo-preservado .MsoNormal {
        margin: 0 0 0.85rem;
        line-height: 1.45;
        white-space: pre-wrap;
        white-space: break-spaces;
        word-break: break-word;
    }

    .documento-conteudo-preservado .MsoNormal {
        margin-bottom: 1.15rem;
        line-height: 1.6;
    }

    .documento-conteudo-preservado p:last-child,
    .documento-conteudo-preservado .MsoNormal:last-child {
        margin-bottom: 0;
    }

    .documento-conteudo-preservado ul,
    .documento-conteudo-preservado ol {
        margin: 0 0 0.85rem 1.25rem;
        padding-left: 1.25rem;
    }

    .documento-conteudo-preservado li {
        margin-bottom: 0.25rem;
    }
</style>
@endpush

@section('content')
@if(isset($processo))
    <meta name="processo-id" content="{{ $processo->id }}">
    <meta name="estabelecimento-id" content="{{ $processo->estabelecimento_id }}">
@endif

{{-- Script de Edição Colaborativa --}}
<script src="{{ asset('js/edicao-colaborativa.js') }}"></script>

<div class="min-h-screen bg-gray-50" x-data="documentoEditor()">
    <div class="max-w-8xl mx-auto px-4 py-8">
        {{-- Header com Breadcrumb --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
                @if(isset($processo))
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}" class="hover:text-blue-600 transition">
                        Processo {{ $processo->numero_processo }}
                    </a>
                @else
                    <a href="{{ route('admin.documentos.index') }}" class="hover:text-blue-600 transition">
                        Documentos
                    </a>
                @endif
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-medium">{{ $permiteSalvarRascunho ? 'Editar Rascunho' : 'Editar Documento' }}</span>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                {{ $permiteSalvarRascunho ? 'Editar Rascunho' : 'Editar Documento' }}
            </h1>
            
            @if(isset($processo))
                <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="font-medium">{{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social }}</span>
                </div>
            @endif

            {{-- Banner Documento em Lote --}}
            @if($documento->isLote())
            <div class="mt-4 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-purple-900">Documento em Lote — {{ count($documento->processos_ids) }} processos</h4>
                        <p class="text-xs text-purple-700 mt-0.5">
                            Ao finalizar e assinar, este documento será distribuído automaticamente para todos os processos vinculados.
                        </p>
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach($documento->processosLote() as $procLote)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-medium text-purple-700 bg-purple-100 rounded">
                                    {{ $procLote->numero_processo }} — {{ $procLote->estabelecimento->nome_fantasia ?? '' }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            {{-- Aviso sobre edição --}}
            @if($documento->assinaturas->where('status', 'assinado')->count() === 0)
            <div class="mt-4 flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm text-blue-900 font-semibold mb-1">Documento editável</p>
                    <p class="text-sm text-blue-800">
                        Este documento pode ser editado livremente enquanto não houver assinaturas. Após a primeira assinatura, não será mais possível fazer alterações.
                    </p>
                    @if($permiteSalvarRascunho)
                    <p class="text-sm text-blue-700 mt-2">
                        💡 <strong>Dica:</strong> Se ainda não finalizou as edições, salve como <strong>Rascunho</strong> para continuar editando depois.
                    </p>
                    @else
                    <p class="text-sm text-blue-700 mt-2">
                        💡 <strong>Dica:</strong> Este documento já foi finalizado para assinatura. Você pode ajustar o conteúdo antes da primeira assinatura, mas ele continuará no fluxo de assinatura.
                    </p>
                    @endif
                </div>
            </div>
            @else
            <div class="mt-4 flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm text-amber-900 font-semibold mb-1">Documento bloqueado para edição</p>
                    <p class="text-sm text-amber-800">
                        Este documento já possui assinaturas e não pode mais ser editado. Para fazer alterações, será necessário criar um novo documento.
                    </p>
                </div>
            </div>
            @endif
        </div>

    <form id="formDocumentoEdit" method="POST" action="{{ route('admin.documentos.update', $documento->id) }}" @submit="handleSubmit">
        @csrf
        @method('PUT')
        
        {{-- Campo hidden para o conteúdo do editor --}}
        <input type="hidden" name="conteudo" x-model="conteudo" form="formDocumentoEdit">

        {{-- Seção: Tipo de Documento --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-3">
            <div class="px-3 py-2 bg-gradient-to-r from-blue-50 to-white border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <span class="flex items-center justify-center w-4 h-4 bg-blue-600 text-white rounded-full text-xs font-bold">1</span>
                    Tipo de Documento
                </h2>
            </div>
            <div class="p-3">
                <input type="hidden" name="tipo_documento_id" value="{{ $documento->tipo_documento_id }}" form="formDocumentoEdit">
                <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700">
                    {{ $documento->tipoDocumento->nome }}
                </div>
                <p class="text-xs text-amber-600 mt-1.5 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    O tipo de documento não pode ser alterado após a criação
                </p>

                @if(isset($pastasProcesso) && $pastasProcesso->isNotEmpty())
                    @php($pastaSelecionada = old('pasta_id', $documento->pasta_id))
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <label for="pasta_id" class="block text-sm font-medium text-gray-700 mb-2">Pasta do Processo</label>
                        <select name="pasta_id"
                                id="pasta_id"
                                form="formDocumentoEdit"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Todos (sem pasta)</option>
                            @foreach($pastasProcesso as $pasta)
                                <option value="{{ $pasta->id }}" {{ (string) $pastaSelecionada === (string) $pasta->id ? 'selected' : '' }}>
                                    {{ $pasta->nome }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1.5">Opcional: altere a pasta para organizar o documento na listagem do processo.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Seção: Editor de Conteúdo --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-3">
            <div class="px-3 py-2 bg-gradient-to-r from-green-50 to-white border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <span class="flex items-center justify-center w-4 h-4 bg-green-600 text-white rounded-full text-xs font-bold">2</span>
                    Conteúdo do Documento
                </h2>
            </div>
            <div class="p-3">

                <div class="mb-2 flex items-center gap-2 relative" style="min-height: 24px;">
                    <span x-show="salvandoAuto" x-transition.opacity class="text-sm text-green-600 flex items-center gap-1.5 font-medium absolute left-0 top-0">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Salvando...
                    </span>
                </div>

                <!-- Editor TinyMCE -->
                <textarea id="editor-tinymce" style="visibility: hidden;"></textarea>
                <input type="hidden" name="conteudo" x-model="conteudo">

            </div>
        </div>

        {{-- Seção: Histórico de Versões (Completo com Restaurar) --}}
        @if($documento->versoes->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-3" x-data="{ historicoAberto: false }">
            <div class="px-3 py-2 bg-gradient-to-r from-orange-50 to-white border-b border-gray-200 cursor-pointer" @click="historicoAberto = !historicoAberto">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-4 h-4 bg-orange-600 text-white rounded-full text-xs font-bold">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        Histórico de Versões
                        <span class="px-1.5 py-0.5 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">{{ $documento->versoes->count() }}</span>
                    </h2>
                    <svg class="w-4 h-4 text-gray-500 transition-transform" :class="historicoAberto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
            <div x-show="historicoAberto" x-transition>
                <div class="p-3">
                    <div class="space-y-2">
                        @foreach($documento->versoes->sortByDesc('versao') as $versao)
                        <div class="border border-gray-200 rounded-lg p-2.5 hover:bg-gray-50 transition-colors" x-data="{ mostrarConteudo: false }">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded">
                                            Versão {{ $versao->versao }}
                                        </span>
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span class="font-medium text-gray-900">{{ $versao->usuarioInterno->nome }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs text-gray-500">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $versao->created_at->format('d/m/Y H:i') }}
                                            <span class="text-gray-400">•</span>
                                            {{ $versao->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-1.5">
                                    <button type="button" 
                                            @click="mostrarConteudo = !mostrarConteudo"
                                            class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                                        <span x-show="!mostrarConteudo">Ver</span>
                                        <span x-show="mostrarConteudo">Ocultar</span>
                                    </button>
                                    
                                    <form action="{{ route('admin.documentos.restaurarVersao', [$documento->id, $versao->id]) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Tem certeza que deseja restaurar esta versão? O conteúdo atual será substituído.')">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Restaurar
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div x-show="mostrarConteudo" x-transition class="mt-2 pt-2 border-t border-gray-200">
                                <div class="bg-gray-50 rounded p-2 max-h-40 overflow-y-auto text-xs documento-conteudo-preservado" style="font-family: 'Times New Roman', serif;">
                                    {!! $versao->conteudo !!}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Seção: Assinaturas Digitais --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-3">
            <div class="px-3 py-2 bg-gradient-to-r from-purple-50 to-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <span class="flex items-center justify-center w-4 h-4 bg-purple-600 text-white rounded-full text-xs font-bold">3</span>
                        Assinaturas Digitais
                    </h2>
                    <span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Obrigatório</span>
                </div>
            </div>
            <div class="p-3">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-1">
                    @foreach($usuariosInternos as $usuario)
                        <label class="flex items-start p-2 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-300 hover:bg-purple-50 transition-all group">
                            <input type="checkbox" 
                                form="formDocumentoEdit"
                                   name="assinaturas[]" 
                                   value="{{ $usuario->id }}"
                                   {{ $documento->assinaturas->contains('usuario_interno_id', $usuario->id) ? 'checked' : '' }}
                                   class="mt-0.5 h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <div class="ml-2 flex-1">
                                <div class="text-xs font-semibold text-gray-900 group-hover:text-purple-900">
                                    {{ $usuario->nome }}
                                    @if($usuario->id == auth('interno')->id())
                                        <span class="ml-1 px-1.5 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full font-medium">Você</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $usuario->cpf_formatado }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

        {{-- Botões de Ação --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex gap-2 w-full sm:w-auto">
                    @if(isset($processo))
                        <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}" 
                           class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Voltar
                        </a>
                    @else
                        <a href="{{ route('admin.documentos.index') }}" 
                           class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Voltar
                        </a>
                    @endif
                </div>

                <div class="flex gap-3 w-full sm:w-auto">
                    @if($permiteSalvarRascunho)
                    <button type="submit" 
                            form="formDocumentoEdit"
                            name="acao" 
                            value="rascunho"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        Salvar Rascunho
                    </button>
                    @endif
                    
                    <button type="submit" 
                            form="formDocumentoEdit"
                            name="acao" 
                            value="finalizar"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $permiteSalvarRascunho ? 'Finalizar Documento' : 'Salvar Alterações' }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/{{ config('app.tinymce_api_key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
function documentoEditor() {
    return {
        tipoSelecionado: {{ $documento->tipo_documento_id }},
        sigiloso: {{ $documento->sigiloso ? 'true' : 'false' }},
        conteudo: '',
        modelos: [],
        salvandoAuto: false,
        ultimoSalvo: '',
        timeoutSalvar: null,
        contadorErros: 0,
        timeoutVerificacao: null,
        chaveLocalStorage: 'documento_rascunho_{{ request()->get("processo_id", "novo") }}',

        init() {
            const self = this;
            this.tipoSelecionado = {{ $documento->tipo_documento_id ?? 'null' }};
            this.conteudo = {!! json_encode($documento->conteudo) !!};
            
            // Inicializa TinyMCE
            tinymce.init({
                selector: '#editor-tinymce',
                language: 'pt_BR',
                language_url: 'https://cdn.tiny.cloud/1/{{ config('app.tinymce_api_key') }}/tinymce/6/langs/pt_BR.js',
                height: 700,
                min_height: 700,
                resize: false,
                menubar: 'file edit view insert format table',
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount', 'pagebreak',
                    'emoticons', 'nonbreaking'
                ],
                toolbar: [
                    'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor removeformat',
                    'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | pagebreak | spellcheck_btn | fullscreen code help'
                ],
                font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 20pt 24pt 28pt 36pt',
                block_formats: 'Parágrafo=p; Título 1=h1; Título 2=h2; Título 3=h3; Título 4=h4; Título 5=h5; Título 6=h6; Pré-formatado=pre',
                content_style: `
                    body { 
                        font-family: Arial, sans-serif; 
                        font-size: 10pt; 
                        line-height: 1.6; 
                        color: #000; 
                        padding: 15px;
                        margin: 0;
                        cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='18' viewBox='0 0 12 18'%3E%3Cline x1='6' y1='1' x2='6' y2='17' stroke='white' stroke-width='3' stroke-linecap='round'/%3E%3Cline x1='3' y1='1' x2='9' y2='1' stroke='white' stroke-width='3' stroke-linecap='round'/%3E%3Cline x1='3' y1='17' x2='9' y2='17' stroke='white' stroke-width='3' stroke-linecap='round'/%3E%3Cline x1='6' y1='1' x2='6' y2='17' stroke='%23000000' stroke-width='2.5' stroke-linecap='round'/%3E%3Cline x1='3' y1='1' x2='9' y2='1' stroke='%23000000' stroke-width='2.5' stroke-linecap='round'/%3E%3Cline x1='3' y1='17' x2='9' y2='17' stroke='%23000000' stroke-width='2.5' stroke-linecap='round'/%3E%3C/svg%3E") 6 9, text;
                        caret-color: #000;
                    }
                    body * {
                        cursor: inherit;
                    }
                    table { border-collapse: collapse; width: 100%; cursor: text; }
                    table td, table th { border: 1px solid #ddd; padding: 8px; cursor: text; }
                    img { max-width: 100%; height: auto; }
                    .variavel-dinamica {
                        background: transparent !important;
                        color: inherit !important;
                        font-family: inherit !important;
                        font-size: inherit !important;
                        padding: 0 !important;
                    }
                    body div,
                    body li,
                    body td,
                    body th,
                    body h1,
                    body h2,
                    body h3,
                    body h4,
                    body h5,
                    body h6 {
                        white-space: pre-wrap;
                        white-space: break-spaces;
                        word-break: break-word;
                    }
                    body p,
                    body .MsoNormal {
                        margin: 0 0 0.85rem;
                        line-height: 1.45;
                        white-space: pre-wrap;
                        white-space: break-spaces;
                        word-break: break-word;
                    }
                    body .MsoNormal {
                        margin-bottom: 1.15rem;
                        line-height: 1.6;
                    }
                    body p:last-child,
                    body .MsoNormal:last-child {
                        margin-bottom: 0;
                    }
                    body ul,
                    body ol {
                        margin: 0 0 0.85rem 1.25rem;
                        padding-left: 1.25rem;
                    }
                    body li {
                        margin-bottom: 0.25rem;
                    }
                `,
                images_upload_handler: (blobInfo) => {
                    return new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result);
                        reader.readAsDataURL(blobInfo.blob());
                    });
                },
                paste_data_images: true,
                automatic_uploads: true,
                branding: false,
                promotion: false,
                browser_spellcheck: true,
                contextmenu: false,
                statusbar: true,
                elementpath: true,
                setup: (editor) => {
                    // Botão de Correção Ortográfica na toolbar
                    editor.ui.registry.addButton('spellcheck_btn', {
                        icon: 'spell-check',
                        tooltip: 'Verificar Ortografia (PT-BR)',
                        onAction: () => self.verificarOrtografia()
                    });
                    editor.on('init', () => {
                        editor.setContent(self.conteudo || '');
                    });
                    
                    editor.on('input change keyup', () => {
                        self.conteudo = editor.getContent();
                        self.salvarAutomaticamente();
                        self.verificarErrosTempoReal();
                    });
                    
                    window._tinymceEditor = editor;
                }
            });
            
            // Inicializa sistema de edição colaborativa
            this.edicaoColaborativa = new EdicaoColaborativa(
                {{ $documento->id }},
                '{{ auth("interno")->user()->nome }}'
            );
        },

        salvarAutomaticamente() {
            clearTimeout(this.timeoutSalvar);
            this.salvandoAuto = true;
            
            this.timeoutSalvar = setTimeout(() => {
                const dados = {
                    conteudo: this.conteudo,
                    timestamp: Date.now()
                };
                localStorage.setItem(this.chaveLocalStorage, JSON.stringify(dados));
                this.salvandoAuto = false;
                this.ultimoSalvo = 'agora';
            }, 1000);
        },

        tempoDecorrido(timestamp) {
            const segundos = Math.floor((Date.now() - timestamp) / 1000);
            if (segundos < 60) return 'agora';
            const minutos = Math.floor(segundos / 60);
            if (minutos < 60) return minutos + ' min';
            const horas = Math.floor(minutos / 60);
            return horas + ' h';
        },

        inserirImagem(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            if (!file.type.startsWith('image/')) {
                alert('Por favor, selecione apenas arquivos de imagem.');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = (e) => {
                const editor = tinymce.get('editor-tinymce');
                if (editor) {
                    editor.insertContent(`<img src="${e.target.result}" style="max-width: 100%; height: auto; margin: 10px 0;" />`);
                    this.conteudo = editor.getContent();
                    this.salvarAutomaticamente();
                }
            };
            reader.readAsDataURL(file);
            event.target.value = '';
        },

        handlePaste(event) {
            // TinyMCE handles paste natively
        },

        inserirTabela() {
            const editor = tinymce.get('editor-tinymce');
            if (editor) {
                const linhas = prompt('Número de linhas:', '3');
                const colunas = prompt('Número de colunas:', '3');
                
                if (!linhas || !colunas) return;
                
                let tabela = '<table border="1" style="border-collapse: collapse; width: 100%; margin: 10px 0;">';
                for (let i = 0; i < parseInt(linhas); i++) {
                    tabela += '<tr>';
                    for (let j = 0; j < parseInt(colunas); j++) {
                        tabela += '<td style="border: 1px solid #ddd; padding: 8px;">&nbsp;</td>';
                    }
                    tabela += '</tr>';
                }
                tabela += '</table><p>&nbsp;</p>';
                
                editor.insertContent(tabela);
                this.conteudo = editor.getContent();
                this.salvarAutomaticamente();
            }
        },

        limparTudo() {
            if (confirm('Tem certeza que deseja limpar todo o conteúdo? Esta ação não pode ser desfeita.')) {
                const editor = tinymce.get('editor-tinymce');
                if (editor) {
                    editor.setContent('<p><br></p>');
                    this.conteudo = '<p><br></p>';
                    this.salvarAutomaticamente();
                }
            }
        },

        async carregarModelos(tipoId) {
            if (!tipoId) return;
            
            try {
                const response = await fetch(`${window.APP_URL}/admin/documentos/modelos/${tipoId}`);
                
                if (!response.ok) {
                    console.warn('Nenhum modelo encontrado para este tipo de documento');
                    return;
                }
                
                this.modelos = await response.json();
                
                if (this.modelos && this.modelos.length > 0) {
                    this.conteudo = this.modelos[0].conteudo;
                    const editor = tinymce.get('editor-tinymce');
                    if (editor) {
                        editor.setContent(this.conteudo);
                        this.salvarAutomaticamente();
                    }
                }
            } catch (error) {
                console.error('Erro ao carregar modelos:', error);
            }
        },

        contarPalavras() {
            const texto = this.conteudo.replace(/<[^>]*>/g, '').trim();
            return texto.split(/\s+/).filter(word => word.length > 0).length;
        },

        verificarErrosTempoReal() {
            clearTimeout(this.timeoutVerificacao);
            
            this.timeoutVerificacao = setTimeout(async () => {
                const editor = tinymce.get('editor-tinymce');
                if (!editor) return;
                const texto = editor.getContent({ format: 'text' });
                
                if (!texto.trim() || texto.length < 10) {
                    this.contadorErros = 0;
                    return;
                }

                try {
                    const response = await fetch('https://api.languagetool.org/v2/check', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            text: texto,
                            language: 'pt-BR',
                            enabledOnly: 'false'
                        })
                    });

                    const data = await response.json();
                    this.contadorErros = data.matches ? data.matches.length : 0;
                } catch (error) {
                    console.error('Erro na verificação em tempo real:', error);
                    this.contadorErros = 0;
                }
            }, 2000);
        },

        async verificarOrtografia(event = null) {
            const tmceEditor = tinymce.get('editor-tinymce');
            if (!tmceEditor) return;
            const texto = tmceEditor.getContent({ format: 'text' });
            
            if (!texto.trim()) {
                alert('Digite algum texto para verificar a ortografia.');
                return;
            }

            const btnVerificar = event?.target?.closest?.('button') || null;
            const originalHTML = btnVerificar ? btnVerificar.innerHTML : '';
            if (btnVerificar) {
                btnVerificar.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                btnVerificar.disabled = true;
            }

            try {
                const response = await fetch('https://api.languagetool.org/v2/check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        text: texto,
                        language: 'pt-BR',
                        enabledOnly: 'false'
                    })
                });

                const data = await response.json();
                
                if (data.matches && data.matches.length > 0) {
                    let errosHTML = '<div style="max-height: 400px; overflow-y: auto;"><ul style="list-style: none; padding: 0;" id="lista-erros">';
                    
                    data.matches.forEach((erro, index) => {
                        const palavraErrada = texto.substring(erro.offset, erro.offset + erro.length);
                        const sugestoes = erro.replacements.slice(0, 3);
                        
                        errosHTML += `
                            <li id="erro-${index}" style="padding: 12px; margin-bottom: 10px; border-left: 3px solid #ef4444; background: #fef2f2; border-radius: 4px; transition: all 0.3s;">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="flex: 1;">
                                        <strong style="color: #dc2626; font-size: 15px;">${palavraErrada}</strong>
                                        <p style="margin: 5px 0; font-size: 14px; color: #374151;">${erro.message}</p>
                                        ${sugestoes.length > 0 ? `
                                            <div style="margin-top: 8px;">
                                                <p style="margin: 0 0 5px 0; font-size: 12px; color: #6b7280; font-weight: 600;">Clique para substituir:</p>
                                                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                                    ${sugestoes.map(s => `
                                                        <button 
                                                            onclick="substituirPalavra('${palavraErrada.replace(/'/g, "\\'")}', '${s.value.replace(/'/g, "\\'")}', ${index})"
                                                            style="padding: 6px 12px; background: #059669; color: white; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; font-weight: 500; transition: all 0.2s;"
                                                            onmouseover="this.style.background='#047857'"
                                                            onmouseout="this.style.background='#059669'">
                                                            ${s.value}
                                                        </button>
                                                    `).join('')}
                                                </div>
                                            </div>
                                        ` : '<p style="margin: 5px 0; font-size: 13px; color: #6b7280; font-style: italic;">Sem sugestões disponíveis</p>'}
                                    </div>
                                </div>
                            </li>
                        `;
                    });
                    
                    errosHTML += '</ul></div>';
                    
                    const modal = document.createElement('div');
                    modal.id = 'modal-ortografia';
                    modal.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
                    modal.innerHTML = `
                        <div style="background: white; border-radius: 12px; max-width: 650px; width: 90%; max-height: 80vh; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                            <div style="background: linear-gradient(to right, #ef4444, #dc2626); padding: 20px; color: white;">
                                <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Verificação Ortográfica</h3>
                                <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Encontrados <span id="contador-erros">${data.matches.length}</span> possíveis erros</p>
                            </div>
                            <div style="padding: 20px;">
                                ${errosHTML}
                            </div>
                            <div style="padding: 15px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                                <span id="status-correcoes" style="font-size: 13px; color: #059669; font-weight: 500;"></span>
                                <button onclick="this.closest('[style*=fixed]').remove()" style="padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">
                                    Fechar
                                </button>
                            </div>
                        </div>
                    `;
                    
                    window.substituirPalavra = (palavraErrada, sugestao, index) => {
                        const tmceEd = tinymce.get('editor-tinymce');
                        if (!tmceEd) return;
                        let conteudo = tmceEd.getContent();
                        const regex = new RegExp(`\\b${palavraErrada.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'gi');
                        let substituido = false;
                        conteudo = conteudo.replace(regex, (match) => {
                            if (!substituido) {
                                substituido = true;
                                return sugestao;
                            }
                            return match;
                        });
                        tmceEd.setContent(conteudo);
                        this.conteudo = conteudo;
                        this.salvarAutomaticamente();
                        
                        const erroItem = document.getElementById(`erro-${index}`);
                        if (erroItem) {
                            erroItem.style.borderLeftColor = '#059669';
                            erroItem.style.background = '#d1fae5';
                            erroItem.innerHTML = `
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <svg style="width: 24px; height: 24px; color: #059669; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <strong style="color: #065f46;">${palavraErrada}</strong> → <strong style="color: #059669;">${sugestao}</strong>
                                        <p style="margin: 5px 0 0 0; font-size: 13px; color: #047857;">✓ Substituído com sucesso!</p>
                                    </div>
                                </div>
                            `;
                            const errosRestantes = document.querySelectorAll('#lista-erros li[style*="border-left: 3px solid rgb(239, 68, 68)"]').length;
                            document.getElementById('contador-erros').textContent = errosRestantes;
                            const totalCorrigidos = data.matches.length - errosRestantes;
                            document.getElementById('status-correcoes').textContent = 
                                totalCorrigidos > 0 ? `✓ ${totalCorrigidos} correção(ões) aplicada(s)` : '';
                        }
                    };
                    
                    document.body.appendChild(modal);
                    modal.onclick = (e) => {
                        if (e.target === modal) modal.remove();
                    };
                } else {
                    alert('✓ Nenhum erro encontrado! Seu texto está correto.');
                }
            } catch (error) {
                console.error('Erro ao verificar ortografia:', error);
                alert('Erro ao verificar ortografia. Verifique sua conexão com a internet.');
            } finally {
                if (btnVerificar) {
                    btnVerificar.innerHTML = originalHTML;
                    btnVerificar.disabled = false;
                }
            }
        },

        handleSubmit(event) {
            const assinaturas = document.querySelectorAll('input[name="assinaturas[]"]:checked');
            if (assinaturas.length === 0) {
                event.preventDefault();
                alert('Selecione pelo menos um usuário para assinar o documento!');
                return false;
            }
            
            // Sincroniza conteúdo do TinyMCE antes de submeter
            const editor = tinymce.get('editor-tinymce');
            if (editor) {
                this.conteudo = editor.getContent();
            }
            
            // Limpa o localStorage após enviar
            const acao = event.submitter ? event.submitter.value : null;
            if (acao === 'finalizar') {
                localStorage.removeItem(this.chaveLocalStorage);
            }
        }
    }
}
</script>

{{-- Assistente de Redação --}}
@include('components.assistente-edicao-documento-chat')
@endsection
