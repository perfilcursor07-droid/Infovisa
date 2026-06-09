@extends('layouts.company')

@section('title', 'Solicitar Credenciamento de Unidade Móvel')
@section('page-title', 'Solicitar Credenciamento de Unidade Móvel')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" class="text-sm text-blue-600 hover:text-blue-700 flex items-center mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <h2 class="text-lg font-semibold text-gray-900">{{ $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social }}</h2>
        <p class="text-sm text-gray-600">Solicite o credenciamento do módulo de Unidade Móvel para este estabelecimento já aprovado. Informe o tipo de unidade, as atividades de interesse e os municípios de atuação no Tocantins.</p>
    </div>

    <form id="formSolicitarUM" method="POST" action="{{ route('company.estabelecimentos.solicitar-unidade-movel.store', $estabelecimento->id) }}"
          x-data="solicitarUnidadeMovelForm()"
          @submit="handleSubmit($event)"
          class="space-y-6"
          novalidate>
        @csrf

        {{-- Erros do servidor --}}
        @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                <li class="text-sm text-red-700">• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ============ Tipo de Unidade ============ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-1">1. Unidade Móvel</h3>
            <p class="text-xs text-gray-500 mb-4">O atendimento é prestado em veículo adaptado (van, ônibus ou carreta).</p>

            <label class="block text-sm font-semibold text-gray-900 mb-2">Qual o tipo de unidade? <span class="text-red-500">*</span></label>
            <select x-model="tipoUnidadeMovel" class="w-full md:w-1/2 px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                <option value="">Selecione...</option>
                <option value="Van ou furgão adaptado">Van ou furgão adaptado</option>
                <option value="Micro-ônibus ou ônibus adaptado">Micro-ônibus ou ônibus adaptado</option>
                <option value="Carreta">Carreta</option>
                <option value="Caminhão adaptado">Caminhão adaptado</option>
                <option value="Trailer ou reboque adaptado">Trailer ou reboque adaptado</option>
                <option value="Outro veículo adaptado">Outro veículo adaptado</option>
            </select>
        </div>

        {{-- ============ Atividades Exercidas ============ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-1">2. Atividades Exercidas</h3>
            <p class="text-xs text-gray-500 mb-4">Selecione as atividades de interesse para a unidade móvel. Somente as atividades contempladas na pactuação de Unidade Móvel podem ser marcadas.</p>

            {{-- Atividade principal --}}
            @if($estabelecimento->cnae_fiscal)
            <div class="flex items-start gap-3 p-3 rounded-lg mb-2 transition-colors"
                 :class="isCnaePermitidoUM(dados.cnae_fiscal) ? 'border border-fuchsia-200 bg-fuchsia-50' : 'border border-gray-100 bg-gray-50 opacity-60'">
                <input type="checkbox" x-model="atividadePrincipalMarcada" @change="onAtividadesChange"
                       :disabled="!isCnaePermitidoUM(dados.cnae_fiscal)"
                       class="mt-1 rounded border-gray-300 text-purple-600 focus:ring-purple-500 disabled:opacity-40 disabled:cursor-not-allowed">
                <span class="text-sm flex-1">
                    <span class="font-medium" :class="isCnaePermitidoUM(dados.cnae_fiscal) ? 'text-gray-900' : 'text-gray-400'" x-text="dados.cnae_fiscal"></span>
                    <span :class="isCnaePermitidoUM(dados.cnae_fiscal) ? 'text-gray-600' : 'text-gray-400'" x-text="' - ' + (dados.cnae_fiscal_descricao || '')"></span>
                    <span class="ml-2 text-xs font-medium" :class="isCnaePermitidoUM(dados.cnae_fiscal) ? 'text-purple-600' : 'text-gray-400'">(principal)</span>
                    <span x-show="isCnaePermitidoUM(dados.cnae_fiscal)" class="ml-2 inline-flex items-center px-1.5 py-0.5 text-xs font-medium bg-fuchsia-100 text-fuchsia-700 rounded">Contemplada</span>
                </span>
            </div>
            @endif

            {{-- Atividades secundárias --}}
            <template x-for="cnae in dados.cnaes_secundarios" :key="cnae.codigo">
                <div class="flex items-start gap-3 p-3 rounded-lg mb-2 transition-colors"
                     :class="isCnaePermitidoUM(cnae.codigo) ? 'border border-fuchsia-200 bg-fuchsia-50' : 'border border-gray-100 bg-gray-50 opacity-60'">
                    <input type="checkbox" :value="String(cnae.codigo)" x-model="atividadesExercidas" @change="onAtividadesChange"
                           :disabled="!isCnaePermitidoUM(cnae.codigo)"
                           class="mt-1 rounded border-gray-300 text-purple-600 focus:ring-purple-500 disabled:opacity-40 disabled:cursor-not-allowed">
                    <span class="text-sm flex-1">
                        <span class="font-medium" :class="isCnaePermitidoUM(cnae.codigo) ? 'text-gray-900' : 'text-gray-400'" x-text="cnae.codigo"></span>
                        <span :class="isCnaePermitidoUM(cnae.codigo) ? 'text-gray-600' : 'text-gray-400'" x-text="' - ' + (cnae.descricao || cnae.texto || '')"></span>
                        <span x-show="isCnaePermitidoUM(cnae.codigo)" class="ml-2 inline-flex items-center px-1.5 py-0.5 text-xs font-medium bg-fuchsia-100 text-fuchsia-700 rounded">Contemplada</span>
                    </span>
                </div>
            </template>

            {{-- Aviso se nenhuma atividade da empresa é contemplada --}}
            <div x-show="!temAlgumCnaeContempladoNaEmpresa()"
                 class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                <strong>Solicitação não permitida:</strong> Nenhuma das atividades deste estabelecimento está na lista de CNAEs contemplados para Unidade Móvel.
            </div>

            {{-- Lembrete quando há atividades contempladas mas nenhuma foi marcada --}}
            <div x-show="temAlgumCnaeContempladoNaEmpresa() && getCnaesSelecionados().length === 0"
                 class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                Marque ao menos uma atividade contemplada para prosseguir.
            </div>
        </div>

        {{-- ============ Municípios de Atuação ============ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-lg font-medium text-gray-900">3. Municípios de Atuação no Tocantins</h3>
                <button type="button" @click="adicionarMunicipio"
                        class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700">
                    + Adicionar município
                </button>
            </div>
            <p class="text-xs text-gray-500 mb-4">Para cada município informe o período de atuação. A competência é calculada automaticamente.</p>

            <div x-show="municipiosAtuacao.length === 0" class="text-sm text-gray-500 py-4 text-center border border-dashed border-gray-300 rounded-lg">
                Nenhum município adicionado. Clique em "+ Adicionar município".
            </div>

            <div class="space-y-4">
                <template x-for="(linha, index) in municipiosAtuacao" :key="index">
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                            <div class="md:col-span-5">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Município</label>
                                <select x-model="linha.municipio_id" @change="verificarMunicipio(index)"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                    <option value="">Selecione...</option>
                                    <template x-for="m in municipiosDisponiveis" :key="m.id">
                                        <option :value="m.id" x-text="m.nome"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Início</label>
                                <input type="date" x-model="linha.data_inicio" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fim</label>
                                <input type="date" x-model="linha.data_fim" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            </div>
                            <div class="md:col-span-1 flex justify-end">
                                <button type="button" @click="removerMunicipio(index)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg" title="Remover">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-3" x-show="linha.municipio_id">
                            <div x-show="linha.verificando" class="text-xs text-gray-500">Calculando competência...</div>

                            <template x-if="!linha.verificando && linha.competencia === 'estadual'">
                                <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Competência Estadual</span>
                            </template>

                            <template x-if="!linha.verificando && linha.competencia === 'municipal' && linha.usa_infovisa">
                                <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Competência Municipal</span>
                            </template>

                            <template x-if="!linha.verificando && linha.competencia === 'municipal' && !linha.usa_infovisa">
                                <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span class="text-xs text-amber-800" x-text="linha.aviso || ('Para o município selecionado, procure diretamente a Vigilância Sanitária Municipal. Este município ainda não utiliza o InfoVISA.')"></span>
                                </div>
                            </template>

                            <template x-if="!linha.verificando && linha.competencia === 'nao_sujeito_visa'">
                                <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">Não sujeito à VISA</span>
                            </template>

                            <template x-if="!linha.verificando && !linha.competencia && linha.avisoSemAtividade">
                                <span class="text-xs text-amber-700" x-text="linha.avisoSemAtividade"></span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Resumo da situação dos municípios --}}
            <div x-show="municipiosAtuacao.length > 0 && municipiosAtuacao.some(l => l.competencia)" class="mt-4">
                <div x-show="!municipiosAtuacao.some(l => l.competencia === 'estadual' || (l.competencia === 'municipal' && l.usa_infovisa))"
                     class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                    <strong>Solicitação não pode prosseguir:</strong> Todos os municípios informados não utilizam o InfoVISA.
                    É necessário que ao menos um município possua competência estadual ou utilize o sistema.
                </div>

                <div x-show="municipiosAtuacao.some(l => l.competencia === 'estadual' || (l.competencia === 'municipal' && l.usa_infovisa)) && municipiosAtuacao.some(l => l.competencia === 'municipal' && !l.usa_infovisa)"
                     class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                    <strong>Atenção:</strong> Municípios que não utilizam o InfoVISA não terão processo gerado no sistema — para esses, procure diretamente a Vigilância Sanitária Municipal.
                    Os demais municípios serão processados normalmente.
                </div>

                <div x-show="municipiosAtuacao.some(l => l.competencia === 'estadual')"
                     class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                    Esta solicitação possui ao menos uma atividade de competência <strong>estadual</strong>. O credenciamento será analisado pela <strong>Vigilância Sanitária Estadual</strong>.
                </div>
            </div>
        </div>

        {{-- Campos ocultos enviados ao servidor --}}
        <input type="hidden" name="tipo_unidade_movel" :value="tipoUnidadeMovel">
        <input type="hidden" name="atividades_exercidas" :value="JSON.stringify(getAtividadesExercidas())">
        <input type="hidden" name="municipios_atuacao" :value="JSON.stringify(getMunicipiosAtuacaoPayload())">
        <input type="hidden" name="respostas_unidade_movel" :value="JSON.stringify(getRespostasUnidadeMovel())">

        <div class="flex justify-end">
            <button type="submit" :disabled="submitting"
                    class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-semibold disabled:opacity-60">
                <span x-show="!submitting">Enviar solicitação</span>
                <span x-show="submitting">Enviando...</span>
            </button>
        </div>

        {{-- Modal de erros --}}
        <div x-show="modalErro.visivel" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="modalErro.visivel = false">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-3">Verifique os campos</h4>
                <ul class="space-y-1 mb-4">
                    <template x-for="(m, i) in modalErro.mensagens" :key="i">
                        <li class="text-sm text-red-700">• <span x-text="m"></span></li>
                    </template>
                </ul>
                <div class="flex justify-end">
                    <button type="button" @click="modalErro.visivel = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Entendi</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function solicitarUnidadeMovelForm() {
    return {
        submitting: false,
        modalErro: { visivel: false, mensagens: [] },

        tipoUnidadeMovel: '',

        dados: {
            cnae_fiscal: @json($estabelecimento->cnae_fiscal),
            cnae_fiscal_descricao: @json($estabelecimento->cnae_fiscal_descricao),
            cnaes_secundarios: @json($estabelecimento->cnaes_secundarios ?? []),
        },

        atividadePrincipalMarcada: false,
        atividadesExercidas: [],
        cnaesPermitidosUM: @json($cnaesPermitidosUM),

        municipiosDisponiveis: @json($municipios),
        municipiosAtuacao: [],

        getCnaesSelecionados() {
            const cnaes = [];
            if (this.atividadePrincipalMarcada && this.dados.cnae_fiscal) {
                cnaes.push(String(this.dados.cnae_fiscal));
            }
            this.atividadesExercidas.forEach(c => cnaes.push(String(c)));
            return cnaes;
        },

        normalizarCnae(cnae) {
            return String(cnae).replace(/[.\-\/\s]/g, '');
        },

        isCnaePermitidoUM(cnae) {
            if (!cnae) return false;
            const norm = this.normalizarCnae(cnae);
            return this.cnaesPermitidosUM.some(c => c === norm);
        },

        temAlgumCnaeContempladoNaEmpresa() {
            if (this.dados.cnae_fiscal && this.isCnaePermitidoUM(this.dados.cnae_fiscal)) return true;
            return (this.dados.cnaes_secundarios || []).some(c => this.isCnaePermitidoUM(c.codigo));
        },

        getAtividadesExercidas() {
            const atividades = [];
            if (this.atividadePrincipalMarcada && this.dados.cnae_fiscal) {
                atividades.push({ codigo: String(this.dados.cnae_fiscal), descricao: this.dados.cnae_fiscal_descricao, principal: true });
            }
            this.atividadesExercidas.forEach(codigoSelecionado => {
                const codigoStr = String(codigoSelecionado);
                const cnae = (this.dados.cnaes_secundarios || []).find(c => String(c.codigo) === codigoStr);
                if (cnae) {
                    atividades.push({ codigo: String(cnae.codigo), descricao: cnae.descricao || cnae.texto || '', principal: false });
                }
            });
            return atividades;
        },

        getRespostasUnidadeMovel() {
            return {
                p1_atende_unidade_movel: 'sim',
                p2_tipo_unidade: this.tipoUnidadeMovel
            };
        },

        onAtividadesChange() {
            this.recalcularTodosMunicipios();
        },

        adicionarMunicipio() {
            this.municipiosAtuacao.push({
                municipio_id: '', data_inicio: '', data_fim: '',
                competencia: null, usa_infovisa: false, aviso: null, avisoSemAtividade: null, verificando: false
            });
        },

        removerMunicipio(index) {
            this.municipiosAtuacao.splice(index, 1);
        },

        async verificarMunicipio(index) {
            const linha = this.municipiosAtuacao[index];
            linha.competencia = null;
            linha.aviso = null;
            linha.avisoSemAtividade = null;
            if (!linha.municipio_id) return;

            const cnaes = this.getCnaesSelecionados();
            if (cnaes.length === 0) {
                linha.avisoSemAtividade = 'Selecione as atividades exercidas para calcular a competência.';
                return;
            }

            linha.verificando = true;
            try {
                const response = await fetch('{{ route("company.estabelecimentos.verificar-competencia-municipio") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        atividades: cnaes,
                        municipio_id: linha.municipio_id,
                        respostas_questionario: {},
                        respostas_questionario2: {}
                    })
                });
                const result = await response.json();
                linha.competencia = result.competencia;
                linha.usa_infovisa = !!result.usa_infovisa;
                linha.aviso = result.aviso || null;
            } catch (e) {
                console.error('Erro ao verificar competência do município:', e);
            } finally {
                linha.verificando = false;
            }
        },

        recalcularTodosMunicipios() {
            this.municipiosAtuacao.forEach((linha, i) => {
                if (linha.municipio_id) this.verificarMunicipio(i);
            });
        },

        getMunicipiosAtuacaoPayload() {
            return this.municipiosAtuacao
                .filter(l => l.municipio_id)
                .map(l => {
                    const m = this.municipiosDisponiveis.find(x => String(x.id) === String(l.municipio_id));
                    return {
                        municipio_id: l.municipio_id,
                        municipio_nome: m ? m.nome : '',
                        data_inicio: l.data_inicio,
                        data_fim: l.data_fim
                    };
                });
        },

        validar() {
            const erros = [];
            if (!this.tipoUnidadeMovel) erros.push('Selecione o tipo de unidade móvel.');
            if (!this.temAlgumCnaeContempladoNaEmpresa()) {
                erros.push('Nenhuma das atividades deste estabelecimento está contemplada para Unidade Móvel.');
            } else if (this.getCnaesSelecionados().length === 0) {
                erros.push('Selecione ao menos uma atividade contemplada para Unidade Móvel.');
            }
            const municipios = this.getMunicipiosAtuacaoPayload();
            if (municipios.length === 0) erros.push('Adicione ao menos um município de atuação.');
            municipios.forEach((m, i) => {
                if (!m.data_inicio || !m.data_fim) {
                    erros.push(`Informe as datas de início e fim do município ${m.municipio_nome || (i + 1)}.`);
                }
            });
            const temMunicipioValido = this.municipiosAtuacao.some(l =>
                l.competencia === 'estadual' || (l.competencia === 'municipal' && l.usa_infovisa)
            );
            if (municipios.length > 0 && !temMunicipioValido) {
                erros.push('Todos os municípios informados não utilizam o InfoVISA. É necessário que ao menos um município possua competência estadual ou utilize o sistema.');
            }
            return erros;
        },

        handleSubmit(event) {
            const erros = this.validar();
            if (erros.length > 0) {
                event.preventDefault();
                this.modalErro.mensagens = erros;
                this.modalErro.visivel = true;
                return;
            }
            this.submitting = true;
        }
    }
}
</script>
@endpush
