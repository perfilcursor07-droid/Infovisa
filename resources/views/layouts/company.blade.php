<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - InfoVISA Empresa</title>
    
    {{-- URL base para chamadas JavaScript --}}
    <script>
        window.APP_URL = '{{ url('/') }}';
    </script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-expanded { width: 240px; }
        .sidebar-collapsed { width: 72px; }
        @media (max-width: 1023px) {
            .sidebar-expanded, .sidebar-collapsed { width: 240px; }
        }
    </style>
    
    {{-- Script inline para aplicar estado do sidebar ANTES do render --}}
    <script>
        (function() {
            // Aplica classe CSS baseada no localStorage ANTES do Alpine carregar
            const stored = localStorage.getItem('sidebarExpanded');
            const isExpanded = stored === null ? true : stored === 'true';
            
            // Salva no window para o Alpine usar
            window.__sidebarExpanded = isExpanded;
            
            // Injeta CSS dinâmico para definir estado inicial correto SEM transição
            const style = document.createElement('style');
            style.id = 'sidebar-initial-state';
            style.textContent = `
                /* Remove transições durante carregamento inicial */
                aside.fixed { 
                    width: ${isExpanded ? '240px' : '72px'} !important;
                    transition: none !important;
                }
                /* Controla visibilidade dos botões de toggle ANTES do Alpine */
                .sidebar-toggle-collapse { display: ${isExpanded ? 'flex' : 'none'} !important; }
                .sidebar-toggle-expand { display: ${isExpanded ? 'none' : 'flex'} !important; }
                /* Esconde todos os textos/labels do sidebar quando colapsado */
                aside.fixed span[x-show="showLabels()"],
                aside.fixed div[x-show="showLabels()"],
                aside.fixed p[x-show="showLabels()"] { 
                    display: ${isExpanded ? '' : 'none'} !important; 
                }
                /* Esconde elementos com x-cloak até Alpine estar pronto */
                [x-cloak] { display: none !important; }
                @media (max-width: 1023px) {
                    aside.fixed { width: 240px !important; }
                    .sidebar-toggle-collapse { display: flex !important; }
                    .sidebar-toggle-expand { display: none !important; }
                    aside.fixed span[x-show="showLabels()"],
                    aside.fixed div[x-show="showLabels()"],
                    aside.fixed p[x-show="showLabels()"] { 
                        display: inline !important; 
                    }
                }
            `;
            document.head.appendChild(style);
            
            // Remove o estilo após Alpine inicializar e aplicar seu estado
            document.addEventListener('alpine:initialized', function() {
                // Aguarda 2 frames para garantir que Alpine aplicou tudo
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        const initialStyle = document.getElementById('sidebar-initial-state');
                        if (initialStyle) initialStyle.remove();
                    });
                });
            });
        })();
    </script>
    @stack('styles')
</head>
<body class="bg-gray-50" x-data="sidebarState()" x-init="init()">
    
    <script>
        // Define o estado do sidebar usando o valor pré-calculado
        function sidebarState() {
            // Usa o valor já calculado pelo script inline no <head>
            const initialExpanded = window.__sidebarExpanded !== undefined 
                ? window.__sidebarExpanded 
                : (localStorage.getItem('sidebarExpanded') !== 'false');
            
            return {
                sidebarOpen: false,
                sidebarExpanded: initialExpanded,
                userMenuOpen: false,
                helpMenuOpen: false,
                isMobile: window.innerWidth < 1024,
                
                init() {
                    // Listener para resize
                    window.addEventListener('resize', () => {
                        this.isMobile = window.innerWidth < 1024;
                    });
                },
                
                toggleSidebar() {
                    this.sidebarExpanded = !this.sidebarExpanded;
                    localStorage.setItem('sidebarExpanded', this.sidebarExpanded.toString());
                },
                
                showLabels() {
                    return this.isMobile || this.sidebarExpanded;
                }
            };
        }
    </script>
    
    {{-- Overlay Mobile --}}
    <div x-show="sidebarOpen" 
         x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside class="fixed lg:relative inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-gray-200 shadow-lg"
               :class="{
                   'translate-x-0': sidebarOpen,
                   '-translate-x-full lg:translate-x-0': !sidebarOpen,
                   'sidebar-expanded': sidebarExpanded,
                   'sidebar-collapsed': !sidebarExpanded
               }"
               x-bind:style="'transition: width 300ms ease-in-out, transform 300ms ease-in-out;'">
        
            {{-- Logo Header --}}
            <div class="flex items-center h-14 bg-blue-600 border-b border-blue-700 px-4"
                 :class="showLabels() ? 'justify-between' : 'lg:justify-center'">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <span x-show="showLabels()" x-cloak class="sidebar-label text-white font-bold text-lg">InfoVISA</span>
                </div>
                {{-- Toggle Desktop (Colapsar) --}}
                <button @click="toggleSidebar()" 
                        x-show="sidebarExpanded"
                        x-cloak
                        class="sidebar-toggle-collapse hidden lg:flex items-center justify-center w-6 h-6 rounded text-white/70 hover:text-white hover:bg-white/10 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
                {{-- Close Mobile --}}
                <button @click="sidebarOpen = false" 
                        class="lg:hidden flex items-center justify-center w-6 h-6 rounded text-white/70 hover:text-white hover:bg-white/10 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Expand Button (quando collapsed) --}}
            <button @click="toggleSidebar()" 
                    x-show="!sidebarExpanded"
                    x-cloak
                    class="sidebar-toggle-expand hidden lg:flex items-center justify-center h-10 text-gray-400 hover:text-blue-600 hover:bg-gray-50 transition border-b border-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3" :class="!showLabels() ? 'lg:px-2' : ''">
                <div class="space-y-1">
                    {{-- Dashboard --}}
                    <a href="{{ route('company.dashboard') }}" 
                       title="Dashboard"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('company.dashboard') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium truncate">Dashboard</span>
                    </a>

                    {{-- Estabelecimentos --}}
                    <a href="{{ route('company.estabelecimentos.index') }}"
                       title="Meus Estabelecimentos"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('company.estabelecimentos.*') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium truncate">Estabelecimentos</span>
                    </a>

                    {{-- Processos --}}
                    <a href="{{ route('company.processos.index') }}" 
                       title="Meus Processos"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('company.processos.*') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium truncate">Processos</span>
                    </a>

                    {{-- Alertas --}}
                    {{-- Alertas --}}
                    @php
                        $alertasPendentesCount = 0;
                        $documentosPendentesCount = 0;
                        $documentosRejeitadosCount = 0;
                        $documentosComPrazoCount = 0;
                        if (auth('externo')->check()) {
                            $estabelecimentoIds = \App\Models\Estabelecimento::where('usuario_externo_id', auth('externo')->id())
                                ->orWhereHas('usuariosVinculados', function($q) {
                                    $q->where('usuario_externo_id', auth('externo')->id());
                                })
                                ->pluck('id');
                            $processoIds = \App\Models\Processo::whereIn('estabelecimento_id', $estabelecimentoIds)->pluck('id');
                            $alertasPendentesCount = \App\Models\ProcessoAlerta::whereIn('processo_id', $processoIds)
                                ->where('status', '!=', 'concluido')
                                ->count();
                            $documentosPendentesCount = \App\Models\DocumentoDigital::whereIn('processo_id', $processoIds)
                                ->where('status', 'assinado')
                                ->where('sigiloso', false)
                                ->whereDoesntHave('visualizacoes')
                                ->count();
                            $documentosRejeitadosCount = \App\Models\ProcessoDocumento::whereIn('processo_id', $processoIds)
                                ->where('status_aprovacao', 'rejeitado')
                                ->count();
                            $documentosComPrazoCount = \App\Models\DocumentoDigital::whereIn('processo_id', $processoIds)
                                ->where('status', 'assinado')
                                ->where('sigiloso', false)
                                ->where('prazo_notificacao', true)
                                ->whereNotNull('prazo_iniciado_em')
                                ->whereNull('prazo_finalizado_em')
                                ->count();
                        }
                        $totalNotificacoes = $alertasPendentesCount + $documentosPendentesCount + $documentosRejeitadosCount + $documentosComPrazoCount;
                    @endphp
                    <a href="{{ route('company.alertas.index') }}" 
                       title="Alertas{{ $documentosRejeitadosCount > 0 ? " - {$documentosRejeitadosCount} documento(s) rejeitado(s)" : '' }}{{ $documentosPendentesCount > 0 ? " - {$documentosPendentesCount} documento(s) pendente(s)" : '' }}"
                       class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('company.alertas.*') ? 'bg-orange-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium truncate">Alertas</span>
                        @if($totalNotificacoes > 0)
                        <span class="absolute top-1 left-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                              :class="!showLabels() ? 'lg:-top-1 lg:-right-1' : ''">
                            {{ $totalNotificacoes > 9 ? '9+' : $totalNotificacoes }}
                        </span>
                        @endif
                    </a>

                    {{-- Meu Perfil --}}
                    <a href="{{ route('company.perfil.index') }}" 
                       title="Meu Perfil"
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('company.perfil.*') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                       :class="!showLabels() ? 'lg:justify-center' : ''">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span x-show="showLabels()" class="text-sm font-medium truncate">Meu Perfil</span>
                    </a>
                </div>

                {{-- Logout --}}
                <div class="pt-4 mt-4 border-t border-gray-200">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                title="Sair"
                                class="group flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50 transition-all duration-200"
                                :class="!showLabels() ? 'lg:justify-center' : ''">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span x-show="showLabels()" class="text-sm font-medium">Sair</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        {{-- Conteúdo principal --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Header --}}
            <div class="sticky top-0 z-10 flex h-14 bg-white border-b border-gray-200 shadow-sm">
                <div class="flex-1 flex justify-between px-4 sm:px-6">
                    {{-- Botão hamburguer (mobile) --}}
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = !sidebarOpen" 
                                class="lg:hidden flex items-center justify-center w-8 h-8 rounded text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <h2 class="text-base font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Botão de Ajuda (?) - Documentos Instrutivos --}}
                        <div class="relative" @click.away="helpMenuOpen = false">
                            <button id="btn-ajuda"
                                    @click="helpMenuOpen = !helpMenuOpen; userMenuOpen = false"
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 hover:text-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    title="Documentos de Ajuda">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>

                            <div x-show="helpMenuOpen"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="origin-top-right absolute right-0 mt-2 w-80 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                <div class="px-4 py-3 border-b border-gray-200 bg-blue-50 rounded-t-lg">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        <div>
                                            <h3 class="font-semibold text-sm text-blue-900">Documentos de Ajuda</h3>
                                            <p class="text-xs text-blue-600">Instrutivos e manuais do sistema</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="max-h-80 overflow-y-auto py-1">
                                    {{-- Manual fixo do InfoVISA 3.0 --}}
                                    <a href="{{ asset('Manual/manual-infovisa.html') }}" 
                                       target="_blank"
                                       class="flex items-start gap-3 px-4 py-3 hover:bg-blue-50 transition-colors group border-b border-gray-100">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-blue-900 group-hover:text-blue-700">Manual InfoVISA 3.0</p>
                                            <p class="text-xs text-gray-500 mt-0.5">Guia completo do sistema</p>
                                        </div>
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </div>
                                    </a>
                                    @if(isset($documentosAjuda) && $documentosAjuda->count() > 0)
                                    @foreach($documentosAjuda as $docAjuda)
                                    <a href="{{ route('company.documentos-ajuda.visualizar', $docAjuda->id) }}" 
                                       target="_blank"
                                       class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors group">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600 truncate">{{ $docAjuda->titulo }}</p>
                                            @if($docAjuda->descricao)
                                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $docAjuda->descricao }}</p>
                                            @endif
                                            <p class="text-xs text-gray-400 mt-1">{{ $docAjuda->tamanho_formatado }}</p>
                                        </div>
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </div>
                                    </a>
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Menu do usuário --}}
                        <div class="relative" @click.away="userMenuOpen = false">
                            <button @click="userMenuOpen = !userMenuOpen; helpMenuOpen = false"
                                    class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 px-2 py-1 hover:bg-gray-100 transition-colors">
                                <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center mr-2">
                                    <span class="text-white font-medium text-sm">
                                        {{ substr(auth('externo')->user()->nome, 0, 1) }}
                                    </span>
                                </div>
                                <span class="text-gray-700 text-sm font-medium hidden md:block mr-1">
                                    {{ Str::limit(auth('externo')->user()->nome, 20) }}
                                </span>
                                <svg class="h-4 w-4 text-gray-400 hidden md:block" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>

                            <div x-show="userMenuOpen"
                                 x-cloak
                                 class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <div class="font-medium text-sm text-gray-900">{{ auth('externo')->user()->nome }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ auth('externo')->user()->email }}</div>
                                </div>
                                <a href="{{ route('company.perfil.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Meu Perfil
                                </a>
                                <div class="border-t border-gray-200"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Sair
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Conteúdo da página --}}
            <main class="flex-1 overflow-y-auto p-6 sm:p-8 lg:p-10">
                {{-- Alertas --}}
                @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
