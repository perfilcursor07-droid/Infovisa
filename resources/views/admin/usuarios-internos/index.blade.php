@extends('layouts.admin')

@section('title', 'Usuários Internos')
@section('page-title', 'Usuários Internos')

@section('content')
<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-900 tracking-tight leading-tight">Usuários Internos</h1>
                <p class="text-xs text-slate-400">Gerencie os usuários internos do sistema</p>
            </div>
        </div>
        <a href="{{ route('admin.usuarios-internos.create') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold shadow-sm shadow-blue-600/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Usuário
        </a>
    </div>

    {{-- Abas --}}
    <div class="mb-4 inline-flex max-w-full overflow-x-auto items-center gap-1 p-1 bg-white rounded-xl border border-slate-200/80 shadow-sm">
        <a href="{{ route('admin.usuarios-internos.index', array_merge(request()->except('page'), ['aba' => 'usuarios'])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg whitespace-nowrap transition {{ $aba === 'usuarios' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/25' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            Lista de usuários
        </a>
        <a href="{{ route('admin.usuarios-internos.index', ['aba' => 'cadastros']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg whitespace-nowrap transition {{ $aba === 'cadastros' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/25' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            Cadastro por link
        </a>
        <a href="{{ route('admin.usuarios-internos.index', ['aba' => 'atividade']) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg whitespace-nowrap transition {{ $aba === 'atividade' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Atividade no sistema
        </a>
    </div>

    @if($aba === 'atividade')

    <div x-data="detalhesAtividadeModal()">
    {{-- Filtros de Escopo --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4 mb-4">
        <form method="GET" action="{{ route('admin.usuarios-internos.index') }}" class="flex flex-wrap gap-3 items-end" x-data="{ escopo: '{{ request('escopo_atividade', '') }}' }">
            <input type="hidden" name="aba" value="atividade">
            <div class="w-44">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Escopo</label>
                <select name="escopo_atividade" x-model="escopo"
                        class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                    <option value="">Todos</option>
                    <option value="estadual">🏛️ Estadual</option>
                    <option value="municipal">🏘️ Municipal</option>
                </select>
            </div>
            <div class="w-52" x-show="escopo === 'municipal'" x-cloak>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Município</label>
                <select name="municipio_id_atividade"
                        class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                    <option value="">Todos os municípios</option>
                    @foreach($municipios as $mun)
                    <option value="{{ $mun->id }}" {{ request('municipio_id_atividade') == $mun->id ? 'selected' : '' }}>{{ $mun->nome }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 shadow-sm transition">Filtrar</button>
            @if(request('escopo_atividade'))
            <a href="{{ route('admin.usuarios-internos.index', ['aba' => 'atividade']) }}" class="px-3 py-2 text-sm text-slate-500 hover:text-slate-700">Limpar</a>
            @endif
        </form>
    </div>

    {{-- Ranking de Atividade no Sistema --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4 mb-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Usuários que mais fizeram ações no sistema</h2>
                <p class="text-xs text-slate-500 mt-0.5">Métricas: aprovou documentos, criou documentos, upload de documentos e ações em processos/estabelecimentos.</p>
            </div>
            <span class="text-xs px-2 py-1 rounded-full bg-indigo-100 text-indigo-700 font-semibold">Top {{ $rankingAtividadeUsuarios->count() }}</span>
        </div>

        @if($rankingAtividadeUsuarios->isNotEmpty())
            <div class="space-y-2">
                @foreach($rankingAtividadeUsuarios as $index => $usuarioAtivo)
                    <div class="border border-slate-100 rounded-lg p-3 hover:bg-slate-50 transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center flex-shrink-0">
                                    #{{ $index + 1 }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('admin.usuarios-internos.show', $usuarioAtivo->id) }}" class="text-sm font-semibold text-slate-900 hover:text-indigo-600 truncate">
                                            {{ $usuarioAtivo->nome }}
                                        </a>
                                        <span class="px-1.5 py-0.5 text-[10px] font-medium rounded-full {{ $usuarioAtivo->nivel_acesso->color() }}">
                                            {{ $usuarioAtivo->nivel_acesso->label() }}
                                        </span>
                                        @if(!$usuarioAtivo->ativo)
                                            <span class="px-1.5 py-0.5 text-[10px] font-medium rounded-full bg-red-100 text-red-700">Inativo</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-1.5">
                                        <button type="button" @click="abrirDetalhes({{ $usuarioAtivo->id }}, 'documentos_criados', '{{ addslashes($usuarioAtivo->nome) }}')" class="text-[11px] px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded hover:bg-blue-100 cursor-pointer transition">Criou docs: {{ $usuarioAtivo->documentos_criados_count }}</button>
                                        <button type="button" @click="abrirDetalhes({{ $usuarioAtivo->id }}, 'documentos_aprovados', '{{ addslashes($usuarioAtivo->nome) }}')" class="text-[11px] px-1.5 py-0.5 bg-green-50 text-green-700 rounded hover:bg-green-100 cursor-pointer transition">Aprovou docs: {{ $usuarioAtivo->documentos_aprovados_count }}</button>
                                        <button type="button" @click="abrirDetalhes({{ $usuarioAtivo->id }}, 'uploads', '{{ addslashes($usuarioAtivo->nome) }}')" class="text-[11px] px-1.5 py-0.5 bg-amber-50 text-amber-700 rounded hover:bg-amber-100 cursor-pointer transition">Uploads: {{ $usuarioAtivo->uploads_documentos_count }}</button>
                                        <button type="button" @click="abrirDetalhes({{ $usuarioAtivo->id }}, 'acoes_processos', '{{ addslashes($usuarioAtivo->nome) }}')" class="text-[11px] px-1.5 py-0.5 bg-purple-50 text-purple-700 rounded hover:bg-purple-100 cursor-pointer transition">Ações processo: {{ $usuarioAtivo->acoes_processos_count }}</button>
                                        <button type="button" @click="abrirDetalhes({{ $usuarioAtivo->id }}, 'acoes_estabelecimentos', '{{ addslashes($usuarioAtivo->nome) }}')" class="text-[11px] px-1.5 py-0.5 bg-pink-50 text-pink-700 rounded hover:bg-pink-100 cursor-pointer transition">Ações estabelecimento: {{ $usuarioAtivo->acoes_estabelecimentos_count }}</button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-[11px] text-slate-500">Total de ações</p>
                                <p class="text-lg font-bold text-indigo-700">{{ $usuarioAtivo->total_acoes }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6">
                <p class="text-sm text-slate-500">Nenhuma ação registrada para os usuários dentro do seu escopo.</p>
            </div>
        @endif
    </div>

    {{-- Usuários sem atividade e sem login --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4 mb-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Usuários sem nenhuma ação e sem login</h2>
                <p class="text-xs text-slate-500 mt-0.5">Usuários que nunca logaram e não possuem ações registradas.</p>
            </div>
            <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-700 font-semibold">{{ $usuariosSemAtividadeSemLogin->count() }}</span>
        </div>

        @if($usuariosSemAtividadeSemLogin->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/80 border-b border-slate-100">
                        <tr>
                            <th class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Nome</th>
                            <th class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Nível</th>
                            <th class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-3 py-2 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($usuariosSemAtividadeSemLogin as $usuarioSemAtividade)
                            <tr class="hover:bg-blue-50/40 transition-colors">
                                <td class="px-3 py-2 text-sm font-medium text-slate-900">{{ $usuarioSemAtividade->nome }}</td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $usuarioSemAtividade->nivel_acesso->color() }}">
                                        {{ $usuarioSemAtividade->nivel_acesso->label() }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    @if($usuarioSemAtividade->ativo)
                                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ route('admin.usuarios-internos.show', $usuarioSemAtividade->id) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Visualizar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-6">
                <p class="text-sm text-slate-500">Nenhum usuário sem ação e sem login no escopo atual.</p>
            </div>
        @endif
    </div>

    {{-- Lista Completa de Todos os Usuários --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Lista Completa de Usuários</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Todos os usuários com suas métricas de atividade</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-700 font-semibold">{{ $todosUsuariosAtividade->count() }} usuários</span>
            </div>
        </div>

        @if($todosUsuariosAtividade->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Usuário</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Nível</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Docs Criados</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Aprovações</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Uploads</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Processos</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Estabelec.</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-2.5 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Último Login</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($todosUsuariosAtividade as $usr)
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('admin.usuarios-internos.show', $usr->id) }}" class="text-sm font-medium text-slate-900 hover:text-indigo-600">{{ $usr->nome }}</a>
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $usr->nivel_acesso->color() }}">
                                {{ $usr->nivel_acesso->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            @if($usr->ativo)
                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                            @else
                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-center text-sm">
                            <button type="button" @click="abrirDetalhes({{ $usr->id }}, 'documentos_criados', '{{ addslashes($usr->nome) }}')" class="{{ $usr->documentos_criados_count > 0 ? 'text-blue-700 font-medium hover:underline cursor-pointer' : 'text-slate-400' }}">{{ $usr->documentos_criados_count }}</button>
                        </td>
                        <td class="px-4 py-2.5 text-center text-sm">
                            <button type="button" @click="abrirDetalhes({{ $usr->id }}, 'documentos_aprovados', '{{ addslashes($usr->nome) }}')" class="{{ $usr->documentos_aprovados_count > 0 ? 'text-green-700 font-medium hover:underline cursor-pointer' : 'text-slate-400' }}">{{ $usr->documentos_aprovados_count }}</button>
                        </td>
                        <td class="px-4 py-2.5 text-center text-sm">
                            <button type="button" @click="abrirDetalhes({{ $usr->id }}, 'uploads', '{{ addslashes($usr->nome) }}')" class="{{ $usr->uploads_documentos_count > 0 ? 'text-amber-700 font-medium hover:underline cursor-pointer' : 'text-slate-400' }}">{{ $usr->uploads_documentos_count }}</button>
                        </td>
                        <td class="px-4 py-2.5 text-center text-sm">
                            <button type="button" @click="abrirDetalhes({{ $usr->id }}, 'acoes_processos', '{{ addslashes($usr->nome) }}')" class="{{ $usr->acoes_processos_count > 0 ? 'text-purple-700 font-medium hover:underline cursor-pointer' : 'text-slate-400' }}">{{ $usr->acoes_processos_count }}</button>
                        </td>
                        <td class="px-4 py-2.5 text-center text-sm">
                            <button type="button" @click="abrirDetalhes({{ $usr->id }}, 'acoes_estabelecimentos', '{{ addslashes($usr->nome) }}')" class="{{ $usr->acoes_estabelecimentos_count > 0 ? 'text-pink-700 font-medium hover:underline cursor-pointer' : 'text-slate-400' }}">{{ $usr->acoes_estabelecimentos_count }}</button>
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="text-sm font-bold {{ $usr->total_acoes > 0 ? 'text-indigo-700' : 'text-slate-400' }}">{{ $usr->total_acoes }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center text-xs text-slate-500">
                            @if($usr->ultimo_login_em)
                                {{ \Carbon\Carbon::parse($usr->ultimo_login_em)->format('d/m/Y H:i') }}
                            @elseif($usr->total_acoes > 0)
                                <span class="text-amber-600" title="Usuário tem ações registradas mas o registro de login não existia antes de 25/02/2026">Sem registro ⚠️</span>
                            @else
                                <span class="text-slate-400">Nunca</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8">
            <p class="text-sm text-slate-500">Nenhum usuário encontrado para o filtro selecionado.</p>
        </div>
        @endif
    </div>

    {{-- Modal de Detalhes de Atividade --}}
    <div x-show="modalAberto" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="modalAberto" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="fecharModal()"></div>
            <div x-show="modalAberto" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full p-6 max-h-[85vh] overflow-y-auto">
                
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900" x-text="tituloModal"></h3>
                        <p class="text-sm text-slate-500">Usuário: <strong x-text="nomeUsuario"></strong> — <span x-text="totalItens"></span> registro(s)</p>
                    </div>
                    <button @click="fecharModal()" class="p-1 text-slate-400 hover:text-slate-600 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="carregando" class="py-8 text-center">
                    <svg class="w-8 h-8 animate-spin text-indigo-500 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm text-slate-500 mt-2">Carregando...</p>
                </div>

                <div x-show="!carregando && itens.length > 0">
                    <div class="overflow-x-auto border border-slate-200 rounded-lg">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50/80 border-b border-slate-100">
                                <tr>
                                    <template x-for="col in colunas" :key="col.key">
                                        <th class="px-3 py-2 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider" x-text="col.label"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(item, idx) in itens" :key="idx">
                                    <tr class="hover:bg-slate-50">
                                        <template x-for="col in colunas" :key="col.key + '-' + idx">
                                            <td class="px-3 py-2 text-slate-700" x-text="item[col.key] || '-'"></td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginação --}}
                    <div x-show="totalPaginas > 1" class="flex items-center justify-between mt-3 pt-3 border-t border-slate-200">
                        <p class="text-xs text-slate-500">Página <span x-text="paginaAtual"></span> de <span x-text="totalPaginas"></span></p>
                        <div class="flex gap-2">
                            <button @click="mudarPagina(paginaAtual - 1)" :disabled="paginaAtual <= 1"
                                    class="px-3 py-1 text-xs border rounded hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">← Anterior</button>
                            <button @click="mudarPagina(paginaAtual + 1)" :disabled="paginaAtual >= totalPaginas"
                                    class="px-3 py-1 text-xs border rounded hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">Próximo →</button>
                        </div>
                    </div>
                </div>

                <div x-show="!carregando && itens.length === 0 && !erro" class="py-8 text-center">
                    <p class="text-sm text-slate-500">Nenhum registro encontrado.</p>
                </div>

                <div x-show="erro" class="py-4 text-center">
                    <p class="text-sm text-red-600" x-text="erro"></p>
                </div>
            </div>
        </div>
    </div>

    </div>{{-- Fim x-data detalhesAtividadeModal --}}

    <script>
    function detalhesAtividadeModal() {
        return {
            modalAberto: false,
            carregando: false,
            nomeUsuario: '',
            tipoAtual: '',
            usuarioIdAtual: null,
            itens: [],
            colunas: [],
            totalItens: 0,
            paginaAtual: 1,
            totalPaginas: 1,
            erro: '',

            get tituloModal() {
                const titulos = {
                    'documentos_criados': '📄 Documentos Criados',
                    'documentos_aprovados': '✅ Documentos Aprovados',
                    'uploads': '📤 Uploads de Documentos',
                    'acoes_processos': '⚙️ Ações em Processos',
                    'acoes_estabelecimentos': '🏢 Ações em Estabelecimentos',
                };
                return titulos[this.tipoAtual] || 'Detalhes';
            },

            getColunas(tipo) {
                const mapa = {
                    'documentos_criados': [
                        { key: 'numero_documento', label: 'Nº Documento' },
                        { key: 'nome', label: 'Nome' },
                        { key: 'status', label: 'Status' },
                        { key: 'data', label: 'Data' },
                    ],
                    'documentos_aprovados': [
                        { key: 'processo', label: 'Processo' },
                        { key: 'nome', label: 'Arquivo' },
                        { key: 'tipo_documento', label: 'Tipo' },
                        { key: 'data', label: 'Aprovado em' },
                    ],
                    'uploads': [
                        { key: 'processo', label: 'Processo' },
                        { key: 'nome', label: 'Arquivo' },
                        { key: 'status_aprovacao', label: 'Status' },
                        { key: 'data', label: 'Data' },
                    ],
                    'acoes_processos': [
                        { key: 'processo', label: 'Processo' },
                        { key: 'titulo', label: 'Ação' },
                        { key: 'descricao', label: 'Descrição' },
                        { key: 'data', label: 'Data' },
                    ],
                    'acoes_estabelecimentos': [
                        { key: 'estabelecimento', label: 'Estabelecimento' },
                        { key: 'acao', label: 'Ação' },
                        { key: 'observacao', label: 'Observação' },
                        { key: 'data', label: 'Data' },
                    ],
                };
                return mapa[tipo] || [];
            },

            abrirDetalhes(usuarioId, tipo, nome) {
                this.usuarioIdAtual = usuarioId;
                this.tipoAtual = tipo;
                this.nomeUsuario = nome;
                this.colunas = this.getColunas(tipo);
                this.paginaAtual = 1;
                this.modalAberto = true;
                this.carregarDados();
            },

            async carregarDados() {
                this.carregando = true;
                this.erro = '';
                this.itens = [];

                try {
                    const url = `{{ url('admin/usuarios-internos') }}/${this.usuarioIdAtual}/detalhes-atividade?tipo=${this.tipoAtual}&pagina=${this.paginaAtual}`;
                    const response = await fetch(url);
                    
                    if (!response.ok) throw new Error('Erro ao carregar dados');
                    
                    const data = await response.json();
                    this.itens = data.itens || [];
                    this.totalItens = data.total || 0;
                    this.totalPaginas = data.total_paginas || 1;
                } catch (error) {
                    console.error('Erro:', error);
                    this.erro = 'Erro ao carregar os dados. Tente novamente.';
                } finally {
                    this.carregando = false;
                }
            },

            mudarPagina(pagina) {
                if (pagina < 1 || pagina > this.totalPaginas) return;
                this.paginaAtual = pagina;
                this.carregarDados();
            },

            fecharModal() {
                this.modalAberto = false;
                this.itens = [];
                this.erro = '';
            }
        }
    }
    </script>
    @endif

    @if($aba === 'cadastros')

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
        <div class="xl:col-span-1 bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Gerar link de cadastro</h2>
                    <p class="text-xs text-slate-500 mt-1">Crie um link para o usuário interno se cadastrar sozinho e entrar em fila de aprovação.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.usuarios-internos.convites.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Título do link</label>
                    <input type="text" name="titulo" value="{{ old('titulo') }}" placeholder="Ex: Cadastro técnicos de Augustinópolis"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-sm transition">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Nível de acesso</label>
                    <select name="nivel_acesso" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-sm transition">
                        <option value="">Selecione</option>
                        @foreach($niveisPermitidos as $nivel)
                            <option value="{{ $nivel->value }}" {{ old('nivel_acesso') === $nivel->value ? 'selected' : '' }}>
                                {{ $nivel->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Município</label>
                    <select name="municipio_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-sm transition"
                        @if(auth('interno')->user()->nivel_acesso->value === 'gestor_municipal' && auth('interno')->user()->municipio_id) disabled @endif>
                        <option value="">Não vincular município</option>
                        @foreach($municipios as $municipio)
                            <option value="{{ $municipio->id }}" {{ (string) old('municipio_id', auth('interno')->user()->municipio_id) === (string) $municipio->id ? 'selected' : '' }}>
                                {{ $municipio->nome }}
                            </option>
                        @endforeach
                    </select>
                    @if(auth('interno')->user()->nivel_acesso->value === 'gestor_municipal' && auth('interno')->user()->municipio_id)
                        <input type="hidden" name="municipio_id" value="{{ auth('interno')->user()->municipio_id }}">
                    @endif
                    <p class="mt-1 text-xs text-slate-500">Obrigatório para convites municipais.</p>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Expira em</label>
                    <input type="datetime-local" name="expira_em" value="{{ old('expira_em') }}"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-sm transition">
                </div>

                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-sm shadow-blue-600/20 transition text-sm font-semibold">
                    Gerar link de cadastro
                </button>
            </form>
        </div>

        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Links de cadastro</h2>
                    <p class="text-xs text-slate-500 mt-1">Compartilhe o link ou QR Code com a equipe. O cadastro entra como pendente até aprovação.</p>
                </div>
                <span class="px-2 py-1 text-xs rounded-full bg-slate-100 text-slate-700 font-semibold">{{ $convitesCadastro->count() }}</span>
            </div>

            @if($convitesCadastro->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach($convitesCadastro as $convite)
                        @php($linkCadastro = route('cadastro-interno.show', $convite->token))
                        <div class="p-4">
                            <div class="flex items-start gap-4">
                                {{-- QR Code sempre visível --}}
                                <div class="flex-shrink-0 flex flex-col items-center gap-1.5">
                                    <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-sm">
                                        @if(!empty($qrCodes[$convite->id]))
                                            <img src="data:image/png;base64,{{ $qrCodes[$convite->id] }}" alt="QR Code" class="w-28 h-28">
                                        @else
                                            <div class="w-28 h-28 flex items-center justify-center">
                                                <p class="text-[10px] text-red-500 text-center">Erro ao gerar QR Code</p>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-slate-400 text-center leading-tight">Aponte a câmera<br>para acessar</p>
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-semibold text-slate-900">{{ $convite->titulo }}</p>
                                        <span class="px-2 py-0.5 text-[10px] font-medium rounded-full {{ \App\Enums\NivelAcesso::from($convite->nivel_acesso)->color() }}">
                                            {{ \App\Enums\NivelAcesso::from($convite->nivel_acesso)->label() }}
                                        </span>
                                        @if($convite->isDisponivel())
                                            <span class="w-2 h-2 rounded-full bg-green-500" title="Ativo"></span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-red-500" title="Indisponível"></span>
                                        @endif
                                    </div>

                                    <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                        <span>{{ $convite->municipio?->nome ?? 'Sem município' }}</span>
                                        <span>{{ $convite->usuarios_count }} cadastro(s)</span>
                                        <span>{{ $convite->expira_em ? 'Expira ' . $convite->expira_em->format('d/m/Y H:i') : 'Sem expiração' }}</span>
                                    </div>

                                    {{-- Link do cadastro --}}
                                    <div class="mt-2 flex items-center gap-2 bg-slate-50 rounded-lg px-3 py-2 border border-slate-200">
                                        <a href="{{ $linkCadastro }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 hover:underline truncate flex-1" title="{{ $linkCadastro }}">{{ $linkCadastro }}</a>
                                        <button type="button"
                                                onclick="navigator.clipboard.writeText('{{ $linkCadastro }}'); this.textContent='Copiado!'; setTimeout(() => this.textContent='Copiar', 1500)"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-medium whitespace-nowrap">
                                            Copiar
                                        </button>
                                    </div>

                                    {{-- Ações --}}
                                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                                        <a href="{{ $linkCadastro }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                            Abrir
                                        </a>
                                        <form method="POST" action="{{ route('admin.usuarios-internos.convites.destroy', $convite) }}"
                                              onsubmit="return confirm('Tem certeza que deseja excluir este link de cadastro?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center text-sm text-slate-500">
                    Nenhum link de cadastro foi gerado ainda.
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Cadastros pendentes de aprovação</h2>
                <p class="text-xs text-slate-500 mt-1">O usuário preenche o próprio cadastro e fica aguardando liberação do administrador.</p>
            </div>
            <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-700 font-semibold">{{ $pendentesAprovacao->count() }}</span>
        </div>

        @if($pendentesAprovacao->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50/80 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Usuário</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Município</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Convite</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Solicitado em</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pendentesAprovacao as $pendente)
                            <tr class="hover:bg-blue-50/40 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900">{{ $pendente->nome }}</div>
                                    <div class="text-xs text-slate-500">{{ $pendente->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ $pendente->municipioRelacionado?->nome ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ $pendente->convite?->titulo ?? 'Cadastro manual por link antigo' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ $pendente->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <form method="POST" action="{{ route('admin.usuarios-internos.aprovar-cadastro', $pendente) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-xs font-medium">
                                                Aprovar
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.usuarios-internos.rejeitar-cadastro', $pendente) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-xs font-medium">
                                                Rejeitar
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.usuarios-internos.show', $pendente) }}"
                                           class="px-3 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition text-xs font-medium">
                                            Ver
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6 text-center text-sm text-slate-500">
                Não há cadastros pendentes neste momento.
            </div>
        @endif
    </div>

    @endif

    @if($aba === 'usuarios')

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-3 mb-4">
        <form method="GET" action="{{ route('admin.usuarios-internos.index') }}" class="space-y-3">
            <input type="hidden" name="aba" value="usuarios">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                {{-- Nome --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Nome</label>
                    <input type="text" name="nome" value="{{ request('nome') }}" 
                           placeholder="Buscar por nome"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-sm transition">
                </div>

                {{-- CPF --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">CPF</label>
                    <input type="text" name="cpf" value="{{ request('cpf') }}" 
                           placeholder="000.000.000-00"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-sm transition">
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Email</label>
                    <input type="email" name="email" value="{{ request('email') }}" 
                           placeholder="email@exemplo.com"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-sm transition">
                </div>

                {{-- Município --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Município</label>
                    <input type="text" name="municipio" value="{{ request('municipio') }}" 
                           placeholder="Nome do município"
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-sm transition">
                </div>

                {{-- Nível de Acesso --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Nível de Acesso</label>
                    <select name="nivel_acesso" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-sm transition">
                        <option value="">Todos</option>
                        @foreach($niveisPermitidos as $nivel)
                            <option value="{{ $nivel->value }}" {{ request('nivel_acesso') == $nivel->value ? 'selected' : '' }}>
                                {{ $nivel->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                    <select name="ativo" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-sm transition">
                        <option value="">Todos</option>
                        <option value="1" {{ request('ativo') === '1' ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ request('ativo') === '0' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.usuarios-internos.index') }}" class="px-3.5 py-1.5 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition text-sm font-semibold">
                    Limpar Filtros
                </a>
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-sm transition text-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                            <a href="{{ route('admin.usuarios-internos.index', array_merge(request()->all(), ['sort' => 'nome', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-blue-600">
                                Nome
                                @if(request('sort') === 'nome')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction') === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                                    </svg>
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">CPF</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Município</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Nível de Acesso</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Último Login</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($usuarios as $usuario)
                        <tr class="hover:bg-blue-50/40 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-blue-600 font-bold text-[11px]">{{ strtoupper(substr($usuario->nome, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-[13px] font-semibold text-slate-800">{{ $usuario->nome }}</div>
                                        <div class="flex items-center gap-2 flex-wrap mt-1">
                                            @if($usuario->cargo)
                                                <span class="text-xs text-slate-500">{{ $usuario->cargo }}</span>
                                            @endif
                                            @if($usuario->status_cadastro === 'pendente')
                                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-amber-100 text-amber-700">Aguardando aprovação</span>
                                            @elseif($usuario->status_cadastro === 'rejeitado')
                                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-red-100 text-red-700">Cadastro rejeitado</span>
                                            @endif
                                            @if($usuario->convite_id)
                                                <span class="px-2 py-0.5 text-[10px] font-medium rounded-full bg-slate-100 text-slate-700">Via link</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-[13px] text-slate-600 whitespace-nowrap tabular-nums">{{ $usuario->cpf_formatado }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $usuario->email }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">
                                @if($usuario->municipioRelacionado)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-medium">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $usuario->municipioRelacionado->nome }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $usuario->nivel_acesso->color() }}">
                                    {{ $usuario->nivel_acesso->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-[13px] text-slate-600 whitespace-nowrap tabular-nums">
                                @if($usuario->ultimo_login_em)
                                    {{ $usuario->ultimo_login_em->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-slate-400 text-xs">Nunca acessou</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($usuario->status_cadastro === 'pendente')
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-100 text-amber-800">Pendente</span>
                                @elseif($usuario->status_cadastro === 'rejeitado')
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-red-100 text-red-800">Rejeitado</span>
                                @elseif($usuario->ativo)
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                @else
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.usuarios-internos.show', $usuario) }}" 
                                       class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Visualizar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.usuarios-internos.edit', $usuario) }}" 
                                       class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.usuarios-internos.destroy', $usuario) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="Excluir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                Nenhum usuário encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($usuarios->hasPages())
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

    {{-- Resumo --}}
    <div class="mt-3 text-xs text-slate-500">
        Mostrando {{ $usuarios->firstItem() ?? 0 }} a {{ $usuarios->lastItem() ?? 0 }} de {{ $usuarios->total() }} usuários
    </div>
    @endif
</div>
@endsection
