@extends('layouts.admin')

@section('title', 'Documentos Obrigatórios')
@section('page-title', 'Documentos Obrigatórios do Estabelecimento')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Documentos Obrigatórios (Definição Manual)</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $estabelecimento->nome_fantasia ?? $estabelecimento->nome_razao_social }} - {{ $estabelecimento->documento_formatado }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Instruções --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-blue-900 mb-1">Como funciona?</h3>
                <p class="text-sm text-blue-800">
                    Selecione os documentos que este estabelecimento deverá apresentar no <strong>processo de licenciamento</strong>.
                    Os documentos marcados aparecem no checklist de envio do estabelecimento na área da empresa.
                </p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.estabelecimentos.documentos-manuais.update', $estabelecimento->id) }}"
          x-data="{
              selecionados: @js($selecionados),
              busca: '',
              toggle(id) {
                  const idx = this.selecionados.indexOf(id);
                  if (idx === -1) this.selecionados.push(id);
                  else this.selecionados.splice(idx, 1);
              }
          }">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Documentos Disponíveis</h2>
                    <p class="text-sm text-gray-500 mt-1">Marque os documentos exigidos para este estabelecimento</p>
                </div>
                <div class="flex items-center gap-2 bg-green-50 px-4 py-2 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm font-semibold text-green-900">
                        <span x-text="selecionados.length"></span> selecionado(s)
                    </span>
                </div>
            </div>

            {{-- Busca --}}
            <div class="relative mb-4">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="busca" placeholder="Buscar documento..."
                       class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <div class="space-y-2 max-h-[55vh] overflow-y-auto pr-1">
                @foreach($tiposDocumento as $tipoDoc)
                <label x-show="busca === '' || {{ json_encode(mb_strtolower($tipoDoc->nome)) }}.includes(busca.toLowerCase())"
                       class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition"
                       :class="selecionados.includes({{ $tipoDoc->id }}) ? 'bg-green-50 border-green-300' : 'bg-white border-gray-200 hover:border-green-200 hover:bg-green-50/50'">
                    <input type="checkbox" name="documentos_manuais[]" value="{{ $tipoDoc->id }}"
                           @change="toggle({{ $tipoDoc->id }})"
                           :checked="selecionados.includes({{ $tipoDoc->id }})"
                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mt-0.5 flex-shrink-0">
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-gray-800">{{ $tipoDoc->nome }}</span>
                        @if($tipoDoc->descricao)
                        <span class="block text-xs text-gray-500 mt-0.5">{{ $tipoDoc->descricao }}</span>
                        @endif
                    </span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                Salvar Documentos
            </button>
        </div>
    </form>
</div>
@endsection
