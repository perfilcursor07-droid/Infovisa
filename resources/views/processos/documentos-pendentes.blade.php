@extends('layouts.admin')

@section('title', 'Documentos Pendentes de Aprovação')
@section('page-title', 'Documentos Pendentes')

@section('content')
<div class="container-fluid px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Documentos Pendentes de Aprovação</h1>
            <p class="text-sm text-gray-600 mt-1">Documentos enviados por empresas aguardando análise</p>
            <p class="text-xs text-amber-600 mt-1 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Conforme portaria, documentos de <strong>Licenciamento</strong> devem ser analisados em até 5 dias úteis
            </p>
        </div>
        <a href="{{ route('admin.processos.index-geral') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar aos Processos
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('admin.documentos-pendentes.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="estabelecimento" class="block text-sm font-medium text-gray-700 mb-1">Estabelecimento</label>
                <input type="text" id="estabelecimento" name="estabelecimento" value="{{ request('estabelecimento') }}"
                       placeholder="Nome ou CNPJ"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
            </div>
            <div class="w-48">
                <label for="tipo_processo" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Processo</label>
                <select id="tipo_processo" name="tipo_processo"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                    <option value="">Todos</option>
                    @foreach($tiposProcesso as $tipo)
                        <option value="{{ $tipo->codigo }}" {{ request('tipo_processo') == $tipo->codigo ? 'selected' : '' }}>
                            {{ $tipo->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                Filtrar
            </button>
            <a href="{{ route('admin.documentos-pendentes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium">
                Limpar
            </a>
        </form>
    </div>

    <!-- Stats -->
    @php
        $totalAtrasados = 0;
        $totalUrgentes = 0;
        // Prazo de 5 dias aplica-se APENAS a processos de licenciamento
        foreach($documentosPendentes as $doc) {
            if ($doc->processo->tipo === 'licenciamento') {
                $diasPendente = (int) $doc->created_at->diffInDays(now());
                if ($diasPendente > 5) $totalAtrasados++;
                elseif ($diasPendente >= 4) $totalUrgentes++;
            }
        }
        foreach($respostasPendentes as $resp) {
            if ($resp->documentoDigital->processo->tipo === 'licenciamento') {
                $diasPendente = (int) $resp->created_at->diffInDays(now());
                if ($diasPendente > 5) $totalAtrasados++;
                elseif ($diasPendente >= 4) $totalUrgentes++;
            }
        }
    @endphp
    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-purple-100 rounded flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-purple-700">{{ count($documentosPendentes) }}</p>
                    <p class="text-xs text-purple-600">Arquivos</p>
                </div>
            </div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-blue-700">{{ count($respostasPendentes) }}</p>
                    <p class="text-xs text-blue-600">Respostas</p>
                </div>
            </div>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-orange-100 rounded flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-orange-700">{{ $totalUrgentes }}</p>
                    <p class="text-xs text-orange-600">Urgentes</p>
                </div>
            </div>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-red-100 rounded flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-red-700">{{ $totalAtrasados }}</p>
                    <p class="text-xs text-red-600">Atrasados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Documentos -->
    <div class="space-y-6">
        <!-- Arquivos do Processo -->
        @if(count($documentosPendentes) > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-purple-50/50">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Arquivos Enviados no Processo
                </h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($documentosPendentes->sortByDesc(fn($d) => $d->created_at->diffInDays(now())) as $doc)
                @php
                    $isLicenciamento = $doc->processo->tipo === 'licenciamento';
                    $diasPendente = (int) $doc->created_at->diffInDays(now());
                    $diasRestantes = 5 - $diasPendente;
                    // Atrasado/urgente só para licenciamento
                    $atrasado = $isLicenciamento && $diasPendente > 5;
                    $urgente = $isLicenciamento && $diasPendente >= 4 && $diasPendente <= 5;
                @endphp
                <div class="p-4 hover:bg-gray-50 transition-colors {{ $atrasado ? 'bg-red-50/50' : ($urgente ? 'bg-orange-50/50' : '') }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4 min-w-0 flex-1">
                            <div class="flex-shrink-0 w-10 h-10 {{ $atrasado ? 'bg-red-100' : ($urgente ? 'bg-orange-100' : 'bg-purple-100') }} rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 {{ $atrasado ? 'text-red-600' : ($urgente ? 'text-orange-600' : 'text-purple-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] px-1.5 py-0.5 rounded {{ $isLicenciamento ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $doc->processo->tipo_nome }}
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $doc->nome_original }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <span class="font-medium">{{ $doc->processo->estabelecimento->nome_fantasia ?? $doc->processo->estabelecimento->razao_social }}</span>
                                    • Processo {{ $doc->processo->numero_processo }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Enviado por {{ $doc->usuarioExterno->nome ?? 'Usuário' }} em {{ $doc->created_at->format('d/m/Y H:i') }}
                                    • {{ $doc->tamanho_formatado }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            @if($isLicenciamento)
                                @if($atrasado)
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/>
                                        </svg>
                                        {{ $diasPendente - 5 }} dia(s) atrasado
                                    </span>
                                @elseif($urgente)
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-700 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $diasRestantes }} dia(s) restante(s)
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">
                                        {{ $diasRestantes }} dia(s) restante(s)
                                    </span>
                                @endif
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                    Verificar
                                </span>
                            @endif
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$doc->processo->estabelecimento_id, $doc->processo_id]) }}" 
                               class="px-3 py-1.5 {{ $atrasado ? 'bg-red-600 hover:bg-red-700' : 'bg-purple-600 hover:bg-purple-700' }} text-white text-xs font-medium rounded-lg transition">
                                Analisar
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Respostas a Documentos -->
        @if(count($respostasPendentes) > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-blue-50/50">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                    Respostas a Documentos com Prazo
                </h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($respostasPendentes->sortByDesc(fn($r) => $r->created_at->diffInDays(now())) as $resposta)
                @php
                    $isLicenciamento = $resposta->documentoDigital->processo->tipo === 'licenciamento';
                    $diasPendente = (int) $resposta->created_at->diffInDays(now());
                    $diasRestantes = 5 - $diasPendente;
                    // Atrasado/urgente só para licenciamento
                    $atrasado = $isLicenciamento && $diasPendente > 5;
                    $urgente = $isLicenciamento && $diasPendente >= 4 && $diasPendente <= 5;
                @endphp
                <div class="p-4 hover:bg-gray-50 transition-colors {{ $atrasado ? 'bg-red-50/50' : ($urgente ? 'bg-orange-50/50' : '') }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4 min-w-0 flex-1">
                            <div class="flex-shrink-0 w-10 h-10 {{ $atrasado ? 'bg-red-100' : ($urgente ? 'bg-orange-100' : 'bg-blue-100') }} rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 {{ $atrasado ? 'text-red-600' : ($urgente ? 'text-orange-600' : 'text-blue-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] px-1.5 py-0.5 rounded {{ $isLicenciamento ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $resposta->documentoDigital->processo->tipo_nome }}
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $resposta->nome_original }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Resposta para: <span class="font-medium">{{ $resposta->documentoDigital->tipoDocumento->nome ?? 'Documento' }}</span>
                                    ({{ $resposta->documentoDigital->numero_documento }})
                                    @if($resposta->itemAtendimento)
                                        <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold">
                                            Item {{ $resposta->itemAtendimento->ordem }}
                                        </span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <span class="font-medium">{{ $resposta->documentoDigital->processo->estabelecimento->nome_fantasia ?? $resposta->documentoDigital->processo->estabelecimento->razao_social }}</span>
                                    • Processo {{ $resposta->documentoDigital->processo->numero_processo }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Enviado por {{ $resposta->usuarioExterno->nome ?? 'Usuário' }} em {{ $resposta->created_at->format('d/m/Y H:i') }}
                                    • {{ $resposta->tamanho_formatado }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            @if($isLicenciamento)
                                @if($atrasado)
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/>
                                        </svg>
                                        {{ $diasPendente - 5 }} dia(s) atrasado
                                    </span>
                                @elseif($urgente)
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-700 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $diasRestantes }} dia(s) restante(s)
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">
                                        {{ $diasRestantes }} dia(s) restante(s)
                                    </span>
                                @endif
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                    Verificar
                                </span>
                            @endif
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$resposta->documentoDigital->processo->estabelecimento_id, $resposta->documentoDigital->processo_id]) }}" 
                               class="px-3 py-1.5 {{ $atrasado ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-xs font-medium rounded-lg transition">
                                Analisar
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Sem Resultados -->
        @if(count($documentosPendentes) == 0 && count($respostasPendentes) == 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum documento pendente</h3>
            <p class="mt-1 text-sm text-gray-500">Todos os documentos foram analisados.</p>
        </div>
        @endif
    </div>
</div>
@endsection
