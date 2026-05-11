@extends('layouts.admin')

@section('title', 'Editar Modelo de Documento')
@section('page-title', 'Editar Modelo de Documento')

@section('content')
<div class="max-w-8xl mx-auto">
    {{-- Breadcrumb --}}
    @php
        $usuario = auth('interno')->user();
        $podeAcessarConfiguracoes = $usuario->isAdmin() || $usuario->isGestor();
        $escopoInicial = old('escopo', $modeloDocumento->escopo ?? 'estadual');
    @endphp

    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-600">
            @if($podeAcessarConfiguracoes)
                <a href="{{ route('admin.configuracoes.index') }}" class="hover:text-blue-600">Configurações</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            @endif
            <a href="{{ route('admin.configuracoes.modelos-documento.index') }}" class="hover:text-blue-600">Modelos de Documentos</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-900 font-medium">Editar: {{ $modeloDocumento->tipoDocumento->nome ?? 'Modelo' }}</span>
        </nav>
    </div>

    {{-- Formulário --}}
    @php
        $subcategoriasPorTipo = $tiposDocumento->mapWithKeys(function ($tipo) {
            return [$tipo->id => $tipo->subcategoriasAtivas->map(fn ($s) => [
                'id' => $s->id,
                'nome' => $s->nome,
            ])->values()->all()];
        });
    @endphp
    <form method="POST" action="{{ route('admin.configuracoes.modelos-documento.update', $modeloDocumento->id) }}" class="space-y-6"
          x-data="modeloDocumentoForm({
              escopoInicial: @js($escopoInicial),
              tipoInicial: @js(old('tipo_documento_id', $modeloDocumento->tipo_documento_id)),
              subcategoriaInicial: @js(old('subcategoria_id', $modeloDocumento->subcategoria_id)),
              subcategoriasPorTipo: {{ $subcategoriasPorTipo->toJson() }}
          })">
        @csrf
        @method('PUT')

        {{-- Card: Informações Básicas --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span class="flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs font-bold">1</span>
                Informações do Modelo
            </h3>

            <div class="space-y-6">
                {{-- Grid: Tipo, Código, Escopo e Ordem --}}
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                    {{-- Tipo de Documento --}}
                    <div class="md:col-span-2">
                        <label for="tipo_documento_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Documento <span class="text-red-500">*</span>
                        </label>
                        <select name="tipo_documento_id"
                                id="tipo_documento_id"
                                x-model="tipoId"
                                @change="onTipoChange()"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tipo_documento_id') border-red-500 @enderror">
                            <option value="">Selecione o tipo</option>
                            @foreach($tiposDocumento as $tipo)
                                <option value="{{ $tipo->id }}" {{ old('tipo_documento_id', $modeloDocumento->tipo_documento_id) == $tipo->id ? 'selected' : '' }}>
                                    {{ $tipo->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo_documento_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Escopo --}}
                    <div>
                        <label for="escopo" class="block text-sm font-medium text-gray-700 mb-2">
                            Escopo <span class="text-red-500">*</span>
                        </label>
                        @if($usuario->nivel_acesso->value === 'gestor_estadual')
                            <input type="hidden" name="escopo" value="estadual">
                            <div class="px-4 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-700">Estadual</div>
                        @elseif($usuario->nivel_acesso->value === 'gestor_municipal')
                            <input type="hidden" name="escopo" value="municipal">
                            <div class="px-4 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-700">Municipal</div>
                        @else
                            <select name="escopo"
                                    id="escopo"
                                    x-model="escopo"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('escopo') border-red-500 @enderror">
                                <option value="estadual" {{ $escopoInicial === 'estadual' ? 'selected' : '' }}>Estadual</option>
                                <option value="municipal" {{ $escopoInicial === 'municipal' ? 'selected' : '' }}>Municipal</option>
                            </select>
                        @endif
                        @error('escopo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Código --}}
                    <div>
                        <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                            Código
                        </label>
                        <input type="text" 
                               name="codigo" 
                               id="codigo" 
                               value="{{ old('codigo', $modeloDocumento->codigo) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('codigo') border-red-500 @enderror"
                               placeholder="Ex: alvara_sanitario">
                        @error('codigo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Ordem --}}
                    <div>
                        <label for="ordem" class="block text-sm font-medium text-gray-700 mb-2">
                            Ordem
                        </label>
                        <input type="number" 
                               name="ordem" 
                               id="ordem" 
                               value="{{ old('ordem', $modeloDocumento->ordem) }}"
                               min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('ordem') border-red-500 @enderror">
                        @error('ordem')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Município --}}
                <div x-show="escopo === 'municipal'" x-cloak>
                    <label for="municipio_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Município <span class="text-red-500">*</span>
                    </label>
                    @if($usuario->nivel_acesso->value === 'gestor_municipal')
                        <input type="hidden" name="municipio_id" value="{{ $usuario->municipio_id }}">
                        <div class="px-4 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-700">
                            {{ $usuario->municipioRelacionado?->nome ?? 'Município não vinculado' }}
                        </div>
                    @else
                        <select name="municipio_id"
                                id="municipio_id"
                                :disabled="escopo !== 'municipal'"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('municipio_id') border-red-500 @enderror">
                            <option value="">Selecione o município</option>
                            @foreach($municipios as $municipio)
                                <option value="{{ $municipio->id }}" {{ old('municipio_id', $modeloDocumento->municipio_id) == $municipio->id ? 'selected' : '' }}>
                                    {{ $municipio->nome }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    @error('municipio_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Subcategoria --}}
                <div x-show="subcategorias.length > 0" x-cloak>
                    <label for="subcategoria_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Subcategoria
                    </label>
                    <select name="subcategoria_id"
                            id="subcategoria_id"
                            x-model="subcategoriaId"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 @error('subcategoria_id') border-red-500 @enderror">
                        <option value="">Todas as subcategorias (modelo genérico)</option>
                        <template x-for="sub in subcategorias" :key="sub.id">
                            <option :value="sub.id" x-text="sub.nome"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Deixe em branco para aplicar este modelo a qualquer subcategoria deste tipo.</p>
                    @error('subcategoria_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descrição --}}
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição
                    </label>
                    <textarea name="descricao" 
                              id="descricao" 
                              rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descricao') border-red-500 @enderror"
                              placeholder="Descreva o propósito deste modelo">{{ old('descricao', $modeloDocumento->descricao) }}</textarea>
                    @error('descricao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label for="ativo" class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <div class="flex items-center">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="ativo" 
                                   id="ativo" 
                                   value="1"
                                   {{ old('ativo', $modeloDocumento->ativo) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Ativo</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Editor de Conteúdo --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span class="flex items-center justify-center w-6 h-6 bg-green-600 text-white rounded-full text-xs font-bold">2</span>
                Conteúdo do Modelo <span class="text-red-500">*</span>
            </h3>

            @php $conteudoInicial = old('conteudo', $modeloDocumento->conteudo); @endphp
            @include('configuracoes.modelos-documento.partials.editor-wysiwyg')
        </div>

        {{-- Botões --}}
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.configuracoes.modelos-documento.index') }}" 
               class="px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
    function modeloDocumentoForm(config) {
        const mapa = config.subcategoriasPorTipo || {};
        return {
            escopo: config.escopoInicial || 'estadual',
            tipoId: config.tipoInicial ? String(config.tipoInicial) : '',
            subcategoriaId: config.subcategoriaInicial ? String(config.subcategoriaInicial) : '',
            subcategorias: [],
            init() {
                this.atualizarSubcategorias();
            },
            onTipoChange() {
                this.subcategoriaId = '';
                this.atualizarSubcategorias();
            },
            atualizarSubcategorias() {
                const lista = mapa[this.tipoId] || [];
                this.subcategorias = lista;
            },
        };
    }
</script>
@endsection
