@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $isGestorOuAdmin = auth('interno')->user()->isGestor() || auth('interno')->user()->isAdmin();
    $docsAtrasados = 0;
    // Prazo de 5 dias aplica-se APENAS a processos de licenciamento
    foreach($documentos_pendentes_aprovacao ?? [] as $doc) {
        if ($doc->processo && $doc->processo->tipo === 'licenciamento') {
            if ((int) $doc->created_at->diffInDays(now()) > 5) $docsAtrasados++;
        }
    }
    foreach($respostas_pendentes_aprovacao ?? [] as $resp) {
        if ($resp->documentoDigital && $resp->documentoDigital->processo && $resp->documentoDigital->processo->tipo === 'licenciamento') {
            if ((int) $resp->created_at->diffInDays(now()) > 5) $docsAtrasados++;
        }
    }
@endphp

@php
    $countAvisos = (isset($avisos_sistema) ? $avisos_sistema->count() : 0)
        + ((($stats['estabelecimentos_pendentes'] ?? 0) > 0) ? 1 : 0);
    $countAcompanhamento = (isset($processos_acompanhados) ? count($processos_acompanhados) : 0);
    $mostraAvisos = $countAvisos > 0 || auth('interno')->user()->isGestor() || auth('interno')->user()->isAdmin();
    $mostraAcompanhamento = $countAcompanhamento > 0;
@endphp
<div class="space-y-4" x-data="{ tab: localStorage.getItem('dashboardTab') || 'trabalho' }" x-init="$watch('tab', v => localStorage.setItem('dashboardTab', v))">
    {{-- Modal de Data de Nascimento (se não preenchida) --}}
    @if(!auth('interno')->user()->data_nascimento)
    <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            {{-- Modal --}}
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form action="{{ route('admin.perfil.atualizar-nascimento') }}" method="POST">
                    @csrf
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="text-center">
                            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-blue-100 mb-4">
                                <svg class="h-7 w-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900" id="modal-title">
                                Complete seu cadastro
                            </h3>
                            <p class="text-sm text-gray-500 mt-2">
                                Por favor, informe sua data de nascimento para continuar.
                            </p>
                        </div>
                        
                        <div class="mt-5">
                            <label for="data_nascimento_modal" class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Nascimento <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   id="data_nascimento_modal" 
                                   name="data_nascimento" 
                                   required
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-lg">
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-4">
                        <button type="submit" 
                                class="w-full px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Salvar e Continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900">Olá, {{ Str::words(auth('interno')->user()->nome, 1, '') }}!</h1>
            <p class="text-[11px] text-gray-400">{{ now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM') }}</p>
        </div>
        @if(false && $isGestorOuAdmin && $docsAtrasados > 0)
        <a href="{{ route('admin.documentos-pendentes.index') }}" class="flex items-center gap-2 px-3 py-2 bg-red-50 text-red-700 text-sm font-medium rounded-lg hover:bg-red-100 transition">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $docsAtrasados }} {{ $docsAtrasados == 1 ? 'documento atrasado' : 'documentos atrasados' }}
        </a>
        @endif
    </div>

    {{-- Aniversariantes do Dia (fora das abas) --}}
    @if(isset($aniversariantes_mes) && $aniversariantes_mes->count() > 0)
    @php
        $hojeDiaMesBanner = now()->format('d/m');
        $aniversariantesHojeBanner = $aniversariantes_mes->filter(function($anv) use ($hojeDiaMesBanner) {
            return (bool)($anv->eh_hoje ?? false) || (!empty($anv->dia_aniversario) && $anv->dia_aniversario === $hojeDiaMesBanner);
        });
    @endphp
    @if($aniversariantesHojeBanner->count() > 0)
    <div class="bg-gradient-to-r from-pink-50 to-rose-50 rounded-xl border border-pink-200 p-3 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-pink-500 flex items-center justify-center flex-shrink-0">
            <span class="text-white text-sm">🎂</span>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-pink-800">
                🎉 {{ $aniversariantesHojeBanner->map(fn($a) => \Str::words($a->nome, 2, ''))->implode(', ') }} faz{{ $aniversariantesHojeBanner->count() > 1 ? 'em' : '' }} aniversário hoje!
            </p>
        </div>
    </div>
    @endif
    @endif

    {{-- ============================ --}}
    {{-- BARRA DE ABAS --}}
    {{-- ============================ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="flex items-stretch overflow-x-auto scrollbar-thin">
            <button type="button" @click="tab = 'trabalho'"
                :class="tab === 'trabalho' ? 'border-blue-500 text-blue-600 bg-blue-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Meu Painel
                <span x-show="$store.dashboard.trabalhoTotal > 0" class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold" x-text="$store.dashboard.trabalhoTotal"></span>
            </button>

            @if($mostraAvisos)
            <button type="button" @click="tab = 'avisos'"
                :class="tab === 'avisos' ? 'border-amber-500 text-amber-700 bg-amber-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                Avisos
                <span x-show="$store.dashboard.avisosTotal > 0" class="text-[10px] px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 font-bold" x-text="$store.dashboard.avisosTotal"></span>
            </button>
            @endif

            @if($mostraAcompanhamento)
            <button type="button" @click="tab = 'acompanhamento'"
                :class="tab === 'acompanhamento' ? 'border-indigo-500 text-indigo-600 bg-indigo-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Acompanhamento
                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-bold">{{ $countAcompanhamento }}</span>
            </button>
            @endif
        </div>
    </div>

    {{-- ============================ --}}
    {{-- ABA: AVISOS --}}
    {{-- ============================ --}}
    <div x-show="tab === 'avisos'" x-cloak class="space-y-3">

    {{-- Avisos do Sistema --}}
    @if(isset($avisos_sistema) && $avisos_sistema->count() > 0)
    <div class="space-y-2">
        @foreach($avisos_sistema as $aviso)
        <div class="flex items-start gap-3 p-3 rounded-lg border {{ $aviso->tipo_color }}">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $aviso->tipo_icone }}"/>
            </svg>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium">{{ $aviso->titulo }}</p>
                <p class="text-xs mt-0.5 opacity-80">{{ $aviso->mensagem }}</p>
                @if($aviso->link)
                <a href="{{ $aviso->link }}" target="_blank" class="inline-flex items-center gap-1 text-xs mt-1 underline hover:opacity-80">
                    {{ $aviso->link }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Card de OSs Vencidas (apenas para gestores e admins) --}}
    @if(auth('interno')->user()->isGestor() || auth('interno')->user()->isAdmin())
    <div x-data="ordensServicoVencidas()" x-show="ordens.length > 0" x-cloak class="space-y-2">
        <button type="button"
                @click="aberto = !aberto"
                class="w-full flex items-center gap-3 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition group text-left">
            <div class="w-8 h-8 rounded-lg bg-red-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-sm font-semibold text-red-800">OS Atrasadas</span>
                <span class="text-xs text-red-600 ml-2">+15 dias sem encerramento</span>
            </div>
            <span class="text-xs px-2 py-1 bg-red-100 text-red-700 rounded-full font-bold" x-text="ordens.length"></span>
            <svg class="w-4 h-4 text-red-400 group-hover:text-red-600 transition-transform" :class="aberto ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        <div x-show="aberto" class="bg-white rounded-lg border border-red-200 shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-50 max-h-[320px] overflow-y-auto scrollbar-thin scrollbar-thumb-red-500 scrollbar-track-red-100">
                <template x-for="os in ordens" :key="os.id">
                    <a :href="os.url" class="flex items-center gap-3 px-3 py-2.5 hover:bg-red-50/50 transition">
                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                <span x-text="'OS #' + os.numero"></span>
                                <span class="text-[10px] px-1.5 py-0.5 bg-red-100 text-red-700 rounded-full font-bold" x-text="os.dias_atraso + 'd'"></span>
                            </p>
                            <p class="text-xs text-gray-500 truncate" x-text="os.estabelecimento"></p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate" x-text="os.tecnicos.length > 0 ? os.tecnicos.join(', ') : 'Sem técnico'"></p>
                        </div>
                        <span class="text-xs text-gray-400" x-text="os.data_fim"></span>
                    </a>
                </template>
            </div>
        </div>
    </div>
    @endif

    {{-- Card de Respostas com Análise ATRASADA (gestores e admins) --}}
    @if(auth('interno')->user()->isGestor() || auth('interno')->user()->isAdmin())
    <div x-data="respostasAtrasadasAnalise()" x-show="respostas.length > 0" x-cloak class="space-y-2">
        <button type="button"
                @click="aberto = !aberto"
                class="w-full flex items-center gap-3 px-4 py-2.5 bg-orange-50 border border-orange-200 rounded-lg hover:bg-orange-100 transition group text-left">
            <div class="w-8 h-8 rounded-lg bg-orange-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-sm font-semibold text-orange-800">Respostas Atrasadas para Analisar</span>
                <span class="text-xs text-orange-600 ml-2">Técnicos não analisaram dentro do prazo</span>
            </div>
            <span class="text-xs px-2 py-1 bg-orange-100 text-orange-700 rounded-full font-bold" x-text="respostas.length"></span>
            <svg class="w-4 h-4 text-orange-400 group-hover:text-orange-600 transition-transform" :class="aberto ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        <div x-show="aberto" class="bg-white rounded-lg border border-orange-200 shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-50 max-h-[320px] overflow-y-auto">
                <template x-for="r in respostas" :key="r.id">
                    <a :href="r.url" class="flex items-center gap-3 px-3 py-2.5 hover:bg-orange-50/50 transition">
                        <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 flex items-center gap-2 flex-wrap">
                                <span x-text="r.tipo_documento"></span>
                                <template x-if="r.processo_numero">
                                    <span class="text-[11px] text-gray-500" x-text="'#' + r.processo_numero"></span>
                                </template>
                                <span class="text-[10px] px-1.5 py-0.5 bg-orange-100 text-orange-700 rounded-full font-bold" x-text="r.dias_atraso + 'd atraso'"></span>
                            </p>
                            <p class="text-xs text-gray-500 truncate" x-text="r.estabelecimento"></p>
                            <p class="text-[11px] text-gray-400 mt-0.5 truncate">
                                <svg class="inline w-3 h-3 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span x-text="r.tecnicos.length > 0 ? r.tecnicos.join(', ') : 'Sem técnico atribuído'"></span>
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-[10px] text-gray-400">Protocolado</p>
                            <p class="text-xs text-gray-600" x-text="r.data_resposta"></p>
                            <p class="text-[10px] text-orange-600 mt-0.5">Limite: <span x-text="r.data_limite_analise"></span></p>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </div>
    @endif

    {{-- Cadastros Pendentes --}}
    @if(($stats['estabelecimentos_pendentes'] ?? 0) > 0)
    <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="flex items-center gap-3 px-4 py-2.5 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition group">
        <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <div class="flex-1">
            <span class="text-sm font-semibold text-amber-800">{{ $stats['estabelecimentos_pendentes'] }} cadastro(s) aguardando aprovação</span>
        </div>
        <svg class="w-4 h-4 text-amber-400 group-hover:text-amber-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
    @endif

    @if(!$mostraAvisos)
    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <p class="text-sm font-medium text-gray-500">Sem avisos ou pendências</p>
    </div>
    @endif
    </div>
    {{-- /ABA: AVISOS --}}

    {{-- ============================ --}}
    {{-- ABA: TRABALHO (Suas Demandas) --}}
    {{-- ============================ --}}
    <div x-show="tab === 'trabalho'" x-cloak>
    {{-- Layout Principal --}}
    <div class="grid grid-cols-1 {{ $isGestorOuAdmin ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }} gap-4">
        
        {{-- Coluna 1: PARA MIM --}}
        <div class="space-y-4" x-data="{ cardTab1: 'os' }">
        <div id="tour-minhas-tarefas" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="tarefasPaginadas()">
            <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-blue-500 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Minhas demandas</h3>
                        <p class="text-[10px] text-gray-400">Tarefas atribuídas a você</p>
                    </div>
                </div>
                <a href="{{ route('admin.dashboard.todas-tarefas') }}" class="text-[11px] text-blue-500 hover:text-blue-700 font-medium transition">ver todos →</a>
            </div>

            {{-- Abas internas do card --}}
            <div class="flex items-stretch border-b border-gray-200 bg-gray-50/50 overflow-x-auto">
                <button type="button" @click="cardTab1 = 'os'"
                    :class="cardTab1 === 'os' ? 'text-blue-600 border-blue-500 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700'"
                    class="flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider border-b-2 transition whitespace-nowrap">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    OS
                    <span class="text-[9px] px-1 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold" x-text="tarefas.filter(t => t.tipo === 'os').length || '0'"></span>
                </button>
                <button type="button" @click="cardTab1 = 'processos'"
                    :class="cardTab1 === 'processos' ? 'text-indigo-600 border-indigo-500 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700'"
                    class="flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider border-b-2 transition whitespace-nowrap">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Processos
                    <span x-show="$store.dashboard.processosMeuDireto > 0" class="text-[9px] px-1 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-bold" x-text="$store.dashboard.processosMeuDireto"></span>
                </button>
                <button type="button" @click="cardTab1 = 'assinatura'"
                    :class="cardTab1 === 'assinatura' ? 'text-amber-600 border-amber-500 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700'"
                    class="flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider border-b-2 transition whitespace-nowrap">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Assinar
                    <span x-show="tarefas.filter(t => t.tipo === 'assinatura').length > 0" class="text-[9px] px-1 py-0.5 rounded-full bg-amber-100 text-amber-700 font-bold" x-text="tarefas.filter(t => t.tipo === 'assinatura').length"></span>
                </button>
                <button type="button" @click="cardTab1 = 'rascunho'"
                    :class="cardTab1 === 'rascunho' ? 'text-purple-600 border-purple-500 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700'"
                    class="flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider border-b-2 transition whitespace-nowrap">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Rascunhos
                    <span x-show="tarefas.filter(t => t.tipo === 'rascunho' || t.tipo === 'rascunho_lote').length > 0" class="text-[9px] px-1 py-0.5 rounded-full bg-purple-100 text-purple-700 font-bold" x-text="tarefas.filter(t => t.tipo === 'rascunho' || t.tipo === 'rascunho_lote').length"></span>
                </button>
                {{-- NOVA ABA: Analisar Resposta (respostas de documentos que o usuário assinou) --}}
                <button type="button" @click="cardTab1 = 'analisar_resposta'"
                    :class="cardTab1 === 'analisar_resposta' ? 'text-emerald-600 border-emerald-500 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700'"
                    class="flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider border-b-2 transition whitespace-nowrap">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    Analisar Resposta
                    <span x-show="tarefas.filter(t => t.tipo === 'resposta' && t.assinou_documento).length > 0" class="text-[9px] px-1 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-bold" x-text="tarefas.filter(t => t.tipo === 'resposta' && t.assinou_documento).length"></span>
                </button>
            </div>

            <div x-show="cardTab1 === 'os'" x-cloak class="divide-y divide-gray-50 min-h-[120px] max-h-[510px] overflow-y-auto">
                <template x-if="loading">
                    <div class="p-6 text-center">
                        <svg class="animate-spin h-5 w-5 text-blue-300 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </div>
                </template>
                <template x-if="!loading && tarefas.filter(t => t.tipo === 'os').length > 0">
                    <div>
                        {{-- Ordens de Serviço --}}
                        <template x-if="tarefas.filter(t => t.tipo === 'os').length > 0">
                            <div>
                                <div class="px-3 py-1.5 bg-blue-50/60 border-b border-blue-100/60">
                                    <span class="text-[11px] font-semibold text-blue-600 uppercase tracking-wider flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        Ordens de Serviço
                                    </span>
                                </div>
                                <template x-for="t in tarefas.filter(t => t.tipo === 'os')" :key="'os-' + t.id">
                                    <a :href="t.url" class="flex items-center gap-2.5 px-3 py-2 hover:bg-blue-50/50 transition" :class="t.atrasado ? 'bg-red-50/30' : (t.em_finalizacao ? 'bg-amber-50/30' : '')">
                                        <div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0" :class="t.atrasado ? 'bg-red-100' : (t.em_finalizacao ? 'bg-amber-100' : 'bg-blue-100')">
                                            <svg class="w-3 h-3" :class="t.atrasado ? 'text-red-600' : (t.em_finalizacao ? 'text-amber-600' : 'text-blue-600')" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-medium text-gray-800 truncate" x-text="t.titulo"></p>
                                            <p class="text-[11px] text-gray-400 truncate" x-text="t.subtitulo"></p>
                                            <template x-if="t.em_finalizacao || t.atrasado">
                                                <p class="text-[10px] font-medium truncate flex items-center gap-0.5 mt-0.5" :class="t.atrasado ? 'text-red-500' : 'text-amber-600'">
                                                    <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span x-text="t.atrasado ? 'Prazo de finalização expirado!' : 'Prazo p/ finalizar até ' + t.prazo_finalizacao_formatado"></span>
                                                </p>
                                            </template>
                                            <template x-if="!t.em_finalizacao && !t.atrasado && t.data_fim_formatada">
                                                <p class="text-[10px] text-gray-400 truncate mt-0.5">
                                                    Encerramento: <span x-text="t.data_fim_formatada"></span> • Finalizar em até 15 dias após
                                                </p>
                                            </template>
                                        </div>
                                        <span class="text-[9px] font-medium px-1.5 py-0.5 rounded-full whitespace-nowrap" :class="getBadgeClass(t)" x-text="getBadgeText(t)"></span>
                                    </a>
                                </template>
                            </div>
                        </template>

                    </div>
                </template>
                <template x-if="!loading && tarefas.filter(t => t.tipo === 'os').length === 0">
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Tudo em dia</p>
                        <p class="text-xs text-gray-300 mt-1">Nenhuma demanda pendente</p>
                    </div>
                </template>
            </div>

            {{-- Processos atribuídos a mim --}}
            <div x-show="cardTab1 === 'processos'" x-cloak x-data="processosAtribuidos('meu_direto')">
                <div class="px-3 py-1.5 bg-indigo-50/60 border-b border-indigo-100/60 flex items-center justify-between gap-2">
                    <span class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wider flex items-center gap-1.5 min-w-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="truncate">Processos sob minha responsabilidade</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-bold" x-text="totalMeuDireto"></span>
                    </span>
                    <a href="{{ route('admin.dashboard.processos-responsabilidade') }}" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-medium transition whitespace-nowrap">ver todos →</a>
                </div>
                <div class="divide-y divide-gray-50 max-h-[160px] overflow-y-auto">
                    <template x-if="loading">
                        <div class="p-3 text-center"><svg class="animate-spin h-4 w-4 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>
                    </template>
                    <template x-if="!loading && processos.length > 0">
                        <div>
                            <template x-for="p in processos" :key="'meu-proc-' + p.id">
                                <a :href="p.url" class="flex items-center gap-2.5 px-3 py-2 hover:bg-blue-50/50 transition" :class="p.prazo && p.prazo.vencido ? 'bg-red-50/50' : (p.prazo && p.prazo.proximo ? 'bg-amber-50/30' : '')">
                                    <div class="w-6 h-6 rounded-md bg-blue-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[13px] font-medium text-gray-800 flex items-center gap-1">
                                            <span x-text="p.numero_processo"></span>
                                            <template x-if="p.docs_total > 0">
                                                <span class="text-[9px] px-1 py-0.5 rounded" :class="p.docs_enviados >= p.docs_total ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500'" x-text="p.docs_enviados + '/' + p.docs_total"></span>
                                            </template>
                                            <template x-if="p.prazo">
                                                <span class="text-[9px] px-1.5 py-0.5 rounded-full font-medium flex items-center gap-0.5" :class="p.prazo.vencido ? 'bg-red-100 text-red-700' : (p.prazo.proximo ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700')">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span x-text="'Prazo: ' + (p.prazo.vencido ? 'Vencido' : (Math.abs(p.prazo.dias_restantes) + 'd'))"></span>
                                                </span>
                                            </template>
                                        </p>
                                        <p class="text-[11px] text-gray-400 truncate" x-text="p.estabelecimento"></p>
                                        <template x-if="p.recebido_em_humano">
                                            <p class="text-[10px] text-sky-700 truncate mt-0.5" :title="p.recebido_em">
                                                Recebido em <span x-text="p.recebido_em"></span> (<span x-text="p.recebido_em_humano"></span>)
                                            </p>
                                        </template>
                                        <template x-if="!p.recebido_em_humano && p.aguardando_ciencia">
                                            <p class="text-[10px] text-amber-600 truncate mt-0.5" :title="p.tramitado_em">
                                                Tramitado em <span x-text="p.tramitado_em"></span> (aguardando ciência)
                                            </p>
                                        </template>
                                        <template x-if="p.motivo_atribuicao">
                                            <p class="text-[10px] text-indigo-600 mt-0.5 line-clamp-2" :title="p.motivo_atribuicao">
                                                Motivo da atribuição: <span x-text="p.motivo_atribuicao"></span>
                                            </p>
                                        </template>
                                    </div>
                                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full" :class="getStatusClass(p.status)" x-text="p.status_nome"></span>
                                </a>
                            </template>
                        </div>
                    </template>
                    <template x-if="!loading && processos.length === 0">
                        <div class="p-3 text-center text-[11px] text-gray-300">Nenhum processo atribuído</div>
                    </template>
                </div>
                <template x-if="lastPage > 1">
                    <div class="px-3 py-1.5 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-[10px] text-gray-400">Pg <span x-text="currentPage"></span>/<span x-text="lastPage"></span></span>
                        <div class="flex gap-1">
                            <button @click="prevPage()" :disabled="currentPage <= 1" class="p-1 rounded hover:bg-gray-100 disabled:opacity-30 transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                            <button @click="nextPage()" :disabled="currentPage >= lastPage" class="p-1 rounded hover:bg-gray-100 disabled:opacity-30 transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Aba Assinar (minhas demandas de assinatura) --}}
            <div x-show="cardTab1 === 'assinatura'" x-cloak class="divide-y divide-gray-50 min-h-[120px] max-h-[510px] overflow-y-auto">
                <template x-if="tarefas.filter(t => t.tipo === 'assinatura').length === 0">
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Tudo em dia</p>
                        <p class="text-xs text-gray-300 mt-1">Nenhum documento pendente de assinatura</p>
                    </div>
                </template>
                <template x-if="tarefas.filter(t => t.tipo === 'assinatura').length > 0">
                    <div>
                        <div class="px-3 py-1.5 bg-amber-50/60 border-b border-amber-100/60">
                            <span class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                Pendentes de Assinatura
                            </span>
                        </div>
                        <template x-for="t in tarefas.filter(t => t.tipo === 'assinatura')" :key="'minhas-ass-' + t.id">
                            <a :href="t.url" class="flex items-center gap-2.5 px-3 py-2 hover:bg-amber-50/50 transition">
                                <div class="w-6 h-6 rounded-md bg-amber-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] font-medium text-gray-800 truncate" x-text="t.titulo"></p>
                                    <p class="text-[11px] text-gray-400 truncate" x-text="t.subtitulo"></p>
                                </div>
                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700" x-text="t.is_lote ? 'Lote' : 'Assinar'"></span>
                            </a>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Aba Rascunhos (meus rascunhos) --}}
            <div x-show="cardTab1 === 'rascunho'" x-cloak class="divide-y divide-gray-50 min-h-[120px] max-h-[510px] overflow-y-auto">
                <template x-if="tarefas.filter(t => t.tipo === 'rascunho' || t.tipo === 'rascunho_lote').length === 0">
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Sem rascunhos</p>
                        <p class="text-xs text-gray-300 mt-1">Nenhum documento em rascunho</p>
                    </div>
                </template>
                <template x-if="tarefas.filter(t => t.tipo === 'rascunho' || t.tipo === 'rascunho_lote').length > 0">
                    <div>
                        <div class="px-3 py-1.5 bg-purple-50/60 border-b border-purple-100/60">
                            <span class="text-[11px] font-semibold text-purple-600 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Documentos em Rascunho
                            </span>
                        </div>
                        <template x-for="t in tarefas.filter(t => t.tipo === 'rascunho' || t.tipo === 'rascunho_lote')" :key="'minhas-rascunho-' + t.id + '-' + t.tipo">
                            <a :href="t.url" class="flex items-center gap-2.5 px-3 py-2 hover:bg-purple-50/50 transition">
                                <div class="w-6 h-6 rounded-md bg-purple-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] font-medium text-gray-800 truncate" x-text="t.titulo"></p>
                                    <p class="text-[11px] text-gray-400 truncate" x-text="t.subtitulo"></p>
                                </div>
                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-purple-100 text-purple-700" x-text="t.tipo === 'rascunho_lote' ? 'Editar' : 'Abrir'"></span>
                            </a>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Aba Analisar Resposta (respostas de documentos que o usuário assinou) --}}
            <div x-show="cardTab1 === 'analisar_resposta'" x-cloak class="divide-y divide-gray-50 min-h-[120px] max-h-[510px] overflow-y-auto">
                <template x-if="tarefas.filter(t => t.tipo === 'resposta' && t.assinou_documento).length === 0">
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Nenhuma resposta para analisar</p>
                        <p class="text-xs text-gray-300 mt-1">Você receberá respostas de documentos que assinou</p>
                    </div>
                </template>
                <template x-if="tarefas.filter(t => t.tipo === 'resposta' && t.assinou_documento).length > 0">
                    <div>
                        <div class="px-3 py-1.5 bg-emerald-50/60 border-b border-emerald-100/60">
                            <span class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                Respostas de Documentos que Você Assinou
                            </span>
                        </div>
                        <template x-for="t in tarefas.filter(t => t.tipo === 'resposta' && t.assinou_documento)" :key="'minhas-resp-assinante-' + (t.id || t.processo_id)">
                            <a :href="t.url" class="flex items-start gap-2.5 px-3 py-2 hover:bg-emerald-50/50 transition" :class="t.atrasado ? 'bg-red-50/30' : ''">
                                <div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0 mt-0.5" :class="t.atrasado ? 'bg-red-100' : 'bg-emerald-100'">
                                    <svg class="w-3 h-3" :class="t.atrasado ? 'text-red-500' : 'text-emerald-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] font-medium text-gray-800 truncate" x-text="t.titulo"></p>
                                    <p class="text-[11px] text-gray-400 truncate" x-text="t.subtitulo"></p>
                                    <template x-if="t.prazo_analise_data_limite">
                                        <p class="text-[10px] mt-0.5 truncate" :class="t.atrasado ? 'text-red-500 font-medium' : 'text-emerald-600'">
                                            <svg class="inline w-2.5 h-2.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Prazo: <span x-text="t.prazo_analise_data_limite"></span>
                                        </p>
                                    </template>
                                </div>
                                <span class="text-[9px] font-medium px-1.5 py-0.5 rounded-full whitespace-nowrap" :class="getBadgeClass(t)" x-text="getBadgeText(t)"></span>
                            </a>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        </div>

        {{-- Coluna 2: DEMANDAS DO SETOR (apenas gestor/admin) --}}
        @if($isGestorOuAdmin)
        <div id="tour-processos-setor" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ cardTab2: 'aprovacoes' }">
            <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-purple-500 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Demandas do Setor</h3>
                        <p class="text-[10px] text-gray-400">Pendências da sua gerência</p>
                    </div>
                </div>
                <a href="{{ route('admin.dashboard.todas-tarefas') }}" class="text-[11px] text-purple-500 hover:text-purple-700 font-medium transition">ver todos →</a>
            </div>

            {{-- Abas internas do card --}}
            <div class="flex items-stretch border-b border-gray-200 bg-gray-50/50">
                <button type="button" @click="cardTab2 = 'aprovacoes'"
                    :class="cardTab2 === 'aprovacoes' ? 'text-purple-600 border-purple-500 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700'"
                    class="flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider border-b-2 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Aprovações
                    <span x-show="$store.dashboard.aprovacoesCount > 0" class="text-[9px] px-1 py-0.5 rounded-full bg-purple-100 text-purple-700 font-bold" x-text="$store.dashboard.aprovacoesCount"></span>
                </button>
                <button type="button" @click="cardTab2 = 'processos'"
                    :class="cardTab2 === 'processos' ? 'text-teal-600 border-teal-500 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700'"
                    class="flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider border-b-2 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Processos do Setor
                    <span x-show="$store.dashboard.processosSetor > 0" class="text-[9px] px-1 py-0.5 rounded-full bg-teal-100 text-teal-700 font-bold" x-text="$store.dashboard.processosSetor"></span>
                </button>
            </div>

            {{-- Documentos do setor --}}
            <div x-show="cardTab2 === 'aprovacoes'" x-cloak x-data="tarefasPaginadas()">
                <div class="divide-y divide-gray-50 max-h-[250px] overflow-y-auto">
                    <template x-if="loading">
                        <div class="p-6 text-center"><svg class="animate-spin h-5 w-5 text-purple-300 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>
                    </template>
                    <template x-if="!loading && tarefas.filter(t => t.tipo === 'aprovacao').length > 0">
                        <div>
                            <template x-if="tarefas.filter(t => t.tipo === 'aprovacao').length > 0">
                                <div>
                                    <div class="px-3 py-1.5 bg-purple-50/60 border-b border-purple-100/60">
                                        <span class="text-[11px] font-semibold text-purple-600 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Documentos pendentes de aprovação
                                        </span>
                                    </div>
                                    <template x-for="t in tarefas.filter(t => t.tipo === 'aprovacao')" :key="'aprov-' + (t.id || t.processo_id)">
                                        <a :href="t.url" class="flex items-center gap-2.5 px-3 py-2 hover:bg-purple-50/50 transition" :class="t.atrasado ? 'bg-red-50/30' : ''">
                                            <div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0" :class="t.atrasado ? 'bg-red-100' : 'bg-purple-100'">
                                                <svg class="w-3 h-3" :class="t.atrasado ? 'text-red-500' : 'text-purple-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-1 mb-0.5">
                                                    <template x-if="t.tipo_processo">
                                                        <span class="text-[9px] px-1 py-0.5 rounded" :class="t.is_licenciamento ? 'bg-blue-50 text-blue-600' : 'bg-gray-50 text-gray-500'" x-text="t.tipo_processo"></span>
                                                    </template>
                                                    <template x-if="t.total && t.total > 1">
                                                        <span class="text-[9px] px-1 py-0.5 rounded bg-purple-50 text-purple-600" x-text="'+' + (t.total - 1)"></span>
                                                    </template>
                                                </div>
                                                <p class="text-[13px] font-medium text-gray-800 truncate" x-text="t.titulo"></p>
                                                <p class="text-[11px] text-gray-400 truncate" x-text="t.subtitulo"></p>
                                            </div>
                                            <span class="text-[9px] font-medium px-1.5 py-0.5 rounded-full" :class="getBadgeClass(t)" x-text="getBadgeText(t)"></span>
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!loading && tarefas.filter(t => t.tipo === 'aprovacao').length === 0">
                        <div class="p-5 text-center text-[11px] text-gray-400">
                            <p>Nenhum documento pendente no setor</p>
                            <p class="text-gray-300 mt-0.5">Documentos enviados por empresas para aprovação aparecerão aqui</p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Processos do Setor --}}
            <div x-show="cardTab2 === 'processos'" x-cloak x-data="processosAtribuidos('setor')">
                <div class="px-3 py-1.5 bg-teal-50/60 border-b border-teal-100/60 flex items-center justify-between gap-2">
                    <span class="text-[11px] font-semibold text-teal-600 uppercase tracking-wider flex items-center gap-1.5 min-w-0">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span>Processos sob responsabilidade do meu Setor</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-teal-100 text-teal-700 font-bold" x-text="totalDoSetor"></span>
                    </span>
                    @if(auth('interno')->user()->setor)
                        <a href="{{ route('admin.processos.index-geral', ['setor' => auth('interno')->user()->setor, 'apenas_ativos' => 1]) }}"
                           class="text-[11px] text-gray-400 hover:text-teal-700 transition whitespace-nowrap">
                            ver todos
                        </a>
                    @endif
                </div>
                <div class="divide-y divide-gray-50 max-h-[180px] overflow-y-auto">
                    <template x-if="loading">
                        <div class="p-3 text-center"><svg class="animate-spin h-4 w-4 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>
                    </template>
                    <template x-if="!loading && processos.length > 0">
                        <div>
                            <template x-for="p in processos" :key="'setor-proc-' + p.id">
                                <a :href="p.url" class="flex items-center gap-2.5 px-3 py-2 hover:bg-purple-50/50 transition">
                                    <div class="w-6 h-6 rounded-md bg-teal-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[13px] font-medium text-gray-800 flex items-center gap-1">
                                            <span x-text="p.numero_processo"></span>
                                            <template x-if="p.tramitado_para_setor">
                                                <span class="text-[9px] px-1 py-0.5 rounded bg-teal-100 text-teal-700">Seu setor</span>
                                            </template>
                                            <template x-if="p.docs_pendentes > 0">
                                                <span class="text-[9px] px-1 py-0.5 rounded bg-yellow-50 text-yellow-600" x-text="p.docs_pendentes + ' pend.'"></span>
                                            </template>
                                            <template x-if="p.prazo">
                                                <span class="text-[9px] px-1 py-0.5 rounded flex items-center gap-0.5"
                                                      :class="p.prazo.vencido ? 'bg-red-50 text-red-600' : (p.prazo.proximo ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600')">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span x-text="p.prazo.data"></span>
                                                </span>
                                            </template>
                                        </p>
                                        <p class="text-[11px] text-gray-400 truncate" x-text="p.estabelecimento"></p>
                                        <template x-if="p.tramitado_para_setor && p.tramitado_em_humano">
                                            <p class="text-[10px] text-teal-700 truncate mt-0.5" :title="p.tramitado_em">
                                                Tramitado para seu setor em <span x-text="p.tramitado_em"></span> (<span x-text="p.tramitado_em_humano"></span>)
                                            </p>
                                        </template>
                                        <template x-if="!p.tramitado_para_setor && p.recebido_em_humano">
                                            <p class="text-[10px] text-sky-700 truncate mt-0.5" :title="p.recebido_em">
                                                Recebido em <span x-text="p.recebido_em"></span> (<span x-text="p.recebido_em_humano"></span>)
                                            </p>
                                        </template>
                                        <template x-if="!p.tramitado_para_setor && !p.recebido_em_humano && p.aguardando_ciencia">
                                            <p class="text-[10px] text-amber-600 truncate mt-0.5" :title="p.tramitado_em">
                                                Tramitado em <span x-text="p.tramitado_em"></span> (aguardando ciência)
                                            </p>
                                        </template>
                                    </div>
                                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full" :class="getStatusClass(p.status)" x-text="p.status_nome"></span>
                                </a>
                            </template>
                        </div>
                    </template>
                    <template x-if="!loading && processos.length === 0">
                        <div class="p-3 text-center text-[11px] text-gray-400">
                            <p>Nenhum processo no setor</p>
                            <p class="text-gray-300 mt-0.5">Processos tramitados para sua gerência aparecerão aqui</p>
                        </div>
                    </template>
                </div>
                <template x-if="lastPage > 1">
                    <div class="px-3 py-1.5 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-[10px] text-gray-400">Pg <span x-text="currentPage"></span>/<span x-text="lastPage"></span></span>
                        <div class="flex gap-1">
                            <button @click="prevPage()" :disabled="currentPage <= 1" class="p-1 rounded hover:bg-gray-100 disabled:opacity-30 transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                            <button @click="nextPage()" :disabled="currentPage >= lastPage" class="p-1 rounded hover:bg-gray-100 disabled:opacity-30 transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        @endif

        {{-- Coluna 3: ACOMPANHAMENTO --}}
        <div class="space-y-4" x-data="{ cardTab3: 'prazo' }">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="tarefasPaginadas()" x-show="tarefas.filter(t => t.tipo === 'resposta' || t.tipo === 'prazo_documento').length > 0" x-cloak>
                <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-amber-50 to-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-500 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">Acompanhamento de Documentos</h3>
                            <p class="text-[10px] text-gray-400">Prazos e respostas de documentos digitais</p>
                        </div>
                        <span class="text-[10px] px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded-full font-bold" x-text="tarefas.filter(t => t.tipo === 'resposta' || t.tipo === 'prazo_documento').length || '0'"></span>
                    </div>
                    <a href="{{ route('admin.dashboard.todas-tarefas') }}" class="text-[11px] text-amber-500 hover:text-amber-700 font-medium transition">ver todos →</a>
                </div>

                {{-- Abas internas do card --}}
                <div class="flex items-stretch border-b border-gray-200 bg-gray-50/50 overflow-x-auto">
                    <button type="button" @click="cardTab3 = 'prazo'"
                        :class="cardTab3 === 'prazo' ? 'text-rose-600 border-rose-500 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700'"
                        class="flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider border-b-2 transition whitespace-nowrap">
                        Prazos
                        <span x-show="tarefas.filter(t => t.tipo === 'prazo_documento').length > 0" class="text-[9px] px-1 py-0.5 rounded-full bg-rose-100 text-rose-700 font-bold" x-text="tarefas.filter(t => t.tipo === 'prazo_documento').length"></span>
                    </button>
                    <button type="button" @click="cardTab3 = 'resposta'"
                        :class="cardTab3 === 'resposta' ? 'text-emerald-600 border-emerald-500 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700'"
                        class="flex items-center gap-1 px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider border-b-2 transition whitespace-nowrap">
                        Respostas
                        <span x-show="tarefas.filter(t => t.tipo === 'resposta').length > 0" class="text-[9px] px-1 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-bold" x-text="tarefas.filter(t => t.tipo === 'resposta').length"></span>
                    </button>
                </div>

                <div class="divide-y divide-gray-50 max-h-[440px] overflow-y-auto">
                    <template x-if="cardTab3 === 'prazo' && tarefas.filter(t => t.tipo === 'prazo_documento').length === 0">
                        <div class="p-5 text-center text-[11px] text-gray-400">Nenhum documento com prazo em aberto</div>
                    </template>
                    <template x-if="cardTab3 === 'resposta' && tarefas.filter(t => t.tipo === 'resposta').length === 0">
                        <div class="p-5 text-center text-[11px] text-gray-400">Nenhuma resposta para analisar</div>
                    </template>

                    <template x-if="cardTab3 === 'prazo'">
                        <div>
                            <div class="px-3 py-1.5 bg-rose-50/60 border-b border-rose-100/60">
                                <span class="text-[11px] font-semibold text-rose-600 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Documentos com Prazo
                                </span>
                            </div>
                            <template x-for="t in tarefas.filter(t => t.tipo === 'prazo_documento')" :key="'prazo-docs-' + t.id">
                                <a :href="t.url" class="flex items-center gap-2.5 px-3 py-2 hover:bg-rose-50/50 transition" :class="t.atrasado ? 'bg-red-50/40' : 'bg-amber-50/20'">
                                    <div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0" :class="t.atrasado ? 'bg-red-100' : 'bg-rose-100'">
                                        <svg class="w-3 h-3" :class="t.atrasado ? 'text-red-600' : 'text-rose-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[13px] font-medium text-gray-800 truncate" x-text="t.titulo"></p>
                                        <p class="text-[11px] text-gray-400 truncate" x-text="t.subtitulo"></p>
                                        <p class="text-[10px] mt-0.5 truncate" :class="t.atrasado ? 'text-red-500' : 'text-amber-600'" x-text="t.prazo_texto"></p>
                                    </div>
                                    <span class="text-[9px] font-medium px-1.5 py-0.5 rounded-full whitespace-nowrap" :class="getBadgeClass(t)" x-text="getBadgeText(t)"></span>
                                </a>
                            </template>
                        </div>
                    </template>

                    <template x-if="cardTab3 === 'resposta'">
                        <div>
                            <div class="px-3 py-1.5 bg-emerald-50/60 border-b border-emerald-100/60">
                                <span class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    Respostas para Analisar
                                </span>
                            </div>
                            <template x-for="t in tarefas.filter(t => t.tipo === 'resposta')" :key="'resp-docs-' + (t.id || t.processo_id)">
                                <a :href="t.url" class="flex items-center gap-2.5 px-3 py-2 hover:bg-emerald-50/50 transition" :class="t.atrasado ? 'bg-red-50/30' : ''">
                                    <div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0" :class="t.atrasado ? 'bg-red-100' : 'bg-emerald-100'">
                                        <svg class="w-3 h-3" :class="t.atrasado ? 'text-red-500' : 'text-emerald-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[13px] font-medium text-gray-800 truncate" x-text="t.titulo"></p>
                                        <p class="text-[11px] text-gray-400 truncate" x-text="t.subtitulo"></p>
                                        <template x-if="t.prazo_analise_data_limite">
                                            <p class="text-[10px] mt-0.5 truncate" :class="t.atrasado ? 'text-red-500 font-medium' : 'text-emerald-600'">
                                                <svg class="inline w-2.5 h-2.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Prazo de análise: <span x-text="t.prazo_analise_data_limite"></span>
                                            </p>
                                        </template>
                                    </div>
                                    <span class="text-[9px] font-medium px-1.5 py-0.5 rounded-full" :class="getBadgeClass(t)" x-text="getBadgeText(t)"></span>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    </div>
    {{-- /ABA: TRABALHO --}}

    {{-- ============================ --}}
    {{-- ABA: ACOMPANHAMENTO (Monitorando + Aniversariantes) --}}
    {{-- ============================ --}}
    @if($mostraAcompanhamento)
    <div x-show="tab === 'acompanhamento'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Monitorando --}}
        @if(count($processos_acompanhados ?? []) > 0)
        <div id="tour-monitorando" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-indigo-500 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Monitorando</h3>
                        <p class="text-[10px] text-gray-400">Processos que você acompanha</p>
                    </div>
                    <span class="text-[10px] px-1.5 py-0.5 bg-indigo-100 text-indigo-700 rounded-full font-bold">{{ count($processos_acompanhados ?? []) }}</span>
                </div>
                <a href="{{ route('admin.processos.index-geral', ['monitorando' => 1]) }}" class="text-[11px] text-indigo-500 hover:text-indigo-700 font-medium transition">ver todos →</a>
            </div>
            <div class="divide-y divide-gray-50 max-h-[200px] overflow-y-auto">
                @forelse(($processos_acompanhados ?? collect())->take(5) as $proc)
                <a href="{{ route('admin.estabelecimentos.processos.show', [$proc->estabelecimento_id, $proc->id]) }}" class="flex items-center gap-2.5 px-3 py-2 hover:bg-gray-50 transition">
                    <div class="w-6 h-6 rounded-md bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-gray-800 flex items-center gap-1">
                            {{ $proc->numero_processo }}
                            @if($proc->tipoProcesso)
                            <span class="text-[9px] px-1 py-0.5 rounded bg-gray-50 text-gray-400">{{ $proc->tipoProcesso->nome }}</span>
                            @endif
                        </p>
                        <p class="text-[11px] text-gray-400 truncate">{{ $proc->estabelecimento->nome_fantasia ?? $proc->estabelecimento->razao_social ?? '-' }}</p>
                        @php
                            $meuAcompanhamento = $proc->acompanhamentos->first();
                        @endphp
                        @if($meuAcompanhamento && $meuAcompanhamento->descricao)
                            <p class="text-[10px] text-indigo-500 truncate mt-0.5">📝 {{ $meuAcompanhamento->descricao }}</p>
                        @endif
                    </div>
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full {{ $proc->status === 'aberto' ? 'bg-blue-100 text-blue-600' : ($proc->status === 'arquivado' ? 'bg-gray-100 text-gray-500' : 'bg-yellow-100 text-yellow-600') }}">
                        {{ ucfirst($proc->status) }}
                    </span>
                </a>
                @empty
                <div class="p-6 text-center">
                    <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <p class="text-xs text-gray-400">Nenhum processo monitorado</p>
                    <p class="text-[10px] text-gray-300 mt-0.5">Acompanhe processos para vê-los aqui</p>
                </div>
                @endforelse
            </div>
        </div>
        @endif

    </div>
    @endif

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('dashboard', {
        tarefasTotal: 0,
        aprovacoesCount: 0,
        processosMeuDireto: 0,
        processosSetor: 0,
        osVencidas: 0,
        get avisosTotal() {
            return {{ $countAvisos }} + this.osVencidas;
        },
        get trabalhoTotal() {
            return this.tarefasTotal + this.processosMeuDireto + this.processosSetor;
        }
    });
});

function tarefasPaginadas() {
    return {
        tarefas: [], loading: true, currentPage: 1, lastPage: 1, total: 0,
        init() { this.load(); },
        async load() {
            this.loading = true;
            try {
                const r = await fetch(`{{ route('admin.dashboard.tarefas') }}?page=${this.currentPage}&per_page=100`);
                const d = await r.json();
                this.tarefas = d.data; this.currentPage = d.current_page; this.lastPage = d.last_page; this.total = d.total;
                if (Alpine.store('dashboard')) {
                    Alpine.store('dashboard').tarefasTotal = d.total;
                    Alpine.store('dashboard').aprovacoesCount = (d.data || []).filter(t => t.tipo === 'aprovacao').length;
                }
            } catch(e) { console.error(e); }
            this.loading = false;
        },
        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.load(); } },
        nextPage() { if (this.currentPage < this.lastPage) { this.currentPage++; this.load(); } },
        getBadgeClass(t) {
            if (t.tipo === 'rascunho' || t.tipo === 'rascunho_lote') return 'bg-purple-100 text-purple-700';
            if (t.tipo === 'prazo_documento') {
                if (t.atrasado) return 'bg-red-100 text-red-700';
                if (t.dias_restantes === 0) return 'bg-orange-100 text-orange-700';
                if (t.dias_restantes !== null && t.dias_restantes <= 2) return 'bg-amber-100 text-amber-700';
                return 'bg-yellow-100 text-yellow-700';
            }
            if (t.tipo === 'os') {
                const diasOs = t.dias_para_finalizar;
                if (t.atrasado) return 'bg-red-100 text-red-700'; // Passou 15 dias após data_fim
                if (diasOs === null) return 'bg-gray-100 text-gray-600';
                if (diasOs === 0) return 'bg-orange-100 text-orange-700';
                if (t.em_finalizacao) {
                    if (diasOs <= 3) return 'bg-orange-100 text-orange-700';
                    if (diasOs <= 7) return 'bg-amber-100 text-amber-700';
                    return 'bg-yellow-100 text-yellow-700';
                }
                return 'bg-green-100 text-green-700';
            }
            // Respostas: usa prazo de análise (agora sempre existe, não depende de licenciamento)
            if (t.tipo === 'resposta') {
                if (t.atrasado) return 'bg-red-100 text-red-700';
                if (t.dias_restantes === 0) return 'bg-orange-100 text-orange-700';
                if (t.dias_restantes !== null && t.dias_restantes <= 2) return 'bg-amber-100 text-amber-700';
                if (t.dias_restantes === null) return 'bg-gray-100 text-gray-600';
                return 'bg-green-100 text-green-700';
            }
            if (t.is_licenciamento === false) return 'bg-gray-100 text-gray-600';
            if (t.atrasado) return 'bg-red-100 text-red-700';
            if (t.dias_restantes === 0) return 'bg-orange-100 text-orange-700';
            if (t.dias_restantes !== null && t.dias_restantes <= 3) return 'bg-amber-100 text-amber-700';
            if (t.dias_restantes === null) return 'bg-gray-100 text-gray-600';
            return 'bg-green-100 text-green-700';
        },
        getBadgeText(t) {
            if (t.tipo === 'assinatura') return 'Assinar';
            if (t.tipo === 'rascunho_lote') return 'Editar';
            if (t.tipo === 'rascunho') return 'Abrir';
            if (t.tipo === 'prazo_documento') {
                if (t.atrasado) return 'prazo venc.';
                if (t.dias_restantes === 0) return 'hoje p/ vencer';
                if (t.dias_restantes === null) return 'Prazo';
                return t.dias_restantes + 'd p/ vencer';
            }
            if (t.tipo === 'os') {
                const diasOs = t.dias_para_finalizar;
                if (t.atrasado) return 'finaliz. venc.';
                if (diasOs === null) return '-';
                if (diasOs === 0) return 'hoje p/ finalizar';
                return diasOs + 'd p/ finalizar';
            }
            // Respostas: usa prazo de análise específico (dias_restantes vem do backend como dias_restantes_analise)
            if (t.tipo === 'resposta') {
                if (t.dias_restantes === null || t.dias_restantes === undefined) return 'analisar';
                if (t.atrasado || t.dias_restantes < 0) {
                    const d = Math.abs(t.dias_restantes);
                    return 'venc. há ' + d + 'd';
                }
                if (t.dias_restantes === 0) return 'vence hoje';
                if (t.dias_restantes === 1) return 'vence amanhã';
                return t.dias_restantes + 'd p/ analisar';
            }
            if (t.is_licenciamento === false) return 'Verificar';
            if (t.atrasado) return t.tipo === 'aprovacao' ? (t.dias_pendente - 5) + 'd atras.' : Math.abs(t.dias_restantes) + 'd atras.';
            if (t.dias_restantes === 0) return 'hoje p/ analisar';
            if (t.dias_restantes === null) return '-';
            return t.dias_restantes + 'd p/ analisar';
        }
    }
}

// Funções de referência (para coluna 2 que precisa de ambos os dados)
function tarefasPaginadasRef() { return {}; }
function processosAtribuidosRef() { return {}; }

function processosAtribuidos(escopo = 'todos') {
    return {
        processos: [], loading: true, currentPage: 1, lastPage: 1, total: 0, totalMeuDireto: 0, totalDoSetor: 0,
        init() { this.load(); },
        async load() {
            this.loading = true;
            try {
                const r = await fetch(`{{ route('admin.dashboard.processos-atribuidos') }}?page=${this.currentPage}&escopo=${escopo}`);
                const d = await r.json();
                this.processos = d.data;
                this.currentPage = d.current_page;
                this.lastPage = d.last_page;
                this.total = d.total;
                this.totalMeuDireto = d.total_meu_direto ?? (escopo === 'meu_direto' ? d.total : this.processos.filter(p => p.is_meu_direto).length);
                this.totalDoSetor = d.total_do_setor ?? (escopo === 'setor' ? d.total : this.processos.filter(p => p.is_do_setor).length);
                if (Alpine.store('dashboard')) {
                    if (escopo === 'meu_direto') Alpine.store('dashboard').processosMeuDireto = this.totalMeuDireto;
                    if (escopo === 'setor') Alpine.store('dashboard').processosSetor = this.totalDoSetor;
                }
            } catch(e) { console.error(e); }
            this.loading = false;
        },
        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.load(); } },
        nextPage() { if (this.currentPage < this.lastPage) { this.currentPage++; this.load(); } },
        getStatusClass(s) {
            return { 'aberto': 'bg-blue-100 text-blue-700', 'em_analise': 'bg-yellow-100 text-yellow-700', 'pendente': 'bg-orange-100 text-orange-700' }[s] || 'bg-gray-100 text-gray-700';
        }
    }
}

function ordensServicoVencidas() {
    return {
        ordens: [],
        aberto: true,
        init() { 
            console.log('Carregando OSs vencidas...');
            this.load(); 
        },
        async load() {
            try {
                const r = await fetch('{{ route('admin.dashboard.ordens-servico-vencidas') }}');
                const d = await r.json();
                console.log('OSs vencidas recebidas:', d);
                this.ordens = d;
                if (Alpine.store('dashboard')) Alpine.store('dashboard').osVencidas = d.length || 0;
            } catch(e) {
                console.error('Erro ao carregar OSs vencidas:', e); 
            }
        }
    }
}

function respostasAtrasadasAnalise() {
    return {
        respostas: [],
        aberto: true,
        init() {
            this.load();
        },
        async load() {
            try {
                const r = await fetch('{{ route('admin.dashboard.respostas-atrasadas-analise') }}');
                const d = await r.json();
                this.respostas = d;
                if (Alpine.store('dashboard')) Alpine.store('dashboard').respostasAtrasadas = d.length || 0;
            } catch(e) {
                console.error('Erro ao carregar respostas atrasadas:', e);
            }
        }
    }
}
</script>
@endsection
