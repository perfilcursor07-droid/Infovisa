@extends('layouts.company')

@section('title', 'Cadastrar PJ Unidade Móvel')
@section('page-title', 'Cadastrar PJ Unidade Móvel')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('company.estabelecimentos.create') }}" class="text-sm text-blue-600 hover:text-blue-700 flex items-center mb-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
        <p class="text-sm text-gray-600">Cadastro de estabelecimento que presta serviço itinerante/temporário com unidade móvel em municípios do Tocantins.</p>
    </div>

    {{-- Formulário --}}
    <form id="formUnidadeMovel" method="POST" action="{{ route('company.estabelecimentos.store') }}"
          x-data="unidadeMovelForm()"
          @submit="handleSubmit($event)"
          class="space-y-6"
          novalidate>
        @csrf
        <input type="hidden" name="tipo_pessoa" value="juridica">
        <input type="hidden" name="is_unidade_movel" value="1">

        {{-- Indicador de progresso (stepper) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <template x-for="(label, i) in passosLabels" :key="i">
                    <div class="flex items-center flex-1 last:flex-none">
                        <div class="flex flex-col items-center">
                            <div :class="passo > (i + 1) ? 'bg-purple-600 text-white' : (passo === (i + 1) ? 'bg-purple-600 text-white ring-4 ring-purple-100' : 'bg-gray-200 text-gray-500')"
                                 class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold transition-all">
                                <template x-if="passo > (i + 1)">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <span x-show="passo <= (i + 1)" x-text="i + 1"></span>
                            </div>
                            <span :class="passo === (i + 1) ? 'text-purple-700 font-semibold' : 'text-gray-500'"
                                  class="text-[11px] sm:text-xs mt-1.5 text-center hidden sm:block" x-text="label"></span>
                        </div>
                        <div x-show="i < passosLabels.length - 1"
                             :class="passo > (i + 1) ? 'bg-purple-600' : 'bg-gray-200'"
                             class="flex-1 h-0.5 mx-2 transition-all"></div>
                    </div>
                </template>
            </div>
        </div>

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

        {{-- Modal de Erros (cliente) --}}
        <div x-cloak x-show="modalErro.visivel" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-md bg-white rounded-xl shadow-2xl border border-red-200">
                <div class="bg-red-600 px-5 py-4 rounded-t-xl">
                    <h3 class="text-white font-semibold">Verifique os campos</h3>
                </div>
                <div class="px-5 py-4">
                    <ul class="space-y-2">
                        <template x-for="(msg, i) in modalErro.mensagens" :key="i">
                            <li class="text-sm text-gray-700 flex items-start gap-2">
                                <span class="text-red-500">•</span><span x-text="msg"></span>
                            </li>
                        </template>
                    </ul>
                </div>
                <div class="bg-gray-50 px-5 py-3 flex justify-end rounded-b-xl">
                    <button type="button" @click="modalErro.visivel = false" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Entendi</button>
                </div>
            </div>
        </div>

        {{-- ============ PASSO 1: Identificação (CNPJ) ============ --}}
        <div x-show="passo === 1" x-cloak class="space-y-6">
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-purple-800">
                        Informe o CNPJ da empresa. Após o cadastro, o estabelecimento ficará
                        <strong>Pendente</strong> até a análise da Vigilância Sanitária.
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">1. Identificação (CNPJ)</h3>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="text" x-model="cnpjBusca" @input="formatarCnpj" @keydown.enter.prevent="buscarCnpj" placeholder="00.000.000/0000-00"
                           class="flex-1 px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <button type="button" @click="buscarCnpj" :disabled="loading"
                            class="px-6 py-3 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 disabled:bg-gray-400 flex items-center justify-center gap-2">
                        <svg x-show="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="loading ? 'Buscando...' : 'Buscar dados'"></span>
                    </button>
                </div>
                <p x-show="mensagem" x-text="mensagem" :class="tipoMensagem === 'error' ? 'text-red-600' : 'text-green-600'" class="text-sm mt-2"></p>

                <div x-show="dadosCarregados" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                    <span class="font-medium" x-text="dados.razao_social"></span> — dados carregados. Clique em "Próximo" para continuar.
                </div>
            </div>

            <div class="flex justify-end">
                <button type="button" @click="proximoPasso()" :disabled="!dadosCarregados"
                        :class="dadosCarregados ? 'bg-purple-600 hover:bg-purple-700' : 'bg-gray-300 cursor-not-allowed'"
                        class="px-6 py-3 text-white rounded-lg text-sm font-semibold">
                    Próximo →
                </button>
            </div>
        </div>

        {{-- ============ PASSO 2: Dados + Endereço ============ --}}
        <div x-show="passo === 2" x-cloak class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">2. Dados do Estabelecimento</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ</label>
                        <input type="text" x-model="dados.cnpj" readonly class="w-full px-4 py-2.5 text-sm border border-gray-200 bg-gray-50 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Razão Social</label>
                        <input type="text" x-model="dados.razao_social" readonly class="w-full px-4 py-2.5 text-sm border border-gray-200 bg-gray-50 rounded-lg">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia <span class="text-red-500">*</span></label>
                        <input type="text" x-model="dados.nome_fantasia" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-1">3. Endereço da Sede</h3>
                <p class="text-xs text-gray-500 mb-4">Endereço da sede da empresa (pode ser em qualquer estado ou município do Tocantins).</p>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro <span class="text-red-500">*</span></label>
                        <input type="text" x-model="dados.endereco" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número <span class="text-red-500">*</span></label>
                        <input type="text" x-model="dados.numero" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CEP <span class="text-red-500">*</span></label>
                        <input type="text" x-model="dados.cep" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bairro <span class="text-red-500">*</span></label>
                        <input type="text" x-model="dados.bairro" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                        <input type="text" x-model="dados.complemento" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cidade <span class="text-red-500">*</span></label>
                        <input type="text" x-model="dados.cidade" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">UF <span class="text-red-500">*</span></label>
                        <input type="text" x-model="dados.estado" maxlength="2" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg uppercase focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <div class="flex justify-between">
                <button type="button" @click="passoAnterior()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">← Voltar</button>
                <button type="button" @click="proximoPasso()" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-semibold">Próximo →</button>
            </div>
        </div>

        {{-- ============ PASSO 3: Unidade Móvel + Atividades ============ --}}
        <div x-show="passo === 3" x-cloak class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">4. Sobre a Unidade Móvel</h3>

                <div class="mb-5">
                    <p class="text-sm font-semibold text-gray-900 mb-3">O atendimento é feito em unidade móvel (carreta/veículo adaptado)? <span class="text-red-500">*</span></p>
                    <div class="flex gap-3">
                        <button type="button" @click="atendeUnidadeMovel = 'sim'"
                                :class="atendeUnidadeMovel === 'sim' ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-gray-700 border-gray-300'"
                                class="px-5 py-2 text-sm font-medium border rounded-lg">Sim</button>
                        <button type="button" @click="atendeUnidadeMovel = 'nao'"
                                :class="atendeUnidadeMovel === 'nao' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 border-gray-300'"
                                class="px-5 py-2 text-sm font-medium border rounded-lg">Não</button>
                    </div>
                </div>

                {{-- Resposta NÃO: bloqueio --}}
                <div x-show="atendeUnidadeMovel === 'nao'" x-cloak
                     class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-red-800">Cadastro não permitido</p>
                        <p class="text-sm text-red-700 mt-1">
                            Este formulário é exclusivo para estabelecimentos que realizam atendimento em unidade móvel.
                            Se o seu serviço é prestado em local fixo, utilize o cadastro de
                            <a href="{{ route('company.estabelecimentos.create.juridica') }}" class="underline font-medium">Pessoa Jurídica</a>
                            ou <a href="{{ route('company.estabelecimentos.create.fisica') }}" class="underline font-medium">Pessoa Física</a>.
                        </p>
                    </div>
                </div>

                {{-- Resposta SIM: tipo de unidade --}}
                <div x-show="atendeUnidadeMovel === 'sim'" x-cloak>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Qual o tipo de unidade? <span class="text-red-500">*</span></label>
                    <select x-model="tipoUnidadeMovel" class="w-full md:w-1/2 px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Selecione...</option>
                        <option value="Vans ou furgões adaptados">Vans ou furgões adaptados</option>
                        <option value="Micro-ônibus ou ônibus adaptados">Micro-ônibus ou ônibus adaptados</option>
                        <option value="Carretas">Carretas</option>
                    </select>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-1">5. Atividades Exercidas</h3>
                <p class="text-xs text-gray-500 mb-4">Selecione as atividades de interesse para a unidade móvel. Somente as atividades contempladas na pactuação de Unidade Móvel podem ser marcadas.</p>

                {{-- Atividade principal --}}
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
                <div x-show="dados.cnae_fiscal && !temAlgumCnaeContempladoNaEmpresa()"
                     class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                    <strong>Cadastro não permitido:</strong> Nenhuma das atividades desta empresa (CNPJ) está na lista de CNAEs contemplados para Unidade Móvel. A empresa precisa ter ao menos um CNAE autorizado para este tipo de cadastro.
                </div>

                {{-- Lembrete quando há atividades contempladas mas nenhuma foi marcada --}}
                <div x-show="dados.cnae_fiscal && temAlgumCnaeContempladoNaEmpresa() && getCnaesSelecionados().length === 0"
                     class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                    Marque ao menos uma atividade contemplada para prosseguir.
                </div>

                {{-- Botão para atualizar CNAEs via API em tempo real --}}
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <button type="button" @click="atualizarCnaes()"
                            :disabled="atualizandoCnaes || jaAtualizouCnaes"
                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                        <svg x-show="!atualizandoCnaes" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <svg x-show="atualizandoCnaes" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="jaAtualizouCnaes ? 'CNAEs já atualizados ✓' : (atualizandoCnaes ? 'Consultando Receita Federal...' : 'Atualizei meus CNAEs e não aparecem aqui')"></span>
                    </button>
                    <p x-show="msgAtualizacaoCnaes" x-text="msgAtualizacaoCnaes" class="mt-2 text-xs" :class="tipoMsgCnaes === 'success' ? 'text-green-700' : 'text-red-700'"></p>
                </div>

            </div>

            <div class="flex justify-between">
                <button type="button" @click="passoAnterior()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">← Voltar</button>
                <button type="button" @click="proximoPasso()" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-semibold">Próximo →</button>
            </div>
        </div>

        {{-- ============ PASSO 4: Municípios de Atuação ============ --}}
        <div x-show="passo === 4" x-cloak class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-medium text-gray-900">6. Municípios de Atuação no Tocantins</h3>
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
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        Competência Estadual
                                    </span>
                                </template>

                                <template x-if="!linha.verificando && linha.competencia === 'municipal' && linha.usa_infovisa">
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        Competência Municipal
                                    </span>
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
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
                                        Não sujeito à VISA
                                    </span>
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
                    {{-- Aviso: TODOS sem InfoVisa (bloqueia) --}}
                    <div x-show="!municipiosAtuacao.some(l => l.competencia === 'estadual' || (l.competencia === 'municipal' && l.usa_infovisa))"
                         class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                        <strong>Cadastro não pode prosseguir:</strong> Todos os municípios informados não utilizam o InfoVISA.
                        É necessário que ao menos um município possua competência estadual ou utilize o sistema.
                    </div>

                    {{-- Informativo: Tem válidos + alguns sem InfoVisa (ok, pode prosseguir) --}}
                    <div x-show="municipiosAtuacao.some(l => l.competencia === 'estadual' || (l.competencia === 'municipal' && l.usa_infovisa)) && municipiosAtuacao.some(l => l.competencia === 'municipal' && !l.usa_infovisa)"
                         class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                        <strong>Atenção:</strong> Municípios que não utilizam o InfoVISA não terão processo gerado no sistema — para esses, procure diretamente a Vigilância Sanitária Municipal.
                        Os demais municípios serão processados normalmente.
                    </div>

                    {{-- Informativo: Tem estadual → processo do estado --}}
                    <div x-show="municipiosAtuacao.some(l => l.competencia === 'estadual')"
                         class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                        Este cadastro possui ao menos uma atividade de competência <strong>estadual</strong>. O credenciamento será analisado pela <strong>Vigilância Sanitária Estadual</strong>.
                    </div>
                </div>
            </div>

            <div class="flex justify-between">
                <button type="button" @click="passoAnterior()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">← Voltar</button>
                <button type="button" @click="proximoPasso()" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-semibold">Próximo →</button>
            </div>
        </div>

        {{-- ============ PASSO 5: Contato e Vínculo ============ --}}
        <div x-show="passo === 5" x-cloak class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">7. Contato e Vínculo</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone <span class="text-red-500">*</span></label>
                        <input type="text" x-model="dados.telefone" @input="formatarTelefone" placeholder="(00) 00000-0000"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail <span class="text-red-500">*</span></label>
                        <input type="email" x-model="dados.email" placeholder="contato@empresa.com.br"
                               class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Seu vínculo com o estabelecimento <span class="text-red-500">*</span></label>
                        <select x-model="dados.vinculo_usuario" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="">Selecione seu vínculo...</option>
                            <option value="responsavel_legal">Responsável Legal</option>
                            <option value="responsavel_tecnico">Responsável Técnico</option>
                            <option value="funcionario">Funcionário</option>
                            <option value="contador">Contador</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Resumo --}}
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Resumo do Cadastro</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div><span class="text-gray-500">CNPJ:</span> <span class="font-medium text-gray-900 ml-1" x-text="dados.cnpj"></span></div>
                    <div><span class="text-gray-500">Razão Social:</span> <span class="font-medium text-gray-900 ml-1" x-text="dados.razao_social"></span></div>
                    <div><span class="text-gray-500">Tipo de Unidade:</span> <span class="font-medium text-gray-900 ml-1" x-text="tipoUnidadeMovel || '—'"></span></div>
                    <div><span class="text-gray-500">Sede:</span> <span class="font-medium text-gray-900 ml-1" x-text="dados.cidade + ' - ' + dados.estado"></span></div>
                    <div class="md:col-span-2"><span class="text-gray-500">Municípios de atuação:</span> <span class="font-medium text-gray-900 ml-1" x-text="getMunicipiosAtuacaoPayload().length"></span></div>
                </div>
            </div>

            <div class="flex justify-between pb-10">
                <button type="button" @click="passoAnterior()" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">← Voltar</button>
                <button type="submit" :disabled="submitting"
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:bg-gray-400 text-sm font-semibold">
                    <span x-text="submitting ? 'Enviando...' : 'Cadastrar Unidade Móvel'"></span>
                </button>
            </div>
        </div>

        {{-- Hidden inputs enviados ao servidor (sempre presentes no DOM) --}}
        <input type="hidden" name="cnpj" :value="dados.cnpj">
        <input type="hidden" name="razao_social" :value="dados.razao_social">
        <input type="hidden" name="nome_fantasia" :value="dados.nome_fantasia">
        <input type="hidden" name="natureza_juridica" :value="dados.natureza_juridica">
        <input type="hidden" name="porte" :value="dados.porte">
        <input type="hidden" name="descricao_situacao_cadastral" :value="dados.descricao_situacao_cadastral">
        <input type="hidden" name="capital_social" :value="dados.capital_social">
        <input type="hidden" name="cnae_fiscal" :value="dados.cnae_fiscal">
        <input type="hidden" name="cnae_fiscal_descricao" :value="dados.cnae_fiscal_descricao">
        <input type="hidden" name="cnaes_secundarios" :value="JSON.stringify(dados.cnaes_secundarios)">
        <input type="hidden" name="codigo_municipio_ibge" :value="dados.codigo_municipio_ibge">
        <input type="hidden" name="endereco" :value="dados.endereco">
        <input type="hidden" name="numero" :value="dados.numero">
        <input type="hidden" name="complemento" :value="dados.complemento">
        <input type="hidden" name="bairro" :value="dados.bairro">
        <input type="hidden" name="cidade" :value="dados.cidade">
        <input type="hidden" name="estado" :value="dados.estado">
        <input type="hidden" name="cep" :value="dados.cep.replace(/\D/g, '')">
        <input type="hidden" name="telefone" :value="dados.telefone.replace(/\D/g, '')">
        <input type="hidden" name="email" :value="dados.email">
        <input type="hidden" name="tipo_setor" :value="dados.tipo_setor">
        <input type="hidden" name="vinculo_usuario" :value="dados.vinculo_usuario">
        <input type="hidden" name="atividades_exercidas" :value="JSON.stringify(getAtividadesExercidas())">
        <input type="hidden" name="tipo_unidade_movel" :value="tipoUnidadeMovel">
        <input type="hidden" name="respostas_unidade_movel" :value="JSON.stringify(getRespostasUnidadeMovel())">
        <input type="hidden" name="municipios_atuacao" :value="JSON.stringify(getMunicipiosAtuacaoPayload())">
    </form>
</div>
@endsection

@push('scripts')
<script>
function unidadeMovelForm() {
    return {
        // Wizard
        passo: 1,
        passosLabels: ['Identificação', 'Estabelecimento', 'Unidade Móvel', 'Municípios', 'Contato'],

        loading: false,
        submitting: false,
        dadosCarregados: false,
        cnpjBusca: '',
        mensagem: '',
        tipoMensagem: '',
        modalErro: { visivel: false, mensagens: [] },

        atualizandoCnaes: false,
        jaAtualizouCnaes: false,
        msgAtualizacaoCnaes: '',
        tipoMsgCnaes: '',

        dados: {
            cnpj: '', razao_social: '', nome_fantasia: '', natureza_juridica: '', porte: '',
            descricao_situacao_cadastral: '', capital_social: '', cnae_fiscal: '', cnae_fiscal_descricao: '',
            cnaes_secundarios: [], endereco: '', numero: '', complemento: '', bairro: '', cidade: '',
            estado: '', cep: '', codigo_municipio_ibge: '', telefone: '', email: '',
            tipo_setor: 'privado', vinculo_usuario: ''
        },

        // P1 / P2
        atendeUnidadeMovel: 'sim',
        tipoUnidadeMovel: '',

        // Atividades / questionários
        atividadePrincipalMarcada: false,
        atividadesExercidas: [],
        cnaesPermitidosUM: @json($cnaesPermitidosUM),

        // P4
        municipiosDisponiveis: @json($municipios),
        municipiosAtuacao: [],

        get totalPassos() { return this.passosLabels.length; },

        // ---------- Navegação ----------
        proximoPasso() {
            const erros = this.validarPasso(this.passo);
            if (erros.length > 0) {
                this.modalErro.mensagens = erros;
                this.modalErro.visivel = true;
                return;
            }
            if (this.passo < this.totalPassos) {
                this.passo++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        passoAnterior() {
            if (this.passo > 1) {
                this.passo--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        validarPasso(n) {
            const erros = [];

            if (n === 1) {
                if (!this.dadosCarregados) erros.push('Busque um CNPJ válido para continuar');
            }

            if (n === 2) {
                if (!this.dados.nome_fantasia) erros.push('Nome Fantasia é obrigatório');
                if (!this.dados.cep) erros.push('CEP é obrigatório');
                if (!this.dados.endereco) erros.push('Logradouro é obrigatório');
                if (!this.dados.numero) erros.push('Número é obrigatório');
                if (!this.dados.bairro) erros.push('Bairro é obrigatório');
                if (!this.dados.cidade) erros.push('Cidade é obrigatória');
                if (!this.dados.estado) erros.push('UF é obrigatória');
            }

            if (n === 3) {
                if (this.atendeUnidadeMovel === 'nao') erros.push('O cadastro de Unidade Móvel é exclusivo para atendimentos em veículos adaptados. Utilize o cadastro de Pessoa Jurídica ou Pessoa Física.');
                if (!this.tipoUnidadeMovel) erros.push('Selecione o tipo de unidade móvel');
                if (!this.temAlgumCnaeContempladoNaEmpresa()) {
                    erros.push('Nenhuma das atividades desta empresa está contemplada para Unidade Móvel. Verifique se o CNPJ possui CNAEs autorizados para este tipo de cadastro.');
                } else {
                    const cnaes = this.getCnaesSelecionados();
                    if (cnaes.length === 0) erros.push('Selecione ao menos uma atividade contemplada para Unidade Móvel');
                }
            }

            if (n === 4) {
                const municipios = this.getMunicipiosAtuacaoPayload();
                if (municipios.length === 0) erros.push('Adicione ao menos um município de atuação');
                municipios.forEach((m, i) => {
                    if (!m.data_inicio || !m.data_fim) {
                        erros.push(`Informe as datas de início e fim do município ${m.municipio_nome || (i + 1)}`);
                    }
                });
                // Verifica se pelo menos um município pode gerar processo no sistema
                const temMunicipioValido = this.municipiosAtuacao.some(l =>
                    l.competencia === 'estadual' || (l.competencia === 'municipal' && l.usa_infovisa)
                );
                if (municipios.length > 0 && !temMunicipioValido) {
                    erros.push('Todos os municípios informados não utilizam o InfoVISA. É necessário que ao menos um município possua competência estadual ou utilize o sistema para prosseguir com o cadastro.');
                }
            }

            if (n === 5) {
                if (!this.dados.telefone) erros.push('Telefone é obrigatório');
                if (!this.dados.email) erros.push('E-mail é obrigatório');
                if (!this.dados.vinculo_usuario) erros.push('Selecione o seu vínculo com o estabelecimento');
            }

            return erros;
        },

        // ---------- Formatação ----------
        formatarCnpj() {
            let v = this.cnpjBusca.replace(/\D/g, '');
            if (v.length > 14) v = v.substring(0, 14);
            v = v.replace(/^(\d{2})(\d)/, '$1.$2');
            v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
            v = v.replace(/(\d{4})(\d)/, '$1-$2');
            this.cnpjBusca = v;
        },

        formatarTelefone() {
            let v = this.dados.telefone.replace(/\D/g, '');
            if (v.length > 11) v = v.substring(0, 11);
            if (v.length > 10) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            } else if (v.length > 6) {
                v = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
            }
            this.dados.telefone = v;
        },

        async buscarCnpj() {
            const cnpj = this.cnpjBusca.replace(/\D/g, '');
            if (cnpj.length !== 14) {
                this.mensagem = 'CNPJ deve ter 14 dígitos';
                this.tipoMensagem = 'error';
                return;
            }
            this.loading = true;
            this.mensagem = '';
            try {
                const response = await fetch('{{ url("/api/consultar-cnpj") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ cnpj: this.cnpjBusca })
                });
                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'CNPJ não encontrado em nenhuma base de dados');
                }
                const data = result.data || {};
                const apiSource = result.api_source || 'API';

                this.dados.cnpj = this.cnpjBusca;
                this.dados.razao_social = data.razao_social || '';
                this.dados.nome_fantasia = data.nome_fantasia || data.razao_social || '';
                this.dados.natureza_juridica = data.natureza_juridica || '';
                this.dados.porte = data.porte || '';
                this.dados.descricao_situacao_cadastral = data.descricao_situacao_cadastral || data.situacao_cadastral || '';
                this.dados.capital_social = data.capital_social || 0;
                this.dados.cnae_fiscal = data.cnae_fiscal?.toString() || '';
                this.dados.cnae_fiscal_descricao = data.cnae_fiscal_descricao || '';
                this.dados.cnaes_secundarios = data.cnaes_secundarios || [];
                this.dados.endereco = data.endereco || data.logradouro || '';
                this.dados.numero = data.numero || '';
                this.dados.complemento = data.complemento || '';
                this.dados.bairro = data.bairro || '';
                this.dados.cidade = data.cidade || data.municipio || '';
                this.dados.estado = data.estado || data.uf || '';
                this.dados.cep = (data.cep || '').replace(/\D/g, '');
                if (this.dados.cep) {
                    this.dados.cep = this.dados.cep.replace(/(\d{5})(\d{3})/, '$1-$2');
                }
                this.dados.codigo_municipio_ibge = data.codigo_municipio_ibge?.toString() || '';
                const telefoneApi = data.telefone || data.ddd_telefone_1 || '';
                if (telefoneApi) {
                    this.dados.telefone = telefoneApi.replace(/\D/g, '');
                    this.formatarTelefone();
                }
                this.dados.email = data.email || '';
                const natureza = (data.natureza_juridica || '').toLowerCase();
                this.dados.tipo_setor = data.tipo_setor || ((natureza.includes('público') || natureza.includes('administração pública')) ? 'publico' : 'privado');

                this.dadosCarregados = true;
                this.mensagem = `Dados carregados com sucesso via ${apiSource}!`;
                this.tipoMensagem = 'success';

                // Se o email não veio da primeira API, busca na API atualizada (CNPJa)
                if (!this.dados.email) {
                    this.buscarEmailAutomatico();
                }

                // Avança automaticamente para o passo 2
                this.passo = 2;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (error) {
                this.mensagem = 'Erro ao buscar CNPJ: ' + error.message;
                this.tipoMensagem = 'error';
            } finally {
                this.loading = false;
            }
        },

        async atualizarCnaes() {
            if (!this.dados.cnpj || this.atualizandoCnaes || this.jaAtualizouCnaes) return;
            this.atualizandoCnaes = true;
            this.msgAtualizacaoCnaes = '';
            try {
                const response = await fetch('{{ url("/api/consultar-cnpj-atualizado") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ cnpj: this.dados.cnpj })
                });
                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Não foi possível obter dados atualizados.');
                }
                const data = result.data || {};
                this.dados.cnae_fiscal = data.cnae_fiscal?.toString() || this.dados.cnae_fiscal;
                this.dados.cnae_fiscal_descricao = data.cnae_fiscal_descricao || this.dados.cnae_fiscal_descricao;
                this.dados.cnaes_secundarios = data.cnaes_secundarios || this.dados.cnaes_secundarios;
                if (data.email && !this.dados.email) {
                    this.dados.email = data.email;
                }
                this.msgAtualizacaoCnaes = `CNAEs atualizados com sucesso via ${result.api_source || 'Receita Federal'}!`;
                this.tipoMsgCnaes = 'success';
                this.jaAtualizouCnaes = true;
                this.atividadePrincipalMarcada = false;
                this.atividadesExercidas = [];
            } catch (error) {
                this.msgAtualizacaoCnaes = error.message;
                this.tipoMsgCnaes = 'error';
            } finally {
                this.atualizandoCnaes = false;
            }
        },

        async buscarEmailAutomatico() {
            if (!this.dados.cnpj) return;
            try {
                const response = await fetch('{{ url("/api/consultar-cnpj-atualizado") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ cnpj: this.dados.cnpj })
                });
                const result = await response.json();
                if (response.ok && result.success && result.data?.email && !this.dados.email) {
                    this.dados.email = result.data.email;
                }
            } catch (e) {
                // Silencioso — o usuário pode preencher manualmente
            }
        },

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
            const norm = this.normalizarCnae(cnae);
            return this.cnaesPermitidosUM.some(c => c === norm);
        },

        temCnaeContempladoSelecionado() {
            return this.getCnaesSelecionados().some(c => this.isCnaePermitidoUM(c));
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
                const cnae = this.dados.cnaes_secundarios.find(c => String(c.codigo) === codigoStr);
                if (cnae) {
                    atividades.push({ codigo: String(cnae.codigo), descricao: cnae.descricao || cnae.texto || '', principal: false });
                }
            });
            return atividades;
        },

        getRespostasUnidadeMovel() {
            return {
                p1_atende_unidade_movel: this.atendeUnidadeMovel,
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
                linha.avisoSemAtividade = 'Selecione as atividades exercidas (passo anterior) para calcular a competência.';
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

        handleSubmit(event) {
            // Validação final de todos os passos antes de enviar
            let erros = [];
            for (let n = 1; n <= this.totalPassos; n++) {
                erros = erros.concat(this.validarPasso(n));
            }
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
