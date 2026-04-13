@extends('layouts.admin')

@section('title', 'Editar Usuário Interno')

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.usuarios-internos.show', $usuarioInterno) }}" 
               class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Editar Usuário Interno</h1>
                <p class="text-sm text-gray-600 mt-1">Atualize as informações do usuário</p>
            </div>
        </div>
    </div>

    {{-- Formulário --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.usuarios-internos.update', $usuarioInterno) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Informações Pessoais --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Informações Pessoais
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nome --}}
                    <div class="md:col-span-2">
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">
                            Nome Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nome" 
                               name="nome" 
                               value="{{ old('nome', $usuarioInterno->nome) }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase @error('nome') border-red-500 @enderror">
                        @error('nome')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- CPF --}}
                    <div>
                        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">
                            CPF <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="cpf" 
                               name="cpf" 
                               value="{{ old('cpf', $usuarioInterno->cpf) }}"
                               placeholder="000.000.000-00"
                               maxlength="14"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('cpf') border-red-500 @enderror">
                        @error('cpf')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $usuarioInterno->email) }}"
                               placeholder="email@exemplo.com"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Telefone --}}
                    <div>
                        <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">
                            Telefone
                        </label>
                        <input type="text" 
                               id="telefone" 
                               name="telefone" 
                               value="{{ old('telefone', $usuarioInterno->telefone) }}"
                               placeholder="(00) 00000-0000"
                               maxlength="20"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('telefone') border-red-500 @enderror">
                        @error('telefone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Data de Nascimento --}}
                    <div>
                        <label for="data_nascimento" class="block text-sm font-medium text-gray-700 mb-1">
                            Data de Nascimento
                        </label>
                        <input type="date" 
                               id="data_nascimento" 
                               name="data_nascimento" 
                               value="{{ old('data_nascimento', $usuarioInterno->data_nascimento?->format('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('data_nascimento') border-red-500 @enderror">
                        @error('data_nascimento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Informações Profissionais --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Informações Profissionais
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Matrícula --}}
                    <div>
                        <label for="matricula" class="block text-sm font-medium text-gray-700 mb-1">
                            Matrícula
                        </label>
                        <input type="text" 
                               id="matricula" 
                               name="matricula" 
                               value="{{ old('matricula', $usuarioInterno->matricula) }}"
                               maxlength="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase @error('matricula') border-red-500 @enderror">
                        @error('matricula')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Cargo --}}
                    <div>
                        <label for="cargo" class="block text-sm font-medium text-gray-700 mb-1">
                            Cargo
                        </label>
                        <input type="text" 
                               id="cargo" 
                               name="cargo" 
                               value="{{ old('cargo', $usuarioInterno->cargo) }}"
                               maxlength="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase @error('cargo') border-red-500 @enderror">
                        @error('cargo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nível de Acesso --}}
                    <div>
                        <label for="nivel_acesso" class="block text-sm font-medium text-gray-700 mb-1">
                            Nível de Acesso <span class="text-red-500">*</span>
                        </label>
                        <select id="nivel_acesso" 
                                name="nivel_acesso" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nivel_acesso') border-red-500 @enderror">
                            <option value="">Selecione um nível</option>
                            @foreach($niveisPermitidos as $nivel)
                                <option value="{{ $nivel->value }}" 
                                        {{ old('nivel_acesso', $usuarioInterno->nivel_acesso->value) == $nivel->value ? 'selected' : '' }}>
                                    {{ $nivel->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('nivel_acesso')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500" id="nivel-descricao"></p>
                    </div>

                    {{-- Setor (select único para técnicos, multi-select para gestores municipais) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Setor
                        </label>
                        {{-- Select único (técnicos e outros) --}}
                        <div id="setor-unico">
                            <select id="setor" 
                                    name="setor" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('setor') border-red-500 @enderror">
                                <option value="">Selecione o nível de acesso primeiro</option>
                            </select>
                        </div>
                        {{-- Multi-select (gestor municipal) --}}
                        <div id="setor-multi" style="display: none;">
                            <div id="setor-multi-checkboxes" class="max-h-48 overflow-y-auto rounded-lg border border-gray-300 p-2 space-y-1">
                            </div>
                            <p class="mt-1 text-xs text-blue-600">Gestor municipal pode acessar múltiplos setores.</p>
                        </div>
                        @error('setor')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500" id="setor-ajuda">Selecione um nível de acesso para ver os setores disponíveis</p>
                    </div>

                    {{-- Município (apenas para Gestor/Técnico Municipal) --}}
                    <div id="campo-municipio" style="display: none;">
                        <label for="municipio_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Município <span class="text-red-500" id="municipio-obrigatorio">*</span>
                        </label>
                        <select id="municipio_id" 
                                name="municipio_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('municipio_id') border-red-500 @enderror">
                            <option value="">Selecione um município</option>
                            @if(isset($municipios))
                                @foreach($municipios as $mun)
                                    <option value="{{ $mun->id }}" {{ old('municipio_id', $usuarioInterno->municipio_id) == $mun->id ? 'selected' : '' }}>
                                        {{ $mun->nome }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('municipio_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500" id="municipio-ajuda"></p>
                    </div>
                </div>
            </div>

            {{-- Alterar Senha --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Alterar Senha
                </h2>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-800">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Deixe os campos em branco se não desejar alterar a senha
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nova Senha --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Nova Senha
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               minlength="8"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Mínimo de 8 caracteres</p>
                    </div>

                    {{-- Confirmar Nova Senha --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmar Nova Senha
                        </label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               minlength="8"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Status
                </h2>
                
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="ativo" 
                           name="ativo" 
                           value="1"
                           {{ old('ativo', $usuarioInterno->ativo) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="ativo" class="ml-2 text-sm font-medium text-gray-700">
                        Usuário ativo
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-500">Usuários inativos não poderão acessar o sistema</p>
            </div>

            {{-- Botões --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.usuarios-internos.show', $usuarioInterno) }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Executar após o DOM estar completamente carregado
(function() {
    console.log('Script de edição de usuário interno iniciando...');
    
    // Função para converter texto para maiúsculas
    function toUpperCase(input) {
        if (input) {
            input.addEventListener('input', function(e) {
                // Não converte se for campo de email
                if (e.target.type !== 'email') {
                    e.target.value = e.target.value.toUpperCase();
                }
            });
        }
    }
    
    // Aplicar uppercase nos campos de texto (exceto email)
    const nomeInput = document.getElementById('nome');
    const matriculaInput = document.getElementById('matricula');
    const cargoInput = document.getElementById('cargo');
    
    toUpperCase(nomeInput);
    toUpperCase(matriculaInput);
    toUpperCase(cargoInput);
    
    // Formatar CPF ao carregar
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        let value = cpfInput.value.replace(/\D/g, '');
        if (value.length === 11) {
            value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            cpfInput.value = value;
        }

        // Máscara de CPF
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = value;
            }
        });
    }

    // Máscara de Telefone
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length <= 10) {
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                }
                e.target.value = value;
            }
        });
    }

    // Remove máscara do CPF e telefone antes de enviar o formulário
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Formulário de edição sendo enviado...');
            
            // Converte campos de texto para maiúsculas (exceto email)
            if (nomeInput) nomeInput.value = nomeInput.value.toUpperCase();
            if (matriculaInput) matriculaInput.value = matriculaInput.value.toUpperCase();
            if (cargoInput) cargoInput.value = cargoInput.value.toUpperCase();
            
            // Remove máscara do CPF
            if (cpfInput) {
                console.log('CPF antes de remover máscara:', cpfInput.value);
                cpfInput.value = cpfInput.value.replace(/\D/g, '');
                console.log('CPF após remover máscara:', cpfInput.value);
            }
            
            // Remove máscara do telefone
            if (telefoneInput && telefoneInput.value) {
                console.log('Telefone antes de remover máscara:', telefoneInput.value);
                telefoneInput.value = telefoneInput.value.replace(/\D/g, '');
                console.log('Telefone após remover máscara:', telefoneInput.value);
            }
        });
    }

    // Dados dos setores vindos do banco de dados
    const tipoSetoresData = @json($tipoSetores);
    const setorAtual = @json(old('setor', $usuarioInterno->setor));
    const setoresVinculadosIds = @json($setoresVinculadosIds);

    // Controla exibição e opções do campo setor
    function atualizarSetores(nivelAcesso) {
        const setorSelect = document.getElementById('setor');
        const setorAjuda = document.getElementById('setor-ajuda');
        const setorUnico = document.getElementById('setor-unico');
        const setorMulti = document.getElementById('setor-multi');
        const setorMultiCheckboxes = document.getElementById('setor-multi-checkboxes');
        const municipioSelect = document.getElementById('municipio_id');
        const municipioId = municipioSelect ? municipioSelect.value : null;
        const isGestorMunicipal = nivelAcesso === 'gestor_municipal';
        
        if (!setorSelect) return;
        
        // Filtra setores disponíveis
        const setoresDisponiveis = tipoSetoresData.filter(setor => {
            if (setor.niveis_acesso && setor.niveis_acesso.length > 0) {
                if (!setor.niveis_acesso.includes(nivelAcesso)) return false;
            }
            if (setor.municipio_ids && setor.municipio_ids.length > 0) {
                return municipioId && setor.municipio_ids.includes(Number(municipioId));
            }
            return true;
        });

        if (isGestorMunicipal) {
            // Multi-select para gestor municipal
            setorUnico.style.display = 'none';
            setorMulti.style.display = '';
            setorMultiCheckboxes.innerHTML = '';

            if (setoresDisponiveis.length > 0) {
                setoresDisponiveis.forEach(setor => {
                    const isChecked = setoresVinculadosIds.includes(setor.id);
                    const label = document.createElement('label');
                    label.className = 'flex items-center gap-2 px-2 py-1.5 rounded hover:bg-gray-50 cursor-pointer';
                    label.innerHTML = '<input type="checkbox" name="setores[]" value="' + setor.id + '" ' +
                        (isChecked ? 'checked' : '') +
                        ' class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">' +
                        '<span class="text-sm text-gray-700">' + setor.nome + '</span>';
                    setorMultiCheckboxes.appendChild(label);
                });
                if (setorAjuda) {
                    setorAjuda.textContent = setoresDisponiveis.length + ' setor(es) disponível(is)';
                    setorAjuda.classList.remove('text-gray-500');
                    setorAjuda.classList.add('text-blue-600');
                }
            } else {
                setorMultiCheckboxes.innerHTML = '<p class="text-sm text-gray-500 p-2">Nenhum setor disponível</p>';
            }
            // Também manter o select com o setor principal para compatibilidade
            setorSelect.innerHTML = '<option value="">Selecione um setor</option>';
            setoresDisponiveis.forEach(setor => {
                const option = document.createElement('option');
                option.value = setor.codigo;
                option.textContent = setor.nome;
                if (setorAtual && setor.codigo === setorAtual) option.selected = true;
                setorSelect.appendChild(option);
            });
        } else {
            // Select único para outros níveis
            setorUnico.style.display = '';
            setorMulti.style.display = 'none';
            setorSelect.innerHTML = '<option value="">Selecione um setor</option>';
            
            if (setoresDisponiveis.length > 0) {
                setoresDisponiveis.forEach(setor => {
                    const option = document.createElement('option');
                    option.value = setor.codigo;
                    option.textContent = setor.nome;
                    if (setorAtual && setor.codigo === setorAtual) option.selected = true;
                    setorSelect.appendChild(option);
                });
                setorSelect.disabled = false;
                if (setorAjuda) {
                    setorAjuda.textContent = setoresDisponiveis.length + ' setor(es) disponível(is) para este nível';
                    setorAjuda.classList.remove('text-gray-500');
                    setorAjuda.classList.add('text-blue-600');
                }
            } else {
                setorSelect.innerHTML = '<option value="">Nenhum setor disponível</option>';
                setorSelect.disabled = true;
                if (setorAjuda) {
                    setorAjuda.textContent = 'Nenhum setor cadastrado para este nível de acesso';
                    setorAjuda.classList.remove('text-blue-600');
                    setorAjuda.classList.add('text-gray-500');
                }
            }
        }
    }

    // Descrição do nível de acesso e controle do campo município
    const nivelSelect = document.getElementById('nivel_acesso');
    const nivelDescricao = document.getElementById('nivel-descricao');
    
    const descricoes = {
        'administrador': 'Acesso completo ao sistema, incluindo gestão de usuários',
        'gestor_estadual': 'Gestão de processos e estabelecimentos em nível estadual',
        'gestor_municipal': 'Gestão de processos e estabelecimentos em nível municipal',
        'tecnico_estadual': 'Análise técnica de processos em nível estadual',
        'tecnico_municipal': 'Análise técnica de processos em nível municipal'
    };

    if (nivelSelect) {
        nivelSelect.addEventListener('change', function() {
            console.log('Nível selecionado:', this.value);
            if (nivelDescricao) {
                nivelDescricao.textContent = descricoes[this.value] || '';
            }
            atualizarSetores(this.value);
            toggleCampoMunicipio(this.value);
        });

        // Mostrar descrição, setores e campo de município inicial
        if (nivelSelect.value) {
            if (nivelDescricao) {
                nivelDescricao.textContent = descricoes[nivelSelect.value] || '';
            }
            atualizarSetores(nivelSelect.value);
            toggleCampoMunicipio(nivelSelect.value);
        }
    }

    // Controla exibição do campo município
    function toggleCampoMunicipio(nivelAcesso) {
        console.log('toggleCampoMunicipio chamado com:', nivelAcesso);
        
        const campoMunicipio = document.getElementById('campo-municipio');
        const selectMunicipio = document.getElementById('municipio_id');
        const municipioObrigatorio = document.getElementById('municipio-obrigatorio');
        const municipioAjuda = document.getElementById('municipio-ajuda');
        
        console.log('Elemento campo-municipio:', campoMunicipio);
        console.log('Elemento municipio_id:', selectMunicipio);
        
        if (!campoMunicipio || !selectMunicipio) {
            console.error('Elementos do município não encontrados!');
            return;
        }
        
        // Perfis municipais precisam de município
        const perfisMunicipais = ['gestor_municipal', 'tecnico_municipal'];
        
        if (perfisMunicipais.includes(nivelAcesso)) {
            console.log('Mostrando campo de município');
            campoMunicipio.style.display = 'block';
            selectMunicipio.required = true;
            if (municipioObrigatorio) municipioObrigatorio.style.display = 'inline';
            if (municipioAjuda) municipioAjuda.textContent = 'Obrigatório para usuários municipais';
        } else {
            console.log('Escondendo campo de município');
            campoMunicipio.style.display = 'none';
            selectMunicipio.required = false;
            if (municipioObrigatorio) municipioObrigatorio.style.display = 'none';
            if (municipioAjuda) municipioAjuda.textContent = '';
        }
    }

    // Re-filtrar setores quando o município mudar
    const municipioSelectEl = document.getElementById('municipio_id');
    if (municipioSelectEl) {
        municipioSelectEl.addEventListener('change', function() {
            const nivelSelect = document.getElementById('nivel_acesso');
            if (nivelSelect && nivelSelect.value) {
                atualizarSetores(nivelSelect.value);
            }
        });
    }
})();
</script>

@endsection
