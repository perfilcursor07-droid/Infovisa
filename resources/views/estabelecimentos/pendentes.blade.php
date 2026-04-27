@extends('layouts.admin')

@section('title', 'Estabelecimentos Pendentes')
@section('page-title', 'Estabelecimentos Pendentes de Aprovação')

@section('content')
<div class="max-w-8xl mx-auto space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Pendentes de Aprovação</h2>
            <p class="text-xs text-gray-400 mt-0.5">Estabelecimentos aguardando análise e aprovação</p>
        </div>
        <a href="{{ route('admin.estabelecimentos.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Voltar
        </a>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-gray-100 rounded-lg p-1 text-sm">
        <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="flex-1 text-center px-3 py-2 rounded-md font-medium transition {{ request()->routeIs('admin.estabelecimentos.pendentes') ? 'bg-white text-amber-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            Pendentes
            @if(isset($totalPendentes) && $totalPendentes > 0)
                <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 font-bold">{{ $totalPendentes }}</span>
            @endif
        </a>
        <a href="{{ route('admin.estabelecimentos.rejeitados') }}" class="flex-1 text-center px-3 py-2 rounded-md font-medium transition {{ request()->routeIs('admin.estabelecimentos.rejeitados') ? 'bg-white text-red-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            Rejeitados
            @if(isset($totalRejeitados) && $totalRejeitados > 0)
                <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full bg-red-100 text-red-700 font-bold">{{ $totalRejeitados }}</span>
            @endif
        </a>
        <a href="{{ route('admin.estabelecimentos.desativados') }}" class="flex-1 text-center px-3 py-2 rounded-md font-medium transition {{ request()->routeIs('admin.estabelecimentos.desativados') ? 'bg-white text-gray-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            Desativados
            @if(isset($totalDesativados) && $totalDesativados > 0)
                <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full bg-gray-200 text-gray-700 font-bold">{{ $totalDesativados }}</span>
            @endif
        </a>
    </div>

    {{-- Busca --}}
    <form method="GET" action="{{ route('admin.estabelecimentos.pendentes') }}" class="flex gap-2">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por CNPJ, CPF, nome fantasia, razão social, município..."
                   class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">Buscar</button>
        @if(request('search'))
            <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">Limpar</a>
        @endif
    </form>

    {{-- Lista --}}
    @if($estabelecimentos->count() > 0)
        <div class="space-y-3">
            @foreach($estabelecimentos as $estabelecimento)
                @php
                    $atividades = $atividadesPorEstabelecimento[$estabelecimento->id] ?? collect();
                    $tempoEspera = (int) $estabelecimento->created_at->diffInDays(now());
                @endphp
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow"
                     x-data="{ showAtividades: false }">
                    <div class="p-4">
                        {{-- Linha 1: Nome + Badges --}}
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 leading-tight">{{ $estabelecimento->nome_razao_social }}</h3>
                                @if($estabelecimento->nome_fantasia && $estabelecimento->tipo_pessoa === 'juridica' && $estabelecimento->nome_fantasia !== $estabelecimento->razao_social)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $estabelecimento->nome_fantasia }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-[11px] px-2 py-0.5 rounded-full font-medium {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600' }}">
                                    {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'PJ' : 'PF' }}
                                </span>
                                @if($tempoEspera > 5)
                                    <span class="text-[11px] px-2 py-0.5 rounded-full font-medium {{ $tempoEspera > 15 ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600' }}">
                                        {{ $tempoEspera }}d esperando
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Linha 2: Dados --}}
                        <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                {{ $estabelecimento->documento_formatado }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $estabelecimento->cidade }}/{{ $estabelecimento->estado }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $estabelecimento->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        {{-- Linha 3: Ações --}}
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Analisar
                            </a>
                            <button @click="showAtividades = !showAtividades"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                <span x-text="showAtividades ? 'Ocultar' : 'Ver Atividades'"></span>
                            </button>
                            <form action="{{ route('admin.estabelecimentos.aprovar', $estabelecimento->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" onclick="return confirm('Aprovar este estabelecimento?')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Aprovar
                                </button>
                            </form>
                            <button @click="window.dispatchEvent(new CustomEvent('abrir-modal-rejeitar', { detail: { id: {{ $estabelecimento->id }}, nome: '{{ addslashes($estabelecimento->nome_razao_social) }}' } }))"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Rejeitar
                            </button>
                        </div>
                    </div>

                    {{-- Painel de Atividades (expandível) --}}
                    <div x-show="showAtividades" x-collapse x-cloak
                         class="border-t border-gray-100 bg-indigo-50 px-4 py-3">
                        <h4 class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Atividades marcadas pelo estabelecimento</h4>
                        @if($atividades->count() > 0)
                            <div class="space-y-1.5">
                                @foreach($atividades as $atividade)
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="w-5 h-5 rounded bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                        <span class="text-indigo-600 font-mono font-medium">{{ $atividade->codigo }}</span>
                                        @if($atividade->descricao)
                                            <span class="text-gray-700">{{ $atividade->descricao }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-500">Nenhuma atividade encontrada para este estabelecimento.</p>
                            <a href="{{ route('admin.estabelecimentos.atividades.edit', $estabelecimento->id) }}"
                               class="inline-flex items-center gap-1 mt-2 text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                                Gerenciar atividades
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($estabelecimentos->hasPages())
            <div class="mt-4">{{ $estabelecimentos->links() }}</div>
        @endif
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900">Nenhum estabelecimento pendente</h3>
            <p class="text-sm text-gray-400 mt-1">Todos os cadastros foram analisados</p>
            <a href="{{ route('admin.estabelecimentos.index') }}" class="inline-flex items-center gap-1 mt-4 text-sm text-blue-600 hover:text-blue-700 font-medium">
                Ver todos os estabelecimentos
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    @endif
</div>

{{-- Modal de Rejeição --}}
<div
    x-data="{
        aberto: false,
        id: null,
        nome: '',
        motivo: '',
        observacao: '',
        action: '',
        init() {
            window.addEventListener('abrir-modal-rejeitar', (e) => {
                this.id = e.detail.id;
                this.nome = e.detail.nome;
                this.motivo = '';
                this.observacao = '';
                this.action = '{{ url('admin/estabelecimentos') }}/' + this.id + '/rejeitar';
                this.aberto = true;
                this.$nextTick(() => this.$refs.motivo.focus());
            });
        }
    }"
    x-show="aberto"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    @keydown.escape.window="aberto = false"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="aberto = false"></div>

    {{-- Modal --}}
    <div
        x-show="aberto"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-md bg-white rounded-2xl shadow-xl z-10"
        @click.stop
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Rejeitar Estabelecimento</h3>
                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-1" x-text="nome"></p>
                </div>
            </div>
            <button @click="aberto = false" class="text-gray-400 hover:text-gray-600 transition p-1 rounded-lg hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Form --}}
        <form :action="action" method="POST">
            @csrf
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Motivo da Rejeição <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        x-ref="motivo"
                        name="motivo_rejeicao"
                        x-model="motivo"
                        rows="3"
                        required
                        placeholder="Descreva o motivo pelo qual este estabelecimento está sendo rejeitado..."
                        class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none placeholder-gray-400"
                    ></textarea>
                    <p class="text-xs text-gray-400 mt-1">Este motivo será exibido ao responsável pelo estabelecimento.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Observação <span class="text-gray-400 font-normal">(opcional)</span>
                    </label>
                    <textarea
                        name="observacao"
                        x-model="observacao"
                        rows="2"
                        placeholder="Informações adicionais para uso interno..."
                        class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none placeholder-gray-400"
                    ></textarea>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                <button type="button" @click="aberto = false"
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
                <button type="submit"
                        :disabled="!motivo.trim()"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Confirmar Rejeição
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
