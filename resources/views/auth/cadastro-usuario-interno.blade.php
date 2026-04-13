@extends('layouts.auth')

@section('title', 'Cadastro de Usuário Interno')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 py-10 px-4">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">

            <div class="px-6 py-5 md:px-8">
                {{-- Card com dados do convite e QR Code --}}
                <div class="mb-6 bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl border border-gray-200 p-4">
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        @if($qrCodeBase64)
                            <div class="flex-shrink-0 bg-white p-2 rounded-lg border border-gray-200 shadow-sm">
                                <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code" class="w-28 h-28">
                            </div>
                        @endif
                        <div class="flex-1 text-center sm:text-left">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-gray-500">Dados do convite</p>
                            <h3 class="text-base font-bold text-gray-900 mt-0.5">{{ $convite->titulo }}</h3>
                            <div class="mt-1 space-y-0.5 text-xs text-gray-600">
                                <p><span class="font-medium text-gray-700">Nível:</span> {{ $nivelAcesso->label() }}</p>
                                @if($convite->municipio)
                                    <p><span class="font-medium text-gray-700">Município:</span> {{ $convite->municipio->nome }}</p>
                                @endif
                                @if($convite->expira_em)
                                    <p><span class="font-medium text-gray-700">Expira:</span> {{ $convite->expira_em->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="mb-5 bg-green-50 border border-green-200 rounded-xl p-4 flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-green-800">{{ session('success') }}</p>
                            <a href="{{ route('login') }}" class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-green-700 hover:text-green-900 underline">
                                Ir para o login →
                            </a>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 bg-red-50 border border-red-200 rounded-xl p-3">
                        <p class="text-sm font-semibold text-red-800">Não foi possível enviar o cadastro.</p>
                        <ul class="mt-1 text-xs text-red-700 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p class="text-xs text-gray-500 mb-4">Campos com <span class="text-red-500">*</span> são obrigatórios.</p>

                <form method="POST" action="{{ route('cadastro-interno.store', $convite->token) }}" class="space-y-6">
                    @csrf

                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 mb-3 pb-1 border-b border-gray-100">Dados pessoais</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="md:col-span-2">
                                <label for="nome" class="block text-xs font-medium text-gray-700 mb-1">Nome completo <span class="text-red-500">*</span></label>
                                <input type="text" id="nome" name="nome" value="{{ old('nome') }}" required
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase">
                            </div>

                            <div>
                                <label for="cpf" class="block text-xs font-medium text-gray-700 mb-1">CPF <span class="text-red-500">*</span></label>
                                <input type="text" id="cpf" name="cpf" value="{{ old('cpf') }}" maxlength="14" required
                                       placeholder="000.000.000-00"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="telefone" class="block text-xs font-medium text-gray-700 mb-1">Telefone <span class="text-red-500">*</span></label>
                                <input type="text" id="telefone" name="telefone" value="{{ old('telefone') }}" maxlength="15"
                                       placeholder="(00) 00000-0000"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="data_nascimento" class="block text-xs font-medium text-gray-700 mb-1">Data de nascimento <span class="text-red-500">*</span></label>
                                <input type="date" id="data_nascimento" name="data_nascimento" value="{{ old('data_nascimento') }}" max="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 mb-3 pb-1 border-b border-gray-100">Dados profissionais</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label for="matricula" class="block text-xs font-medium text-gray-700 mb-1">Matrícula <span class="text-red-500">*</span></label>
                                <input type="text" id="matricula" name="matricula" value="{{ old('matricula') }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase">
                            </div>

                            <div>
                                <label for="cargo" class="block text-xs font-medium text-gray-700 mb-1">Cargo <span class="text-red-500">*</span></label>
                                <input type="text" id="cargo" name="cargo" value="{{ old('cargo') }}"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase">
                            </div>

                            <div class="md:col-span-2">
                                <label for="municipio_search" class="block text-xs font-medium text-gray-700 mb-1">Município <span class="text-red-500">*</span></label>
                                <div class="relative" id="municipio-dropdown">
                                    <input type="text" id="municipio_search" autocomplete="off"
                                        value="{{ old('municipio_nome', old('municipio_id') ? optional($municipios->firstWhere('id', (int) old('municipio_id')))->nome : ($municipio?->nome ?? '')) }}"
                                        placeholder="Digite para pesquisar o município..."
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <div id="municipio-results" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto hidden"></div>
                                    <input type="hidden" id="municipio_id" name="municipio_id" value="{{ old('municipio_id', $municipio?->id) }}">
                                </div>
                                @error('municipio_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Setor: escondido até selecionar município --}}
                            <div class="md:col-span-2" id="campo-setor" style="display: none;">
                                <label for="setor" class="block text-xs font-medium text-gray-700 mb-1">Setor <span class="text-red-500">*</span></label>
                                <select id="setor" name="setor"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Selecione um setor</option>
                                </select>
                                <p class="mt-1 text-[11px] text-gray-500" id="setor-ajuda"></p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 mb-3 pb-1 border-b border-gray-100">Senha de acesso</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label for="password" class="block text-xs font-medium text-gray-700 mb-1">Senha <span class="text-red-500">*</span></label>
                                <input type="password" id="password" name="password" required minlength="8"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-[11px] text-gray-500">Mínimo 8 caracteres.</p>
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">Confirmar senha <span class="text-red-500">*</span></label>
                                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between pt-2">
                        <a href="{{ route('login') }}" class="text-xs text-gray-500 hover:text-gray-700">Voltar para o login</a>
                        <button type="submit"
                                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold text-sm">
                            Enviar cadastro para aprovação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const cpfInput = document.getElementById('cpf');
    const telefoneInput = document.getElementById('telefone');
    const municipioSearchInput = document.getElementById('municipio_search');
    const municipioIdInput = document.getElementById('municipio_id');
    const municipioResults = document.getElementById('municipio-results');
    const campoSetor = document.getElementById('campo-setor');
    const municipios = @json($municipios->map(fn ($item) => ['id' => $item->id, 'nome' => $item->nome])->values());

    cpfInput?.addEventListener('input', function () {
        let value = this.value.replace(/\D/g, '').slice(0, 11);
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        this.value = value;
    });

    telefoneInput?.addEventListener('input', function () {
        let value = this.value.replace(/\D/g, '').slice(0, 11);
        if (value.length > 10) {
            value = value.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
        } else if (value.length > 6) {
            value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
        } else if (value.length > 0) {
            value = value.replace(/^(\d*)/, '($1');
        }
        this.value = value;
    });

    // Dropdown de município
    function renderMunicipioResults(filtrados) {
        municipioResults.innerHTML = '';
        if (filtrados.length === 0) {
            municipioResults.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">Nenhum município encontrado.</div>';
            municipioResults.classList.remove('hidden');
            return;
        }
        filtrados.slice(0, 30).forEach(function (item) {
            const div = document.createElement('div');
            div.textContent = item.nome;
            div.className = 'px-3 py-2 text-sm text-gray-700 cursor-pointer hover:bg-blue-50 hover:text-blue-700 transition';
            div.addEventListener('mousedown', function (e) {
                e.preventDefault();
                municipioSearchInput.value = item.nome;
                selecionarMunicipio(item.id);
                municipioResults.classList.add('hidden');
                municipioSearchInput.classList.remove('border-gray-300');
                municipioSearchInput.classList.add('border-green-400');
            });
            municipioResults.appendChild(div);
        });
        municipioResults.classList.remove('hidden');
    }

    function selecionarMunicipio(id) {
        municipioIdInput.value = id;
        atualizarSetoresCadastro();
    }

    municipioSearchInput?.addEventListener('input', function () {
        const termo = this.value.trim().toLowerCase();
        municipioIdInput.value = '';
        municipioSearchInput.classList.remove('border-green-400');
        municipioSearchInput.classList.add('border-gray-300');
        // Esconder setor quando limpa município
        campoSetor.style.display = 'none';

        if (termo.length < 2) { municipioResults.classList.add('hidden'); return; }
        const filtrados = municipios.filter(function (m) { return m.nome.toLowerCase().indexOf(termo) !== -1; });
        renderMunicipioResults(filtrados);
    });

    municipioSearchInput?.addEventListener('focus', function () {
        const termo = this.value.trim().toLowerCase();
        if (termo.length >= 2) {
            const filtrados = municipios.filter(function (m) { return m.nome.toLowerCase().indexOf(termo) !== -1; });
            renderMunicipioResults(filtrados);
        }
    });

    municipioSearchInput?.addEventListener('blur', function () {
        setTimeout(function () { municipioResults.classList.add('hidden'); }, 200);
    });

    // === Filtragem dinâmica de setores ===
    const tipoSetoresData = @json($tipoSetores);
    const nivelAcessoConvite = @json($convite->nivel_acesso);
    const setorAtual = @json(old('setor', ''));

    function atualizarSetoresCadastro() {
        const setorSelect = document.getElementById('setor');
        const setorAjuda = document.getElementById('setor-ajuda');
        const municipioId = municipioIdInput ? municipioIdInput.value : null;

        if (!setorSelect) return;

        setorSelect.innerHTML = '<option value="">Selecione um setor</option>';

        if (!municipioId) {
            campoSetor.style.display = 'none';
            return;
        }

        const setoresDisponiveis = tipoSetoresData.filter(function(setor) {
            if (setor.niveis_acesso && setor.niveis_acesso.length > 0) {
                if (!setor.niveis_acesso.includes(nivelAcessoConvite)) return false;
            }
            if (setor.municipio_ids && setor.municipio_ids.length > 0) {
                return setor.municipio_ids.includes(Number(municipioId));
            }
            return true;
        });

        // Mostrar campo setor
        campoSetor.style.display = '';

        if (setoresDisponiveis.length > 0) {
            setoresDisponiveis.forEach(function(setor) {
                const option = document.createElement('option');
                option.value = setor.codigo;
                option.textContent = setor.nome;
                if (setorAtual && setor.codigo === setorAtual) option.selected = true;
                setorSelect.appendChild(option);
            });
            setorSelect.disabled = false;
            if (setorAjuda) {
                setorAjuda.textContent = setoresDisponiveis.length + ' setor(es) disponível(is)';
                setorAjuda.className = 'mt-1 text-[11px] text-blue-600';
            }
        } else {
            setorSelect.innerHTML = '<option value="">Nenhum setor disponível</option>';
            setorSelect.disabled = true;
            if (setorAjuda) {
                setorAjuda.textContent = 'Nenhum setor cadastrado para este município';
                setorAjuda.className = 'mt-1 text-[11px] text-gray-500';
            }
        }
    }

    // Se já tem município pré-selecionado, marca verde e carrega setores
    if (municipioIdInput?.value) {
        municipioSearchInput?.classList.remove('border-gray-300');
        municipioSearchInput?.classList.add('border-green-400');
        atualizarSetoresCadastro();
    }
</script>
@endsection
