@extends('layouts.admin')

@section('title', 'Minhas Pendências')

@section('content')
@php
    $totalPendencias = $assinaturas->count() + $processos->count() + $respostas->count() + $ordensServico->count();
@endphp

<div class="space-y-6" x-data="{ tab: 'todas' }">
    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-700">Minhas Pendências</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Minhas Pendências</h1>
            <p class="text-gray-500 mt-1">Tudo que depende de você para avançar</p>
        </div>
    </div>

    {{-- Cards de Resumo --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <button @click="tab = 'todas'" :class="tab === 'todas' ? 'ring-2 ring-indigo-500 border-indigo-200' : 'border-gray-200'"
                class="bg-white rounded-xl border p-3 text-center transition hover:shadow-sm">
            <p class="text-2xl font-bold text-gray-900">{{ $totalPendencias }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total</p>
        </button>
        <button @click="tab = 'assinaturas'" :class="tab === 'assinaturas' ? 'ring-2 ring-amber-500 border-amber-200' : 'border-gray-200'"
                class="bg-white rounded-xl border p-3 text-center transition hover:shadow-sm">
            <p class="text-2xl font-bold text-amber-600">{{ $assinaturas->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">✍️ Assinaturas</p>
        </button>
        <button @click="tab = 'processos'" :class="tab === 'processos' ? 'ring-2 ring-blue-500 border-blue-200' : 'border-gray-200'"
                class="bg-white rounded-xl border p-3 text-center transition hover:shadow-sm">
            <p class="text-2xl font-bold text-blue-600">{{ $processos->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">📋 Processos</p>
        </button>
        <button @click="tab = 'respostas'" :class="tab === 'respostas' ? 'ring-2 ring-orange-500 border-orange-200' : 'border-gray-200'"
                class="bg-white rounded-xl border p-3 text-center transition hover:shadow-sm">
            <p class="text-2xl font-bold text-orange-600">{{ $respostas->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">📎 Respostas</p>
        </button>
        <button @click="tab = 'os'" :class="tab === 'os' ? 'ring-2 ring-purple-500 border-purple-200' : 'border-gray-200'"
                class="bg-white rounded-xl border p-3 text-center transition hover:shadow-sm">
            <p class="text-2xl font-bold text-purple-600">{{ $ordensServico->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">🔧 OS</p>
        </button>
    </div>

    {{-- Assinaturas Pendentes --}}
    <div x-show="tab === 'todas' || tab === 'assinaturas'" x-cloak>
        @if($assinaturas->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-white flex items-center justify-between">
                <h2 class="text-sm font-bold text-amber-800 flex items-center gap-2">
                    ✍️ Documentos Aguardando Assinatura
                    <span class="px-2 py-0.5 bg-amber-200/70 text-amber-800 rounded-full text-xs font-bold">{{ $assinaturas->count() }}</span>
                </h2>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($assinaturas as $ass)
                <a href="{{ $ass['url'] }}" class="flex items-center gap-3 px-5 py-3 hover:bg-amber-50/40 transition group">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-200 transition">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $ass['tipo_documento'] }}</p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ $ass['numero_documento'] }} • {{ $ass['estabelecimento'] }}
                            @if($ass['processo_numero']) • {{ $ass['processo_numero'] }} @endif
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs text-gray-400">{{ $ass['criado_em']?->format('d/m/Y') }}</p>
                        @if($ass['dias_pendente'] > 3)
                            <p class="text-[10px] font-semibold text-red-500">{{ $ass['dias_pendente'] }}d pendente</p>
                        @else
                            <p class="text-[10px] text-gray-400">{{ $ass['dias_pendente'] }}d</p>
                        @endif
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-amber-500 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endforeach
            </div>
        </div>
        @elseif($tab === 'assinaturas')
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
            <p class="text-sm text-gray-500">✅ Nenhuma assinatura pendente</p>
        </div>
        @endif
    </div>

    {{-- Processos sob Responsabilidade --}}
    <div x-show="tab === 'todas' || tab === 'processos'" x-cloak>
        @if($processos->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white flex items-center justify-between">
                <h2 class="text-sm font-bold text-blue-800 flex items-center gap-2">
                    📋 Processos sob Minha Responsabilidade
                    <span class="px-2 py-0.5 bg-blue-200/70 text-blue-800 rounded-full text-xs font-bold">{{ $processos->count() }}</span>
                </h2>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($processos as $proc)
                <a href="{{ $proc['url'] }}" class="flex items-center gap-3 px-5 py-3 hover:bg-blue-50/40 transition group">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-200 transition">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ $proc['numero_processo'] }}
                            <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full {{ $proc['status'] === 'parado' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">{{ ucfirst($proc['status']) }}</span>
                        </p>
                        <p class="text-xs text-gray-500 truncate">{{ $proc['tipo'] }} • {{ $proc['estabelecimento'] }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs text-gray-400">{{ $proc['criado_em']?->format('d/m/Y') }}</p>
                        <p class="text-[10px] {{ $proc['dias_aberto'] > 30 ? 'text-red-500 font-semibold' : 'text-gray-400' }}">{{ $proc['dias_aberto'] }}d aberto</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-500 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endforeach
            </div>
        </div>
        @elseif($tab === 'processos')
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
            <p class="text-sm text-gray-500">✅ Nenhum processo sob sua responsabilidade</p>
        </div>
        @endif
    </div>

    {{-- Respostas Pendentes de Análise --}}
    <div x-show="tab === 'todas' || tab === 'respostas'" x-cloak>
        @if($respostas->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-white flex items-center justify-between">
                <h2 class="text-sm font-bold text-orange-800 flex items-center gap-2">
                    📎 Respostas Pendentes de Análise
                    <span class="px-2 py-0.5 bg-orange-200/70 text-orange-800 rounded-full text-xs font-bold">{{ $respostas->count() }}</span>
                </h2>
                <p class="text-[10px] text-orange-500">Documentos que você assinou e o estabelecimento respondeu</p>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($respostas as $resp)
                <a href="{{ $resp['url'] }}" class="flex items-center gap-3 px-5 py-3 hover:bg-orange-50/40 transition group {{ $resp['atrasado'] ? 'bg-red-50/30' : '' }}">
                    <div class="w-9 h-9 rounded-lg {{ $resp['atrasado'] ? 'bg-red-100' : 'bg-orange-100' }} flex items-center justify-center flex-shrink-0 group-hover:{{ $resp['atrasado'] ? 'bg-red-200' : 'bg-orange-200' }} transition">
                        <svg class="w-4 h-4 {{ $resp['atrasado'] ? 'text-red-600' : 'text-orange-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ $resp['tipo_documento'] }} - {{ $resp['numero_documento'] }}
                            @if($resp['atrasado'])
                                <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-red-100 text-red-700 font-bold">{{ abs($resp['dias_restantes']) }}d atraso</span>
                            @elseif($resp['dias_restantes'] !== null && $resp['dias_restantes'] <= 3)
                                <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 font-bold">{{ $resp['dias_restantes'] }}d</span>
                            @elseif($resp['dias_restantes'] !== null)
                                <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-green-100 text-green-700">{{ $resp['dias_restantes'] }}d</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 truncate">{{ $resp['arquivo'] }} • {{ $resp['estabelecimento'] }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs text-gray-400">{{ $resp['data_resposta']?->format('d/m/Y') }}</p>
                        @if($resp['prazo_analise'])
                            <p class="text-[10px] {{ $resp['atrasado'] ? 'text-red-600 font-semibold' : 'text-gray-400' }}">Limite: {{ $resp['prazo_analise']->format('d/m/Y') }}</p>
                        @endif
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-orange-500 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endforeach
            </div>
        </div>
        @elseif($tab === 'respostas')
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
            <p class="text-sm text-gray-500">✅ Nenhuma resposta pendente de análise</p>
        </div>
        @endif
    </div>

    {{-- Ordens de Serviço --}}
    <div x-show="tab === 'todas' || tab === 'os'" x-cloak>
        @if($ordensServico->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-white flex items-center justify-between">
                <h2 class="text-sm font-bold text-purple-800 flex items-center gap-2">
                    🔧 Ordens de Serviço com Atividades Pendentes
                    <span class="px-2 py-0.5 bg-purple-200/70 text-purple-800 rounded-full text-xs font-bold">{{ $ordensServico->count() }}</span>
                </h2>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($ordensServico as $os)
                <a href="{{ $os['url'] }}" class="flex items-center gap-3 px-5 py-3 hover:bg-purple-50/40 transition group">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0 group-hover:bg-purple-200 transition">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            OS #{{ $os['numero'] }}
                            <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full bg-purple-100 text-purple-700 font-bold">{{ $os['atividades_pendentes'] }} atividade(s)</span>
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ $os['estabelecimento'] }}
                            @if($os['nomes_atividades']) • {{ $os['nomes_atividades'] }} @endif
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-xs text-gray-400">{{ $os['data_abertura']?->format('d/m/Y') }}</p>
                        @if($os['data_fim'])
                            <p class="text-[10px] text-gray-400">Prazo: {{ $os['data_fim']->format('d/m/Y') }}</p>
                        @endif
                    </div>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-purple-500 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endforeach
            </div>
        </div>
        @elseif($tab === 'os')
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
            <p class="text-sm text-gray-500">✅ Nenhuma OS com atividades pendentes</p>
        </div>
        @endif
    </div>

    {{-- Sem pendências --}}
    @if($totalPendencias === 0)
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900">Tudo em dia! 🎉</h3>
        <p class="text-sm text-gray-500 mt-1">Você não possui pendências no momento.</p>
    </div>
    @endif
</div>
@endsection
