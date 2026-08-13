@extends('layouts.auth')

@section('title', 'Cadastro de Usuário Externo')

@section('content')
<div x-data="registroForm()" class="min-h-screen bg-slate-50 overflow-y-auto flex justify-center">
    <div class="w-full max-w-2xl px-4 sm:px-6 py-8 sm:py-12 my-auto">

        <!-- Header -->
        <div class="mb-8 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Criar Conta</h1>
            <p class="text-sm text-gray-500 mt-2">Informe seu CPF para iniciar o cadastro</p>
        </div>

        <!-- Mensagem de CPF Habilitado (query string) -->
        @if(request('cpf'))
        <div class="mb-6 bg-green-50 border border-green-300 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-green-900 mb-1">✓ CPF Habilitado para Cadastro</h3>
                    <p class="text-sm text-green-800">Seu CPF está autorizado para realizar o cadastro. Complete as informações abaixo para criar sua conta.</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Alerts -->
        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-xl p-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ============================================================ -->
        <!-- ETAPA 1: CPF (sempre visível)                                -->
        <!-- ============================================================ -->
        <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50/70 p-4 sm:p-5">
            <div class="mb-3 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold">1</span>
                <p class="text-sm font-semibold text-gray-800">Validação de CPF</p>
            </div>
            <label for="cpf" class="block text-sm font-semibold text-gray-700 mb-1.5">
                CPF <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input
                    type="text"
                    id="cpf"
                    x-model="cpf"
                    @input="onCpfInput()"
                    maxlength="14"
                    required
                    class="w-full px-3 py-2 border-2 rounded-xl transition text-sm tracking-wide"
                    :class="{
                        'border-green-400 bg-green-50 focus:ring-green-500 focus:border-green-500': cpfStatus === 'valido',
                        'border-red-400 bg-red-50 focus:ring-red-500 focus:border-red-500': cpfStatus === 'invalido' || cpfStatus === 'cadastrado',
                        'border-blue-400 focus:ring-blue-500 focus:border-blue-500': cpfStatus === 'consultando',
                        'border-gray-300 focus:ring-blue-500 focus:border-blue-500': cpfStatus === 'aguardando'
                    }"
                    placeholder="000.000.000-00"
                    :disabled="consultando"
                >
                <!-- Ícone de status -->
                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <!-- Carregando -->
                    <template x-if="cpfStatus === 'consultando'">
                        <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </template>
                    <!-- Válido -->
                    <template x-if="cpfStatus === 'valido'">
                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <!-- Inválido / Cadastrado -->
                    <template x-if="cpfStatus === 'invalido' || cpfStatus === 'cadastrado'">
                        <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </template>
                </div>
            </div>
            <!-- Mensagem de feedback -->
            <p x-show="mensagemCpf" x-text="mensagemCpf" class="mt-1.5 text-sm"
               :class="{
                   'text-green-600': cpfStatus === 'valido',
                   'text-red-600': cpfStatus === 'invalido' || cpfStatus === 'cadastrado',
                   'text-blue-600': cpfStatus === 'consultando'
               }"></p>
            @error('cpf')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- ============================================================ -->
        <!-- ETAPA 2: Formulário completo (aparece após CPF validado)     -->
        <!-- ============================================================ -->
                <form action="{{ route('registro.submit') }}" method="POST"
              x-show="cpfStatus === 'valido'"
              x-transition:enter="transition ease-out duration-300"
              x-transition:enter-start="opacity-0 -translate-y-4"
              x-transition:enter-end="opacity-100 translate-y-0"
                            class="space-y-6 rounded-xl border border-gray-200 bg-white p-4 sm:p-6">
            @csrf

                        <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-600 text-white text-xs font-bold">2</span>
                                <p class="text-sm font-semibold text-gray-800">Complete seus dados</p>
                        </div>

            <!-- CPF hidden (valor real que vai pro submit) -->
            <input type="hidden" name="cpf" :value="cpf">

            <!-- Nome Completo + badge se veio automático -->
            <div>
                <label for="nome" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Nome Completo <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        x-model="nome"
                        @input="nome = nome.toUpperCase()"
                        required
                        class="w-full px-3 py-2 border-2 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition uppercase text-sm"
                        :class="nomeAutoPreenchido ? 'border-green-300 bg-green-50' : 'border-gray-300'"
                        placeholder="DIGITE SEU NOME COMPLETO"
                        style="text-transform: uppercase;"
                    >
                    <template x-if="nomeAutoPreenchido">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Automático
                            </span>
                        </span>
                    </template>
                </div>
                <p x-show="nomeAutoPreenchido" class="mt-1 text-xs text-green-600">
                    Nome preenchido automaticamente. Você pode editá-lo se necessário.
                </p>
                @error('nome')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email e Telefone (duas colunas) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        E-mail <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="w-full px-3 py-2 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                        placeholder="seu@email.com"
                    >
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telefone -->
                <div>
                    <label for="telefone" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Telefone <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="telefone"
                        name="telefone"
                        x-model="telefone"
                        @input="formatTelefone()"
                        maxlength="15"
                        required
                        class="w-full px-3 py-2 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                        placeholder="(00) 00000-0000"
                    >
                    @error('telefone')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Senha e Confirmar Senha (duas colunas) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Senha -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Senha <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="w-full px-3 py-2 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                        placeholder="Mínimo 8 caracteres"
                    >
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Senha -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Confirmar Senha <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        class="w-full px-3 py-2 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                        placeholder="Digite novamente"
                    >
                </div>
            </div>

            <!-- Info sobre senha -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3">
                <p class="text-xs text-blue-800">
                    <strong>Senha:</strong> Mínimo 8 caracteres incluindo letras.
                </p>
            </div>

            <!-- Aceite de Termos -->
            <div x-data="{ termosAceitos: false, modalAberto: false, termosLidos: false }">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input
                            id="aceite_termos"
                            name="aceite_termos"
                            type="checkbox"
                            required
                            x-model="termosAceitos"
                            :disabled="!termosLidos"
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 @error('aceite_termos') border-red-300 @enderror disabled:cursor-not-allowed disabled:opacity-50"
                        >
                    </div>
                    <div class="ml-3">
                        <label for="aceite_termos" class="text-sm text-gray-700">
                            Li, compreendi e aceito os
                            <button
                                type="button"
                                @click="modalAberto = true"
                                class="text-blue-600 hover:text-blue-700 font-semibold underline"
                            >
                                Termos e Condições de Uso
                            </button>
                            <span class="text-red-500">*</span>
                        </label>
                        <p x-show="!termosLidos" class="mt-1 text-xs text-amber-600">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Clique em "Termos e Condições de Uso" para ler e aceitar
                        </p>
                        @error('aceite_termos')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Modal de Termos e Condições -->
                <div
                    x-show="modalAberto"
                    x-cloak
                    class="fixed inset-0 z-50 overflow-y-auto"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <!-- Overlay -->
                        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="modalAberto = false"></div>

                        <!-- Modal -->
                        <div
                            class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-auto transform transition-all"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            @click.stop
                        >
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 rounded-t-2xl">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-white flex items-center">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Termos e Condições de Uso
                                    </h3>
                                    <button
                                        type="button"
                                        @click="modalAberto = false"
                                        class="text-white/80 hover:text-white transition"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Conteúdo -->
                            <div class="px-6 py-5 max-h-[28rem] overflow-y-auto text-left">
                                <h4 class="font-bold text-gray-900 text-center mb-5 text-lg border-b pb-3">
                                    DECLARAÇÃO E ACEITE DOS TERMOS DE USO DO SISTEMA INFOVISA
                                </h4>

                                <div class="space-y-4 text-sm text-gray-700 leading-relaxed">
                                    <p>
                                        <strong class="text-gray-900">DECLARO</strong> que conheço a legislação sanitária e demais normas pertinentes às atividades CNAEs exercidas na Instituição* que neste ato represento.
                                    </p>

                                    <p>
                                        <strong class="text-gray-900">DECLARO</strong> que todo o documento protocolado é verdadeiro e está sujeito ao aceite da DIRETORIA DE VIGILÂNCIA SANITÁRIA – DVISA, podendo ser recusado se não atender aos critérios exigidos.
                                    </p>

                                    <p>
                                        Estou <strong class="text-gray-900">CIENTE E ACEITO</strong> que os documentos relacionados à instituição e ao processo de licenciamento são tramitados exclusivamente pelo sistema INFOVISA e produzem <strong class="text-gray-900">EFEITO DE NOTIFICAÇÃO OFICIAL</strong> nos termos do <strong class="text-gray-900">Art. 14, §1º da Portaria Nº 305/2026/SES/GASEC</strong>:
                                    </p>

                                    <blockquote class="border-l-4 border-blue-500 bg-blue-50 px-4 py-3 text-sm text-gray-800 italic">
                                        “§1º O estabelecimento é considerado notificado oficialmente quando o INFOVISA for acessado por um dos colaboradores da empresa, independente da visualização ou não do documento, ou após 5 (cinco) dias de sua disponibilidade no INFOVISA.”
                                    </blockquote>

                                    <p class="text-sm text-gray-800">
                                        <strong class="text-gray-900">Em outras palavras:</strong> a notificação oficial ocorre na <strong>primeira</strong> das hipóteses abaixo, o que acontecer primeiro:
                                    </p>
                                    <ol class="list-decimal list-inside space-y-2 text-sm text-gray-800 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                                        <li>
                                            quando <strong>qualquer colaborador da empresa acessar o INFOVISA</strong>, mesmo sem abrir ou visualizar o documento específico; ou
                                        </li>
                                        <li>
                                            <strong>automaticamente, após 5 (cinco) dias</strong> da disponibilização do documento no INFOVISA, mesmo que ninguém tenha acessado o sistema.
                                        </li>
                                    </ol>
                                    <p class="text-xs text-gray-600">
                                        Ou seja: não é necessário abrir o documento para que a notificação seja considerada válida. O simples acesso ao sistema por um colaborador, ou o prazo de 5 dias, já produz o efeito oficial.
                                    </p>

                                    <p>
                                        Estou <strong class="text-gray-900">CIENTE</strong> que qualquer alteração de Responsável Legal ou Técnico, estrutura física, procedimentos operacionais e/ou atividade exercida devo comunicar oficialmente pelo INFOVISA** a Vigilância Sanitária Estadual no prazo de cinco dias úteis.
                                    </p>

                                    <p>
                                        <strong class="text-gray-900">DECLARO</strong> ainda, sob as penas da lei, serem verdadeiras as informações prestadas e que estou ciente de que, sendo constatada a omissão de qualquer informação relevante ou a declaração falsa no cadastro da instituição e/ou processo de licenciamento sanitário, ficará configurado <strong class="text-red-600">crime de falsidade ideológica</strong>, previsto no artigo 299 do Código Penal Brasileiro, ensejando na cassação automática da Licença Sanitária, sem prejuízo de sanções civis e criminais cabíveis.
                                    </p>
                                </div>

                                <div class="mt-5 pt-4 border-t border-gray-200 text-xs text-gray-500 space-y-1">
                                    <p><strong>*Instituição:</strong> empresa, estabelecimento ou serviço de natureza jurídica pública ou privada.</p>
                                    <p><strong>**INFOVISA:</strong> Sistema oficial de informação e gerenciamento da Vigilância Sanitária Estadual.</p>
                                </div>
                            </div>

                            <!-- Footer com botões -->
                            <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex flex-col sm:flex-row gap-3">
                                <button
                                    type="button"
                                    @click="modalAberto = false"
                                    class="flex-1 px-4 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-100 transition"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    @click="termosLidos = true; termosAceitos = true; modalAberto = false"
                                    class="flex-1 px-4 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl font-semibold hover:from-green-700 hover:to-green-800 transition flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Li e Aceito os Termos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botão de Submit -->
            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
            >
                <span>Criar Conta</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </button>
        </form>

        <!-- Mensagem para CPF já cadastrado -->
        <div x-show="cpfStatus === 'cadastrado'" x-transition class="mt-5">
            <div class="bg-amber-50 border border-amber-300 rounded-xl p-4 text-center">
                <svg class="w-10 h-10 text-amber-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <p class="text-sm font-semibold text-amber-800 mb-2" x-text="mensagemCpf"></p>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Ir para Login
                </a>
            </div>
        </div>

        <!-- Link para Login -->
        <div class="mt-7 text-center border-t border-gray-100 pt-5">
            <p class="text-sm text-gray-600">
                Já tem uma conta?
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                    Faça login
                </a>
            </p>
        </div>

    </div>
</div>

<script>
function registroForm() {
    return {
        cpf: '{{ request('cpf', old('cpf')) }}',
        nome: '{{ old('nome') }}',
        telefone: '{{ old('telefone') }}',
        cpfStatus: 'aguardando', // aguardando | consultando | valido | invalido | cadastrado
        mensagemCpf: '',
        nomeAutoPreenchido: false,
        consultando: false,
        debounceTimer: null,

        init() {
            // Se veio CPF por query string ou old(), consultar automaticamente
            if (this.cpf && this.cpf.replace(/\D/g, '').length === 11) {
                this.formatCpf();
                this.consultarCpf();
            }
        },

        formatCpf() {
            let value = this.cpf.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                this.cpf = value;
            }
        },

        formatTelefone() {
            let value = this.telefone.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length === 11) {
                    value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
                } else {
                    value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
                }
                this.telefone = value;
            }
        },

        onCpfInput() {
            this.formatCpf();

            // Reset quando edita o CPF
            const digitos = this.cpf.replace(/\D/g, '');

            if (digitos.length < 11) {
                this.cpfStatus = 'aguardando';
                this.mensagemCpf = '';
                this.nome = '';
                this.nomeAutoPreenchido = false;
                return;
            }

            // Debounce para não consultar a cada tecla
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                if (digitos.length === 11) {
                    this.consultarCpf();
                }
            }, 400);
        },

        async consultarCpf() {
            const digitos = this.cpf.replace(/\D/g, '');
            if (digitos.length !== 11) return;

            this.cpfStatus = 'consultando';
            this.mensagemCpf = 'Consultando CPF...';
            this.consultando = true;

            try {
                const response = await fetch('/api/consultar-cpf', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ cpf: digitos })
                });

                const data = await response.json();

                if (!response.ok || !data.valido) {
                    this.cpfStatus = 'invalido';
                    this.mensagemCpf = data.mensagem || 'CPF inválido. Verifique os números digitados.';
                    this.nome = '';
                    this.nomeAutoPreenchido = false;
                    return;
                }

                if (data.cadastrado) {
                    this.cpfStatus = 'cadastrado';
                    this.mensagemCpf = data.mensagem || 'Este CPF já possui cadastro no sistema.';
                    this.nome = '';
                    this.nomeAutoPreenchido = false;
                    return;
                }

                // CPF válido
                this.cpfStatus = 'valido';
                this.mensagemCpf = data.mensagem || 'CPF válido.';

                if (data.encontrado && data.nome) {
                    this.nome = data.nome;
                    this.nomeAutoPreenchido = true;
                    this.mensagemCpf = 'CPF válido — nome preenchido automaticamente!';
                } else {
                    this.nomeAutoPreenchido = false;
                    if (!this.nome) {
                        this.nome = '';
                    }
                }

            } catch (error) {
                console.error('Erro ao consultar CPF:', error);
                // Em caso de erro de rede, permite continuar manualmente
                this.cpfStatus = 'valido';
                this.mensagemCpf = 'Não foi possível consultar. Preencha os dados manualmente.';
                this.nomeAutoPreenchido = false;
            } finally {
                this.consultando = false;
            }
        }
    }
}
</script>
@endsection
