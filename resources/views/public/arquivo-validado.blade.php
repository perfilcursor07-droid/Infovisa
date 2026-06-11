@extends('layouts.public')

@section('title', 'Arquivo Validado - ' . $documento->nome_original)

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4">
    {{-- Banner de Validação --}}
    <div class="mb-8 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="flex-shrink-0 w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-green-800">Arquivo Aprovado pela Vigilância Sanitária</h2>
                <p class="text-sm text-green-700 mt-0.5">Este arquivo foi conferido e aprovado oficialmente. As informações abaixo confirmam sua autenticidade.</p>
            </div>
        </div>
    </div>

    {{-- Card Principal --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        {{-- Cabeçalho --}}
        <div class="bg-gradient-to-r from-blue-700 to-indigo-700 px-8 py-6">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-blue-200 text-xs font-medium uppercase tracking-wider mb-1">Arquivo do Processo</p>
                    <h1 class="text-2xl font-bold text-white break-words">{{ $documento->tipoDocumentoObrigatorio->nome ?? 'Documento' }}</h1>
                    <div class="flex items-center gap-3 mt-3 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 backdrop-blur-sm rounded-full text-sm font-medium text-white max-w-full">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="truncate">{{ $documento->nome_original }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-500/30 backdrop-blur-sm rounded-full text-sm font-medium text-green-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Aprovado
                        </span>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-blue-200 text-xs font-medium">Enviado em</p>
                    <p class="text-white font-semibold text-sm mt-0.5">{{ $documento->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <div class="p-8 space-y-8">
            {{-- Aprovação --}}
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Verificação</h3>
                <div class="flex items-start gap-4 p-4 bg-green-50 rounded-xl border border-green-100">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900">
                            Arquivo verificado por: {{ mb_strtoupper($documento->aprovadoPor->nome ?? 'Vigilância Sanitária') }}
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            em {{ $documento->aprovado_em?->format('d/m/Y H:i:s') ?? 'N/A' }}
                        </p>
                        @if($documento->hash_arquivo)
                        <p class="text-[11px] text-gray-500 mt-2 break-all">
                            <span class="font-semibold">SHA-256 do arquivo original:</span> {{ $documento->hash_arquivo }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Processo --}}
            @if($documento->processo)
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Processo</h3>
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Tipo do Processo</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $documento->processo->tipoProcesso->nome ?? ($documento->processo->tipo_nome ?? 'N/A') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Número do Processo</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $documento->processo->numero_processo ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Abertura</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $documento->processo->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Estabelecimento --}}
            @if($documento->processo && $documento->processo->estabelecimento)
            @php $estab = $documento->processo->estabelecimento; @endphp
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Estabelecimento</h3>
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Nome Fantasia</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $estab->nome_fantasia ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Razão Social</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $estab->nome_razao_social }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">{{ $estab->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $estab->documento_formatado }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Município</p>
                            <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $estab->cidade }}/{{ $estab->estado }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Ações --}}
            @if($documento->temVersaoCarimbada())
            <div class="flex justify-center pt-2">
                <a href="{{ route('validar.arquivo.pdf', $documento->codigo_validacao) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Visualizar Arquivo Aprovado
                </a>
            </div>
            @endif

            <p class="text-[11px] text-gray-400 text-center">
                Código de validação: <span class="font-mono">{{ $documento->codigo_validacao }}</span>
            </p>
        </div>
    </div>
</div>
@endsection
