@extends('layouts.admin')

@section('title', 'Novo Tipo de Serviço')
@section('page-title', 'Novo Tipo de Serviço')

@section('content')
<div class="max-w-3xl mx-auto" x-data="tipoServicoForm()">
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-servico']) }}" 
           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.configuracoes.tipos-servico.store') }}" method="POST" @submit="onSubmit">
            @csrf

            <div class="space-y-5">
                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="nome" id="nome" value="{{ old('nome') }}" required
                           placeholder="Ex: Serviço de Alimentação, Serviço de Saúde"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror">
                    @error('nome')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="3"
                              placeholder="Descreva o tipo de serviço..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descricao') border-red-500 @enderror">{{ old('descricao') }}</textarea>
                    @error('descricao')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Escopo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Escopo *</label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer transition"
                               :class="escopo === 'estadual' ? 'border-blue-400 bg-blue-50' : 'border-gray-200'">
                            <input type="radio" name="escopo" value="estadual" x-model="escopo" class="text-blue-600">
                            <span class="text-sm">🏛️ Estadual</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer transition"
                               :class="escopo === 'municipal' ? 'border-green-400 bg-green-50' : 'border-gray-200'">
                            <input type="radio" name="escopo" value="municipal" x-model="escopo" class="text-green-600">
                            <span class="text-sm">🏘️ Municipal</span>
                        </label>
                    </div>
                </div>

                <!-- Município (aparece só quando municipal) -->
                <div x-show="escopo === 'municipal'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Município *</label>
                    <select name="municipio_id" x-model="municipioId" :required="escopo === 'municipal'"
                            @change="onMunicipioChange()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione...</option>
                        @foreach($municipios as $mun)
                        <option value="{{ $mun->id }}" {{ old('municipio_id') == $mun->id ? 'selected' : '' }}>{{ $mun->nome }}</option>
                        @endforeach
                    </select>
                    @error('municipio_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Importar CNAEs da Pactuação -->
                <div x-show="escopo === 'municipal' && municipioId" x-cloak>
                    <div class="border border-green-200 rounded-lg bg-green-50/50 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="text-sm font-medium text-green-800 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Importar Atividades da Pactuação
                                </h4>
                                <p class="text-xs text-green-600 mt-1">Selecione os CNAEs que deseja importar como atividades deste tipo de serviço (facultativo)</p>
                            </div>
                            <button type="button" @click="carregarCnaes()" 
                                    :disabled="carregando"
                                    class="px-3 py-1.5 text-xs font-medium text-green-700 bg-green-100 border border-green-300 rounded-lg hover:bg-green-200 transition disabled:opacity-50">
                                <span x-show="!carregando">🔄 Buscar CNAEs</span>
                                <span x-show="carregando">⏳ Carregando...</span>
                            </button>
                        </div>

                        <!-- Lista de CNAEs carregados -->
                        <div x-show="cnaesCarregados" x-cloak>
                            <!-- Estatísticas e ações em massa -->
                            <div class="flex items-center justify-between mb-2 pb-2 border-b border-green-200">
                                <span class="text-xs text-green-700">
                                    <span x-text="cnaes.length"></span> CNAE(s) encontrado(s) para <strong x-text="municipioNome"></strong>
                                </span>
                                <div class="flex gap-2">
                                    <button type="button" @click="selecionarTodos()" class="text-xs text-green-700 hover:text-green-900 underline">
                                        Selecionar todos
                                    </button>
                                    <button type="button" @click="deselecionarTodos()" class="text-xs text-green-700 hover:text-green-900 underline">
                                        Limpar seleção
                                    </button>
                                </div>
                            </div>

                            <!-- Filtro de busca -->
                            <div class="mb-2" x-show="cnaes.length > 10">
                                <input type="text" x-model="filtroCnae" placeholder="Filtrar por código ou descrição..."
                                       class="w-full px-2 py-1.5 text-xs border border-green-200 rounded-lg focus:ring-1 focus:ring-green-400 focus:border-green-400">
                            </div>

                            <!-- Contador de selecionados -->
                            <div x-show="selecionados.length > 0" class="mb-2 px-2 py-1 bg-blue-50 border border-blue-200 rounded text-xs text-blue-700">
                                ✅ <span x-text="selecionados.length"></span> CNAE(s) selecionado(s) para importação
                            </div>

                            <!-- Lista scrollável -->
                            <div class="max-h-64 overflow-y-auto space-y-1 border border-green-100 rounded-lg bg-white p-2">
                                <template x-for="cnae in cnaeFiltrados" :key="cnae.cnae_codigo">
                                    <label class="flex items-start gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer transition">
                                        <input type="checkbox" 
                                               :value="cnae.cnae_codigo"
                                               x-model="selecionados"
                                               class="mt-0.5 w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-mono font-medium text-gray-700" x-text="formatarCnae(cnae.cnae_codigo)"></span>
                                                <span x-show="cnae.origem === 'descentralizado'" 
                                                      class="px-1.5 py-0.5 text-[10px] font-medium bg-purple-100 text-purple-700 rounded">
                                                    Descentralizado
                                                </span>
                                                <span x-show="cnae.classificacao_risco" 
                                                      class="px-1.5 py-0.5 text-[10px] font-medium rounded"
                                                      :class="{
                                                          'bg-red-100 text-red-700': cnae.classificacao_risco === 'alto',
                                                          'bg-yellow-100 text-yellow-700': cnae.classificacao_risco === 'medio',
                                                          'bg-green-100 text-green-700': cnae.classificacao_risco === 'baixo'
                                                      }"
                                                      x-text="'Risco ' + cnae.classificacao_risco">
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-600 truncate" x-text="cnae.cnae_descricao"></p>
                                        </div>
                                    </label>
                                </template>

                                <!-- Mensagem quando não há CNAEs -->
                                <div x-show="cnaes.length === 0" class="py-4 text-center text-xs text-gray-500">
                                    Nenhum CNAE encontrado na pactuação para este município.
                                </div>

                                <!-- Mensagem quando filtro não encontra -->
                                <div x-show="cnaes.length > 0 && cnaeFiltrados.length === 0" class="py-4 text-center text-xs text-gray-500">
                                    Nenhum CNAE corresponde ao filtro.
                                </div>
                            </div>
                        </div>

                        <!-- Hidden inputs para envio -->
                        <template x-for="codigo in selecionados" :key="'input-' + codigo">
                            <input type="hidden" name="importar_cnaes[]" :value="codigo">
                        </template>
                    </div>
                </div>

                <div>
                    <label for="ordem" class="block text-sm font-medium text-gray-700 mb-1">Ordem de Exibição</label>
                    <input type="number" name="ordem" id="ordem" value="{{ old('ordem', 0) }}" min="0"
                           class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Menor número aparece primeiro</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="ativo" id="ativo" value="1" {{ old('ativo', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="ativo" class="text-sm text-gray-700">Ativo</label>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-servico']) }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" :disabled="salvando"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                    <span x-show="!salvando">Salvar</span>
                    <span x-show="salvando">Salvando...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function tipoServicoForm() {
    return {
        escopo: '{{ old('escopo', 'estadual') }}',
        municipioId: '{{ old('municipio_id', '') }}',
        municipioNome: '',
        cnaes: [],
        cnaesCarregados: false,
        carregando: false,
        salvando: false,
        selecionados: [],
        filtroCnae: '',

        get cnaeFiltrados() {
            if (!this.filtroCnae) return this.cnaes;
            const filtro = this.filtroCnae.toLowerCase();
            return this.cnaes.filter(c => 
                (c.cnae_codigo && c.cnae_codigo.toLowerCase().includes(filtro)) ||
                (c.cnae_descricao && c.cnae_descricao.toLowerCase().includes(filtro))
            );
        },

        onMunicipioChange() {
            this.cnaesCarregados = false;
            this.cnaes = [];
            this.selecionados = [];
            this.filtroCnae = '';
            
            if (this.municipioId) {
                this.carregarCnaes();
            }
        },

        async carregarCnaes() {
            if (!this.municipioId) return;
            
            this.carregando = true;
            try {
                const response = await fetch(`{{ url('admin/configuracoes/tipos-servico/buscar-cnaes-municipio') }}/${this.municipioId}`);
                const data = await response.json();
                
                this.cnaes = data.cnaes || [];
                this.municipioNome = data.municipio || '';
                this.cnaesCarregados = true;
            } catch (error) {
                console.error('Erro ao carregar CNAEs:', error);
                alert('Erro ao buscar CNAEs da pactuação. Tente novamente.');
            } finally {
                this.carregando = false;
            }
        },

        selecionarTodos() {
            this.selecionados = this.cnaeFiltrados.map(c => c.cnae_codigo);
        },

        deselecionarTodos() {
            this.selecionados = [];
        },

        formatarCnae(codigo) {
            if (!codigo) return '';
            const numerico = codigo.replace(/[^0-9]/g, '');
            if (numerico.length === 7) {
                return numerico.substr(0, 4) + '-' + numerico.substr(4, 1) + '/' + numerico.substr(5, 2);
            }
            return codigo;
        },

        onSubmit() {
            this.salvando = true;
        }
    }
}
</script>
@endsection
