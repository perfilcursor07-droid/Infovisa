@extends('layouts.admin')

@section('title', 'Ordens de Serviço')

@section('content')
<div class="container-fluid px-4 py-6" x-data="{
    modalExcluir: false,
    osId: null,
    osNumero: '',
    senhaAssinatura: '',
    erro: '',
    carregando: false,
    
    abrirModalExcluir(id, numero) {
        this.osId = id;
        this.osNumero = numero;
        this.senhaAssinatura = '';
        this.erro = '';
        this.carregando = false;
        this.modalExcluir = true;
        this.$nextTick(() => {
            this.$refs.senhaInput?.focus();
        });
    },
    
    async executarExclusao() {
        if (!this.senhaAssinatura) {
            this.erro = 'Digite sua senha de assinatura digital';
            return;
        }
        
        this.carregando = true;
        this.erro = '';
        
        try {
            const response = await fetch(`{{ url('/admin/ordens-servico') }}/${this.osId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'DELETE',
                    senha_assinatura: this.senhaAssinatura
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.modalExcluir = false;
                window.location.reload();
            } else {
                this.erro = data.message || 'Erro ao excluir ordem de serviço';
            }
        } catch (error) {
            console.error('Erro:', error);
            this.erro = 'Erro ao processar exclusão';
        } finally {
            this.carregando = false;
        }
    }
}" @excluir-os.window="abrirModalExcluir($event.detail.id, $event.detail.numero)">
    {{-- Cabeçalho --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ordens de Serviço</h1>
            <p class="text-sm text-gray-600 mt-1">Gerencie as ordens de serviço do sistema</p>
        </div>
        @if(!in_array(auth('interno')->user()->nivel_acesso->value, ['tecnico_estadual', 'tecnico_municipal']))
        <a href="{{ route('admin.ordens-servico.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova Ordem de Serviço
        </a>
        @endif
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('admin.ordens-servico.index') }}" class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
        <div class="px-6 py-5">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                {{-- Estabelecimento --}}
                <div>
                    <label for="estabelecimento" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Estabelecimento</label>
                    <div class="relative">
                        <input type="text"
                               id="estabelecimento"
                               name="estabelecimento"
                               value="{{ $filters['estabelecimento'] ?? '' }}"
                               placeholder="Buscar por CNPJ/CPF, fantasia ou razão social"
                               class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M5 11a6 6 0 1112 0 6 6 0 01-12 0z"/>
                            </svg>
                        </span>
                    </div>
                </div>

                {{-- Técnico --}}
                <div>
                    <label for="tecnico" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Técnico</label>
                    <div class="relative">
                        <input type="text"
                               id="tecnico"
                               name="tecnico"
                               value="{{ $filters['tecnico'] ?? '' }}"
                               placeholder="Buscar por nome ou e-mail do técnico"
                               class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                    </div>
                </div>

                {{-- Data Início --}}
                <div>
                    <label for="data_inicio" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Data de Início</label>
                    <input type="date"
                           id="data_inicio"
                           name="data_inicio"
                           value="{{ $filters['data_inicio'] ?? '' }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                {{-- Data Fim --}}
                <div>
                    <label for="data_fim" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Data de Término</label>
                    <input type="date"
                           id="data_fim"
                           name="data_fim"
                           value="{{ $filters['data_fim'] ?? '' }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>

                {{-- Status --}}
                <div>
                    <label for="status" class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">Status</label>
                    <select id="status"
                            name="status"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Todos</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-xl border-t border-gray-200">
            <a href="{{ route('admin.ordens-servico.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 transition">
                Limpar
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                Aplicar filtros
            </button>
        </div>
    </form>

    {{-- Mensagens de sucesso --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    {{-- Tabela de Ordens de Serviço --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($ordensServico->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Número
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estabelecimento
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Técnicos
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data Início
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data Fim
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($ordensServico as $os)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('admin.ordens-servico.show', $os) }}'">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-blue-600 hover:text-blue-800">{{ $os->numero }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @php($todosEstabsIndex = $os->getTodosEstabelecimentos())
                            @if($todosEstabsIndex->count() > 0)
                                <div class="space-y-1.5">
                                    @foreach($todosEstabsIndex->take(2) as $est)
                                        <div>
                                            <div class="text-sm text-gray-900">{{ $est->nome_fantasia ?: $est->razao_social }}</div>
                                            <div class="text-xs text-gray-500">{{ $est->razao_social }}</div>
                                        </div>
                                    @endforeach
                                    @if($todosEstabsIndex->count() > 2)
                                        <div class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-medium">
                                            +{{ $todosEstabsIndex->count() - 2 }} estabelecimento(s)
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="flex items-center gap-1 text-amber-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span class="text-xs font-medium">Sem estabelecimento</span>
                                </div>
                                <div class="text-xs text-gray-500">Vincular ao editar/finalizar</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($os->tecnicos()->count() > 0)
                                <div class="text-sm text-gray-900">
                                    @foreach($os->tecnicos() as $tecnico)
                                        <div class="text-xs">{{ $tecnico->nome }}</div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $os->data_inicio ? $os->data_inicio->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                {{ $os->data_fim ? $os->data_fim->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {!! $os->status_badge !!}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.ordens-servico.show', $os) }}" 
                                   class="text-green-600 hover:text-green-900"
                                   title="Visualizar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if(!in_array(auth('interno')->user()->nivel_acesso->value, ['tecnico_estadual', 'tecnico_municipal']))
                                <a href="{{ route('admin.ordens-servico.edit', $os) }}" 
                                   class="text-blue-600 hover:text-blue-900"
                                   title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endif
                                @if(!in_array(auth('interno')->user()->nivel_acesso->value, ['tecnico_estadual', 'tecnico_municipal']))
                                <button type="button"
                                        @click.stop="$dispatch('excluir-os', { id: {{ $os->id }}, numero: '{{ $os->numero }}' })"
                                        class="text-red-600 hover:text-red-900"
                                        title="Excluir">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $ordensServico->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma ordem de serviço</h3>
            <p class="mt-1 text-sm text-gray-500">Comece criando uma nova ordem de serviço.</p>
            @if(!in_array(auth('interno')->user()->nivel_acesso->value, ['tecnico_estadual', 'tecnico_municipal']))
            <div class="mt-6">
                <a href="{{ route('admin.ordens-servico.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nova Ordem de Serviço
                </a>
            </div>
            @endif
        </div>
        @endif
    </div>
    
    {{-- Modal de Confirmação de Exclusão --}}
    <div x-show="modalExcluir" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="modalExcluir = false"></div>

            {{-- Modal --}}
            <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                {{-- Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-red-600 to-red-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Confirmar Exclusão
                    </h3>
                    <button type="button" @click="modalExcluir = false" class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Conteúdo --}}
                <div class="px-6 py-6">
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm font-bold text-red-800">Atenção!</p>
                                <p class="text-sm text-red-700 mt-1">Esta ação não pode ser desfeita. A ordem de serviço será excluída permanentemente.</p>
                            </div>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 mb-4">
                        Você está prestes a excluir a Ordem de Serviço: <strong class="text-red-600" x-text="osNumero"></strong>
                    </p>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Senha de Assinatura Digital <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               x-ref="senhaInput"
                               x-model="senhaAssinatura"
                               @keyup.enter="executarExclusao()"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               :class="{ 'border-red-500': erro }"
                               placeholder="Digite sua senha de assinatura">
                        <p class="mt-1 text-xs text-gray-500">
                            Use a mesma senha configurada em <a href="{{ route('admin.assinatura.configurar-senha') }}" class="text-blue-600 hover:underline" target="_blank">Configurar Senha de Assinatura</a>
                        </p>
                        <p x-show="erro" x-text="erro" class="mt-1 text-sm text-red-600"></p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                    <button type="button" 
                            @click="modalExcluir = false" 
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                            :disabled="carregando">
                        Cancelar
                    </button>
                    <button type="button" 
                            @click="executarExclusao()"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2"
                            :disabled="carregando || !senhaAssinatura">
                        <svg x-show="carregando" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="carregando ? 'Excluindo...' : 'Confirmar Exclusão'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
