@extends('layouts.admin')

@section('title', 'Novo Tipo de Setor')

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.configuracoes.tipo-setores.index') }}" 
               class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Novo Tipo de Setor</h1>
                <p class="text-sm text-gray-600 mt-1">Cadastre um novo tipo de setor e defina os níveis de acesso</p>
            </div>
        </div>
    </div>

    {{-- Formulário --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.configuracoes.tipo-setores.store') }}" class="space-y-6">
            @csrf

            {{-- Informações Básicas --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    Informações Básicas
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nome --}}
                    <div class="md:col-span-2">
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">
                            Nome do Setor <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nome" 
                               name="nome" 
                               value="{{ old('nome') }}"
                               required
                               placeholder="Ex: Vigilância Sanitária"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror">
                        @error('nome')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Código --}}
                    <div>
                        <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">
                            Código <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="codigo" 
                               name="codigo" 
                               value="{{ old('codigo') }}"
                               required
                               placeholder="Ex: vigilancia_sanitaria"
                               maxlength="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm @error('codigo') border-red-500 @enderror">
                        @error('codigo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Use apenas letras minúsculas, números e underscore (_)</p>
                    </div>

                    {{-- Status --}}
                    <div class="flex items-center pt-6">
                        <input type="checkbox" 
                               id="ativo" 
                               name="ativo" 
                               value="1"
                               {{ old('ativo', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="ativo" class="ml-2 text-sm font-medium text-gray-700">
                            Setor ativo
                        </label>
                    </div>

                    {{-- Descrição --}}
                    <div class="md:col-span-2">
                        <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">
                            Descrição
                        </label>
                        <textarea id="descricao" 
                                  name="descricao" 
                                  rows="3"
                                  placeholder="Descreva as atribuições e responsabilidades deste setor..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descricao') border-red-500 @enderror">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Níveis de Acesso --}}
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2 pb-2 border-b border-gray-200">
                    Níveis de Acesso Permitidos
                </h2>
                <p class="text-sm text-gray-600 mb-4">
                    Selecione quais níveis de acesso poderão utilizar este setor. Se nenhum for selecionado, o setor estará disponível para todos os níveis.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($niveisAcesso as $nivel)
                        <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                            <input type="checkbox" 
                                   name="niveis_acesso[]" 
                                   value="{{ $nivel->value }}"
                                   {{ is_array(old('niveis_acesso')) && in_array($nivel->value, old('niveis_acesso')) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-900">{{ $nivel->label() }}</span>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $nivel->descricao() }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                
                @error('niveis_acesso')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Vínculo Municipal (só aparece quando gestor_municipal ou tecnico_municipal estiver selecionado) --}}
            <div id="secao-vinculo-municipal" style="display: none;">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 pb-2 border-b border-gray-200">
                    Vínculo Municipal
                </h2>
                <p class="text-sm text-gray-600 mb-4">
                    Selecione os municípios onde este setor estará disponível. Se nenhum for selecionado, o setor será global.
                </p>

                <div class="max-w-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Municípios</label>
                    <div class="relative" id="municipio-setor-dropdown">
                        <input type="text"
                               id="busca-municipio-setor"
                               placeholder="Digite para buscar município..."
                               autocomplete="off"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <div id="municipio-setor-results"
                             class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto hidden">
                        </div>
                    </div>

                    {{-- Tags dos municípios selecionados --}}
                    <div id="municipios-selecionados" class="flex flex-wrap gap-2 mt-2"></div>

                    {{-- Hidden inputs gerados via JS --}}
                    <div id="municipios-hidden-inputs"></div>
                </div>
            </div>

            {{-- Botões --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.configuracoes.tipo-setores.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Criar Tipo de Setor
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Gera código automaticamente baseado no nome
document.getElementById('nome').addEventListener('input', function(e) {
    const codigoInput = document.getElementById('codigo');
    if (!codigoInput.value || codigoInput.dataset.autoGenerated === 'true') {
        let codigo = e.target.value
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s]/g, '')
            .replace(/\s+/g, '_')
            .substring(0, 50);
        codigoInput.value = codigo;
        codigoInput.dataset.autoGenerated = 'true';
    }
});
document.getElementById('codigo').addEventListener('input', function() {
    if (this.value) this.dataset.autoGenerated = 'false';
});

// === Vínculo Municipal: mostrar/esconder conforme níveis de acesso ===
const niveisMunicipais = ['gestor_municipal', 'tecnico_municipal'];
const secaoVinculo = document.getElementById('secao-vinculo-municipal');
const checkboxesNivel = document.querySelectorAll('input[name="niveis_acesso[]"]');

function atualizarVisibilidadeVinculo() {
    const selecionados = Array.from(checkboxesNivel)
        .filter(cb => cb.checked)
        .map(cb => cb.value);
    const temMunicipal = selecionados.some(v => niveisMunicipais.includes(v));
    secaoVinculo.style.display = temMunicipal ? '' : 'none';
}

checkboxesNivel.forEach(cb => cb.addEventListener('change', atualizarVisibilidadeVinculo));
atualizarVisibilidadeVinculo();

// === Multi-select de municípios com busca ===
const todosMunicipios = @json($municipios);
const municipiosSelecionados = new Map();
const inputBusca = document.getElementById('busca-municipio-setor');
const resultsDiv = document.getElementById('municipio-setor-results');
const tagsDiv = document.getElementById('municipios-selecionados');
const hiddenDiv = document.getElementById('municipios-hidden-inputs');

function renderResults(filtrados) {
    resultsDiv.innerHTML = '';
    if (filtrados.length === 0) {
        resultsDiv.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">Nenhum município encontrado.</div>';
        resultsDiv.classList.remove('hidden');
        return;
    }
    filtrados.slice(0, 20).forEach(mun => {
        const div = document.createElement('div');
        div.textContent = mun.nome;
        const jaSelecionado = municipiosSelecionados.has(mun.id);
        div.className = 'px-3 py-2 text-sm cursor-pointer transition ' +
            (jaSelecionado ? 'bg-blue-50 text-blue-400 cursor-default' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700');
        if (!jaSelecionado) {
            div.addEventListener('mousedown', function(e) {
                e.preventDefault();
                adicionarMunicipio(mun);
                inputBusca.value = '';
                resultsDiv.classList.add('hidden');
            });
        }
        resultsDiv.appendChild(div);
    });
    resultsDiv.classList.remove('hidden');
}

function adicionarMunicipio(mun) {
    if (municipiosSelecionados.has(mun.id)) return;
    municipiosSelecionados.set(mun.id, mun.nome);
    renderTags();
    renderHiddenInputs();
}

function removerMunicipio(id) {
    municipiosSelecionados.delete(id);
    renderTags();
    renderHiddenInputs();
}

function renderTags() {
    tagsDiv.innerHTML = '';
    municipiosSelecionados.forEach((nome, id) => {
        const tag = document.createElement('span');
        tag.className = 'inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg text-xs font-medium';
        tag.innerHTML = nome +
            '<button type="button" class="ml-1 text-blue-600 hover:text-blue-900" onclick="removerMunicipio(' + id + ')">' +
            '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
            '</button>';
        tagsDiv.appendChild(tag);
    });
}

function renderHiddenInputs() {
    hiddenDiv.innerHTML = '';
    municipiosSelecionados.forEach((nome, id) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'municipios[]';
        input.value = id;
        hiddenDiv.appendChild(input);
    });
}

inputBusca.addEventListener('input', function() {
    const termo = this.value.trim().toLowerCase();
    if (termo.length < 1) { resultsDiv.classList.add('hidden'); return; }
    const filtrados = todosMunicipios.filter(m => m.nome.toLowerCase().includes(termo));
    renderResults(filtrados);
});

inputBusca.addEventListener('focus', function() {
    const termo = this.value.trim().toLowerCase();
    if (termo.length >= 1) {
        const filtrados = todosMunicipios.filter(m => m.nome.toLowerCase().includes(termo));
        renderResults(filtrados);
    }
});

inputBusca.addEventListener('blur', function() {
    setTimeout(() => resultsDiv.classList.add('hidden'), 200);
});

// Expor para onclick das tags
window.removerMunicipio = removerMunicipio;
</script>
@endsection
