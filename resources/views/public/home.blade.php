@extends('layouts.public')

@section('title', 'InfoVISA - Sistema de Vigilância Sanitária')

@section('content')
<div x-data="{ codigoVerificador: '' }">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-white">
        <!-- Background Decor -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div class="absolute -top-32 -right-32 w-[32rem] h-[32rem] bg-blue-100 rounded-full blur-3xl opacity-70 animate-blob"></div>
            <div class="absolute -bottom-40 -left-32 w-[28rem] h-[28rem] bg-violet-100 rounded-full blur-3xl opacity-70 animate-blob" style="animation-delay: 2s"></div>
            <svg class="absolute inset-0 w-full h-full opacity-[0.35]" aria-hidden="true">
                <defs><pattern id="home-grid" width="32" height="32" patternUnits="userSpaceOnUse"><path d="M32 0H0V32" fill="none" stroke="#e2e8f0" stroke-width="1"/></pattern></defs>
                <rect width="100%" height="100%" fill="url(#home-grid)"/>
            </svg>
            <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-white to-transparent"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-20 md:pt-20 md:pb-28">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                <!-- Texto -->
                <div class="lg:col-span-7 text-center lg:text-left">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-white/80 backdrop-blur border border-blue-100 text-blue-700 rounded-full text-xs sm:text-sm font-semibold mb-6 shadow-sm">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                        </span>
                        Sistema de Vigilância Sanitária Digital
                    </div>

                    <!-- Main Heading -->
                    <h1 class="text-4xl sm:text-5xl lg:text-[3.4rem] font-extrabold text-slate-900 mb-5 tracking-tight leading-[1.1]">
                        Vigilância Sanitária <br class="hidden md:block" />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600">Digital e Integrada</span>
                    </h1>

                    <!-- Subheading -->
                    <p class="text-base md:text-lg text-slate-600 mb-8 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                        Sistema oficial de Vigilância Sanitária do Estado do Tocantins. Protocole processos de licenciamento,
                        análise de projetos, rotulagem e outros serviços sanitários. Acompanhe cada etapa com total transparência.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start items-center">
                        <a href="{{ route('fila.processos') }}" class="group relative w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-slate-900 text-white text-sm font-bold rounded-2xl hover:bg-slate-800 transition-all shadow-xl shadow-slate-900/20 hover:shadow-2xl overflow-hidden">
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:animate-shimmer"></div>
                            <svg class="w-4 h-4 mr-2 group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Consultar Processo
                        </a>
                        <a href="#verificar" class="group w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-white text-slate-700 text-sm font-bold rounded-2xl border border-slate-200 hover:border-blue-200 hover:bg-blue-50/60 transition-all shadow-sm hover:shadow-lg">
                            <svg class="w-4 h-4 mr-2 text-blue-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Verificar Autenticidade
                        </a>
                    </div>

                    <!-- Selos -->
                    <div class="mt-8 flex flex-wrap gap-x-6 gap-y-2 justify-center lg:justify-start text-xs font-medium text-slate-500">
                        <span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Assinatura digital</span>
                        <span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Verificação por QR-Code</span>
                        <span class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Acompanhamento online</span>
                    </div>
                </div>

                <!-- Cartão de acesso rápido -->
                <div class="lg:col-span-5">
                    <div class="relative max-w-md mx-auto">
                        <div class="absolute -inset-4 bg-gradient-to-br from-blue-500/20 via-indigo-500/10 to-violet-500/20 rounded-[2rem] blur-2xl"></div>
                        <div class="relative bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl shadow-slate-900/10 ring-1 ring-slate-200/80 p-5 sm:p-6">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900">Acesso rápido</p>
                                    <p class="text-xs text-slate-500">O que você deseja fazer?</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <a href="{{ route('fila.processos') }}" class="group flex items-center gap-3 p-3 rounded-2xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50/60 transition">
                                    <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </span>
                                    <span class="flex-1 min-w-0">
                                        <span class="block text-sm font-semibold text-slate-800">Consultar processo</span>
                                        <span class="block text-xs text-slate-500">Veja a fila e o andamento</span>
                                    </span>
                                    <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-500 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <a href="#verificar" class="group flex items-center gap-3 p-3 rounded-2xl border border-slate-100 hover:border-emerald-200 hover:bg-emerald-50/60 transition">
                                    <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                    </span>
                                    <span class="flex-1 min-w-0">
                                        <span class="block text-sm font-semibold text-slate-800">Verificar documento</span>
                                        <span class="block text-xs text-slate-500">Pelo código ou QR-Code</span>
                                    </span>
                                    <svg class="w-4 h-4 text-slate-300 group-hover:text-emerald-500 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <a href="{{ route('login') }}" class="group flex items-center gap-3 p-3 rounded-2xl border border-slate-100 hover:border-violet-200 hover:bg-violet-50/60 transition">
                                    <span class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center flex-shrink-0 group-hover:bg-violet-600 group-hover:text-white transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                    </span>
                                    <span class="flex-1 min-w-0">
                                        <span class="block text-sm font-semibold text-slate-800">Entrar no sistema</span>
                                        <span class="block text-xs text-slate-500">Empresas e servidores</span>
                                    </span>
                                    <svg class="w-4 h-4 text-slate-300 group-hover:text-violet-500 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>

                            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                                <p class="text-xs text-slate-500">Ainda não tem conta?</p>
                                <a href="{{ route('registro') }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800">
                                    Cadastre-se grátis
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600 mb-2">Recursos</p>
                <h2 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Tudo em um só lugar</h2>
                <p class="mt-3 text-slate-500">Serviços de Vigilância Sanitária com agilidade, segurança e transparência.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="group bg-white p-7 rounded-3xl shadow-sm ring-1 ring-slate-200/80 hover:shadow-xl hover:shadow-blue-900/5 hover:-translate-y-1 transition-all duration-300">
                    <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white mb-5 shadow-lg shadow-blue-500/25 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Múltiplos Processos</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Licenciamento sanitário, análise de projetos arquitetônicos, rotulagem, receituários e outros serviços de vigilância sanitária.</p>
                </div>

                <!-- Card 2 -->
                <div class="group bg-white p-7 rounded-3xl shadow-sm ring-1 ring-slate-200/80 hover:shadow-xl hover:shadow-violet-900/5 hover:-translate-y-1 transition-all duration-300">
                    <div class="h-12 w-12 bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl flex items-center justify-center text-white mb-5 shadow-lg shadow-violet-500/25 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Documentos Digitais</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Alvarás, licenças e documentos assinados digitalmente com código de verificação e QR-CODE para autenticação.</p>
                </div>

                <!-- Card 3 -->
                <div class="group bg-white p-7 rounded-3xl shadow-sm ring-1 ring-slate-200/80 hover:shadow-xl hover:shadow-emerald-900/5 hover:-translate-y-1 transition-all duration-300">
                    <div class="h-12 w-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center text-white mb-5 shadow-lg shadow-emerald-500/25 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Rastreabilidade Total</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">Transparência completa em todas as etapas, com histórico de inspeções, pareceres técnicos e comunicações oficiais.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Verification Section -->
    <section id="verificar" class="py-20 bg-white relative overflow-hidden scroll-mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="bg-gradient-to-br from-blue-700 via-indigo-700 to-violet-800 rounded-[2rem] p-8 md:p-14 text-white shadow-2xl shadow-indigo-900/20 relative overflow-hidden">
                <!-- Decorative -->
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-fuchsia-500/20 rounded-full blur-3xl"></div>
                <svg class="pointer-events-none absolute inset-0 w-full h-full opacity-[0.06]" aria-hidden="true">
                    <defs><pattern id="verif-dots" width="22" height="22" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.5" fill="white"/></pattern></defs>
                    <rect width="100%" height="100%" fill="url(#verif-dots)"/>
                </svg>

                <div class="relative grid md:grid-cols-2 gap-10 items-center">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-white/15 ring-1 ring-white/25 flex items-center justify-center mb-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-bold mb-4 tracking-tight">Verificação de Autenticidade</h2>
                        <p class="text-blue-100 text-base mb-6 leading-relaxed">
                            Verifique a autenticidade de documentos emitidos pela Vigilância Sanitária do Tocantins. A verificação é pública, gratuita e instantânea através do código ou QR-CODE.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-blue-50 text-sm">
                                <span class="bg-white/15 ring-1 ring-white/20 p-1.5 rounded-full">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                Validação em tempo real junto à base de dados oficial
                            </li>
                            <li class="flex items-center gap-3 text-blue-50 text-sm">
                                <span class="bg-white/15 ring-1 ring-white/20 p-1.5 rounded-full">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                Prevenção contra fraudes e adulterações de documentos
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-2xl text-slate-800">
                        <form action="{{ route('verificar.documento') }}" method="POST" class="space-y-5">
                            @csrf
                            <div>
                                <label for="codigo_verificador" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                                    Código Verificador
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        id="codigo_verificador"
                                        name="codigo_verificador"
                                        x-model="codigoVerificador"
                                        placeholder="EX: ABC-123-XYZ"
                                        required
                                        class="w-full pl-4 pr-12 py-3.5 text-sm font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 text-slate-900 placeholder-slate-400 uppercase tracking-wider transition-all"
                                    >
                                    <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    O código encontra-se no rodapé do documento oficial
                                </p>
                            </div>

                            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3.5 px-6 rounded-xl text-sm font-bold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg shadow-blue-600/25 hover:shadow-xl flex items-center justify-center gap-2 group">
                                Verificar Agora
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
