<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Vigilância Sanitária Municipal - Consulte processos e verifique documentos">
    <title>@yield('title', 'InfoVISA - Sistema de Vigilância Sanitária Municipal')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-200/70 transition-all duration-300">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16">
            <div class="flex items-center justify-between h-full">
                <!-- Logo Align Left -->
                <div class="flex-none flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center group transition-all duration-300 hover:scale-105">
                        <img src="{{ asset('img/logo.png') }}" alt="InfoVISA" class="h-10 w-auto drop-shadow-sm group-hover:drop-shadow-md transition-all" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMjU2M2ViIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+PHBhdGggZD0iTTkgMTJsMiAyIDQtNG01LjYxOC00LjAxNkExMS45NTUgMTEuOTU1IDAgMDExMiAyLjk0NGExMS45NTUgMTEuOTU1IDAgMDEtOC42MTggMy4wNEExMi4wMiAxMi4wMiAwIDAwMyA5YzAgNS41OTEgMy44MjQgMTAuMjkgOSAxMS42MjIgNS4xNzYtMS4zMzIgOS02LjAzIDktMTEuNjIyIDAtMS4wNDItLjEzMy0yLjA1Mi0uMzgyLTMuMDE2eiIvPjwvc3ZnPg=='">
                    </a>
                </div>

                <!-- Botões de Ação -->
                <div class="flex items-center gap-4 flex-1 justify-end">
                    @auth('interno')
                        <div class="flex items-center gap-3 bg-slate-50 pl-4 pr-1.5 py-1.5 rounded-full border border-slate-200/80">
                            <span class="hidden md:block text-sm font-medium text-slate-700">Olá, <span class="text-blue-600">{{ auth('interno')->user()->nome }}</span></span>
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center p-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-full hover:from-purple-700 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5" title="Painel Administrativo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </a>
                        </div>
                    @elseauth('externo')
                        <div class="flex items-center gap-3 bg-slate-50 pl-4 pr-1.5 py-1.5 rounded-full border border-slate-200/80">
                            <span class="hidden md:block text-sm font-medium text-slate-700">Olá, <span class="text-blue-600">{{ auth('externo')->user()->name ?? 'Usuário' }}</span></span>
                            <a href="{{ route('company.dashboard') }}" class="inline-flex items-center justify-center p-1.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-full hover:from-blue-700 hover:to-blue-800 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5" title="Painel da Empresa">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </a>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-blue-700 px-3.5 py-2 rounded-xl hover:bg-blue-50 transition-all group">
                            <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Entrar
                        </a>
                        <a href="{{ route('registro') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-md shadow-blue-600/25 hover:shadow-lg hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span class="hidden sm:inline">Cadastre-se</span>
                        </a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-300 pt-16 pb-10 mt-0 relative overflow-hidden">
        <div class="pointer-events-none absolute -top-40 left-1/2 -translate-x-1/2 w-[48rem] h-80 rounded-full bg-blue-600/10 blur-3xl"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 items-start">
                <!-- Coluna Logo -->
                <div class="col-span-1 md:col-span-2 space-y-6">
                    <div class="inline-block bg-white/5 p-4 rounded-2xl backdrop-blur-sm border border-white/10">
                        <img src="{{ asset('img/logo.png') }}" alt="InfoVISA" class="h-14 w-auto brightness-200 grayscale contrast-125 opacity-90" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSIjMjU2M2ViIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+PHBhdGggZD0iTTkgMTJsMiAyIDQtNG01LjYxOC00LjAxNkExMS45NTUgMTEuOTU1IDAgMDExMiAyLjk0NGExMS45NTUgMTEuOTU1IDAgMDEtOC42MTggMy4wNEExMi4wMiAxMi4wMiAwIDAwMyA5YzAgNS41OTEgMy44MjQgMTAuMjkgOSAxMS42MjIgNS4xNzYtMS4zMzIgOS02LjAzIDktMTEuNjIyIDAtMS4wNDItLjEzMy0yLjA1Mi0uMzgyLTMuMDE2eiIvPjwvc3ZnPg=='">
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                        Sistema oficial de Vigilância Sanitária do Tocantins. Modernidade, transparência e agilidade para cidadãos e empresas.
                    </p>
                    <div class="flex items-center gap-4 pt-2">
                        <a href="#" class="text-slate-500 hover:text-white transition-colors bg-white/5 p-2 rounded-lg hover:bg-white/10">
                            <span class="sr-only">Instagram</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.468 2.53c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>
                        </a>
                        <!-- Add more social links if needed -->
                    </div>
                </div>

                <!-- Coluna Links Rápidos -->
                <div>
                    <h3 class="text-white font-semibold mb-6 text-lg">Acesso Rápido</h3>
                    <ul class="space-y-4 text-sm">
                        <li>
                            <a href="{{ route('home') }}" class="text-slate-400 hover:text-white hover:translate-x-1 transition-all inline-block">
                                Página Inicial
                            </a>
                        </li>
                        <li>
                            <a href="#verificar" class="text-slate-400 hover:text-white hover:translate-x-1 transition-all inline-block">
                                Verificar Documento
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('fila.processos') }}" class="text-slate-400 hover:text-white hover:translate-x-1 transition-all inline-block">
                                Consulta de Processos
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Coluna Área Restrita -->
                <div>
                    <h3 class="text-white font-semibold mb-6 text-lg">Área do Servidor</h3>
                    <ul class="space-y-4 text-sm">
                        <li>
                            <a href="{{ route('login') }}" class="flex items-center gap-2 text-slate-400 hover:text-white group">
                                <span class="bg-slate-800 p-1.5 rounded-lg group-hover:bg-blue-600 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </span>
                                Login Administrativo
                            </a>
                        </li>
                        <li class="flex items-start gap-2 text-slate-400">
                             <span class="bg-slate-800 p-1.5 rounded-lg flex-shrink-0 mt-0.5">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </span>
                            <span>
                                <span class="block text-white font-medium">Suporte Técnico</span>
                                (63) 3027-4486
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="border-t border-slate-800/50 mt-16 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} InfoVISA. Todos os direitos reservados.</p>
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1.5 opacity-75 hover:opacity-100 transition-opacity">
                        Desenvolvido com
                        <svg class="w-3 h-3 text-red-500 fill-current animate-pulse" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        por Erick Vinicius
                    </span>
                    <span class="px-2 py-0.5 bg-slate-800 rounded text-slate-400">v3.0</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Alpine.js Data -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('app', () => ({
                mobileMenuOpen: false
            }))
        })
    </script>
</body>
</html>
