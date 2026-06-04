@extends('layouts.company')

@section('title', 'Cadastrar Pessoa Física')
@section('page-title', 'Cadastrar Pessoa Física')

@section('content')
<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('company.estabelecimentos.create') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Cadastrar Pessoa Física</h1>
            <p class="text-sm text-gray-600 mt-1">Preencha os dados do profissional autônomo</p>
        </div>
    </div>

    {{-- Alerta Informativo --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-blue-900">Aprovação Necessária</h3>
                <p class="text-sm text-blue-800 mt-1">
                    Após o cadastro, seu estabelecimento ficará pendente de aprovação pela Vigilância Sanitária Municipal.
                    Você será notificado quando o cadastro for aprovado ou se houver necessidade de correções.
                </p>
            </div>
        </div>
    </div>

    {{-- Modal de Erro do Servidor (Popup) --}}
    @if ($errors->any())
    <div x-data="{ showModal: true }" x-cloak>
        {{-- Overlay --}}
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            
            {{-- Modal --}}
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="showModal = false"
                 class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
                
                {{-- Header com ícone --}}
                <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-5">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Não foi possível cadastrar</h3>
                            <p class="text-red-100 text-sm mt-0.5">Verifique as informações abaixo</p>
                        </div>
                    </div>
                </div>
                
                {{-- Conteúdo --}}
                <div class="px-6 py-5">
                    @if($errors->has('cidade') && str_contains($errors->first('cidade'), 'InfoVISA'))
                        {{-- Erro específico de município que não usa InfoVISA --}}
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 mb-2">Município não habilitado</h4>
                                <p class="text-gray-600 text-sm leading-relaxed">{{ $errors->first('cidade') }}</p>
                            </div>
                        </div>
                    @else
                        {{-- Outros erros --}}
                        <ul class="space-y-3">
                            @foreach ($errors->all() as $error)
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-5 h-5 bg-red-100 rounded-full flex items-center justify-center mt-0.5">
                                    <svg class="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                                <span class="text-gray-700 text-sm">{{ $error }}</span>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                
                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <a href="{{ route('company.estabelecimentos.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Voltar aos Estabelecimentos
                    </a>
                    <button type="button" @click="showModal = false" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        Entendi
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('company.estabelecimentos.store') }}" class="space-y-6" id="formPessoaFisica">
        @csrf
        <input type="hidden" name="tipo_pessoa" value="fisica">
        <input type="hidden" name="tipo_setor" value="privado">

        {{-- 1. Dados Cadastrais --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-green-100 text-green-700 text-sm font-bold">1</span>
                Dados Cadastrais
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF <span class="text-red-500">*</span></label>
                    <input type="text" id="cpf_display" value="{{ old('cpf') }}" placeholder="000.000.000-00" maxlength="14" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <input type="hidden" id="cpf" name="cpf" value="{{ old('cpf') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo <span class="text-red-500">*</span></label>
                    <input type="text" name="nome_completo" value="{{ old('nome_completo') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RG <span class="text-red-500">*</span></label>
                    <input type="text" name="rg" value="{{ old('rg') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Órgão Emissor <span class="text-red-500">*</span></label>
                    <input type="text" name="orgao_emissor" value="{{ old('orgao_emissor') }}" placeholder="Ex: SSP/TO" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia <span class="text-red-500">*</span></label>
                    <input type="text" name="nome_fantasia" value="{{ old('nome_fantasia') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase">
                    <p class="text-xs text-gray-500 mt-1">Nome pelo qual seu estabelecimento será conhecido</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', auth('externo')->user()->email ?? '') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone <span class="text-red-500">*</span></label>
                    <input type="text" id="telefone" name="telefone" value="{{ old('telefone') }}" placeholder="(00) 00000-0000" maxlength="15" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Seu vínculo com o estabelecimento <span class="text-red-500">*</span></label>
                    <select name="vinculo_usuario" id="vinculo_usuario" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Selecione...</option>
                        <option value="responsavel_legal" {{ old('vinculo_usuario') == 'responsavel_legal' ? 'selected' : '' }}>Responsável Legal</option>
                        <option value="responsavel_tecnico" {{ old('vinculo_usuario') == 'responsavel_tecnico' ? 'selected' : '' }}>Responsável Técnico</option>
                        <option value="funcionario" {{ old('vinculo_usuario') == 'funcionario' ? 'selected' : '' }}>Funcionário</option>
                        <option value="contador" {{ old('vinculo_usuario') == 'contador' ? 'selected' : '' }}>Contador</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Qual é a sua relação com este estabelecimento?</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Início de Funcionamento <span class="text-red-500">*</span></label>
                    <input type="date" name="data_inicio_atividade" value="{{ old('data_inicio_atividade') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
        </div>

        {{-- 2. Endereço --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-green-100 text-green-700 text-sm font-bold">2</span>
                Endereço Completo
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP <span class="text-red-500">*</span></label>
                    <input type="text" id="cep_display" value="{{ old('cep') }}" placeholder="00000-000" maxlength="9" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <input type="hidden" id="cep" name="cep" value="{{ old('cep') }}">
                    <p class="mt-1 text-xs text-gray-500">Digite o CEP para preencher automaticamente</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereço <span class="text-red-500">*</span></label>
                    <input type="text" id="endereco" name="endereco" value="{{ old('endereco') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número <span class="text-red-500">*</span></label>
                    <input type="text" id="numero" name="numero" value="{{ old('numero') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                    <input type="text" name="complemento" value="{{ old('complemento') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bairro <span class="text-red-500">*</span></label>
                    <input type="text" id="bairro" name="bairro" value="{{ old('bairro') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade <span class="text-red-500">*</span></label>
                    <input type="text" id="cidade" name="cidade" value="{{ old('cidade') }}" readonly required class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 uppercase">
                    <input type="hidden" id="codigo_municipio_ibge" name="codigo_municipio_ibge" value="{{ old('codigo_municipio_ibge') }}">
                    <p id="cidade_info" class="mt-1 text-xs text-gray-500">A cidade será preenchida automaticamente pelo CEP</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF <span class="text-red-500">*</span></label>
                    <input type="text" id="estado" name="estado" value="{{ old('estado', 'TO') }}" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>
            </div>
            
            {{-- Alerta informativo sobre competência municipal --}}
            <div class="mt-4 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900">Competência Municipal</h4>
                        <p class="text-xs text-blue-800 mt-1">
                            Estabelecimentos de pessoa física são de competência municipal. O município será determinado automaticamente pelo CEP informado.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. CNAEs --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-green-100 text-green-700 text-sm font-bold">3</span>
                Atividades Econômicas (CNAE) <span class="text-red-500">*</span>
            </h3>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg mb-4">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">Adicione pelo menos uma atividade econômica</p>
                        <p class="text-xs text-yellow-700 mt-1">Busque pelo código CNAE (7 dígitos) da atividade que você exerce.</p>
                    </div>
                </div>
            </div>

            @if(isset($cnaesPermitidosLista) && $cnaesPermitidosLista->isNotEmpty())
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-blue-900">Atividades permitidas para Pessoa Física</p>
                        <p class="text-xs text-blue-700 mt-1 mb-2">Somente os CNAEs abaixo podem ser cadastrados como Pessoa Física.</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($cnaesPermitidosLista as $cnaePermitido)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-white border border-blue-200 text-blue-800"
                                      title="{{ $cnaePermitido->cnae_descricao }}">
                                    {{ $cnaePermitido->cnae_codigo }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar CNAE (7 dígitos)</label>
                <div class="flex gap-2">
                    <input type="text" id="cnae_busca" placeholder="Ex: 5611201" maxlength="7" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <button type="button" id="btn_buscar_cnae" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Buscar
                    </button>
                </div>
                <p id="cnae_erro" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>
            <div id="cnaes_lista" class="space-y-2"><p class="text-sm text-gray-500">Nenhum CNAE adicionado. Adicione pelo menos um.</p></div>
            <input type="hidden" name="atividades_exercidas" id="cnaes_input" value="{{ old('atividades_exercidas') }}">
        </div>

        <div class="flex justify-between pt-6 pb-8">
            <a href="{{ route('company.estabelecimentos.index') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Cancelar</a>
            <button type="submit" id="btn_cadastrar" class="px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                Cadastrar Estabelecimento
            </button>
        </div>
    </form>
</div>

{{-- Modal de Erro --}}
<div id="modal_erro" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="bg-red-500 px-6 py-4 rounded-t-xl">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="text-lg font-bold text-white">Campos Obrigatórios</h3>
                </div>
            </div>
            <div class="px-6 py-4">
                <p id="modal_erro_mensagem" class="text-gray-700"></p>
            </div>
            <div class="px-6 py-4 bg-gray-50 rounded-b-xl flex justify-end">
                <button type="button" onclick="fecharModalErro()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">Entendi</button>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    let cnaes = [];

    // CNAEs permitidos para Pessoa Física (normalizados, só dígitos).
    // Se a lista estiver vazia, nenhuma restrição é aplicada (recurso opcional).
    const cnaesPermitidosPF = @json($cnaesPermitidos ?? []);
    const restringirCnaesPF = cnaesPermitidosPF.length > 0;
    
    // Máscara de CPF
    const cpfDisplay = document.getElementById('cpf_display');
    const cpfHidden = document.getElementById('cpf');
    
    cpfDisplay.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '');
        cpfHidden.value = v;
        v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = v;
        
        cpfDisplay.classList.remove('border-red-500', 'border-green-500');
        const feedbackDiv = document.getElementById('cpf-feedback');
        if (feedbackDiv) feedbackDiv.remove();
    });
    
    // Validação de CPF ao sair do campo
    cpfDisplay.addEventListener('blur', function() {
        const cpf = cpfHidden.value;
        
        if (cpf.length === 11) {
            if (!validarCPF(cpf)) {
                cpfDisplay.classList.add('border-red-500');
                mostrarFeedbackCPF(cpfDisplay, 'CPF inválido', 'error');
                return;
            }
            
            cpfDisplay.classList.add('border-green-500');
            mostrarFeedbackCPF(cpfDisplay, 'CPF válido', 'success');
        }
    });
    
    function validarCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');
        
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        
        let soma = 0;
        let resto;
        
        for (let i = 1; i <= 9; i++) {
            soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
        }
        
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.substring(9, 10))) return false;
        
        soma = 0;
        for (let i = 1; i <= 10; i++) {
            soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
        }
        
        resto = (soma * 10) % 11;
        if (resto === 10 || resto === 11) resto = 0;
        if (resto !== parseInt(cpf.substring(10, 11))) return false;
        
        return true;
    }
    
    function mostrarFeedbackCPF(element, message, type) {
        const existingFeedback = document.getElementById('cpf-feedback');
        if (existingFeedback) existingFeedback.remove();
        
        const feedbackDiv = document.createElement('div');
        feedbackDiv.id = 'cpf-feedback';
        feedbackDiv.className = `text-xs mt-1 ${type === 'error' ? 'text-red-600' : 'text-green-600'}`;
        feedbackDiv.textContent = message;
        element.parentNode.appendChild(feedbackDiv);
    }
    
    // Máscara de CEP
    const cepDisplay = document.getElementById('cep_display');
    const cepHidden = document.getElementById('cep');
    cepDisplay.addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '');
        cepHidden.value = v;
        v = v.replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = v;
    });
    
    // Máscara Telefone
    document.getElementById('telefone').addEventListener('input', function(e) {
        let v = e.target.value.replace(/\D/g, '').replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = v;
    });
    
    // Consulta CEP
    cepDisplay.addEventListener('blur', function() {
        const cep = cepHidden.value;
        if (cep.length === 8) {
            cepDisplay.classList.add('bg-gray-100');
            
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(r => r.json())
                .then(d => {
                    if (!d.erro) {
                        document.getElementById('endereco').value = d.logradouro ? d.logradouro.toUpperCase() : '';
                        document.getElementById('bairro').value = d.bairro ? d.bairro.toUpperCase() : '';
                        
                        const cidadeInput = document.getElementById('cidade');
                        cidadeInput.value = d.localidade ? d.localidade.toUpperCase() : '';
                        
                        const estadoInput = document.getElementById('estado');
                        estadoInput.value = d.uf ? d.uf.toUpperCase() : 'TO';
                        
                        const codigoIbgeInput = document.getElementById('codigo_municipio_ibge');
                        codigoIbgeInput.value = d.ibge || '';
                        
                        const cidadeInfo = document.getElementById('cidade_info');
                        if (cidadeInfo) {
                            cidadeInfo.textContent = '✓ Cidade identificada pelo CEP';
                            cidadeInfo.classList.remove('text-gray-500');
                            cidadeInfo.classList.add('text-green-600');
                        }
                        
                        setTimeout(() => {
                            const numeroInput = document.getElementById('numero');
                            if (numeroInput) {
                                numeroInput.focus();
                            }
                        }, 100);
                    } else {
                        alert('CEP não encontrado. Verifique o número informado.');
                        
                        const cidadeInfo = document.getElementById('cidade_info');
                        if (cidadeInfo) {
                            cidadeInfo.textContent = '❌ CEP não encontrado';
                            cidadeInfo.classList.remove('text-gray-500', 'text-green-600');
                            cidadeInfo.classList.add('text-red-600');
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar CEP:', error);
                    alert('Erro ao buscar CEP. Tente novamente.');
                })
                .finally(() => {
                    cepDisplay.classList.remove('bg-gray-100');
                });
        }
    });
    
    // Busca CNAE ao pressionar Enter
    document.getElementById('cnae_busca').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('btn_buscar_cnae').click();
        }
    });
    
    // Busca CNAE via API do IBGE
    document.getElementById('btn_buscar_cnae').addEventListener('click', function() {
        const codigo = document.getElementById('cnae_busca').value.trim();
        const erro = document.getElementById('cnae_erro');
        const btn = this;
        
        if (codigo.length !== 7 || !/^\d+$/.test(codigo)) {
            erro.textContent = 'Digite um código CNAE válido (7 dígitos numéricos)';
            erro.classList.remove('hidden');
            return;
        }
        
        if (cnaes.find(c => c.codigo === codigo)) {
            erro.textContent = 'Este CNAE já foi adicionado à lista';
            erro.classList.remove('hidden');
            return;
        }

        if (restringirCnaesPF && !cnaesPermitidosPF.includes(codigo)) {
            erro.textContent = 'Este CNAE não está disponível para cadastro de Pessoa Física. Verifique a lista de atividades permitidas acima.';
            erro.classList.remove('hidden', 'text-green-600');
            erro.classList.add('text-red-500');
            return;
        }
        
        erro.classList.add('hidden');
        
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Buscando...';
        
        fetch(`https://servicodados.ibge.gov.br/api/v2/cnae/subclasses/${codigo}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('CNAE não encontrado');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.id) {
                    cnaes.push({
                        codigo: data.id,
                        descricao: data.descricao,
                        grupo: data.classe && data.classe.grupo ? data.classe.grupo.descricao : null
                    });
                    
                    atualizarLista();
                    document.getElementById('cnae_busca').value = '';
                    
                    erro.textContent = '✓ CNAE adicionado com sucesso!';
                    erro.classList.remove('hidden', 'text-red-500');
                    erro.classList.add('text-green-600');
                    setTimeout(() => {
                        erro.classList.add('hidden');
                        erro.classList.remove('text-green-600');
                        erro.classList.add('text-red-500');
                    }, 3000);
                } else {
                    throw new Error('CNAE não encontrado');
                }
            })
            .catch(error => {
                console.error('Erro ao buscar CNAE:', error);
                erro.textContent = 'CNAE não encontrado na base do IBGE. Verifique o código.';
                erro.classList.remove('hidden');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg> Buscar';
            });
    });
    
    function atualizarLista() {
        const lista = document.getElementById('cnaes_lista');
        if (cnaes.length === 0) {
            lista.innerHTML = '<p class="text-sm text-gray-500">Nenhum CNAE adicionado. Adicione pelo menos um.</p>';
        } else {
            lista.innerHTML = cnaes.map((c, i) => `
                <div class="flex items-start justify-between p-4 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-600 text-white">
                                ${c.codigo}
                            </span>
                            ${i === 0 ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Principal</span>' : ''}
                        </div>
                        <p class="text-sm font-medium text-gray-900">${c.descricao}</p>
                        ${c.grupo ? `<p class="text-xs text-gray-600 mt-1">Grupo: ${c.grupo}</p>` : ''}
                    </div>
                    <button type="button" onclick="removerCnae(${i})" 
                            class="ml-4 inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-700 bg-red-100 hover:bg-red-200 rounded-md transition-colors">
                        Remover
                    </button>
                </div>
            `).join('');
        }
        document.getElementById('cnaes_input').value = JSON.stringify(cnaes);
    }
    
    window.removerCnae = function(index) {
        cnaes.splice(index, 1);
        atualizarLista();
    };
    
    // Validação do formulário antes de enviar
    document.getElementById('formPessoaFisica').addEventListener('submit', function(e) {
        // Verifica se o vínculo foi selecionado
        const vinculo = document.getElementById('vinculo_usuario').value;
        if (!vinculo) {
            e.preventDefault();
            mostrarModalErro('Selecione o seu vínculo com o estabelecimento.');
            document.getElementById('vinculo_usuario').focus();
            return false;
        }
        
        // Verifica se há pelo menos um CNAE adicionado
        if (cnaes.length === 0) {
            e.preventDefault();
            mostrarModalErro('Você deve adicionar pelo menos uma Atividade Econômica (CNAE) antes de cadastrar o estabelecimento.');
            
            const cnaeSection = document.getElementById('cnae_busca').closest('.bg-white');
            if (cnaeSection) {
                cnaeSection.classList.add('ring-2', 'ring-red-500');
                cnaeSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                setTimeout(() => {
                    cnaeSection.classList.remove('ring-2', 'ring-red-500');
                }, 3000);
            }
            
            return false;
        }
        
        // Verifica se a cidade foi preenchida (via CEP)
        const cidade = document.getElementById('cidade').value;
        if (!cidade) {
            e.preventDefault();
            mostrarModalErro('Você deve informar um CEP válido para identificar a cidade do estabelecimento.');
            document.getElementById('cep_display').focus();
            return false;
        }
        
        // Verifica se o CPF é válido
        const cpf = document.getElementById('cpf').value;
        if (!validarCPF(cpf)) {
            e.preventDefault();
            mostrarModalErro('O CPF informado é inválido. Por favor, verifique.');
            document.getElementById('cpf_display').focus();
            return false;
        }
        
        return true;
    });
    
    function mostrarModalErro(mensagem) {
        document.getElementById('modal_erro_mensagem').textContent = mensagem;
        document.getElementById('modal_erro').classList.remove('hidden');
    }
});

function fecharModalErro() {
    document.getElementById('modal_erro').classList.add('hidden');
}
</script>
@endsection
