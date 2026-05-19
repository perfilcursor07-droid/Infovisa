{{-- Modal Importar CNAEs para um Tipo de Serviço específico --}}
<div x-data="modalImportarCnaes{{ $tipoServico->id }}()" 
     @open-modal-atividade-{{ $tipoServico->id }}.window="open = true"
     x-show="open" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-black/50" @click="open = false"></div>
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white rounded-xl shadow-xl max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Adicionar Atividades: {{ $tipoServico->nome }}</h3>
                <button @click="open = false; resetar()" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.configuracoes.atividades.store-multiple') }}" method="POST">
                @csrf
                
                <input type="hidden" name="tipo_servico_id" value="{{ $tipoServico->id }}">

                {{-- Campo para digitar CNAEs --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Digite os códigos CNAE</label>
                    <div class="flex gap-2">
                        <input type="text" 
                               x-model="inputCnae"
                               @keydown.enter.prevent="adicionarCnae()"
                               placeholder="Ex: 5611201 ou 56.11-2-01"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <button type="button" 
                                @click="adicionarCnae()"
                                :disabled="!inputCnae || buscando"
                                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg x-show="buscando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="buscando ? 'Buscando...' : 'Adicionar'"></span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Digite o código CNAE e pressione Enter ou clique em Adicionar. O sistema buscará automaticamente a descrição.
                    </p>
                </div>

                {{-- Importar da Pactuação (apenas para tipos municipais) --}}
                @if($tipoServico->escopo === 'municipal' && $tipoServico->municipio_id)
                <div class="mb-4 p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <label class="block text-sm font-medium text-green-800">📋 Importar da Pactuação</label>
                            <p class="text-xs text-green-600 mt-0.5">Importe todos os CNAEs de competência de <strong>{{ $tipoServico->municipio->nome ?? '' }}</strong></p>
                        </div>
                        <button type="button" 
                                @click="importarDaPactuacao()"
                                :disabled="buscando || importandoPactuacao"
                                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg x-show="importandoPactuacao" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="importandoPactuacao ? 'Importando...' : '🔄 Importar Todos da Pactuação'"></span>
                        </button>
                    </div>
                    <div x-show="pactuacaoMsg" x-transition class="mt-2 text-xs text-green-700" x-text="pactuacaoMsg"></div>
                </div>
                @endif

                {{-- Importar múltiplos de uma vez --}}
                <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ou cole vários CNAEs de uma vez</label>
                    <textarea x-model="inputMultiplos"
                              rows="3"
                              placeholder="Cole aqui vários códigos CNAE separados por vírgula, espaço ou quebra de linha. Ex:&#10;5611201, 5611202&#10;5612100"
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    <button type="button" 
                            @click="importarMultiplos()"
                            :disabled="!inputMultiplos || buscando"
                            class="mt-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Importar Todos
                    </button>
                </div>

                {{-- Erro --}}
                <div x-show="erro" x-transition class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-700" x-text="erro"></p>
                </div>

                {{-- Lista de CNAEs adicionados --}}
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">
                            Atividades a serem cadastradas (<span x-text="atividades.length"></span>)
                        </label>
                        <button type="button" 
                                x-show="atividades.length > 0"
                                @click="atividades = []"
                                class="text-xs text-red-600 hover:underline">
                            Limpar tudo
                        </button>
                    </div>
                    
                    <div x-show="atividades.length === 0" class="p-4 bg-gray-50 rounded-lg text-center">
                        <p class="text-sm text-gray-500">Nenhum CNAE adicionado ainda</p>
                    </div>
                    
                    <div x-show="atividades.length > 0" class="space-y-2 max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-2">
                        <template x-for="(atividade, index) in atividades" :key="index">
                            <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                                <input type="hidden" :name="'atividades[' + index + '][nome]'" :value="atividade.descricao">
                                <input type="hidden" :name="'atividades[' + index + '][codigo_cnae]'" :value="atividade.codigo">
                                <input type="hidden" :name="'atividades[' + index + '][descricao]'" value="">
                                
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-mono font-bold rounded" x-text="atividade.codigo"></span>
                                <span class="flex-1 text-sm text-gray-700" x-text="atividade.descricao"></span>
                                <button type="button" 
                                        @click="atividades.splice(index, 1)"
                                        class="p-1 text-red-500 hover:bg-red-50 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" @click="open = false; resetar()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="atividades.length === 0"
                            class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Salvar <span x-text="atividades.length"></span> Atividade(s)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function modalImportarCnaes{{ $tipoServico->id }}() {
    return {
        open: false,
        inputCnae: '',
        inputMultiplos: '',
        atividades: [],
        buscando: false,
        importandoPactuacao: false,
        pactuacaoMsg: '',
        erro: '',
        
        normalizarCnae(codigo) {
            return String(codigo).replace(/[^0-9]/g, '');
        },
        
        async buscarDescricaoCnae(codigo) {
            const codigoNormalizado = this.normalizarCnae(codigo);
            
            if (codigoNormalizado.length < 5) {
                return null;
            }
            
            try {
                const response = await fetch(`https://servicodados.ibge.gov.br/api/v2/cnae/subclasses/${codigoNormalizado}`);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data && data.id) {
                        return {
                            codigo: codigoNormalizado,
                            descricao: data.descricao
                        };
                    }
                }
                
                const responseClasse = await fetch(`https://servicodados.ibge.gov.br/api/v2/cnae/classes/${codigoNormalizado.slice(0,5)}`);
                if (responseClasse.ok) {
                    const dataClasse = await responseClasse.json();
                    if (dataClasse && dataClasse.id) {
                        return {
                            codigo: codigoNormalizado,
                            descricao: dataClasse.descricao + ' (classe)'
                        };
                    }
                }
                
                return null;
            } catch (error) {
                console.error('Erro ao buscar CNAE:', error);
                return null;
            }
        },
        
        async adicionarCnae() {
            if (!this.inputCnae || this.buscando) return;
            
            this.erro = '';
            this.buscando = true;
            
            const codigoNormalizado = this.normalizarCnae(this.inputCnae);
            
            if (this.atividades.some(a => a.codigo === codigoNormalizado)) {
                this.erro = 'Este CNAE já foi adicionado';
                this.buscando = false;
                return;
            }
            
            const resultado = await this.buscarDescricaoCnae(codigoNormalizado);
            
            if (resultado) {
                this.atividades.push(resultado);
                this.inputCnae = '';
            } else {
                this.erro = `CNAE ${codigoNormalizado} não encontrado. Verifique se o código está correto.`;
            }
            
            this.buscando = false;
        },
        
        async importarMultiplos() {
            if (!this.inputMultiplos || this.buscando) return;
            
            this.erro = '';
            this.buscando = true;
            
            const codigos = this.inputMultiplos
                .split(/[\s,;\n]+/)
                .map(c => this.normalizarCnae(c))
                .filter(c => c.length >= 5);
            
            if (codigos.length === 0) {
                this.erro = 'Nenhum código CNAE válido encontrado';
                this.buscando = false;
                return;
            }
            
            let adicionados = 0;
            let naoEncontrados = [];
            
            for (const codigo of codigos) {
                if (this.atividades.some(a => a.codigo === codigo)) {
                    continue;
                }
                
                const resultado = await this.buscarDescricaoCnae(codigo);
                
                if (resultado) {
                    this.atividades.push(resultado);
                    adicionados++;
                } else {
                    naoEncontrados.push(codigo);
                }
            }
            
            if (naoEncontrados.length > 0) {
                this.erro = `${adicionados} CNAE(s) adicionado(s). Não encontrados: ${naoEncontrados.join(', ')}`;
            }
            
            this.inputMultiplos = '';
            this.buscando = false;
        },
        
        resetar() {
            this.inputCnae = '';
            this.inputMultiplos = '';
            this.atividades = [];
            this.erro = '';
            this.pactuacaoMsg = '';
            this.importandoPactuacao = false;
        },

        async importarDaPactuacao() {
            this.importandoPactuacao = true;
            this.pactuacaoMsg = '';
            this.erro = '';

            try {
                const response = await fetch('/admin/configuracoes/tipos-servico/buscar-cnaes-municipio/{{ $tipoServico->municipio_id ?? 0 }}');
                const data = await response.json();

                if (!data.cnaes || data.cnaes.length === 0) {
                    this.pactuacaoMsg = 'Nenhum CNAE encontrado na pactuação para este município.';
                    this.importandoPactuacao = false;
                    return;
                }

                let adicionados = 0;
                for (const cnae of data.cnaes) {
                    const codigoNormalizado = this.normalizarCnae(cnae.cnae_codigo);
                    if (!this.atividades.some(a => a.codigo === codigoNormalizado)) {
                        this.atividades.push({
                            codigo: codigoNormalizado,
                            descricao: cnae.cnae_descricao || 'CNAE ' + codigoNormalizado
                        });
                        adicionados++;
                    }
                }

                this.pactuacaoMsg = `✅ ${adicionados} CNAE(s) importado(s) da pactuação de ${data.municipio}. Total: ${this.atividades.length} atividade(s).`;
            } catch (error) {
                console.error('Erro ao importar da pactuação:', error);
                this.erro = 'Erro ao buscar CNAEs da pactuação. Tente novamente.';
            } finally {
                this.importandoPactuacao = false;
            }
        }
    }
}
</script>
