@extends('layouts.admin')

@section('title', 'Editar Tipo de Serviço')
@section('page-title', 'Editar Tipo de Serviço')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-servico']) }}" 
           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.configuracoes.tipos-servico.update', $tipo) }}" method="POST" x-data="{ escopo: '{{ old('escopo', $tipo->escopo ?? 'estadual') }}' }">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="nome" id="nome" value="{{ old('nome', $tipo->nome) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror">
                    @error('nome')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descricao') border-red-500 @enderror">{{ old('descricao', $tipo->descricao) }}</textarea>
                    @error('descricao')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Escopo *</label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer transition"
                               :class="escopo === 'estadual' ? 'border-blue-400 bg-blue-50' : 'border-gray-200'">
                            <input type="radio" name="escopo" value="estadual" x-model="escopo" class="text-blue-600">
                            <span class="text-sm">🏛️ Estadual</span>
                        </label>
                        <label class="flex items-center gap-2 px-3 py-2 border rounded-lg cursor-pointer transition"
                               :class="escopo === 'municipal' ? 'border-green-400 bg-green-50' : 'border-gray-200'">
                            <input type="radio" name="escopo" value="municipal" x-model="escopo" class="text-green-600">
                            <span class="text-sm">🏘️ Municipal</span>
                        </label>
                    </div>
                </div>

                <div x-show="escopo === 'municipal'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Município *</label>
                    <select name="municipio_id" :required="escopo === 'municipal'"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione...</option>
                        @foreach($municipios as $mun)
                        <option value="{{ $mun->id }}" {{ old('municipio_id', $tipo->municipio_id) == $mun->id ? 'selected' : '' }}>{{ $mun->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="ordem" class="block text-sm font-medium text-gray-700 mb-1">Ordem de Exibição</label>
                    <input type="number" name="ordem" id="ordem" value="{{ old('ordem', $tipo->ordem) }}" min="0"
                           class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Menor número aparece primeiro</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="ativo" id="ativo" value="1" {{ old('ativo', $tipo->ativo) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="ativo" class="text-sm text-gray-700">Ativo</label>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-servico']) }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
