@extends('layouts.admin')

@section('title', 'Todas as Tarefas')
@section('page-title', 'Todas as Tarefas')

@section('content')
<div class="max-w-8xl mx-auto" x-data="todasTarefas()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-lg font-bold text-gray-900">Todas as Tarefas</h1>
            <p class="text-[11px] text-gray-400">Tarefas pessoais e demandas do setor</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 text-[11px] text-gray-500 hover:text-gray-700 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
        <div class="px-4 py-2.5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Filtrar por</span>
            <span class="text-[10px] text-gray-400" x-text="totalFiltrado + ' tarefa(s)'"></span>
        </div>
        <div class="px-4 py-3 flex flex-wrap gap-2">
            <button @click="setFilter('todos')"
                    class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition border"
                    :class="filtro === 'todos' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                Todas <span class="ml-0.5 opacity-70" x-text="contadores.total"></span>
            </button>
            <span class="w-px h-6 bg-gray-200 self-center"></span>
            <button @click="setFilter('para_mim')"
                    class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition border"
                    :class="filtro === 'para_mim' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-blue-700 border-blue-200 hover:bg-blue-50'">
                Para Mim <span class="ml-0.5 opacity-70" x-text="contadores.para_mim"></span>
            </button>
            <button @click="setFilter('os')"
                    class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition border"
                    :class="filtro === 'os' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-blue-600 border-blue-200 hover:bg-blue-50'">
                OS <span class="ml-0.5 opacity-70" x-text="contadores.os"></span>
            </button>
            <button @click="setFilter('assinatura')"
                    class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition border"
                    :class="filtro === 'assinatura' ? 'bg-amber-600 text-white border-amber-600' : 'bg-white text-amber-700 border-amber-200 hover:bg-amber-50'">
                Assinaturas <span class="ml-0.5 opacity-70" x-text="contadores.assinatura"></span>
            </button>
            <button @click="setFilter('prazo_documento')"
                    class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition border"
                    :class="filtro === 'prazo_documento' ? 'bg-rose-600 text-white border-rose-600' : 'bg-white text-rose-700 border-rose-200 hover:bg-rose-50'">
                Prazos <span class="ml-0.5 opacity-70" x-text="contadores.prazo_documento"></span>
            </button>
            <span class="w-px h-6 bg-gray-200 self-center"></span>
            <button @click="setFilter('setor')"
                    class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition border"
                    :class="filtro === 'setor' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-purple-700 border-purple-200 hover:bg-purple-50'">
                Meu Setor <span class="ml-0.5 opacity-70" x-text="contadores.setor"></span>
            </button>
            <button @click="setFilter('aprovacao')"
                    class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition border"
                    :class="filtro === 'aprovacao' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-purple-600 border-purple-200 hover:bg-purple-50'">
                Aprovações <span class="ml-0.5 opacity-70" x-text="contadores.aprovacao"></span>
            </button>
            <button @click="setFilter('resposta')"
                    class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition border"
                    :class="filtro === 'resposta' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-green-700 border-green-200 hover:bg-green-50'">
                Respostas <span class="ml-0.5 opacity-70" x-text="contadores.resposta"></span>
            </button>
        </div>
    </div>

    {{-- Lista --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- Loading --}}
        <template x-if="loading">
            <div class="p-8 text-center">
                <svg class="animate-spin h-5 w-5 text-gray-300 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>
        </template>

        {{-- Vazio --}}
        <template x-if="!loading && tarefas.length === 0">
            <div class="p-10 text-center">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-700">Nenhuma tarefa pendente</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Tudo em dia!</p>
            </div>
        </template>

        {{-- Itens --}}
        <template x-if="!loading && tarefas.length > 0">
            <div class="divide-y divide-gray-50">
                <template x-for="(t, index) in tarefas" :key="t.tipo + (t.id || t.processo_id) + index">
                    <div>
                        {{-- Separador de grupo --}}
                        <template x-if="filtro === 'todos' && showGroupHeader(t, index)">
                            <div class="px-4 py-1.5 border-b"
                                 :class="t.grupo === 'para_mim' ? 'bg-blue-50/60 border-blue-100/60' : 'bg-purple-50/60 border-purple-100/60'">
                                <span class="text-[11px] font-semibold uppercase tracking-wider flex items-center gap-1.5"
                                      :class="t.grupo === 'para_mim' ? 'text-blue-600' : 'text-purple-600'">
                                    <template x-if="t.grupo === 'para_mim'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </template>
                                    <template x-if="t.grupo !== 'para_mim'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </template>
                                    <span x-text="t.grupo === 'para_mim' ? 'Para Mim' : 'Demandas do Setor'"></span>
                                </span>
                            </div>
                        </template>

                        {{-- Item --}}
                        <a :href="t.url"
                           class="flex items-center gap-2.5 px-4 py-2.5 transition"
                           :class="t.atrasado ? 'bg-red-50/30 hover:bg-red-50/60' : 'hover:bg-gray-50/80'">

                            {{-- Ícone --}}
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                                 :class="{
                                     'bg-red-100': t.atrasado,
                                     'bg-blue-100': !t.atrasado && t.tipo === 'os',
                                     'bg-amber-100': !t.atrasado && t.tipo === 'assinatura',
                                     'bg-rose-100': !t.atrasado && t.tipo === 'prazo_documento',
                                     'bg-purple-100': !t.atrasado && (t.tipo === 'aprovacao' || t.tipo === 'rascunho' || t.tipo === 'rascunho_lote'),
                                     'bg-green-100': !t.atrasado && t.tipo === 'resposta'
                                 }">
                                <template x-if="t.tipo === 'os'">
                                    <svg class="w-3.5 h-3.5" :class="t.atrasado ? 'text-red-600' : 'text-blue-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </template>
                                <template x-if="t.tipo === 'assinatura'">
                                    <svg class="w-3.5 h-3.5" :class="t.atrasado ? 'text-red-600' : 'text-amber-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </template>
                                <template x-if="t.tipo === 'prazo_documento'">
                                    <svg class="w-3.5 h-3.5" :class="t.atrasado ? 'text-red-600' : 'text-rose-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </template>
                                <template x-if="t.tipo === 'aprovacao'">
                                    <svg class="w-3.5 h-3.5" :class="t.atrasado ? 'text-red-600' : 'text-purple-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </template>
                                <template x-if="t.tipo === 'resposta'">
                                    <svg class="w-3.5 h-3.5" :class="t.atrasado ? 'text-red-600' : 'text-green-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                </template>
                                <template x-if="t.tipo === 'rascunho' || t.tipo === 'rascunho_lote'">
                                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </template>
                            </div>

                            {{-- Conteúdo --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded"
                                          :class="{
                                              'bg-blue-100 text-blue-700': t.tipo === 'os',
                                              'bg-amber-100 text-amber-700': t.tipo === 'assinatura',
                                              'bg-rose-100 text-rose-700': t.tipo === 'prazo_documento',
                                              'bg-purple-100 text-purple-700': t.tipo === 'aprovacao' || t.tipo === 'rascunho' || t.tipo === 'rascunho_lote',
                                              'bg-green-100 text-green-700': t.tipo === 'resposta'
                                          }"
                                          x-text="{'os':'OS','assinatura':'Assinatura','prazo_documento':'Prazo','aprovacao':'Aprovação','resposta':'Resposta','rascunho':'Rascunho','rascunho_lote':'Rascunho'}[t.tipo]"></span>
                                    <template x-if="t.numero_processo">
                                        <span class="text-[10px] text-gray-400" x-text="t.numero_processo"></span>
                                    </template>
                                    <template x-if="t.tipo_processo">
                                        <span class="text-[10px] text-gray-300" x-text="t.tipo_processo"></span>
                                    </template>
                                    <template x-if="t.is_lote">
                                        <span class="text-[10px] px-1 py-0.5 rounded bg-purple-50 text-purple-600 font-medium">Lote</span>
                                    </template>
                                </div>
                                <p class="text-[13px] font-medium text-gray-800 truncate mt-0.5" x-text="t.titulo"></p>
                                <p class="text-[11px] text-gray-400 truncate" x-text="t.subtitulo"></p>
                                <template x-if="t.tipo === 'os' && t.tipo_acao">
                                    <p class="text-[10px] text-blue-500 truncate" x-text="t.tipo_acao"></p>
                                </template>
                                <template x-if="t.tipo === 'os' && (t.em_finalizacao || t.atrasado)">
                                    <p class="text-[10px] font-medium mt-0.5 flex items-center gap-0.5"
                                       :class="t.atrasado ? 'text-red-500' : 'text-amber-600'">
                                        <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span x-text="t.atrasado ? 'Prazo de finalização expirado!' : 'Finalizar até ' + t.prazo_finalizacao_formatado"></span>
                                    </p>
                                </template>
                                <template x-if="t.tipo === 'os' && !t.em_finalizacao && !t.atrasado && t.data_fim_formatada">
                                    <p class="text-[10px] text-gray-400 mt-0.5">Encerramento: <span x-text="t.data_fim_formatada"></span></p>
                                </template>
                                <template x-if="t.tipo === 'prazo_documento'">
                                    <p class="text-[10px] mt-0.5" :class="t.atrasado ? 'text-red-500' : 'text-amber-600'" x-text="t.prazo_texto"></p>
                                </template>
                            </div>

                            {{-- Badge + Data --}}
                            <div class="flex-shrink-0 text-right">
                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full whitespace-nowrap"
                                      :class="getBadgeClass(t)"
                                      x-text="getBadgeText(t)"></span>
                                <p class="text-[10px] text-gray-300 mt-1" x-text="t.data"></p>
                            </div>
                        </a>
                    </div>
                </template>
            </div>
        </template>

        {{-- Paginação --}}
        <template x-if="!loading && lastPage > 1">
            <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <span class="text-[11px] text-gray-400">
                    <span x-text="((currentPage - 1) * perPage) + 1"></span>–<span x-text="Math.min(currentPage * perPage, totalFiltrado)"></span> de <span x-text="totalFiltrado"></span>
                </span>
                <div class="flex items-center gap-1">
                    <button @click="prevPage()" :disabled="currentPage <= 1"
                            class="p-1.5 rounded-lg text-gray-400 hover:bg-white hover:text-gray-600 transition disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <span class="text-[11px] text-gray-500 px-1.5" x-text="currentPage + '/' + lastPage"></span>
                    <button @click="nextPage()" :disabled="currentPage >= lastPage"
                            class="p-1.5 rounded-lg text-gray-400 hover:bg-white hover:text-gray-600 transition disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- Legenda --}}
    <div class="flex flex-wrap items-center gap-3 mt-3 text-[10px] text-gray-400 px-1">
        <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> Para mim</span>
        <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span> Setor</span>
        <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Atrasado</span>
    </div>
</div>

<script>
function todasTarefas() {
    return {
        tarefas: [],
        loading: true,
        currentPage: 1,
        lastPage: 1,
        totalFiltrado: 0,
        perPage: 20,
        filtro: @json(request('filtro', 'todos')),
        contadores: { total: 0, aprovacao: 0, resposta: 0, assinatura: 0, rascunho: 0, prazo_documento: 0, os: 0, para_mim: 0, setor: 0 },

        init() { this.load(); },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.currentPage, per_page: this.perPage, filtro: this.filtro });
                const r = await fetch(`{{ route('admin.dashboard.todas-tarefas-paginadas') }}?${params}`);
                const d = await r.json();
                this.tarefas = d.data;
                this.currentPage = d.current_page;
                this.lastPage = d.last_page;
                this.totalFiltrado = d.total;
                this.contadores = d.contadores;
            } catch(e) { console.error(e); }
            this.loading = false;
        },

        prevPage() { if (this.currentPage > 1) { this.currentPage--; this.load(); } },
        nextPage() { if (this.currentPage < this.lastPage) { this.currentPage++; this.load(); } },

        setFilter(value) {
            this.filtro = value;
            this.currentPage = 1;
            this.load();
        },

        showGroupHeader(t, index) {
            if (!t.grupo) return false;
            if (index === 0) return true;
            const prev = this.tarefas[index - 1];
            return prev && prev.grupo !== t.grupo;
        },

        getBadgeClass(t) {
            if (t.tipo === 'assinatura') return 'bg-amber-100 text-amber-700';
            if (t.tipo === 'rascunho' || t.tipo === 'rascunho_lote') return 'bg-purple-100 text-purple-700';
            if (t.tipo === 'prazo_documento') {
                if (t.atrasado) return 'bg-red-100 text-red-700';
                if (t.dias_restantes === 0) return 'bg-orange-100 text-orange-700';
                if (t.dias_restantes !== null && t.dias_restantes <= 2) return 'bg-amber-100 text-amber-700';
                return 'bg-yellow-100 text-yellow-700';
            }
            if (t.tipo === 'os') {
                const diasOs = t.dias_para_finalizar;
                if (t.atrasado) return 'bg-red-100 text-red-700';
                if (diasOs === null) return 'bg-gray-100 text-gray-500';
                if (diasOs === 0) return 'bg-orange-100 text-orange-700';
                if (t.em_finalizacao) {
                    if (diasOs <= 3) return 'bg-orange-100 text-orange-700';
                    if (diasOs <= 7) return 'bg-amber-100 text-amber-700';
                    return 'bg-yellow-100 text-yellow-700';
                }
                return 'bg-green-100 text-green-700';
            }
            if (t.is_licenciamento === false) return 'bg-gray-100 text-gray-500';
            if (t.atrasado) return 'bg-red-100 text-red-700';
            if (t.dias_restantes === 0) return 'bg-orange-100 text-orange-700';
            if (t.dias_restantes !== null && t.dias_restantes <= 3) return 'bg-amber-100 text-amber-700';
            if (t.dias_restantes === null) return 'bg-gray-100 text-gray-500';
            return 'bg-green-100 text-green-700';
        },

        getBadgeText(t) {
            if (t.tipo === 'assinatura') return t.is_lote ? 'Lote' : 'Assinar';
            if (t.tipo === 'rascunho_lote') return 'Editar';
            if (t.tipo === 'rascunho') return 'Abrir';
            if (t.tipo === 'prazo_documento') {
                if (t.atrasado) return 'Vencido';
                if (t.dias_restantes === 0) return 'Hoje';
                if (t.dias_restantes === null) return 'Prazo';
                return t.dias_restantes + 'd';
            }
            if (t.tipo === 'os') {
                const diasOs = t.dias_para_finalizar;
                if (t.atrasado) return 'Atrasado';
                if (diasOs === null) return 'Sem prazo';
                if (t.em_finalizacao) {
                    if (diasOs === 0) return 'Último dia';
                    return diasOs + 'd p/ finalizar';
                }
                if (diasOs === 0) return 'Último dia';
                return diasOs + 'd';
            }
            if (t.is_licenciamento === false) return 'Verificar';
            if (t.tipo === 'resposta') {
                if (t.atrasado) return Math.abs(t.dias_restantes) + 'd atraso';
                if (t.dias_restantes === 0) return 'Hoje';
                if (t.dias_restantes === null) return 'Verificar';
                return t.dias_restantes + 'd';
            }
            if (t.atrasado) return Math.abs(t.dias_restantes) + 'd atraso';
            if (t.dias_restantes === 0) return 'Hoje';
            if (t.dias_restantes === null) return '-';
            return t.dias_restantes + 'd';
        }
    }
}
</script>
@endsection
