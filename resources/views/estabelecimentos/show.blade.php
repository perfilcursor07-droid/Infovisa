@extends('layouts.admin')

@section('title', 'Detalhes do Estabelecimento')
@section('page-title', 'Detalhes do Estabelecimento')

@section('content')
<div class="space-y-6">
    {{-- Header com botões --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.index') }}" 
               class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $estabelecimento->nome_fantasia }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $estabelecimento->documento_formatado }}</p>
            </div>
        </div>

        {{-- Badge de Status --}}
        <div class="flex items-center gap-2">
            @php
                $statusConfig = [
                    'pendente' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Pendente'],
                    'aprovado' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Aprovado'],
                    'rejeitado' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Rejeitado'],
                    'arquivado' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Arquivado'],
                ];
                $config = $statusConfig[$estabelecimento->status] ?? $statusConfig['pendente'];
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                {{ $config['label'] }}
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $estabelecimento->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $estabelecimento->ativo ? 'Ativo' : 'Inativo' }}
            </span>
        </div>
    </div>

    {{-- Alerta de Status Pendente/Rejeitado --}}
    @if($estabelecimento->status === 'pendente')
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-yellow-800">
                    Este estabelecimento está aguardando aprovação
                </p>
                <p class="mt-1 text-sm text-yellow-700">
                    Analise os dados e aprove ou rejeite o cadastro.
                </p>
            </div>
        </div>
    </div>
    @elseif($estabelecimento->status === 'rejeitado')
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-red-800">
                    Este estabelecimento foi rejeitado
                </p>
                @if($estabelecimento->motivo_rejeicao)
                <p class="mt-1 text-sm text-red-700">
                    <strong>Motivo:</strong> {{ $estabelecimento->motivo_rejeicao }}
                </p>
                @endif
                @if($estabelecimento->aprovadoPor)
                <p class="mt-1 text-xs text-red-600">
                    Rejeitado por {{ $estabelecimento->aprovadoPor->nome }} em {{ $estabelecimento->aprovado_em->format('d/m/Y H:i') }}
                </p>
                @endif
            </div>
        </div>
    </div>
    @elseif($estabelecimento->status === 'aprovado' && $estabelecimento->aprovadoPor)
    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">
                    Aprovado por <strong>{{ $estabelecimento->aprovadoPor->nome }}</strong> em {{ $estabelecimento->aprovado_em->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Layout de 2 Colunas --}}
    <style>
        @media (max-width: 768px) {
            .estabelecimento-container {
                flex-direction: column !important;
            }
            .estabelecimento-menu {
                width: 100% !important;
                min-width: unset !important;
            }
            .estabelecimento-menu-sticky {
                position: relative !important;
                top: 0 !important;
            }
        }
    </style>
    <div class="estabelecimento-container" style="display: flex; gap: 1.5rem;">
        {{-- Coluna Esquerda - Menu de Ações --}}
        <div class="estabelecimento-menu space-y-4" style="width: 280px; min-width: 280px;">
            <div class="estabelecimento-menu-sticky bg-white rounded-lg shadow-sm border border-gray-200 p-4 sticky top-20">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Ações</h3>
                <div class="space-y-2">
                    @if($estabelecimento->ativo)
                    {{-- Editar --}}
                    <a href="{{ route('admin.estabelecimentos.edit', $estabelecimento->id) }}" 
                       class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar Dados
                    </a>

                    {{-- Responsáveis (apenas para pessoa jurídica) --}}
                    @if($estabelecimento->tipo_pessoa === 'juridica')
                    <a href="{{ route('admin.estabelecimentos.responsaveis.index', $estabelecimento->id) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Responsáveis
                    </a>
                    @endif

                    {{-- Atividades --}}
                    <a href="{{ route('admin.estabelecimentos.atividades.edit', $estabelecimento->id) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Atividades
                    </a>

                    {{-- Processos --}}
                    <a href="{{ route('admin.estabelecimentos.processos.index', $estabelecimento->id) }}" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Processos
                    </a>

                    {{-- Documentos --}}
                    <button class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Documentos
                    </button>

                    {{-- Histórico --}}
                    <a href="{{ route('admin.estabelecimentos.historico', $estabelecimento->id) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Histórico
                    </a>

                    {{-- Usuários Vinculados --}}
                    <a href="{{ route('admin.estabelecimentos.usuarios.index', $estabelecimento->id) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Usuários Vinculados
                    </a>

                    {{-- Equipamentos de Imagem (apenas para estabelecimentos que exigem) --}}
                    @if(isset($exigeEquipamentosRadiacao) && $exigeEquipamentosRadiacao)
                    <a href="{{ route('admin.estabelecimentos.equipamentos-radiacao.index', $estabelecimento->id) }}"
                       class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-orange-50 hover:text-orange-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <span class="flex-1 text-left">Equipamentos de Imagem</span>
                        <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium {{ $totalEquipamentosRadiacao > 0 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700' }} rounded-full">
                            {{ $totalEquipamentosRadiacao }}
                        </span>
                    </a>
                    @endif

                    <hr class="my-4">

                    {{-- Ações de Aprovação --}}
                    @if($estabelecimento->status === 'pendente')
                        <button onclick="document.getElementById('modal-aprovar').classList.remove('hidden')"
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Aprovar
                        </button>

                        <button onclick="document.getElementById('modal-rejeitar').classList.remove('hidden')"
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Rejeitar
                        </button>
                    @elseif($estabelecimento->status === 'rejeitado')
                        <button onclick="document.getElementById('modal-reiniciar').classList.remove('hidden')"
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reiniciar
                        </button>
                    @endif

                    @if(auth('interno')->user()->nivel_acesso->isAdmin())
                    <hr class="my-4">

                    {{-- Voltar para Pendente (apenas para aprovados sem processos) --}}
                    @if($estabelecimento->status === 'aprovado' && $estabelecimento->processos()->count() === 0)
                        <button onclick="document.getElementById('modal-voltar-pendente').classList.remove('hidden')"
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.333 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"/>
                            </svg>
                            Voltar para Pendente
                        </button>
                    @endif

                    {{-- Alterar Competência (apenas para administradores) --}}
                    <button onclick="document.getElementById('modal-alterar-competencia').classList.remove('hidden')"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-white bg-{{ $estabelecimento->isCompetenciaEstadual() ? 'purple' : 'blue' }}-600 hover:bg-{{ $estabelecimento->isCompetenciaEstadual() ? 'purple' : 'blue' }}-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Alterar Competência
                    </button>

                    {{-- Desativar --}}
                    <button onclick="document.getElementById('modal-desativar').classList.remove('hidden')"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Desativar
                    </button>
                    @endif

                    @else
                    {{-- Estabelecimento Desativado - Mostrar apenas Histórico, Ativar e Excluir --}}
                    {{-- Histórico --}}
                    <a href="{{ route('admin.estabelecimentos.historico', $estabelecimento->id) }}" 
                       class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Histórico
                    </a>

                    @if(auth('interno')->user()->nivel_acesso->isAdmin())
                    <hr class="my-4">

                    <form action="{{ route('admin.estabelecimentos.ativar', $estabelecimento->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Tem certeza que deseja reativar este estabelecimento?')"
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition-colors group">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Ativar
                        </button>
                    </form>
                    @endif
                    @endif

                    {{-- Excluir (apenas admin) --}}
                    @if(auth('interno')->user()->nivel_acesso->isAdmin())
                    <form action="{{ route('admin.estabelecimentos.destroy', $estabelecimento->id) }}" 
                          method="POST" 
                          onsubmit="return confirm('⚠️ ATENÇÃO!\n\nTem certeza que deseja EXCLUIR este estabelecimento?\n\nEsta ação é IRREVERSÍVEL e irá:\n- Remover todos os dados do estabelecimento\n- Desvincular responsáveis (sem excluí-los)\n- Desvincular usuários vinculados (sem excluí-los)\n- Remover histórico e equipamentos\n\nNão será possível excluir se houver processos vinculados.\n\nDeseja continuar?');"
                          class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors group">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Excluir Estabelecimento
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Coluna Direita - Dados do Estabelecimento --}}
        <div class="space-y-6" style="flex: 1;">
            {{-- Informações Gerais --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-gray-200">
                    <h3 class="text-xs font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Informações Gerais
                    </h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'Razão Social' : 'Nome Completo' }}</label>
                            <p class="text-xs font-medium text-gray-900">{{ $estabelecimento->nome_razao_social }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Nome Fantasia</label>
                            <p class="text-xs font-medium text-gray-900">{{ $estabelecimento->nome_fantasia ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">{{ $estabelecimento->tipo_pessoa === 'juridica' ? 'CNPJ' : 'CPF' }}</label>
                            <p class="text-xs font-mono text-gray-900">{{ $estabelecimento->documento_formatado }}</p>
                        </div>
                        
                        @if($estabelecimento->tipo_pessoa === 'fisica')
                        {{-- Campos específicos de Pessoa Física --}}
                        @if($estabelecimento->rg)
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">RG</label>
                            <p class="text-xs text-gray-900">{{ $estabelecimento->rg }}</p>
                        </div>
                        @endif
                        @if($estabelecimento->orgao_emissor)
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Órgão Emissor</label>
                            <p class="text-xs text-gray-900">{{ $estabelecimento->orgao_emissor }}</p>
                        </div>
                        @endif
                        @endif
                        
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Tipo de Setor</label>
                            <p class="text-xs text-gray-900">{{ $estabelecimento->tipo_setor ? ucfirst($estabelecimento->tipo_setor->value) : '-' }}</p>
                        </div>
                        @if($estabelecimento->telefone)
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Telefone</label>
                            <p class="text-xs font-mono text-gray-900">{{ $estabelecimento->telefone }}</p>
                        </div>
                        @endif
                        @if($estabelecimento->email)
                        <div class="col-span-2">
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">E-mail</label>
                            <p class="text-xs text-gray-900">{{ $estabelecimento->email }}</p>
                        </div>
                        @endif

                        {{-- Separador e Endereço --}}
                        <div class="col-span-2 mt-3 pt-3 border-t border-gray-200">
                            <h4 class="text-[10px] font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                                <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Endereço
                            </h4>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Logradouro</label>
                            <p class="text-xs font-medium text-gray-900">{{ $estabelecimento->endereco }}, {{ $estabelecimento->numero }}</p>
                        </div>
                        @if($estabelecimento->complemento)
                        <div class="col-span-2">
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Complemento</label>
                            <p class="text-xs text-gray-900">{{ $estabelecimento->complemento }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Bairro</label>
                            <p class="text-xs text-gray-900">{{ $estabelecimento->bairro }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Município</label>
                            <p class="text-xs text-gray-900">{{ $estabelecimento->cidade }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Estado</label>
                            <p class="text-xs text-gray-900">{{ $estabelecimento->estado }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">CEP</label>
                            <p class="text-xs font-mono text-gray-900">{{ $estabelecimento->cep }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Processos em Andamento --}}
            @php
                $processosAtivos = $estabelecimento->processos()
                    ->whereIn('status', ['aberto', 'em_andamento', 'em_analise', 'parado'])
                    ->orderBy('created_at', 'desc')
                    ->limit(6)
                    ->get();
            @endphp
            @if($processosAtivos->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Processos Ativos
                    </h3>
                    <a href="{{ route('admin.estabelecimentos.processos.index', $estabelecimento->id) }}" 
                       class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                        Ver todos
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 justify-items-center">
                        @foreach($processosAtivos as $processo)
                        <a href="{{ route('admin.estabelecimentos.processos.show', [$estabelecimento->id, $processo->id]) }}" 
                           class="group block w-full max-w-sm bg-gradient-to-b from-white to-blue-50/30 rounded-xl border border-transparent ring-1 ring-gray-200 hover:ring-blue-200 shadow-md hover:shadow-xl transition-all duration-200 overflow-hidden">
                            <div class="p-5 text-center relative">
                                {{-- Header com Status e Menu --}}
                                <div class="flex items-start justify-center mb-4">
                                    @php
                                        $statusColors = [
                                            'aberto' => 'bg-blue-100 text-blue-700',
                                            'em_andamento' => 'bg-amber-100 text-amber-700',
                                            'em_analise' => 'bg-purple-100 text-purple-700',
                                            'parado' => 'bg-orange-100 text-orange-700',
                                        ];
                                        $statusLabels = [
                                            'aberto' => 'Aberto',
                                            'em_andamento' => 'Em Andamento',
                                            'em_analise' => 'Em Análise',
                                            'parado' => 'Parado',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$processo->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $statusLabels[$processo->status] ?? $processo->status }}
                                    </span>
                                    <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>
                                    </button>
                                </div>
                                
                                {{-- Tipo do Processo --}}
                                <div class="mb-3">
                                    <p class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                                        {{ $processo->tipoProcesso->nome ?? 'Sem tipo' }}
                                    </p>
                                </div>
                                
                                {{-- Número do Processo --}}
                                <div class="mb-4">
                                    <p class="text-2xl font-bold text-blue-600 group-hover:text-blue-700 transition-colors">
                                        {{ $processo->numero }}
                                    </p>
                                </div>
                                
                                {{-- Footer com Informações --}}
                                <div class="space-y-2 pt-3 border-t border-gray-100">
                                    <div class="flex items-center justify-center gap-2 text-xs text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Criado em: {{ $processo->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex items-center justify-center gap-2 text-xs text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>Criado por: N/A</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Informações do Sistema --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h3 class="text-xs font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Informações do Sistema
                    </h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Cadastrado em</label>
                            <p class="text-xs text-gray-900">{{ $estabelecimento->created_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Última atualização</label>
                            <p class="text-xs text-gray-900">{{ $estabelecimento->updated_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-medium text-gray-500 mb-0.5">ID do Sistema</label>
                            <p class="text-xs font-mono text-gray-900">#{{ $estabelecimento->id }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Aprovar --}}
    <div id="modal-aprovar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Aprovar Estabelecimento</h3>
                    <button onclick="document.getElementById('modal-aprovar').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('admin.estabelecimentos.aprovar', $estabelecimento->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="observacao" class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
                        <textarea id="observacao" name="observacao" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Adicione uma observação sobre a aprovação..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-aprovar').classList.add('hidden')"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Aprovar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Rejeitar --}}
    <div id="modal-rejeitar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Rejeitar Estabelecimento</h3>
                    <button onclick="document.getElementById('modal-rejeitar').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('admin.estabelecimentos.rejeitar', $estabelecimento->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="motivo_rejeicao" class="block text-sm font-medium text-gray-700 mb-2">Motivo da Rejeição *</label>
                        <textarea id="motivo_rejeicao" name="motivo_rejeicao" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Descreva o motivo da rejeição..."></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="observacao_rejeitar" class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
                        <textarea id="observacao_rejeitar" name="observacao" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Observações adicionais..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-rejeitar').classList.add('hidden')"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Rejeitar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Reiniciar --}}
    <div id="modal-reiniciar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Reiniciar Estabelecimento</h3>
                    <button onclick="document.getElementById('modal-reiniciar').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">O status do estabelecimento voltará para "Pendente" e poderá ser reanalisado.</p>
                <form action="{{ route('admin.estabelecimentos.reiniciar', $estabelecimento->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="observacao_reiniciar" class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
                        <textarea id="observacao_reiniciar" name="observacao" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                  placeholder="Motivo do reinício..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-reiniciar').classList.add('hidden')"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                            Reiniciar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Desativar --}}
    <div id="modal-desativar" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Desativar Estabelecimento</h3>
                    <button onclick="document.getElementById('modal-desativar').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">O estabelecimento será desativado e ficará inativo no sistema.</p>
                <form action="{{ route('admin.estabelecimentos.desativar', $estabelecimento->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="motivo_desativar" class="block text-sm font-medium text-gray-700 mb-2">Motivo da Desativação *</label>
                        <textarea id="motivo_desativar" name="motivo" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Descreva o motivo da desativação..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-desativar').classList.add('hidden')"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Desativar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Voltar para Pendente --}}
    <div id="modal-voltar-pendente" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Voltar para Pendente</h3>
                    <button onclick="document.getElementById('modal-voltar-pendente').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 mb-4">O estabelecimento voltará para o status "Pendente" e poderá ser reanalisado ou rejeitado.</p>
                <form action="{{ route('admin.estabelecimentos.voltar-pendente', $estabelecimento->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="observacao_voltar" class="block text-sm font-medium text-gray-700 mb-2">Motivo *</label>
                        <textarea id="observacao_voltar" name="observacao" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                  placeholder="Informe o motivo para voltar para pendente..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('modal-voltar-pendente').classList.add('hidden')"
                                class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                            Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Alterar Competência --}}
    <div id="modal-alterar-competencia" class="hidden fixed inset-0 bg-gray-600/50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-6 w-full max-w-md">
            <div class="bg-white rounded-xl shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Alterar Competência</h3>
                        <button onclick="document.getElementById('modal-alterar-competencia').classList.add('hidden')" 
                                class="text-gray-400 hover:text-gray-500 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-amber-600 mt-1">
                        ⚠️ Use apenas em casos excepcionais.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.estabelecimentos.alterar-competencia', $estabelecimento->id) }}">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Competência Atual</p>
                            <p class="font-medium text-{{ $estabelecimento->isCompetenciaEstadual() ? 'purple' : 'blue' }}-600">
                                {{ $estabelecimento->isCompetenciaEstadual() ? '🏛️ Estadual' : '🏘️ Municipal' }}
                                @if($estabelecimento->competencia_manual)
                                    <span class="text-xs text-amber-500">(manual)</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nova Competência</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="relative">
                                    <input type="radio" name="competencia_manual" value="municipal" required
                                           class="sr-only peer">
                                    <div class="p-2 border-2 border-gray-200 rounded-lg text-center cursor-pointer
                                              hover:border-blue-300 peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                        <span class="block text-sm font-medium">🏘️</span>
                                        <span class="text-xs">Municipal</span>
                                    </div>
                                </label>
                                
                                <label class="relative">
                                    <input type="radio" name="competencia_manual" value="estadual" required
                                           class="sr-only peer">
                                    <div class="p-2 border-2 border-gray-200 rounded-lg text-center cursor-pointer
                                              hover:border-purple-300 peer-checked:border-purple-500 peer-checked:bg-purple-50">
                                        <span class="block text-sm font-medium">🏛️</span>
                                        <span class="text-xs">Estadual</span>
                                    </div>
                                </label>
                                
                                <label class="relative">
                                    <input type="radio" name="competencia_manual" value="automatica" required
                                           class="sr-only peer">
                                    <div class="p-2 border-2 border-gray-200 rounded-lg text-center cursor-pointer
                                              hover:border-green-300 peer-checked:border-green-500 peer-checked:bg-green-50">
                                        <span class="block text-sm font-medium">⚙️</span>
                                        <span class="text-xs">Automática</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                            <label for="motivo_alteracao" class="block text-sm font-medium text-gray-700 mb-1">
                                Motivo <span class="text-gray-400 text-xs">(obrigatório)</span>
                            </label>
                            <textarea id="motivo_alteracao" name="motivo_alteracao_competencia" 
                                      rows="2" required minlength="10" maxlength="500"
                                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Ex: Conforme Pactuação"></textarea>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" 
                                onclick="document.getElementById('modal-alterar-competencia').classList.add('hidden')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                            Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
            </div>
        </div>
    </div>

</div>
@endsection
