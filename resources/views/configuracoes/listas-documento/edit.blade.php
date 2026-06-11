@extends('layouts.admin')

@section('title', 'Editar Lista de Documentos')
@section('page-title', 'Editar Lista de Documentos')

@section('content')
<div class="max-w-8xl mx-auto" x-data="listaDocumentoForm()">
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.listas-documento.index') }}" 
           class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar
        </a>
    </div>

    @if($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <ul class="list-disc list-inside text-sm text-red-800">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.configuracoes.listas-documento.update', $lista) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Informações Básicas --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 uppercase mb-4">Informações Básicas</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="tipo_processo_id" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Processo *</label>
                    <select name="tipo_processo_id" id="tipo_processo_id" required
                            x-model="tipoProcessoId"
                            @change="onTipoProcessoChange($event)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione o tipo de processo...</option>
                        @foreach($tiposProcesso as $tp)
                        <option value="{{ $tp->id }}" data-codigo="{{ $tp->codigo }}" {{ old('tipo_processo_id', $lista->tipo_processo_id) == $tp->id ? 'selected' : '' }}>
                            {{ $tp->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome da Lista *</label>
                    <input type="text" name="nome" id="nome" value="{{ old('nome', $lista->nome) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('descricao', $lista->descricao) }}</textarea>
                </div>

                <div>
                    <label for="escopo" class="block text-sm font-medium text-gray-700 mb-1">Escopo *</label>
                    <select name="escopo" id="escopo" x-model="escopo" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="estadual">Estadual</option>
                        <option value="municipal">Municipal</option>
                    </select>
                </div>

                <div x-show="escopo === 'municipal'" x-transition>
                    <label for="municipio_id" class="block text-sm font-medium text-gray-700 mb-1">Município *</label>
                    <select name="municipio_id" id="municipio_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione o município...</option>
                        @foreach($municipios as $municipio)
                        <option value="{{ $municipio->id }}" {{ old('municipio_id', $lista->municipio_id) == $municipio->id ? 'selected' : '' }}>
                            {{ $municipio->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="ativo" id="ativo" value="1" {{ old('ativo', $lista->ativo) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="ativo" class="text-sm text-gray-700">Lista Ativa</label>
                </div>
            </div>
        </div>

        {{-- Aviso para Processos Especiais --}}
        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6 mb-6" 
             x-show="isProcessoEspecial" x-transition>
            <div class="flex items-start gap-3 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <svg class="w-5 h-5 text-purple-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-purple-800 mb-2">
                        <strong>Processo Especial:</strong> Para Projeto Arquitetônico e Análise de Rotulagem, a lista é vinculada diretamente ao tipo de processo.
                    </p>
                    <p class="text-xs text-purple-700">
                        Não é necessário selecionar tipos de serviço ou atividades (CNAEs). Os documentos serão exigidos para todos os estabelecimentos que abrirem este tipo de processo.
                    </p>
                </div>
            </div>
        </div>

        {{-- Tipos de Serviço (escondido para processos especiais) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
             x-show="!isProcessoEspecial" x-transition>
            <h3 class="text-sm font-semibold text-gray-900 uppercase mb-4">Tipos de Serviço Vinculados <span x-show="!isProcessoEspecial">*</span></h3>
            <p class="text-xs text-gray-500 mb-4">Selecione os tipos de serviço que exigirão esta lista de documentos. Todas as atividades (CNAEs) dentro do tipo selecionado serão incluídas automaticamente.</p>
            
            @php
                // Pega os tipos de serviço que têm atividades vinculadas a esta lista
                $tiposServicoSelecionados = old('tipos_servico', 
                    $lista->atividades->pluck('tipo_servico_id')->unique()->toArray()
                );
            @endphp
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($tiposServico as $tipoServico)
                <label class="flex items-start gap-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                    <input type="checkbox" name="tipos_servico[]" value="{{ $tipoServico->id }}"
                           {{ in_array($tipoServico->id, $tiposServicoSelecionados) ? 'checked' : '' }}
                           class="w-5 h-5 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">{{ $tipoServico->nome }}</span>
                            <span class="px-2 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800 rounded-full">
                                {{ $tipoServico->atividades_count ?? $tipoServico->atividades->count() }} atividades
                            </span>
                        </div>
                        @if($tipoServico->descricao)
                        <p class="text-xs text-gray-500 mt-1">{{ $tipoServico->descricao }}</p>
                        @endif
                        @if($tipoServico->atividades->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach($tipoServico->atividades->take(3) as $atividade)
                            <span class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">
                                {{ $atividade->codigo_cnae ?: Str::limit($atividade->nome, 20) }}
                            </span>
                            @endforeach
                            @if($tipoServico->atividades->count() > 3)
                            <span class="px-1.5 py-0.5 text-xs text-gray-400">
                                +{{ $tipoServico->atividades->count() - 3 }} mais
                            </span>
                            @endif
                        </div>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Documentos Obrigatórios --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 uppercase mb-4">Documentos Exigidos *</h3>
            
            {{-- Documentos Comuns (Informativo) --}}
            @if($documentosComuns->isNotEmpty())
            <div class="mb-6 p-4 bg-green-50 border-2 border-green-200 rounded-lg">
                <div class="flex items-start gap-3 mb-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-green-900 mb-1">Documentos Comuns para {{ $lista->tipoProcesso->nome ?? 'este tipo de processo' }} ({{ $documentosComuns->count() }})</h4>
                        <p class="text-xs text-green-800">Estes documentos são obrigatórios para todos os serviços deste tipo de processo e aplicados automaticamente.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-3">
                    @foreach($documentosComuns as $doc)
                    <div class="flex items-center gap-2 text-xs text-green-800 bg-green-100 px-2 py-1.5 rounded">
                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="truncate">{{ $doc->nome }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            <p class="text-xs text-gray-500 mb-4">Selecione os documentos <strong>específicos</strong> que serão exigidos e defina se são obrigatórios ou opcionais</p>
            
            @php
                $documentosSelecionados = $lista->tiposDocumentoObrigatorio->keyBy('id');
                $documentosArray = [];
                $indexCounter = 0;
                
                // Primeiro, adiciona os documentos já selecionados
                foreach($lista->tiposDocumentoObrigatorio as $docSelecionado) {
                    $documentosArray[] = [
                        'id' => $docSelecionado->id,
                        'obrigatorio' => $docSelecionado->pivot->obrigatorio,
                        'observacao' => $docSelecionado->pivot->observacao,
                        'selected' => true,
                        'index' => $indexCounter++
                    ];
                }
                
                // Depois, adiciona os documentos não selecionados
                foreach($tiposDocumento as $doc) {
                    if (!$documentosSelecionados->has($doc->id)) {
                        $documentosArray[] = [
                            'id' => $doc->id,
                            'obrigatorio' => true,
                            'observacao' => '',
                            'selected' => false,
                            'index' => $indexCounter++
                        ];
                    }
                }
            @endphp
            
            <div class="space-y-3" x-data="{ documentosData: {{ json_encode($documentosArray) }} }">
                @foreach($tiposDocumento as $doc)
                @php
                    $docData = collect($documentosArray)->firstWhere('id', $doc->id);
                    $isSelected = $docData['selected'];
                    $docIndex = $docData['index'];
                @endphp
                <div class="border border-gray-200 rounded-lg p-4" x-data="{ selecionado: {{ $isSelected ? 'true' : 'false' }} }">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" 
                               x-model="selecionado"
                               class="w-4 h-4 mt-1 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">{{ $doc->nome }}</span>
                                <div class="flex items-center gap-4" x-show="selecionado">
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="documentos[{{ $docIndex }}][obrigatorio]" value="1" 
                                               {{ $docData['obrigatorio'] ? 'checked' : '' }}
                                               x-bind:disabled="!selecionado"
                                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <span class="text-xs text-gray-600">Obrigatório</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="documentos[{{ $docIndex }}][obrigatorio]" value="0"
                                               {{ !$docData['obrigatorio'] ? 'checked' : '' }}
                                               x-bind:disabled="!selecionado"
                                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <span class="text-xs text-gray-600">Opcional</span>
                                    </label>
                                </div>
                            </div>
                            @if($doc->descricao)
                            <p class="text-xs text-gray-500 mt-1">{{ $doc->descricao }}</p>
                            @endif
                            <div x-show="selecionado" x-transition class="mt-2">
                                <input type="hidden" name="documentos[{{ $docIndex }}][id]" value="{{ $doc->id }}" x-bind:disabled="!selecionado">
                                <input type="text" name="documentos[{{ $docIndex }}][observacao]" 
                                       value="{{ $docData['observacao'] }}"
                                       x-bind:disabled="!selecionado"
                                       placeholder="Observação específica para este documento (opcional)"
                                       class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Botões --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.configuracoes.listas-documento.index') }}" 
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
function listaDocumentoForm() {
    return {
        escopo: '{{ old('escopo', $lista->escopo) }}',
        tipoProcessoId: '{{ old('tipo_processo_id', $lista->tipo_processo_id) }}',
        tipoProcessoCodigo: '{{ $lista->tipoProcesso->codigo ?? '' }}',
        isProcessoEspecial: {{ in_array($lista->tipoProcesso->codigo ?? '', ['projeto_arquitetonico', 'analise_rotulagem']) ? 'true' : 'false' }},
        
        init() {
            // Verifica se já tem um tipo de processo selecionado
            if (this.tipoProcessoId) {
                const select = document.getElementById('tipo_processo_id');
                const option = select.querySelector(`option[value="${this.tipoProcessoId}"]`);
                if (option) {
                    this.tipoProcessoCodigo = option.dataset.codigo || '';
                    this.isProcessoEspecial = ['projeto_arquitetonico', 'analise_rotulagem'].includes(this.tipoProcessoCodigo);
                }
            }
        },
        
        onTipoProcessoChange(event) {
            const select = event.target;
            const selectedOption = select.options[select.selectedIndex];
            this.tipoProcessoCodigo = selectedOption.dataset.codigo || '';
            this.isProcessoEspecial = ['projeto_arquitetonico', 'analise_rotulagem'].includes(this.tipoProcessoCodigo);
            
            // Se for processo especial, limpa os tipos de serviço selecionados
            if (this.isProcessoEspecial) {
                document.querySelectorAll('input[name="tipos_servico[]"]').forEach(cb => cb.checked = false);
            }
        }
    }
}

// Add form submit debugging for edit form
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const formData = new FormData(form);
            const documentos = [];
            const tiposServico = formData.getAll('tipos_servico[]');
            
            // Verifica se é processo especial
            const tipoProcessoSelect = document.getElementById('tipo_processo_id');
            const selectedOption = tipoProcessoSelect.options[tipoProcessoSelect.selectedIndex];
            const tipoProcessoCodigo = selectedOption?.dataset?.codigo || '';
            const isProcessoEspecial = ['projeto_arquitetonico', 'analise_rotulagem'].includes(tipoProcessoCodigo);
            
            // Collect documentos data
            let index = 0;
            while (formData.has(`documentos[${index}][id]`)) {
                const docId = formData.get(`documentos[${index}][id]`);
                const obrigatorio = formData.get(`documentos[${index}][obrigatorio]`);
                const observacao = formData.get(`documentos[${index}][observacao]`);
                
                if (docId) {
                    documentos.push({
                        id: docId,
                        obrigatorio: obrigatorio,
                        observacao: observacao
                    });
                }
                index++;
            }
            
            console.log('=== EDIT FORM DEBUG ===');
            console.log('Form submitting with:');
            console.log('Documentos:', documentos);
            console.log('Tipos Serviço:', tiposServico);
            console.log('Total documentos:', documentos.length);
            console.log('Total tipos serviço:', tiposServico.length);
            console.log('Is Processo Especial:', isProcessoEspecial);
            console.log('=======================');
            
            if (documentos.length === 0) {
                // Documentos específicos são opcionais: a lista pode usar apenas
                // os documentos comuns aplicados automaticamente.
            }
            
            // Só valida tipos de serviço se NÃO for processo especial
            if (!isProcessoEspecial && tiposServico.length === 0) {
                alert('ERRO: Nenhum tipo de serviço selecionado!');
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>
@endsection
