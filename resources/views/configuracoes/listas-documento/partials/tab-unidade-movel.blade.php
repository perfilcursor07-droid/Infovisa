<div x-data="unidadeMovelDocs()" x-init="carregarDados()">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Documentos Obrigatórios para Unidade Móvel</h3>
        <p class="text-sm text-gray-600 mt-1">
            Configure quais documentos são exigidos no credenciamento de PJ Unidade Móvel, associando-os aos CNAEs contemplados.
            Documentos <strong>Gerais</strong> são pedidos uma vez (raiz do processo). Documentos <strong>Por Município</strong> são pedidos em cada pasta.
        </p>
    </div>

    {{-- Botão Adicionar --}}
    <div class="mb-6">
        <button type="button" @click="modalAberto = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-fuchsia-600 text-white rounded-lg hover:bg-fuchsia-700 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Adicionar Documento
        </button>
    </div>

    {{-- Documentos Gerais --}}
    <div class="mb-6">
        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <span class="w-3 h-3 bg-green-500 rounded-full"></span>
            Documentos Gerais (pedidos uma vez na raiz do processo)
        </h4>
        <div x-show="documentosGerais.length === 0" class="text-sm text-gray-500 italic py-3">
            Nenhum documento geral configurado.
        </div>
        <div class="space-y-2">
            <template x-for="doc in documentosGerais" :key="doc.id">
                <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900" x-text="doc.tipo_documento_nome"></span>
                            <span x-show="doc.obrigatorio" class="px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded">Obrigatório</span>
                            <span x-show="!doc.obrigatorio" class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">Opcional</span>
                        </div>
                        <div class="flex flex-wrap gap-1 mt-1">
                            <template x-for="cnae in doc.cnaes" :key="cnae">
                                <span class="px-1.5 py-0.5 text-xs bg-fuchsia-100 text-fuchsia-700 rounded font-mono" x-text="cnae"></span>
                            </template>
                        </div>
                    </div>
                    <button type="button" @click="removerDocumento(doc.id)"
                            class="ml-3 px-3 py-1.5 text-xs text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50">
                        Remover
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Documentos Por Município --}}
    <div>
        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
            Documentos Por Município (pedidos em cada pasta/município)
        </h4>
        <div x-show="documentosPorMunicipio.length === 0" class="text-sm text-gray-500 italic py-3">
            Nenhum documento por município configurado.
        </div>
        <div class="space-y-2">
            <template x-for="doc in documentosPorMunicipio" :key="doc.id">
                <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900" x-text="doc.tipo_documento_nome"></span>
                            <span x-show="doc.obrigatorio" class="px-2 py-0.5 text-xs bg-red-100 text-red-700 rounded">Obrigatório</span>
                            <span x-show="!doc.obrigatorio" class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">Opcional</span>
                        </div>
                        <div class="flex flex-wrap gap-1 mt-1">
                            <template x-for="cnae in doc.cnaes" :key="cnae">
                                <span class="px-1.5 py-0.5 text-xs bg-fuchsia-100 text-fuchsia-700 rounded font-mono" x-text="cnae"></span>
                            </template>
                        </div>
                    </div>
                    <button type="button" @click="removerDocumento(doc.id)"
                            class="ml-3 px-3 py-1.5 text-xs text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50">
                        Remover
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Modal Adicionar --}}
    <div x-show="modalAberto" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="modalAberto" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="modalAberto = false"></div>
            <div x-show="modalAberto" x-transition class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Adicionar Documento para Unidade Móvel</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Documento *</label>
                        <select x-model="form.tipo_documento_obrigatorio_id"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-fuchsia-500">
                            <option value="">Selecione...</option>
                            <template x-for="tipo in tiposDocumento" :key="tipo.id">
                                <option :value="tipo.id" x-text="tipo.nome"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Escopo *</label>
                        <select x-model="form.escopo"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-fuchsia-500">
                            <option value="geral">Geral (pedido uma vez na raiz)</option>
                            <option value="por_municipio">Por Município (pedido em cada pasta)</option>
                        </select>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" x-model="form.obrigatorio" class="rounded border-gray-300 text-fuchsia-600 focus:ring-fuchsia-500">
                            <span class="font-medium text-gray-700">Obrigatório</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CNAEs contemplados *</label>
                        <p class="text-xs text-gray-500 mb-2">Marque para quais atividades de Unidade Móvel este documento se aplica.</p>
                        <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-2 space-y-1">
                            <template x-for="cnae in cnaesUnidadeMovel" :key="cnae.cnae_codigo">
                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-fuchsia-50 cursor-pointer text-sm">
                                    <input type="checkbox" :value="cnae.cnae_codigo" x-model="form.cnaes"
                                           class="rounded border-gray-300 text-fuchsia-600 focus:ring-fuchsia-500">
                                    <span class="font-mono text-xs text-gray-800" x-text="cnae.cnae_codigo"></span>
                                    <span class="text-gray-600 truncate" x-text="cnae.cnae_descricao"></span>
                                </label>
                            </template>
                        </div>
                        <button type="button" @click="form.cnaes = cnaesUnidadeMovel.map(c => c.cnae_codigo)" class="mt-1 text-xs text-fuchsia-600 hover:underline">Marcar todos</button>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="modalAberto = false" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancelar</button>
                    <button type="button" @click="salvar()" :disabled="salvando"
                            class="px-4 py-2 text-sm font-medium text-white bg-fuchsia-600 rounded-lg hover:bg-fuchsia-700 disabled:opacity-50">
                        <span x-show="!salvando">Salvar</span>
                        <span x-show="salvando">Salvando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $tiposDocumentoUM = \App\Models\TipoDocumentoObrigatorio::where('ativo', true)->orderBy('nome')->get(['id', 'nome']);
    $cnaesUM = \Illuminate\Support\Facades\Schema::hasColumn('pactuacoes', 'unidade_movel')
        ? \App\Models\Pactuacao::where('unidade_movel', true)->where('ativo', true)->select('cnae_codigo', 'cnae_descricao')->distinct()->orderBy('cnae_codigo')->get()
        : collect();
@endphp

<script>
function unidadeMovelDocs() {
    return {
        documentos: [],
        tiposDocumento: @json($tiposDocumentoUM),
        cnaesUnidadeMovel: @json($cnaesUM),
        modalAberto: false,
        salvando: false,
        form: {
            tipo_documento_obrigatorio_id: '',
            escopo: 'geral',
            obrigatorio: true,
            cnaes: []
        },

        get documentosGerais() {
            return this.documentos.filter(d => d.escopo === 'geral');
        },

        get documentosPorMunicipio() {
            return this.documentos.filter(d => d.escopo === 'por_municipio');
        },

        async carregarDados() {
            try {
                const response = await fetch('{{ route("admin.configuracoes.listas-documento.unidade-movel.index") }}');
                this.documentos = await response.json();
            } catch (e) {
                console.error('Erro ao carregar documentos:', e);
            }
        },

        async salvar() {
            if (!this.form.tipo_documento_obrigatorio_id) return alert('Selecione um tipo de documento');
            if (this.form.cnaes.length === 0) return alert('Selecione ao menos um CNAE');

            this.salvando = true;
            try {
                const response = await fetch('{{ route("admin.configuracoes.listas-documento.unidade-movel.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                const data = await response.json();
                if (data.success) {
                    this.documentos.push(data.documento);
                    this.modalAberto = false;
                    this.form = { tipo_documento_obrigatorio_id: '', escopo: 'geral', obrigatorio: true, cnaes: [] };
                } else {
                    alert(data.message || 'Erro ao salvar');
                }
            } catch (e) {
                alert('Erro ao salvar documento');
            } finally {
                this.salvando = false;
            }
        },

        async removerDocumento(id) {
            if (!confirm('Remover este documento da lista de Unidade Móvel?')) return;
            try {
                const url = `{{ route('admin.configuracoes.listas-documento.unidade-movel.destroy', ['id' => '__ID__']) }}`.replace('__ID__', id);
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    this.documentos = this.documentos.filter(d => d.id !== id);
                }
            } catch (e) {
                alert('Erro ao remover documento');
            }
        }
    };
}
</script>
