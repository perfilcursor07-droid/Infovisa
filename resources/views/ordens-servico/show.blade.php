@extends('layouts.admin')

@section('title', 'Detalhes da Ordem de Serviço')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Clean --}}
    <div class="bg-white border-b border-gray-200">
        <div class="container-fluid px-3 sm:px-6 py-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('admin.ordens-servico.index') }}" 
                       class="shrink-0 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h1 class="min-w-0 text-base sm:text-lg font-semibold text-gray-900 break-words">OS #{{ $ordemServico->numero }}</h1>
                </div>
                <div class="flex flex-col gap-2 w-full lg:w-auto lg:items-end">
                    <div class="flex flex-wrap items-center gap-2">
                        {!! $ordemServico->status_badge !!}
                        {!! $ordemServico->competencia_badge !!}
                    </div>
                    @php
                        $todosEstabPdf = $ordemServico->getTodosEstabelecimentos();
                        $estabPdfInicial = $todosEstabPdf->first();
                        $pdfBaseUrl = route('admin.ordens-servico.pdf', $ordemServico);
                        $pdfInitialUrl = $estabPdfInicial ? ($pdfBaseUrl . '?estabelecimento_id=' . $estabPdfInicial->id) : $pdfBaseUrl;
                    @endphp
                    @if($todosEstabPdf->count() > 1)
                    <div class="relative w-full sm:w-auto" id="dropdownPdfContainer">
                        <div class="flex w-full sm:inline-flex rounded-lg overflow-hidden border border-red-200">
                            <a id="btnBaixarPdfOs" href="{{ $pdfInitialUrl }}" data-base-url="{{ $pdfBaseUrl }}"
                               target="_blank"
                               class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-red-100 text-red-700 hover:bg-red-200 text-sm font-medium transition-colors whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                Baixar PDF
                            </a>
                            <button type="button" onclick="toggleDropdownPdf()" 
                                    class="shrink-0 inline-flex items-center justify-center px-3 py-2 bg-red-100 text-red-700 hover:bg-red-200 border-l border-red-200 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>
                        <div id="dropdownPdfMenu" class="hidden absolute left-0 right-0 sm:left-auto sm:right-0 mt-1 w-full sm:w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="py-1">
                                <a href="{{ route('admin.ordens-servico.pdf-todos', $ordemServico) }}" 
                                   target="_blank"
                                   onclick="document.getElementById('dropdownPdfMenu').classList.add('hidden')"
                                   class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Baixar PDF de Todos ({{ $todosEstabPdf->count() }})
                                </a>
                            </div>
                        </div>
                    </div>
                    @else
                    <a id="btnBaixarPdfOs" href="{{ $pdfInitialUrl }}" data-base-url="{{ $pdfBaseUrl }}"
                       target="_blank"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-3 py-2 bg-red-100 text-red-700 hover:bg-red-200 text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Baixar PDF
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-3 sm:px-4 py-4 sm:py-6">
        {{-- Layout de 2 Colunas: Menu Lateral (25%) + Conteúdo (75%) --}}
        <div class="flex flex-col lg:flex-row gap-6">
            
            {{-- ========================================
                COLUNA ESQUERDA: Menu de Ações (25%)
            ======================================== --}}
            <aside class="lg:w-1/4 space-y-5">
                {{-- Card de Menu de Opções --}}
                <div class="bg-white rounded-lg border border-gray-200 sticky top-6">
                    <div class="p-3 space-y-1.5">
                        @php
                            $isTecnicoAtribuido = $ordemServico->tecnicos_ids && in_array(auth()->id(), $ordemServico->tecnicos_ids);
                            $isGestor = auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual() || auth('interno')->user()->isMunicipal();
                            $isGestorOuAdmin = auth('interno')->user()->isAdmin() || auth('interno')->user()->isGestor();
                        @endphp
                        
                        @if($ordemServico->status === 'finalizada')
                            {{-- Botão Reiniciar OS (apenas para gestores) --}}
                            @if($isGestor)
                            <form method="POST" action="{{ route('admin.ordens-servico.reiniciar', $ordemServico) }}" 
                                  onsubmit="return confirm('Tem certeza que deseja reiniciar esta OS? Ela voltará ao status \'Em Andamento\'.')">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-orange-700 bg-orange-50 rounded-md hover:bg-orange-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reiniciar OS
                                </button>
                            </form>
                            @endif
                        @elseif($ordemServico->status === 'cancelada')
                            {{-- Botão Reativar OS (apenas para gestores) --}}
                            @if($isGestor)
                            <form method="POST" action="{{ route('admin.ordens-servico.reativar', $ordemServico) }}" 
                                  onsubmit="return confirm('Tem certeza que deseja reativar esta OS? Ela voltará ao status Em Andamento.')">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-green-700 bg-green-50 rounded-md hover:bg-green-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reativar OS
                                </button>
                            </form>
                            @else
                            <div class="text-center py-3 px-3 bg-red-50 rounded-md border border-red-200">
                                <p class="text-xs text-red-700 font-medium">Ordem de Serviço Cancelada</p>
                            </div>
                            @endif
                        @else
                            @php
                                $usuarioLogado = auth('interno')->user();
                                $ehTecnico = in_array($usuarioLogado->nivel_acesso->value, ['tecnico_estadual', 'tecnico_municipal']);
                                
                                // Verifica se o técnico está vinculado a alguma atividade pendente
                                $tecnicoTemAtividadePendente = false;
                                if ($ehTecnico && $ordemServico->atividades_tecnicos) {
                                    foreach ($ordemServico->atividades_tecnicos as $ativ) {
                                        $statusAtiv = $ativ['status'] ?? 'pendente';
                                        $tecnicosAtiv = $ativ['tecnicos'] ?? [];
                                        if ($statusAtiv !== 'finalizada' && in_array($usuarioLogado->id, $tecnicosAtiv)) {
                                            $tecnicoTemAtividadePendente = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            
                            @if(!$ehTecnico)
                            {{-- Botão Editar - Apenas para Admin e Gestores --}}
                            <a href="{{ route('admin.ordens-servico.edit', $ordemServico) }}" 
                               class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar OS
                            </a>
                            @endif
                            
                            @if($ehTecnico && $tecnicoTemAtividadePendente && $ordemServico->status === 'em_andamento')
                            {{-- Botão Prosseguir Atividades - Apenas para Técnicos vinculados a atividades pendentes --}}
                            <a href="#secao-atividades" 
                               class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-green-700 bg-green-50 rounded-md hover:bg-green-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Prosseguir Atividade
                            </a>
                            @endif
                            
                            @if(!$ehTecnico)
                            {{-- Botão Cancelar OS - Apenas para Admin e Gestores --}}
                            <button type="button" 
                                    onclick="abrirModalCancelarOS()"
                                    class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cancelar OS
                            </button>
                            @endif
                        @endif
                        
                        {{-- Botão Voltar --}}
                        <a href="{{ route('admin.ordens-servico.index') }}" 
                           class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Voltar
                        </a>
                    </div>
                </div>

                {{-- Card de Informações Rápidas --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-3 space-y-2">
                        @if($ordemServico->processo)
                        <div>
                            <label class="text-xs font-medium text-gray-500">Processo</label>
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$ordemServico->processo->estabelecimento_id, $ordemServico->processo->id]) }}" 
                               class="block text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                {{ $ordemServico->processo->numero_processo }}
                            </a>
                        </div>
                        <div class="border-t border-gray-100 pt-2"></div>
                        @endif
                        @php
                            // Prioriza o município do primeiro estabelecimento (via municipio_id), se existir
                            $municipioExibir = null;
                            $primeiroEstab = $ordemServico->getTodosEstabelecimentos()->first();
                            if ($primeiroEstab && $primeiroEstab->municipio_id) {
                                $municipioExibir = \App\Models\Municipio::find($primeiroEstab->municipio_id);
                            } elseif ($ordemServico->municipio_id) {
                                $municipioExibir = $ordemServico->municipio;
                            }
                        @endphp
                        @if($municipioExibir)
                        <div>
                            <label class="text-xs font-medium text-gray-500">Município</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $municipioExibir->nome }}/{{ $municipioExibir->uf }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Card de Datas --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-3 space-y-1.5">
                        <div class="flex justify-between items-center py-1.5">
                            <label class="text-xs text-gray-500">Abertura</label>
                            <p class="text-xs font-medium text-gray-900">
                                {{ $ordemServico->data_abertura ? $ordemServico->data_abertura->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-t border-gray-100">
                            <label class="text-xs text-gray-500">Início</label>
                            <p class="text-xs font-medium text-gray-900">
                                {{ $ordemServico->data_inicio ? $ordemServico->data_inicio->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-t border-gray-100">
                            <label class="text-xs text-gray-500">Término</label>
                            <p class="text-xs font-medium text-gray-900">
                                {{ $ordemServico->data_fim ? $ordemServico->data_fim->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        @if($ordemServico->data_conclusao)
                        <div class="flex justify-between items-center py-1.5 border-t border-gray-100">
                            <label class="text-xs text-gray-500">Conclusão</label>
                            <p class="text-xs font-medium text-gray-900">
                                {{ $ordemServico->data_conclusao->format('d/m/Y') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

            </aside>

            {{-- ========================================
                COLUNA DIREITA: Conteúdo Principal (75%)
            ======================================== --}}
            <main class="lg:w-3/4 space-y-6">
            {{-- Informações dos Estabelecimentos --}}
            @php
                $todosEstabelecimentos = $ordemServico->getTodosEstabelecimentos();
            @endphp
            @if($todosEstabelecimentos->count() > 0)
            @foreach($todosEstabelecimentos as $estabIndex => $estabelecimentoItem)
            <div class="bg-white rounded-lg border border-gray-200 estabelecimento-slide" data-estabelecimento-index="{{ $estabIndex }}" data-estabelecimento-id="{{ $estabelecimentoItem->id }}" @if($estabIndex > 0) style="display:none;" @endif>
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">
                        Estabelecimento
                        @if($todosEstabelecimentos->count() > 1)
                            <span class="text-xs text-gray-500 ml-1">({{ $estabIndex + 1 }} de {{ $todosEstabelecimentos->count() }})</span>
                        @endif
                    </h2>
                    @if($todosEstabelecimentos->count() > 1)
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="mostrarEstabelecimentoAnterior()" class="inline-flex items-center justify-center w-7 h-7 rounded-md border border-gray-300 text-gray-600 hover:bg-gray-50" title="Estabelecimento anterior">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button type="button" onclick="mostrarProximoEstabelecimento()" class="inline-flex items-center justify-center w-7 h-7 rounded-md border border-gray-300 text-gray-600 hover:bg-gray-50" title="Próximo estabelecimento">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500">Razão Social</label>
                            <a href="{{ route('admin.estabelecimentos.show', $estabelecimentoItem->id) }}" 
                               class="block text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                {{ $estabelecimentoItem->razao_social }}
                            </a>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Nome Fantasia</label>
                            <a href="{{ route('admin.estabelecimentos.show', $estabelecimentoItem->id) }}" 
                               class="block text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                                {{ $estabelecimentoItem->nome_fantasia }}
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="text-xs text-gray-500">
                                {{ $estabelecimentoItem->tipo_pessoa === 'fisica' ? 'CPF' : 'CNPJ' }}
                            </label>
                            <a href="{{ route('admin.estabelecimentos.show', $estabelecimentoItem->id) }}" 
                               class="block text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline transition-colors font-mono">
                                @if($estabelecimentoItem->tipo_pessoa === 'fisica')
                                    {{ $estabelecimentoItem->cpf_formatado ?? '-' }}
                                @else
                                    {{ $estabelecimentoItem->cnpj_formatado ?? '-' }}
                                @endif
                            </a>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">CEP</label>
                            <p class="text-sm font-medium text-gray-900 font-mono">{{ $estabelecimentoItem->cep ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-gray-100">
                        <label class="text-xs text-gray-500">Endereço</label>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $estabelecimentoItem->logradouro ?? $estabelecimentoItem->endereco ?? '-' }}
                            @if($estabelecimentoItem->complemento) - {{ $estabelecimentoItem->complemento }}@endif
                            , {{ $estabelecimentoItem->bairro }}
                            - {{ $estabelecimentoItem->cidade }}/{{ $estabelecimentoItem->estado }}
                        </p>
                    </div>

                    {{-- Processo Vinculado ao Estabelecimento --}}
                    @if($estabelecimentoItem->pivot && $estabelecimentoItem->pivot->processo_id)
                    @php
                        $processoEstab = \App\Models\Processo::find($estabelecimentoItem->pivot->processo_id);
                    @endphp
                    @if($processoEstab)
                    <div class="pt-2 border-t border-gray-100">
                        <label class="text-xs text-gray-500">Processo Vinculado</label>
                        <a href="{{ route('admin.estabelecimentos.processos.show', [$processoEstab->estabelecimento_id, $processoEstab->id]) }}" 
                           class="block text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                            {{ $processoEstab->numero_processo }}
                            @if($processoEstab->tipo_label)
                                <span class="text-xs text-gray-500">({{ $processoEstab->tipo_label }})</span>
                            @endif
                        </a>
                    </div>
                    @endif
                    @endif

                    {{-- Status de Equipamentos de Imagem --}}
                    @php
                        $codigosAtividadesRadiacao = \App\Models\AtividadeEquipamentoRadiacao::where('ativo', true)
                            ->pluck('codigo_atividade')
                            ->map(fn($c) => preg_replace('/[^0-9]/', '', $c))
                            ->unique()
                            ->filter()
                            ->toArray();
                        
                        $atividadesEstabelecimento = $estabelecimentoItem->getTodasAtividades();
                        $exigeEquipamentos = false;
                        foreach ($atividadesEstabelecimento as $codigo) {
                            if (in_array($codigo, $codigosAtividadesRadiacao)) {
                                $exigeEquipamentos = true;
                                break;
                            }
                        }
                    @endphp

                    @if($exigeEquipamentos)
                    <div class="pt-2 border-t border-gray-100">
                        @if($estabelecimentoItem->equipamentosRadiacao()->count() > 0)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-green-800">Equipamentos Registrados</p>
                                        <p class="text-xs text-green-700 mt-1">Este estabelecimento possui <strong>{{ $estabelecimentoItem->equipamentosRadiacao()->count() }}</strong> equipamento(s) de imagem cadastrado(s).</p>
                                        <a href="{{ route('admin.estabelecimentos.equipamentos-radiacao.index', $estabelecimentoItem->id) }}" 
                                           class="inline-flex items-center gap-1 text-xs text-green-600 hover:text-green-800 font-medium mt-2">
                                            Ver equipamentos
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @elseif($estabelecimentoItem->declaracao_sem_equipamentos_imagem)
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-amber-800">Declaração: Não Possui Equipamentos</p>
                                        <p class="text-xs text-amber-700 mt-1">O estabelecimento declarou formalmente que <strong>não possui equipamentos de imagem</strong>, mesmo possuindo atividades que normalmente exigem.</p>
                                        
                                        @if($estabelecimentoItem->declaracao_sem_equipamentos_opcoes)
                                        <div class="mt-2 pt-2 border-t border-amber-200">
                                            <p class="text-xs font-medium text-amber-800 mb-1">Confirmações:</p>
                                            <div class="space-y-1">
                                                @php
                                                    $opcoes = json_decode($estabelecimentoItem->declaracao_sem_equipamentos_opcoes, true) ?? [];
                                                @endphp
                                                @if(in_array('opcao_1', $opcoes))
                                                <div class="flex items-start gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    <span class="text-xs text-amber-800">Não executa atividades de diagnóstico por imagem neste estabelecimento</span>
                                                </div>
                                                @endif
                                                @if(in_array('opcao_2', $opcoes))
                                                <div class="flex items-start gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    <span class="text-xs text-amber-800">Não possui equipamentos de diagnóstico por imagem instalados no local</span>
                                                </div>
                                                @endif
                                                @if(in_array('opcao_3', $opcoes))
                                                <div class="flex items-start gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    <span class="text-xs text-amber-800">Os exames, quando necessários, são integralmente terceirizados ou realizados em outro estabelecimento regularmente licenciado</span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                        
                                        @if($estabelecimentoItem->declaracao_sem_equipamentos_imagem_justificativa)
                                        <p class="text-xs text-amber-700 mt-2 pt-2 border-t border-amber-200"><strong>Justificativa:</strong> {{ $estabelecimentoItem->declaracao_sem_equipamentos_imagem_justificativa }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-red-800">Equipamentos Não Registrados</p>
                                        <p class="text-xs text-red-700 mt-1">Este estabelecimento não possui equipamentos de imagem cadastrados e nem declaração formal.</p>
                                        <a href="{{ route('admin.estabelecimentos.equipamentos-radiacao.index', $estabelecimentoItem->id) }}" 
                                           class="inline-flex items-center gap-1 text-xs text-red-600 hover:text-red-800 font-medium mt-2">
                                            Cadastrar equipamentos
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif

                    {{-- Mapa de Localização --}}
                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Localização
                            </label>
                            @php
                                // Usa logradouro como prioridade (campo da API), senão usa endereco
                                $logradouro = $estabelecimentoItem->logradouro ?? $estabelecimentoItem->endereco ?? '';
                                $numero = $estabelecimentoItem->numero ?? '';
                                $bairro = $estabelecimentoItem->bairro ?? '';
                                $cidade = $estabelecimentoItem->cidade ?? '';
                                $estado = $estabelecimentoItem->estado ?? 'TO';
                                $cep = $estabelecimentoItem->cep ?? '';
                                
                                // Monta o endereço completo para o Google Maps
                                $partes = [];
                                if ($logradouro) {
                                    // Se o logradouro já contém número (ex: "07 DE SETEMBRO, 340-B"), usa direto
                                    $partes[] = $logradouro;
                                }
                                if ($bairro) {
                                    $partes[] = $bairro;
                                }
                                if ($cidade) {
                                    $partes[] = $cidade;
                                }
                                if ($estado) {
                                    $partes[] = $estado;
                                }
                                if ($cep) {
                                    $partes[] = preg_replace('/[^0-9]/', '', $cep);
                                }
                                $partes[] = 'Brasil';
                                
                                $enderecoCompleto = implode(', ', $partes);
                                $endereco = urlencode($enderecoCompleto);
                                
                                $googleMapsUrl = "https://www.google.com/maps/search/?api=1&query=" . $endereco;
                                $googleMapsEmbedUrl = "https://www.google.com/maps?q=" . $endereco . "&output=embed";
                            @endphp
                            <a href="{{ $googleMapsUrl }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Abrir no Maps
                            </a>
                        </div>
                        
                        {{-- Iframe do Google Maps --}}
                        <div class="relative w-full rounded-lg overflow-hidden border border-gray-300 bg-gray-100" style="height: 300px;">
                            <iframe 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                style="border:0" 
                                referrerpolicy="no-referrer-when-downgrade"
                                src="{{ $googleMapsEmbedUrl }}"
                                allowfullscreen>
                            </iframe>
                        </div>
                        
                        
                        {{-- Debug: Mostra o endereço que está sendo usado --}}
                        <div class="mt-2 p-2 bg-gray-50 rounded text-xs text-gray-600">
                            <strong>Endereço usado no mapa:</strong> {{ $enderecoCompleto }}
                        </div>
                        
                        <p class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Clique em "Abrir no Maps" para ver rotas e mais detalhes
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
            @else
            {{-- Aviso quando não há estabelecimento --}}
            <div class="bg-amber-50 rounded-lg shadow border border-amber-200 p-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-amber-900 mb-1">Ordem de Serviço sem Estabelecimento</h3>
                        <p class="text-sm text-amber-800 mb-3">
                            Esta OS foi criada sem um estabelecimento vinculado. Você pode vincular um estabelecimento ao editar ou finalizar a ordem de serviço.
                        </p>
                        @php
                            $usuarioLogado = auth('interno')->user();
                            $ehTecnico = in_array($usuarioLogado->nivel_acesso->value, ['tecnico_estadual', 'tecnico_municipal']);
                        @endphp
                        @if($ordemServico->status !== 'finalizada' && !$ehTecnico)
                        <a href="{{ route('admin.ordens-servico.edit', $ordemServico) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Editar e Vincular Estabelecimento
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Atividades por Técnico (Nova Seção) --}}
            @if($ordemServico->atividades_tecnicos && count($ordemServico->atividades_tecnicos) > 0)
            <div id="secao-atividades" class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Atividades por Técnico
                    </h2>
                    @php
                        $totalAtividades = count($ordemServico->atividades_tecnicos);
                        $atividadesFinalizadas = collect($ordemServico->atividades_tecnicos)->filter(fn($a) => ($a['status'] ?? 'pendente') === 'finalizada')->count();
                    @endphp
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">
                            {{ $atividadesFinalizadas }}/{{ $totalAtividades }} concluídas
                        </span>
                    </div>
                </div>
                <div class="px-5 py-5 space-y-4">
                    @foreach($ordemServico->atividades_tecnicos as $index => $atividade)
                        @php
                            $statusAtividade = $atividade['status'] ?? 'pendente';
                            $responsavelId = $atividade['responsavel_id'] ?? null;
                            $responsavel = $responsavelId ? \App\Models\UsuarioInterno::find($responsavelId) : null;
                            $tecnicosIds = $atividade['tecnicos'] ?? [];
                            $tecnicos = \App\Models\UsuarioInterno::whereIn('id', $tecnicosIds)->get();
                            $finalizadaPor = isset($atividade['finalizada_por']) ? \App\Models\UsuarioInterno::find($atividade['finalizada_por']) : null;
                            $finalizadaEm = isset($atividade['finalizada_em']) ? \Carbon\Carbon::parse($atividade['finalizada_em']) : null;
                            $usuarioLogadoAtribuido = in_array(auth('interno')->id(), $tecnicosIds);
                            $usuarioLogadoResponsavel = $responsavelId && auth('interno')->id() == $responsavelId;
                            $podeFinalizarAtividade = $usuarioLogadoAtribuido && (count($tecnicosIds) <= 1 || !$responsavelId || $usuarioLogadoResponsavel);
                            $documentosDigitaisAtividade = $ordemServico->documentosDigitais->where('atividade_index', $index)->sortByDesc('created_at')->values();
                            $arquivosExternosAtividade = $ordemServico->arquivosExternos->where('atividade_index', $index)->sortByDesc('created_at')->values();
                            $totalItensAtividade = $documentosDigitaisAtividade->count() + $arquivosExternosAtividade->count();
                        @endphp
                        
                        <div class="border rounded-xl overflow-hidden {{ $statusAtividade === 'finalizada' ? 'border-green-200 bg-green-50/50' : 'border-gray-200 bg-white' }}">
                            {{-- Header da Atividade --}}
                            <div class="px-4 py-3 flex items-center justify-between {{ $statusAtividade === 'finalizada' ? 'bg-green-100/50' : 'bg-gray-50' }}">
                                <div class="flex items-center gap-3">
                                    @if($statusAtividade === 'finalizada')
                                        <div class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ $atividade['nome_atividade'] ?? 'Atividade' }}</h4>
                                        @if(!empty($atividade['estabelecimento_id']))
                                            @php
                                                $estabAtividade = $todosEstabelecimentos->firstWhere('id', $atividade['estabelecimento_id']);
                                            @endphp
                                            @if($estabAtividade)
                                            <p class="text-xs text-blue-600 flex items-center gap-1 mt-0.5">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                {{ $estabAtividade->nome_fantasia }}
                                            </p>
                                            @endif
                                        @endif
                                        @if($statusAtividade === 'finalizada')
                                            <p class="text-xs text-green-700">
                                                @if($finalizadaEm)
                                                    Finalizada em {{ $finalizadaEm->format('d/m/Y H:i') }}
                                                    @if($finalizadaPor) por {{ $finalizadaPor->nome }} @endif
                                                @else
                                                    Finalizada
                                                @endif
                                            </p>
                                        @else
                                            <p class="text-xs text-amber-700">Pendente</p>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Botão Finalizar (apenas para técnicos atribuídos e se não finalizada) --}}
                                @if($statusAtividade !== 'finalizada' && $podeFinalizarAtividade && $ordemServico->status === 'em_andamento')
                                    <a href="{{ route('admin.ordens-servico.show-finalizar-atividade', [$ordemServico, $index]) }}"
                                       class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Prosseguir Atividade
                                    </a>
                                @elseif($statusAtividade === 'finalizada')
                                    <div class="flex items-center gap-2">
                                        @php
                                            $statusExecucao = $atividade['status_execucao'] ?? 'concluido';
                                            $statusLabel = match($statusExecucao) {
                                                'concluido' => '✓ Concluída',
                                                'parcial' => '⚠ Parcial',
                                                'nao_concluido' => '✗ Não concluída',
                                                default => '✓ Concluída'
                                            };
                                            $statusClass = match($statusExecucao) {
                                                'concluido' => 'text-green-700 bg-green-100',
                                                'parcial' => 'text-yellow-700 bg-yellow-100',
                                                'nao_concluido' => 'text-red-700 bg-red-100',
                                                default => 'text-green-700 bg-green-100'
                                            };
                                        @endphp
                                        <span class="px-3 py-1.5 text-xs font-medium {{ $statusClass }} rounded-lg">
                                            {{ $statusLabel }}
                                        </span>
                                        @if($isGestorOuAdmin)
                                            <button type="button" 
                                                    onclick="reiniciarAtividade({{ $index }}, '{{ addslashes($atividade['nome_atividade'] ?? 'Atividade') }}')"
                                                    class="px-2 py-1.5 text-xs font-medium text-orange-700 bg-orange-100 rounded-lg hover:bg-orange-200 transition-colors flex items-center gap-1"
                                                    title="Reiniciar esta atividade">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                Reiniciar
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Técnicos da Atividade --}}
                            <div class="px-4 py-3">
                                <p class="text-xs font-medium text-gray-500 mb-2">Técnicos atribuídos:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($tecnicos as $tecnico)
                                        <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs {{ $tecnico->id == $responsavelId ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-gray-100 text-gray-700' }}">
                                            <div class="w-5 h-5 rounded-full {{ $tecnico->id == $responsavelId ? 'bg-indigo-600' : 'bg-gray-500' }} flex items-center justify-center">
                                                <span class="text-white font-bold text-[10px]">{{ strtoupper(substr($tecnico->nome, 0, 1)) }}</span>
                                            </div>
                                            <span class="font-medium">{{ $tecnico->nome }}</span>
                                            @if($tecnico->id == $responsavelId)
                                                <span class="text-[10px] bg-indigo-200 px-1 rounded">Responsável</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                
                                {{-- Observações da finalização --}}
                                @if($statusAtividade === 'finalizada' && !empty($atividade['observacoes_finalizacao']))
                                    <div class="mt-3 p-2 bg-green-50 rounded-lg border border-green-200">
                                        <p class="text-xs font-medium text-green-800 mb-1">Observações:</p>
                                        <p class="text-xs text-green-700">{{ $atividade['observacoes_finalizacao'] }}</p>
                                    </div>
                                @endif

                                @if($statusAtividade === 'finalizada' && !empty($atividade['execucao_estabelecimentos']) && is_array($atividade['execucao_estabelecimentos']))
                                    <div class="mt-3 p-2.5 bg-gray-50 rounded-lg border border-gray-200">
                                        <p class="text-xs font-semibold text-gray-700 mb-2">Execução por estabelecimento</p>
                                        <div class="space-y-2">
                                            @foreach($atividade['execucao_estabelecimentos'] as $execEst)
                                                @php
                                                    $executadaEst = (bool)($execEst['executada'] ?? false);
                                                @endphp
                                                <div class="p-2 rounded border {{ $executadaEst ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <p class="text-xs font-medium {{ $executadaEst ? 'text-green-800' : 'text-red-800' }}">
                                                            {{ $execEst['estabelecimento_nome'] ?? ('ID ' . ($execEst['estabelecimento_id'] ?? '-')) }}
                                                        </p>
                                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded {{ $executadaEst ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                            {{ $executadaEst ? 'Executada' : 'Não executada' }}
                                                        </span>
                                                    </div>
                                                    @if(!$executadaEst && !empty($execEst['justificativa']))
                                                        <p class="text-xs text-red-700 mt-1">
                                                            <span class="font-medium">Justificativa:</span> {{ $execEst['justificativa'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if($totalItensAtividade > 0)
                                    <div class="mt-3 rounded-lg border border-indigo-100 bg-indigo-50/50 overflow-hidden">
                                        <div class="px-3 py-2 border-b border-indigo-100 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <p class="text-xs font-semibold text-indigo-900">Documentos da atividade</p>
                                            <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-white text-indigo-700 rounded-full">{{ $totalItensAtividade }}</span>
                                        </div>
                                        <div class="px-3 py-2.5 space-y-2">
                                            @foreach($documentosDigitaisAtividade as $documentoAtividade)
                                                @php
                                                    $statusDocumentoAtividade = match($documentoAtividade->status) {
                                                        'rascunho' => ['label' => 'Rascunho', 'class' => 'bg-gray-100 text-gray-600'],
                                                        'aguardando_assinatura' => ['label' => 'Ag. assinatura', 'class' => 'bg-yellow-100 text-yellow-700'],
                                                        'assinado' => ['label' => 'Assinado', 'class' => 'bg-green-100 text-green-700'],
                                                        'cancelado' => ['label' => 'Cancelado', 'class' => 'bg-red-100 text-red-700'],
                                                        default => ['label' => ucfirst($documentoAtividade->status), 'class' => 'bg-gray-100 text-gray-600'],
                                                    };
                                                @endphp
                                                <a href="{{ route('admin.documentos.show', $documentoAtividade->id) }}"
                                                   target="_blank"
                                                   class="flex items-center justify-between gap-3 rounded-lg border border-indigo-100 bg-white px-3 py-2 hover:border-indigo-200 hover:bg-indigo-50/50 transition">
                                                    <div class="min-w-0 flex items-center gap-2.5">
                                                        <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="text-xs font-medium text-gray-900 truncate">{{ $documentoAtividade->nome ?? $documentoAtividade->tipoDocumento->nome ?? 'Documento' }}</p>
                                                            <div class="flex flex-wrap items-center gap-2 mt-0.5 text-[11px] text-gray-500">
                                                                <span>#{{ $documentoAtividade->numero_documento }}</span>
                                                                <span>{{ $documentoAtividade->created_at->format('d/m/Y H:i') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-shrink-0 flex items-center gap-2">
                                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded {{ $statusDocumentoAtividade['class'] }}">{{ $statusDocumentoAtividade['label'] }}</span>
                                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-indigo-100 text-indigo-700">Digital</span>
                                                    </div>
                                                </a>
                                            @endforeach
                                            @foreach($arquivosExternosAtividade as $arquivoAtividade)
                                                @php
                                                    $processoArquivoAtividade = $arquivoAtividade->processo;
                                                    $linkArquivoAtividade = $arquivoAtividade->processo_id && $processoArquivoAtividade?->estabelecimento_id
                                                        ? route('admin.estabelecimentos.processos.visualizar', [$processoArquivoAtividade->estabelecimento_id, $arquivoAtividade->processo_id, $arquivoAtividade->id])
                                                        : route('admin.ordens-servico.arquivos-externos.visualizar', [$ordemServico, $arquivoAtividade]);
                                                @endphp
                                                <a href="{{ $linkArquivoAtividade }}"
                                                   target="_blank"
                                                   class="flex items-center justify-between gap-3 rounded-lg border border-blue-100 bg-white px-3 py-2 hover:border-blue-200 hover:bg-blue-50/50 transition">
                                                    <div class="min-w-0 flex items-center gap-2.5">
                                                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                            </svg>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="text-xs font-medium text-gray-900 truncate">{{ $arquivoAtividade->nome_original }}</p>
                                                            <div class="flex flex-wrap items-center gap-2 mt-0.5 text-[11px] text-gray-500">
                                                                <span>{{ $arquivoAtividade->created_at->format('d/m/Y H:i') }}</span>
                                                                <span>{{ $arquivoAtividade->tamanho_formatado }}</span>
                                                                @if($processoArquivoAtividade)
                                                                    <span>Proc. {{ $processoArquivoAtividade->numero_processo ?? '#' . $processoArquivoAtividade->id }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-shrink-0 flex items-center gap-2">
                                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-blue-100 text-blue-700">Arquivo</span>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Informação de documentos da OS --}}
                                @if($statusAtividade === 'finalizada')
                                    @if(!empty($atividade['tem_documentos_os']))
                                    <div class="mt-2 p-2 bg-green-50 rounded-lg border border-green-200 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <p class="text-xs font-medium text-green-800">
                                            {{ $atividade['qtd_documentos_os'] ?? 0 }} {{ ($atividade['qtd_documentos_os'] ?? 0) == 1 ? 'documento vinculado' : 'documentos vinculados' }} à OS.
                                        </p>
                                    </div>
                                    @elseif(!empty($atividade['confirmou_sem_documentos']))
                                    <div class="mt-2 p-2 bg-amber-50 rounded-lg border border-amber-200 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-medium text-amber-900">
                                                {{ $atividade['confirmou_sem_documentos_nome'] ?? 'Técnico' }} confirmou que não existem documentos a serem criados.
                                            </p>
                                            @if(!empty($atividade['confirmou_sem_documentos_em']))
                                                <p class="text-[10px] text-amber-700 mt-0.5">
                                                    {{ \Carbon\Carbon::parse($atividade['confirmou_sem_documentos_em'])->format('d/m/Y \à\s H:i') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    @elseif(!empty($atividade['confirmou_documentos']))
                                    {{-- Compatibilidade com atividades finalizadas antes da mudança --}}
                                    <div class="mt-2 p-2 bg-amber-50 rounded-lg border border-amber-200 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-medium text-amber-900">
                                                {{ $atividade['confirmou_documentos_nome'] ?? 'Técnico' }} confirmou que inseriu os documentos no processo.
                                            </p>
                                            @if(!empty($atividade['confirmou_documentos_em']))
                                                <p class="text-[10px] text-amber-700 mt-0.5">
                                                    {{ \Carbon\Carbon::parse($atividade['confirmou_documentos_em'])->format('d/m/Y \à\s H:i') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            {{-- Técnicos Responsáveis (fallback para OSs antigas sem atividades_tecnicos) --}}
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Técnicos Responsáveis</h2>
                    @if($ordemServico->tecnicos()->count() > 0)
                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">
                        {{ $ordemServico->tecnicos()->count() }}
                    </span>
                    @endif
                </div>
                <div class="px-5 py-5">
                    @if($ordemServico->tecnicos()->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($ordemServico->tecnicos() as $tecnico)
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 rounded-lg border border-green-200">
                                    <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-xs">
                                            {{ strtoupper(substr($tecnico->nome, 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-medium text-gray-900">{{ $tecnico->nome }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">Nenhum técnico atribuído</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Ações Vinculadas e Status de Execução --}}
            @php
                $acoesExecutadasIds = $ordemServico->acoes_executadas_ids ?? [];

                if (($ordemServico->status === 'finalizada') && empty($acoesExecutadasIds) && !empty($ordemServico->atividades_tecnicos)) {
                    $acoesExecutadasIds = collect($ordemServico->atividades_tecnicos)
                        ->filter(function ($atividade) {
                            return ($atividade['status'] ?? 'pendente') === 'finalizada'
                                && (($atividade['status_execucao'] ?? 'concluido') !== 'nao_concluido')
                                && !empty($atividade['tipo_acao_id']);
                        })
                        ->pluck('tipo_acao_id')
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values()
                        ->all();
                }
            @endphp
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Ações Vinculadas</h2>
                    @if($ordemServico->tiposAcao()->count() > 0)
                    <div class="flex items-center gap-2">
                        @if($ordemServico->status === 'finalizada' && !empty($acoesExecutadasIds))
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">
                            {{ count($acoesExecutadasIds) }} executadas
                        </span>
                        @endif
                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-semibold rounded">
                            {{ $ordemServico->tiposAcao()->count() }} total
                        </span>
                    </div>
                    @endif
                </div>
                <div class="px-5 py-5">
                    @if($ordemServico->tiposAcao()->count() > 0)
                        <div class="space-y-2">
                            @foreach($ordemServico->tiposAcao() as $tipoAcao)
                                @php
                                    $foiExecutada = $ordemServico->status === 'finalizada' && 
                                                    !empty($acoesExecutadasIds) && 
                                                    in_array($tipoAcao->id, $acoesExecutadasIds);
                                    $naoFoiExecutada = $ordemServico->status === 'finalizada' && 
                                                       (empty($acoesExecutadasIds) || 
                                                        !in_array($tipoAcao->id, $acoesExecutadasIds));
                                @endphp
                                
                                @if($ordemServico->status === 'finalizada')
                                    {{-- OS Finalizada: Mostra status de execução --}}
                                    @if($foiExecutada)
                                        <div class="flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                            <div class="flex-shrink-0 w-5 h-5 bg-green-600 rounded-full flex items-center justify-center">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-green-900">{{ $tipoAcao->descricao }}</span>
                                            <span class="ml-auto text-xs text-green-600 font-semibold">Executada</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                            <div class="flex-shrink-0 w-5 h-5 bg-gray-400 rounded-full flex items-center justify-center">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-gray-600">{{ $tipoAcao->descricao }}</span>
                                            <span class="ml-auto text-xs text-gray-500 font-semibold">Não executada</span>
                                        </div>
                                    @endif
                                @else
                                    {{-- OS Não Finalizada: Mostra apenas as ações --}}
                                    <div class="flex items-center gap-3 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                        <div class="flex-shrink-0 w-5 h-5 bg-purple-600 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-purple-900">{{ $tipoAcao->descricao }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">Nenhuma ação cadastrada</p>
                    @endif
                </div>
            </div>

            {{-- ========================================
                 DOCUMENTOS DA OS
            ======================================== --}}
            @php
                $documentosOs = $ordemServico->documentosDigitais->sortByDesc('created_at');
                $arquivosExternosOs = $ordemServico->arquivosExternos->sortByDesc('created_at');
                $totalDocumentosOs = $documentosOs->count();
                $totalArquivosExternosOs = $arquivosExternosOs->count();
                $totalItensDocumentosOs = $totalDocumentosOs + $totalArquivosExternosOs;
                $atividadesOs = collect($ordemServico->atividades_tecnicos ?? [])->values();
                $resolverNomeAtividadeOs = function ($atividadeIndex) use ($atividadesOs) {
                    if ($atividadeIndex === null || $atividadeIndex === '') {
                        return null;
                    }

                    $atividade = $atividadesOs->get((int) $atividadeIndex);

                    return $atividade['nome_atividade'] ?? null;
                };

                // Processos vinculados para criação de documentos
                $processosVinculadosOs = $ordemServico->getTodosEstabelecimentos()
                    ->map(function ($est) use ($ordemServico) {
                        $processoId = $est->pivot->processo_id ?? null;
                        if (!$processoId && $ordemServico->estabelecimento_id == $est->id) {
                            $processoId = $ordemServico->processo_id;
                        }
                        return $processoId ? (int) $processoId : null;
                    })
                    ->filter()
                    ->unique()
                    ->values();

                if ($processosVinculadosOs->isEmpty() && $ordemServico->processo_id) {
                    $processosVinculadosOs = collect([(int) $ordemServico->processo_id]);
                }

                $temProcessoVinculado = $processosVinculadosOs->isNotEmpty();

                $parametrosCriacaoDocumentoOs = [
                    'os_id' => $ordemServico->id,
                ];

                if ($temProcessoVinculado) {
                    if ($processosVinculadosOs->count() > 1) {
                        $parametrosCriacaoDocumentoOs['processos_ids'] = $processosVinculadosOs->implode(',');
                    } else {
                        $parametrosCriacaoDocumentoOs['processo_id'] = $processosVinculadosOs->first();
                    }
                }

                $linkCriarDocumentoOs = route('admin.documentos.create') . '?' . http_build_query($parametrosCriacaoDocumentoOs);
            @endphp

            {{-- Documento Anexo --}}
            @if($ordemServico->documento_anexo_path)
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Documento Anexo
                    </h2>
                </div>
                <div class="px-5 py-5">
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $ordemServico->documento_anexo_nome }}</p>
                                <p class="text-xs text-gray-500 mt-1">Documento em PDF</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ Storage::url($ordemServico->documento_anexo_path) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Visualizar
                            </a>
                            <a href="{{ Storage::url($ordemServico->documento_anexo_path) }}" 
                               download
                               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Observações --}}
            @if($ordemServico->observacoes)
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Observações</h2>
                </div>
                <div class="px-5 py-5">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $ordemServico->observacoes }}</p>
                </div>
            </div>
            @endif

            {{-- Informações do Cancelamento --}}
            @if($ordemServico->status === 'cancelada' && $ordemServico->motivo_cancelamento)
            <div class="bg-white rounded-lg border border-red-200">
                <div class="px-5 py-4 border-b border-red-100 bg-red-50">
                    <h2 class="text-base font-semibold text-red-900 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Motivo do Cancelamento
                    </h2>
                </div>
                <div class="px-5 py-5 space-y-4">
                    <div>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $ordemServico->motivo_cancelamento }}</p>
                    </div>
                    @if($ordemServico->cancelada_em)
                    <div class="flex items-center gap-2 text-xs text-gray-500 pt-3 border-t border-gray-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Cancelada em {{ $ordemServico->cancelada_em->format('d/m/Y \à\s H:i') }}</span>
                        @if($ordemServico->cancelada_por)
                        <span class="mx-1">•</span>
                        <span>por {{ \App\Models\UsuarioInterno::find($ordemServico->cancelada_por)->nome ?? 'Usuário' }}</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif
            </main>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- Estilos removidos - mapa agora usa Google Maps --}}
@endpush

@push('scripts')
@php
    $estabelecimentosJs = $ordemServico->getTodosEstabelecimentos()
        ->map(function ($est) use ($ordemServico) {
            $processoId = $est->pivot->processo_id ?? null;
            if (!$processoId && $ordemServico->estabelecimento_id == $est->id) {
                $processoId = $ordemServico->processo_id;
            }

            return [
                'id' => (int) $est->id,
                'nome' => $est->nome_fantasia ?? $est->nome_razao_social,
                'cnpj' => $est->cnpj_formatado ?? $est->cnpj ?? $est->cpf_cnpj ?? null,
                'processo_id' => $processoId ? (int) $processoId : null,
            ];
        })
        ->values()
        ->all();
@endphp
<script>
    let estabelecimentoAtual = 0;
    const atividadesTecnicosOs = @json($ordemServico->atividades_tecnicos ?? []);
    const estabelecimentosOs = @json($estabelecimentosJs);

    function exibirEstabelecimento(indice) {
        const cards = document.querySelectorAll('.estabelecimento-slide');
        if (!cards.length) return;

        const total = cards.length;
        if (indice < 0) {
            indice = total - 1;
        } else if (indice >= total) {
            indice = 0;
        }

        cards.forEach((card, idx) => {
            card.style.display = idx === indice ? '' : 'none';
        });

        estabelecimentoAtual = indice;

        const cardAtual = cards[indice];
        const estabelecimentoId = cardAtual ? cardAtual.getAttribute('data-estabelecimento-id') : null;
        const btnPdf = document.getElementById('btnBaixarPdfOs');
        if (btnPdf) {
            const baseUrl = btnPdf.getAttribute('data-base-url') || btnPdf.href;
            btnPdf.href = estabelecimentoId ? `${baseUrl}?estabelecimento_id=${estabelecimentoId}` : baseUrl;
        }
    }

    function mostrarProximoEstabelecimento() {
        exibirEstabelecimento(estabelecimentoAtual + 1);
    }

    function mostrarEstabelecimentoAnterior() {
        exibirEstabelecimento(estabelecimentoAtual - 1);
    }

    // ========================================
    // Dropdown PDF - Baixar de Todos
    // ========================================
    function toggleDropdownPdf() {
        const menu = document.getElementById('dropdownPdfMenu');
        if (menu) menu.classList.toggle('hidden');
    }

    // Fechar dropdown ao clicar fora
    document.addEventListener('click', function(e) {
        const container = document.getElementById('dropdownPdfContainer');
        const menu = document.getElementById('dropdownPdfMenu');
        if (container && menu && !container.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        exibirEstabelecimento(0);
    });

    // ========================================
    // Funções para Cancelar OS
    // ========================================
    
    // Função para abrir modal de cancelar OS
    function abrirModalCancelarOS() {
        const modal = document.getElementById('modalCancelarOS');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            console.error('Modal de cancelamento não encontrado');
            alert('Erro ao abrir modal. Recarregue a página e tente novamente.');
        }
    }

    // Função para fechar modal de cancelar OS
    function fecharModalCancelarOS() {
        const modal = document.getElementById('modalCancelarOS');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            // Limpa o textarea ao fechar
            const textarea = document.getElementById('motivo_cancelamento');
            if (textarea) {
                textarea.value = '';
                validarMotivoCancelamento();
            }
        }
    }

    // Função para validar motivo de cancelamento
    function validarMotivoCancelamento() {
        const textarea = document.getElementById('motivo_cancelamento');
        const btnConfirmar = document.getElementById('btnConfirmarCancelamento');
        const countElement = document.getElementById('motivoCount');
        const helpElement = document.getElementById('motivoHelp');
        
        if (!textarea || !btnConfirmar || !countElement || !helpElement) return;
        
        const length = textarea.value.length;
        const minLength = 20;
        
        // Atualiza contador
        countElement.textContent = `${length} / ${minLength}`;
        
        // Atualiza cor do contador
        if (length >= minLength) {
            countElement.classList.remove('text-gray-400', 'text-red-500');
            countElement.classList.add('text-green-600');
            helpElement.classList.remove('text-gray-500', 'text-red-500');
            helpElement.classList.add('text-green-600');
            helpElement.textContent = '✓ Mínimo atingido';
            btnConfirmar.disabled = false;
        } else {
            countElement.classList.remove('text-gray-400', 'text-green-600');
            countElement.classList.add('text-red-500');
            helpElement.classList.remove('text-gray-500', 'text-green-600');
            helpElement.classList.add('text-red-500');
            helpElement.textContent = `Faltam ${minLength - length} caracteres`;
            btnConfirmar.disabled = true;
        }
    }

    // ========================================
    // Funções para Finalizar Atividade Individual
    // ========================================
    let payloadFinalizacaoPendente = null;
    
    // Função para abrir modal de finalizar atividade
    function abrirModalFinalizarAtividade(index, nomeAtividade) {
        document.getElementById('atividadeIndex').value = index;
        document.getElementById('nomeAtividadeModal').textContent = nomeAtividade;
        document.getElementById('observacoes_atividade').value = '';
        
        // Limpa seleção de status
        const radios = document.querySelectorAll('input[name="status_execucao"]');
        radios.forEach(radio => radio.checked = false);
        
        // Limpa seleção de estabelecimento se existir
        const selectEstab = document.getElementById('estabelecimento_id_atividade');
        if (selectEstab) selectEstab.value = '';

        const atividade = atividadesTecnicosOs[parseInt(index)];
        const estabelecimentosAtividade = obterEstabelecimentosDaAtividade(atividade);
        const isMulti = estabelecimentosAtividade.length > 1;

        const statusWrap = document.getElementById('status-execucao-wrap');
        const observacoesWrap = document.getElementById('observacoes-wrap');

        if (statusWrap) statusWrap.classList.toggle('hidden', isMulti);
        if (observacoesWrap) observacoesWrap.classList.toggle('hidden', isMulti);

        renderizarExecucaoPorEstabelecimento(index);

        // Reseta checkbox e erro de confirmação quando sem documentos
        const checkSemDoc = document.getElementById('checkSemDocumentos');
        if (checkSemDoc) {
            checkSemDoc.checked = false;
            checkSemDoc.closest('label').classList.remove('ring-2', 'ring-red-400');
            const erroCheck = document.getElementById('erroCheckDocumentos');
            if (erroCheck) erroCheck.classList.add('hidden');
        }
        
        document.getElementById('modalFinalizarAtividade').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function obterEstabelecimentosDaAtividade(atividade) {
        if (!atividade) return [];

        const estAtividadeId = atividade.estabelecimento_id ? parseInt(atividade.estabelecimento_id) : null;
        if (estAtividadeId) {
            return estabelecimentosOs.filter(est => parseInt(est.id) === estAtividadeId);
        }

        return estabelecimentosOs;
    }

    function renderizarExecucaoPorEstabelecimento(index) {
        const wrap = document.getElementById('execucao-estabelecimentos-wrap');
        const container = document.getElementById('execucao-estabelecimentos-container');
        if (!wrap || !container) return;

        const atividade = atividadesTecnicosOs[parseInt(index)];
        const estabelecimentos = obterEstabelecimentosDaAtividade(atividade);

        container.innerHTML = '';

        if (!estabelecimentos || estabelecimentos.length <= 1) {
            wrap.classList.add('hidden');
            return;
        }

        wrap.classList.remove('hidden');

        estabelecimentos.forEach((est) => {
            const bloco = document.createElement('div');
            bloco.className = 'border border-gray-200 rounded-lg p-3 bg-gray-50';
            bloco.innerHTML = `
                <div class="mb-2">
                    <p class="text-sm font-semibold text-gray-900">${est.nome || 'Estabelecimento'}</p>
                    ${est.cnpj ? `<p class="text-xs text-gray-500">${est.cnpj}</p>` : ''}
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
                    <label class="flex items-center gap-2 p-2 bg-white border border-green-200 rounded cursor-pointer">
                        <input type="radio" name="execucao_estab_${est.id}" value="sim" class="execucao-estab-radio" data-est-id="${est.id}">
                        <span class="text-sm text-green-700 font-medium">Executada</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-white border border-red-200 rounded cursor-pointer">
                        <input type="radio" name="execucao_estab_${est.id}" value="nao" class="execucao-estab-radio" data-est-id="${est.id}">
                        <span class="text-sm text-red-700 font-medium">Não executada</span>
                    </label>
                </div>
                <div class="hidden" id="justificativa-wrap-${est.id}">
                    <textarea id="justificativa-estab-${est.id}" rows="2" class="w-full px-3 py-2 text-sm border border-red-300 rounded focus:ring-2 focus:ring-red-500" placeholder="Justifique por que não foi executada neste estabelecimento..."></textarea>
                    <p class="text-xs text-red-600 mt-1">Justificativa obrigatória (mínimo 10 caracteres).</p>
                </div>
            `;
            container.appendChild(bloco);
        });

        container.querySelectorAll('.execucao-estab-radio').forEach((radio) => {
            radio.addEventListener('change', function() {
                const estId = this.dataset.estId;
                const justificativaWrap = document.getElementById(`justificativa-wrap-${estId}`);
                if (!justificativaWrap) return;
                justificativaWrap.classList.toggle('hidden', this.value !== 'nao');
                if (this.value !== 'nao') {
                    const textarea = document.getElementById(`justificativa-estab-${estId}`);
                    if (textarea) textarea.value = '';
                }
            });
        });
    }

    // Função para fechar modal de finalizar atividade
    function fecharModalFinalizarAtividade() {
        document.getElementById('modalFinalizarAtividade').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Função para confirmar finalização da atividade
    async function confirmarFinalizarAtividade() {
        const index = document.getElementById('atividadeIndex').value;
        const observacoes = document.getElementById('observacoes_atividade').value;
        const btnFinalizar = document.getElementById('btnFinalizarAtividade');
        const atividade = atividadesTecnicosOs[parseInt(index)];
        const estabelecimentosAtividade = obterEstabelecimentosDaAtividade(atividade);
        const isMulti = estabelecimentosAtividade.length > 1;
        
        // Para estabelecimento único mantém validações antigas
        let statusSelecionado = document.querySelector('input[name="status_execucao"]:checked');
        if (!isMulti) {
            if (!statusSelecionado) {
                alert('⚠️ Selecione o status da execução.');
                return;
            }
            if (!observacoes || observacoes.trim().length < 10) {
                alert('⚠️ Informe as observações (mínimo 10 caracteres).');
                return;
            }
        }

        let execucaoEstabelecimentosPayload = [];
        if (estabelecimentosAtividade.length > 1) {
            for (const est of estabelecimentosAtividade) {
                const selecionado = document.querySelector(`input[name="execucao_estab_${est.id}"]:checked`);
                if (!selecionado) {
                    alert(`⚠️ Informe se a atividade foi executada no estabelecimento ${est.nome}.`);
                    return;
                }

                const executada = selecionado.value === 'sim';
                let justificativa = null;

                if (!executada) {
                    const txt = document.getElementById(`justificativa-estab-${est.id}`);
                    justificativa = (txt?.value || '').trim();
                    if (justificativa.length < 10) {
                        alert(`⚠️ Informe justificativa (mínimo 10 caracteres) para ${est.nome}.`);
                        txt?.focus();
                        return;
                    }
                }

                execucaoEstabelecimentosPayload.push({
                    estabelecimento_id: est.id,
                    executada: executada,
                    justificativa: justificativa,
                });
            }
        }

        // Valida checkbox "sem documentos" (só existe quando a OS NÃO tem documentos vinculados)
        const checkSemDocumentos = document.getElementById('checkSemDocumentos');
        if (checkSemDocumentos && !checkSemDocumentos.checked) {
            const erroCheck = document.getElementById('erroCheckDocumentos');
            if (erroCheck) erroCheck.classList.remove('hidden');
            checkSemDocumentos.closest('label').classList.add('ring-2', 'ring-red-400');
            checkSemDocumentos.focus();
            return;
        }
        
        // Pega estabelecimento se existir
        const selectEstab = document.getElementById('estabelecimento_id_atividade');
        const estabelecimentoId = selectEstab ? selectEstab.value : null;
        
        // Desabilita botão e mostra loading
        btnFinalizar.disabled = true;
        btnFinalizar.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Finalizando...';

        const temDocumentosOs = {{ $totalItensDocumentosOs > 0 ? 'true' : 'false' }};
        const payloadFinalizacao = {
            atividade_index: index,
            status_execucao: isMulti ? null : statusSelecionado.value,
            observacoes: isMulti ? null : observacoes,
            estabelecimento_id: estabelecimentoId,
            confirmou_sem_documentos: checkSemDocumentos?.checked || false,
            tem_documentos_os: temDocumentosOs,
            execucao_estabelecimentos: execucaoEstabelecimentosPayload
        };

        try {
            const response = await fetch('{{ route("admin.ordens-servico.finalizar-atividade", $ordemServico) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payloadFinalizacao)
            });

            const data = await response.json();

            if (response.ok) {
                // Sucesso
                fecharModalFinalizarAtividade();

                if (data.os_finalizada) {
                    abrirModalOsFinalizada();
                } else {
                    alert('✅ ' + data.message);
                    window.location.reload();
                }
            } else if (data.survey_required) {
                payloadFinalizacaoPendente = payloadFinalizacao;
                const modalPesquisa = document.getElementById('modalPesquisaInterna');

                if (modalPesquisa) {
                    fecharModalFinalizarAtividade();
                    abrirModalPesquisaInterna();
                    alert('⚠️ Para finalizar a atividade, responda a pesquisa de satisfação obrigatória.');
                } else {
                    alert('❌ A pesquisa obrigatória não foi carregada na tela. Recarregue a página e tente novamente.');
                }

                resetarBotaoFinalizarAtividade();
            } else {
                // Erro
                alert('❌ ' + (data.message || 'Erro ao finalizar atividade'));
                resetarBotaoFinalizarAtividade();
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('❌ Erro ao finalizar atividade. Tente novamente.');
            resetarBotaoFinalizarAtividade();
        }
    }

    // Função auxiliar para resetar o botão
    function resetarBotaoFinalizarAtividade() {
        const btnFinalizar = document.getElementById('btnFinalizarAtividade');
        btnFinalizar.disabled = false;
        btnFinalizar.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Finalizar';
    }

    // Aplica textos prontos nas observacoes
    function aplicarObservacaoPreset(texto) {
        const campo = document.getElementById('observacoes_atividade');
        if (!campo) return;
        const valorAtual = campo.value.trim();
        campo.value = valorAtual ? (valorAtual + ' ' + texto) : texto;
        campo.focus();
    }

    // ========================================
    // Função para Reiniciar Atividade Individual (Gestores)
    // ========================================
    async function reiniciarAtividade(index, nomeAtividade) {
        if (!confirm('Tem certeza que deseja reiniciar a atividade "' + nomeAtividade + '"?\n\nEla voltará ao status "Pendente" e o técnico precisará finalizá-la novamente.')) {
            return;
        }

        try {
            const response = await fetch('{{ route("admin.ordens-servico.reiniciar-atividade", $ordemServico) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    atividade_index: index
                })
            });

            const data = await response.json();

            if (response.ok) {
                alert('✅ ' + data.message);
                window.location.reload();
            } else {
                alert('❌ ' + (data.message || 'Erro ao reiniciar atividade'));
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('❌ Erro ao reiniciar atividade. Tente novamente.');
        }
    }

    // Modal de OS finalizada
    function abrirModalOsFinalizada() {
        const modal = document.getElementById('modalOsFinalizada');
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function fecharModalOsFinalizada() {
        const modal = document.getElementById('modalOsFinalizada');
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        window.location.reload();
    }
</script>
@endpush

@push('modals')
{{-- Modal Cancelar OS --}}
<div id="modalCancelarOS" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <form method="POST" action="{{ route('admin.ordens-servico.cancelar', $ordemServico) }}" id="formCancelarOS">
            @csrf
            {{-- Header --}}
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Cancelar Ordem de Serviço
                    </h3>
                    <button type="button" onclick="fecharModalCancelarOS()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 space-y-5">
                {{-- Aviso --}}
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-800">
                        <strong>Atenção:</strong> Esta ação cancelará a ordem de serviço.
                    </p>
                </div>

                {{-- Motivo do Cancelamento --}}
                <div>
                    <label for="motivo_cancelamento" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo do Cancelamento <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        id="motivo_cancelamento" 
                        name="motivo_cancelamento" 
                        rows="4" 
                        required
                        minlength="20"
                        oninput="validarMotivoCancelamento()"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none transition-all"
                        placeholder="Descreva o motivo do cancelamento..."></textarea>
                    <div class="flex items-center justify-between mt-1.5">
                        <p id="motivoHelp" class="text-xs text-gray-500">Mínimo de 20 caracteres</p>
                        <p id="motivoCount" class="text-xs font-medium text-gray-400">0 / 20</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl">
                <button type="button" 
                        onclick="fecharModalCancelarOS()"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                    Voltar
                </button>
                <button type="submit" 
                        id="btnConfirmarCancelamento"
                        disabled
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-sm hover:shadow transition-all disabled:bg-gray-300 disabled:cursor-not-allowed disabled:hover:shadow-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Confirmar Cancelamento
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Finalizar Atividade Individual --}}
<div id="modalFinalizarAtividade" class="hidden fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[85vh] overflow-y-auto">
        {{-- Header Clean --}}
        <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-100">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-10 h-10 bg-green-50 rounded-full mb-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Finalizar Atividade</h3>
                <p id="nomeAtividadeModal" class="text-xs text-gray-500 mt-0.5"></p>
            </div>
            <button type="button" onclick="fecharModalFinalizarAtividade()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Form Clean --}}
        <form id="formFinalizarAtividade" class="px-6 py-5 space-y-4">
            <input type="hidden" id="atividadeIndex" name="atividade_index" value="">
            
            {{-- Status de Execução --}}
            <div id="status-execucao-wrap">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Status da execução <span class="text-red-500">*</span>
                </label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-green-300 hover:bg-green-50/50 transition-all group">
                        <input type="radio" name="status_execucao" value="concluido" required class="w-4 h-4 text-green-600 focus:ring-green-500">
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">Concluído com sucesso</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-yellow-300 hover:bg-yellow-50/50 transition-all group">
                        <input type="radio" name="status_execucao" value="parcial" required class="w-4 h-4 text-yellow-600 focus:ring-yellow-500">
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">Concluído parcialmente</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-red-300 hover:bg-red-50/50 transition-all group">
                        <input type="radio" name="status_execucao" value="nao_concluido" required class="w-4 h-4 text-red-600 focus:ring-red-500">
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">Não concluído</span>
                    </label>
                </div>
            </div>

            {{-- Execução por estabelecimento (somente quando atividade é geral com múltiplos estabelecimentos) --}}
            <div id="execucao-estabelecimentos-wrap" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Execução por estabelecimento <span class="text-red-500">*</span>
                </label>
                <p class="text-xs text-gray-500 mb-2">
                    Marque em quais estabelecimentos esta atividade foi executada. Para os não executados, informe justificativa.
                </p>
                <div id="execucao-estabelecimentos-container" class="space-y-2"></div>
            </div>

            {{-- Observações --}}
            <div id="observacoes-wrap">
                <label for="observacoes_atividade" class="block text-sm font-medium text-gray-700 mb-2">
                    Observações <span class="text-red-500">*</span>
                </label>
                <div class="flex flex-wrap gap-2 mb-2">
                    <button type="button" onclick="aplicarObservacaoPreset('Atividade concluida conforme previsto.')"
                            class="px-2.5 py-1 text-[11px] text-gray-700 bg-gray-100 rounded-full hover:bg-gray-200 transition">
                        Concluida conforme previsto
                    </button>
                    <button type="button" onclick="aplicarObservacaoPreset('Concluida com orientacoes prestadas ao responsavel.')"
                            class="px-2.5 py-1 text-[11px] text-gray-700 bg-gray-100 rounded-full hover:bg-gray-200 transition">
                        Concluida com orientacoes
                    </button>
                    <button type="button" onclick="aplicarObservacaoPreset('Atividade parcialmente executada. Pendencias registradas.')"
                            class="px-2.5 py-1 text-[11px] text-gray-700 bg-gray-100 rounded-full hover:bg-gray-200 transition">
                        Parcial com pendencias
                    </button>
                    <button type="button" onclick="aplicarObservacaoPreset('Nao foi possivel concluir. Reagendar necessario.')"
                            class="px-2.5 py-1 text-[11px] text-gray-700 bg-gray-100 rounded-full hover:bg-gray-200 transition">
                        Nao foi possivel concluir
                    </button>
                </div>
                <textarea 
                    id="observacoes_atividade" 
                    name="observacoes" 
                    rows="3" 
                    required
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none transition-all"
                    placeholder="Descreva como foi a execução desta atividade..."></textarea>
                <p class="mt-1.5 text-xs text-gray-400">Mínimo de 10 caracteres</p>
            </div>

            {{-- Documentos da OS --}}
            <div id="avisoDocumentosProcesso" class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h4 class="text-sm font-semibold text-gray-800">Documentos da OS</h4>
                    @if($totalItensDocumentosOs > 0)
                    <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-green-100 text-green-700 rounded">{{ $totalItensDocumentosOs }}</span>
                    @endif
                </div>

                <div class="px-4 py-3 space-y-3">
                    @if($totalItensDocumentosOs > 0)
                    {{-- Lista resumida de documentos --}}
                    <div class="space-y-1.5 max-h-32 overflow-y-auto">
                        @foreach($documentosOs as $docModal)
                        <div class="flex items-center justify-between px-2.5 py-1.5 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="w-3.5 h-3.5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-xs font-medium text-green-800 truncate">{{ $docModal->nome ?? $docModal->tipoDocumento->nome ?? 'Documento' }}</span>
                                <span class="text-[10px] text-green-600">#{{ $docModal->numero_documento }}</span>
                            </div>
                            @php
                                $statusBadgeModal = match($docModal->status) {
                                    'rascunho' => '<span class="text-[9px] px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded">Rascunho</span>',
                                    'aguardando_assinatura' => '<span class="text-[9px] px-1.5 py-0.5 bg-yellow-100 text-yellow-700 rounded">Ag. assinatura</span>',
                                    'assinado' => '<span class="text-[9px] px-1.5 py-0.5 bg-green-100 text-green-700 rounded">Assinado</span>',
                                    default => '<span class="text-[9px] px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded">' . ucfirst($docModal->status) . '</span>'
                                };
                            @endphp
                            {!! $statusBadgeModal !!}
                        </div>
                        @endforeach
                        @foreach($arquivosExternosOs as $arquivoOsModal)
                        <div class="flex items-center justify-between px-2.5 py-1.5 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="w-3.5 h-3.5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <span class="text-xs font-medium text-blue-800 truncate">{{ $arquivoOsModal->nome_original }}</span>
                                <span class="text-[10px] text-blue-600">Arquivo externo</span>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded">PDF</span>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-green-700 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Documentos vinculados à OS estão disponíveis para esta finalização.
                    </p>
                    @else
                    {{-- Sem documentos — precisa de confirmação --}}
                    <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-lg">
                        <p class="text-xs text-amber-800 mb-2">
                            <strong>⚠️</strong> Nenhum documento foi criado para esta OS.
                            Você pode criar um documento antes de finalizar ou confirmar que não há documentos a serem criados.
                        </p>
                        <a href="{{ $linkCriarDocumentoOs }}"
                           target="_blank"
                           class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-300 rounded-lg hover:bg-indigo-100 transition-all mb-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Criar Documento Digital
                        </a>
                    </div>
                    <label class="flex items-start gap-2 p-2.5 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-100/60 transition-colors">
                        <input type="checkbox" id="checkSemDocumentos" 
                               class="mt-0.5 h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                        <span class="text-xs font-medium text-gray-700 leading-relaxed">
                            Confirmo que não existem documentos a serem criados para esta OS.
                        </span>
                    </label>
                    <p id="erroCheckDocumentos" class="hidden text-xs text-red-600 font-medium -mt-2">
                        ⚠️ Crie um documento ou confirme que não existem documentos antes de finalizar.
                    </p>
                    @endif
                </div>
            </div>

            {{-- Aviso geral --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-2.5">
                <p class="text-xs text-blue-800">
                    <strong>💡</strong> Ao finalizar sua atividade, ela será marcada como concluída. 
                    A OS será automaticamente finalizada quando todas as atividades forem concluídas.
                </p>
            </div>

            {{-- Botões de ação --}}
            <div class="flex items-center justify-between gap-3 pt-2 border-t border-gray-100">
                <button type="button" 
                        onclick="fecharModalFinalizarAtividade()"
                        class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                    Cancelar
                </button>
                <button type="button" 
                        id="btnFinalizarAtividade"
                        onclick="confirmarFinalizarAtividade()"
                        class="inline-flex items-center justify-center gap-2 px-8 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-sm hover:shadow transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Finalizar Atividade
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal OS Finalizada --}}
<div id="modalOsFinalizada" class="hidden fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full">
        <div class="px-6 py-5 border-b border-gray-100 text-center">
            <div class="inline-flex items-center justify-center w-10 h-10 bg-green-50 rounded-full mb-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Ordem de Servico encerrada</h3>
            <p class="text-sm text-gray-600 mt-1">
                Atividade finalizada. A ordem de servico foi encerrada.
            </p>
        </div>
        <div class="px-6 py-4 flex items-center justify-center">
            <button type="button" onclick="fecharModalOsFinalizada()"
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                OK
            </button>
        </div>
    </div>
</div>

{{-- Modal Pesquisa de Satisfação Interna --}}
@if(isset($pesquisaInterna) && $pesquisaInterna && $pesquisaInterna->perguntas->count() > 0)
<div id="modalPesquisaInterna" class="hidden fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center justify-center w-10 h-10 bg-indigo-50 rounded-full">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $pesquisaInterna->titulo }}</h3>
                    @if($pesquisaInterna->descricao)
                        <p class="text-sm text-gray-500">{{ $pesquisaInterna->descricao }}</p>
                    @endif
                </div>
            </div>
        </div>

        <form id="formPesquisaInterna" class="px-6 py-4 space-y-5">
            <input type="hidden" name="pesquisa_id" value="{{ $pesquisaInterna->id }}">
            <input type="hidden" name="ordem_servico_id" value="{{ $ordemServico->id }}">

            @foreach($pesquisaInterna->perguntas as $pi => $pergunta)
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">
                    {{ $pi + 1 }}. {{ $pergunta->texto }}
                    @if($pergunta->obrigatoria)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @if($pergunta->tipo === 'escala_1_5')
                <div class="flex items-center gap-2 flex-wrap">
                    @php
                        $labels = [1 => 'Muito ruim', 2 => 'Ruim', 3 => 'Regular', 4 => 'Bom', 5 => 'Ótimo'];
                        $cores = [1 => '#ef4444', 2 => '#f97316', 3 => '#eab308', 4 => '#3b82f6', 5 => '#22c55e'];
                    @endphp
                    @foreach($labels as $nota => $label)
                    <label class="cursor-pointer text-center" onclick="selecionarNotaInterna(this, {{ $pergunta->id }}, {{ $nota }}, '{{ $cores[$nota] }}')">
                        <input type="radio" name="respostas[{{ $pergunta->id }}]" value="{{ $nota }}" class="hidden pergunta-interna-{{ $pergunta->id }}">
                        <div class="w-10 h-10 rounded-full border-2 border-gray-300 flex items-center justify-center text-sm font-bold text-gray-500 transition-all nota-circulo-interna">
                            {{ $nota }}
                        </div>
                        <span class="text-[9px] text-gray-500 mt-0.5 block">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>

                @elseif($pergunta->tipo === 'multipla_escolha')
                <div class="space-y-1.5">
                    @foreach($pergunta->opcoes as $opcao)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="respostas[{{ $pergunta->id }}]" value="{{ $opcao->texto }}" class="h-4 w-4 text-indigo-600">
                        <span class="text-sm text-gray-700">{{ $opcao->texto }}</span>
                    </label>
                    @endforeach
                </div>

                @elseif($pergunta->tipo === 'texto_livre')
                <textarea name="respostas[{{ $pergunta->id }}]" rows="2" 
                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="Digite sua resposta..."></textarea>
                @endif
            </div>
            @endforeach

            <div class="flex items-center justify-between gap-3 pt-3 border-t border-gray-100">
                <button type="button" onclick="fecharModalPesquisaInterna()"
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                    Cancelar
                </button>
                <button type="button" onclick="enviarPesquisaInterna()"
                        id="btnEnviarPesquisaInterna"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enviar Pesquisa
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalPesquisaInterna() {
        document.getElementById('modalPesquisaInterna').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function fecharModalPesquisaInterna() {
        document.getElementById('modalPesquisaInterna').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function selecionarNotaInterna(el, perguntaId, nota, cor) {
        // Reseta todos da mesma pergunta
        el.closest('.flex').querySelectorAll('.nota-circulo-interna').forEach(c => {
            c.style.backgroundColor = '';
            c.style.borderColor = '#d1d5db';
            c.style.color = '#6b7280';
        });
        // Ativa o selecionado
        const circulo = el.querySelector('.nota-circulo-interna');
        circulo.style.backgroundColor = cor;
        circulo.style.borderColor = cor;
        circulo.style.color = 'white';
        // Marca o radio
        el.querySelector('input[type=radio]').checked = true;
    }

    async function enviarPesquisaInterna() {
        const form = document.getElementById('formPesquisaInterna');
        const formData = new FormData(form);
        const btn = document.getElementById('btnEnviarPesquisaInterna');

        // Validar perguntas obrigatórias
        @foreach($pesquisaInterna->perguntas as $pergunta)
            @if($pergunta->obrigatoria)
            {
                const val = formData.get('respostas[{{ $pergunta->id }}]');
                if (!val || val.trim() === '') {
                    alert('⚠️ Por favor, responda a pergunta: "{{ addslashes($pergunta->texto) }}"');
                    return;
                }
            }
            @endif
        @endforeach

        // Montar payload
        const respostas = {};
        for (const [key, value] of formData.entries()) {
            const match = key.match(/respostas\[(\d+)\]/);
            if (match) {
                respostas[match[1]] = value;
            }
        }

        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Enviando...';

        try {
            const response = await fetch('{{ route("pesquisa.responder.interno") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    pesquisa_id: formData.get('pesquisa_id'),
                    ordem_servico_id: formData.get('ordem_servico_id'),
                    respostas: respostas,
                })
            });

            const data = await response.json();

            if (response.ok) {
                fecharModalPesquisaInterna();
                alert('✅ Pesquisa enviada com sucesso!');

                if (payloadFinalizacaoPendente) {
                    const payload = payloadFinalizacaoPendente;
                    payloadFinalizacaoPendente = null;

                    const responseFinalizar = await fetch('{{ route("admin.ordens-servico.finalizar-atividade", $ordemServico) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(payload)
                    });

                    const dataFinalizar = await responseFinalizar.json();

                    if (responseFinalizar.ok) {
                        if (dataFinalizar.os_finalizada) {
                            abrirModalOsFinalizada();
                        } else {
                            alert('✅ ' + dataFinalizar.message);
                            window.location.reload();
                        }
                    } else {
                        alert('❌ ' + (dataFinalizar.message || 'Erro ao finalizar atividade após responder a pesquisa.'));
                        window.location.reload();
                    }
                } else {
                    window.location.reload();
                }
            } else {
                alert('❌ ' + (data.message || 'Erro ao enviar pesquisa.'));
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Enviar Pesquisa';
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('❌ Erro ao enviar pesquisa. Tente novamente.');
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Enviar Pesquisa';
        }
    }
</script>
@endif
@endpush
