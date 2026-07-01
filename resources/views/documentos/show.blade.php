@extends('layouts.admin')

@section('title', 'Visualizar Documento')

@push('styles')
<style>
    .documento-conteudo-preservado p,
    .documento-conteudo-preservado .MsoNormal {
        margin: 0 0 0.85rem;
        line-height: 1.6;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .documento-conteudo-preservado ul,
    .documento-conteudo-preservado ol {
        margin: 0 0 0.85rem 1.25rem;
        padding-left: 1.25rem;
    }
</style>
@endpush

@section('content')
@php
    $documentoPodeEditar = $documento->podeEditar();
    $temAssinaturaFeita = $documento->assinaturas->where('status', 'assinado')->count() > 0;
    $totalAssinaturas = $documento->assinaturas->count();
    $totalAssinaturasFeitas = $documento->assinaturas->where('status', 'assinado')->count();
    $totalAssinaturasPendentes = max($totalAssinaturas - $totalAssinaturasFeitas, 0);
    $podeBaixarPdf = $documento->status !== 'rascunho' && $documento->arquivo_pdf;
    $percentualAssinaturas = $totalAssinaturas > 0
        ? (int) round(($totalAssinaturasFeitas / $totalAssinaturas) * 100)
        : 0;
    $nomeDocumento = $documento->nome ?? $documento->tipoDocumento->nome;
    $processo = $documento->processo;
    $nomeEstabelecimento = $processo?->estabelecimento->nome_fantasia
        ?? $processo?->estabelecimento->razao_social
        ?? 'Sem estabelecimento';

    // Status do documento (cores e labels)
    $statusConfig = match ($documento->status) {
        'rascunho' => ['label' => 'Rascunho', 'bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'icon' => 'far fa-edit'],
        'aguardando_assinatura' => ['label' => 'Aguardando Assinaturas', 'bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => 'fas fa-file-signature'],
        'assinado' => ['label' => 'Assinado', 'bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => 'fas fa-check-circle'],
        default => ['label' => 'Finalizado', 'bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'fas fa-clipboard-check'],
    };

    $usuarioEhAdmin = auth('interno')->user()?->isAdmin() ?? false;
@endphp

<div class="space-y-4">
    {{-- Cabeçalho com breadcrumb --}}
    <div>
        <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
            <a href="{{ route('admin.documentos.index') }}" class="hover:text-gray-700">Documentos</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700">{{ $documento->numero_documento }}</span>
        </div>
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                        <i class="{{ $statusConfig['icon'] }}" style="font-size: 10px;"></i>
                        {{ $statusConfig['label'] }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                        {{ $documento->tipoDocumento->nome }}
                    </span>
                    @if($documento->isFisico())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700" title="Documento físico (entregue em loco)">
                            <i class="fas fa-file-alt" style="font-size: 10px;"></i>
                            Físico
                        </span>
                    @endif
                    @if($documento->sigiloso)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700" title="Documento sigiloso">
                            <i class="fas fa-lock" style="font-size: 9px;"></i>
                            Sigiloso
                        </span>
                    @endif
                </div>
                <h1 class="text-xl font-bold text-gray-900">{{ $nomeDocumento }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $documento->numero_documento }}</p>
            </div>

            {{-- Botões de ação --}}
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" onclick="abrirModalVisualizacao()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Visualizar
                </button>
                @if($documentoPodeEditar)
                    <a href="{{ route('admin.documentos.edit', $documento->id) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Editar
                    </a>
                @endif
                @if($podeBaixarPdf)
                    <a href="{{ route('admin.documentos.pdf', $documento->id) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        PDF
                    </a>
                @endif
                <a href="{{ route('admin.documentos.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 text-gray-600 hover:text-gray-800 text-xs font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Voltar
                </a>
            </div>
        </div>
    </div>

    {{-- Cards de Resumo --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- Assinaturas --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500">Assinaturas</p>
                    @if($totalAssinaturas > 0)
                        <p class="text-lg font-bold text-gray-900">{{ $totalAssinaturasFeitas }}/{{ $totalAssinaturas }}</p>
                        <p class="text-[10px] text-amber-600">{{ $totalAssinaturasPendentes }} pendente(s)</p>
                    @else
                        <p class="text-sm font-bold text-gray-500">Sem fluxo</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Progresso --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-gray-500">Progresso</p>
                    <p class="text-lg font-bold text-gray-900">{{ $percentualAssinaturas }}%</p>
                    <div class="mt-1 h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full transition-all" style="width: {{ $percentualAssinaturas }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Criado por --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500">Criado por</p>
                    <p class="text-sm font-bold text-gray-900 truncate">{{ $documento->usuarioCriador->nome }}</p>
                    <p class="text-[10px] text-gray-400">{{ $documento->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Vínculo --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500">Vínculo</p>
                    @if($documento->isLote())
                        <p class="text-sm font-bold text-gray-900">Lote</p>
                        <p class="text-[10px] text-emerald-600">{{ count($documento->processos_ids) }} processo(s)</p>
                    @elseif($processo)
                        <p class="text-sm font-bold text-gray-900 truncate">Processo</p>
                        <p class="text-[10px] text-emerald-600 truncate">{{ $processo->numero_processo }}</p>
                    @elseif($documento->os_id)
                        <p class="text-sm font-bold text-gray-900">OS</p>
                        <p class="text-[10px] text-emerald-600">#{{ $documento->os_id }}</p>
                    @else
                        <p class="text-sm font-bold text-gray-500">Avulso</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Grid principal: Conteúdo + Sidebar --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Coluna principal --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Fluxo de Assinaturas --}}
            @if($totalAssinaturas > 0)
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <h2 class="text-sm font-bold text-gray-900">Fluxo de Assinatura</h2>
                        <span class="text-xs text-gray-500">{{ $totalAssinaturasFeitas }}/{{ $totalAssinaturas }}</span>
                    </div>
                    @if((!$temAssinaturaFeita || $usuarioEhAdmin) && $documento->status !== 'assinado')
                        <button onclick="abrirModalGerenciarAssinantes()"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-semibold text-amber-700 bg-amber-100 hover:bg-amber-200 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Gerenciar
                        </button>
                    @endif
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($documento->assinaturas as $assinatura)
                        <div class="flex items-center gap-3 px-4 py-2.5">
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                @if($assinatura->usuarioInterno)
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $assinatura->usuarioInterno->nome }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $assinatura->usuarioInterno->cargo ?? 'Cargo não informado' }}</p>
                                @else
                                    <p class="text-sm font-medium text-gray-400">Usuário removido</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($assinatura->status === 'assinado')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[10px] font-semibold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Assinado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-semibold">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Pendente
                                    </span>
                                    @if((!$temAssinaturaFeita || $usuarioEhAdmin) && $documento->status !== 'assinado')
                                        <button onclick="removerAssinante({{ $assinatura->id }})"
                                                class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition" title="Remover">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Distribuição em Lote --}}
            @if($documento->isLote())
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-white">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <h2 class="text-sm font-bold text-gray-900">Distribuição em Lote</h2>
                        <span class="text-xs text-gray-500">{{ count($documento->processos_ids) }} processo(s)</span>
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach($documento->processosLote() as $procLote)
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$procLote->estabelecimento_id, $procLote->id]) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 hover:bg-emerald-50 border border-gray-200 hover:border-emerald-200 rounded-lg text-xs font-medium text-gray-700 transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                {{ $procLote->numero_processo }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-bold text-gray-900">Detalhes</h2>
                </div>
                <dl class="divide-y divide-gray-50 text-sm">
                    <div class="px-4 py-2.5">
                        <dt class="text-[11px] font-semibold uppercase text-gray-500 mb-0.5">Tipo</dt>
                        <dd class="text-gray-900">{{ $documento->tipoDocumento->nome }}</dd>
                    </div>
                    <div class="px-4 py-2.5">
                        <dt class="text-[11px] font-semibold uppercase text-gray-500 mb-0.5">Número</dt>
                        <dd class="text-gray-900 font-mono">{{ $documento->numero_documento }}</dd>
                    </div>
                    <div class="px-4 py-2.5">
                        <dt class="text-[11px] font-semibold uppercase text-gray-500 mb-0.5">Sigiloso</dt>
                        <dd>
                            @if($documento->sigiloso)
                                <span class="inline-flex items-center gap-1 text-red-700 font-semibold">
                                    <i class="fas fa-lock" style="font-size: 10px;"></i> Sim
                                </span>
                            @else
                                <span class="text-gray-500">Não</span>
                            @endif
                        </dd>
                    </div>
                    @if($processo)
                        <div class="px-4 py-2.5">
                            <dt class="text-[11px] font-semibold uppercase text-gray-500 mb-0.5">Processo</dt>
                            <dd>
                                <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}"
                                   class="text-blue-600 hover:underline font-medium">
                                    {{ $processo->numero_processo }}
                                </a>
                                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $nomeEstabelecimento }}</p>
                            </dd>
                        </div>
                    @endif
                    @if($documento->os_id)
                        <div class="px-4 py-2.5">
                            <dt class="text-[11px] font-semibold uppercase text-gray-500 mb-0.5">Ordem de Serviço</dt>
                            <dd>
                                <a href="{{ route('admin.ordens-servico.show', $documento->os_id) }}"
                                   class="text-blue-600 hover:underline font-medium">
                                    OS #{{ $documento->os_id }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if($documento->data_vencimento)
                        <div class="px-4 py-2.5">
                            <dt class="text-[11px] font-semibold uppercase text-gray-500 mb-0.5">Vencimento</dt>
                            <dd class="text-gray-900">{{ $documento->data_vencimento->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    <div class="px-4 py-2.5">
                        <dt class="text-[11px] font-semibold uppercase text-gray-500 mb-0.5">Criado em</dt>
                        <dd class="text-gray-900">{{ $documento->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Ações rápidas --}}
            @if($processo)
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-bold text-gray-900">Ações</h2>
                </div>
                <div class="p-3 space-y-2">
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id]) }}"
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                        <span class="text-xs font-semibold text-gray-700">Abrir processo</span>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('admin.estabelecimentos.show', $processo->estabelecimento_id) }}"
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                        <span class="text-xs font-semibold text-gray-700">Abrir estabelecimento</span>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            @endif
        </aside>
    </div>
</div>

{{-- Modal de Visualização --}}
<div id="modalVisualizacaoDocumento" class="hidden fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full z-50">
    <div class="relative mx-auto my-6 w-11/12 max-w-6xl rounded-2xl bg-white shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 bg-gray-50">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $nomeDocumento }}</h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ $documento->numero_documento }}</p>
            </div>
            <button type="button" onclick="fecharModalVisualizacao()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="bg-white max-h-[82vh] overflow-y-auto">
            @if($documento->status === 'rascunho')
                <div class="p-6">
                    <div class="prose prose-sm max-w-none border border-gray-200 p-4 rounded-xl bg-white shadow-sm documento-conteudo-preservado">
                        {!! $documento->conteudo !!}
                    </div>
                </div>
            @else
                <div class="border-t border-gray-100 bg-gray-100">
                    <iframe src="{{ route('admin.documentos.visualizar-pdf', $documento->id) }}"
                            class="w-full h-[82vh]" frameborder="0"></iframe>
                </div>
            @endif
        </div>
    </div>
</div>

@include('documentos.partials.modal-gerenciar-assinantes')

<script>
function abrirModalVisualizacao() {
    document.getElementById('modalVisualizacaoDocumento').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}
function fecharModalVisualizacao() {
    document.getElementById('modalVisualizacaoDocumento').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}
function abrirModalGerenciarAssinantes() {
    document.getElementById('modalGerenciarAssinantes').classList.remove('hidden');
}
function fecharModalGerenciarAssinantes() {
    document.getElementById('modalGerenciarAssinantes').classList.add('hidden');
}
function removerAssinante(assinaturaId) {
    if (!confirm('Tem certeza que deseja remover este assinante?')) return;
    const endpoint = @json(route('admin.documentos.remover-assinante-post', ['id' => '__ID__'])).replace('__ID__', assinaturaId);
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json().catch(() => ({ success: false, message: 'Erro ao processar resposta.' })))
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao remover assinante.');
        }
    })
    .catch(err => alert('Erro de conexão: ' + err.message));
}

document.getElementById('modalGerenciarAssinantes')?.addEventListener('click', function(e) {
    if (e.target === this) fecharModalGerenciarAssinantes();
});
document.getElementById('modalVisualizacaoDocumento')?.addEventListener('click', function(e) {
    if (e.target === this) fecharModalVisualizacao();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        fecharModalVisualizacao();
        fecharModalGerenciarAssinantes();
    }
});
</script>
@endsection
