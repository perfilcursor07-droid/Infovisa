@php
    $usuario = auth('interno')->user();
    
    // Buscar estatísticas de alertas
    $alertas = [];
    
    // 0. Notificações de Ordens de Serviço (do banco de dados)
    $notificacoesOS = \App\Models\Notificacao::doUsuario($usuario->id)
        ->naoLidas()
        ->recentes()
        ->limit(5)
        ->get();
    
    foreach ($notificacoesOS as $notif) {
        $alertas[] = [
            'tipo' => 'notificacao_os',
            'titulo' => $notif->titulo,
            'mensagem' => $notif->mensagem,
            'quantidade' => 1,
            'cor' => $notif->cor,
            'icone' => 'clipboard-check',
            'link' => $notif->link,
            'id' => $notif->id,
            'prioridade' => $notif->prioridade,
        ];
    }
    
    // 1. Documentos aguardando assinatura
    $docsAssinatura = \App\Models\DocumentoAssinatura::where('usuario_interno_id', $usuario->id)
        ->where('status', 'pendente')
        ->whereHas('documentoDigital', function($q) {
            $q->where('status', 'aguardando_assinatura');
        })
        ->count();
    
    if ($docsAssinatura > 0) {
        $alertas[] = [
            'tipo' => 'assinatura',
            'titulo' => 'Documentos para Assinar',
            'mensagem' => $docsAssinatura . ' documento(s) aguardando sua assinatura',
            'quantidade' => $docsAssinatura,
            'cor' => 'yellow',
            'icone' => 'edit',
            'link' => route('admin.assinatura.pendentes'),
        ];
    }
    
    // 2. Documentos em rascunho criados pelo usuário logado
    // (evita contagem indevida de assinaturas antigas em rascunhos de terceiros)
    $docsRascunho = \App\Models\DocumentoDigital::where('usuario_criador_id', $usuario->id)
        ->where('status', 'rascunho')
        ->count();
    
    if ($docsRascunho > 0) {
        $alertas[] = [
            'tipo' => 'rascunho',
            'titulo' => 'Rascunhos Pendentes',
            'mensagem' => $docsRascunho . ' documento(s) em rascunho aguardando finalização',
            'quantidade' => $docsRascunho,
            'cor' => 'blue',
            'icone' => 'document',
            'link' => route('admin.documentos.index', ['status' => 'rascunho']),
        ];
    }
    
    // 3. Estabelecimentos pendentes de aprovação
    // Removido do sino para evitar alertas globais fora do escopo de "minhas demandas / meu setor"
    
    // 5. Documentos pendentes de aprovação (enviados por empresas)
    $docsPendentesQuery = \App\Models\ProcessoDocumento::where('status_aprovacao', 'pendente')
        ->where('tipo_usuario', 'externo');
    $respostasPendentesQuery = \App\Models\DocumentoResposta::where('status', 'pendente');
    
    // Filtrar por responsável/setor (minhas demandas + meu setor + setor de análise inicial)
    // Regra unificada: responsável direto, setor_atual do processo, OU setor responsável pela
    // análise inicial do tipo de processo (tipo_processos.tipo_setor_id para estadual, ou
    // tipo_processo_setor_municipio para usuários municipais).
    if (!$usuario->isAdmin()) {
        $setoresUsuario = $usuario->getSetoresCodigos();

        $aplicarFiltroSetor = function ($subQuery) use ($usuario, $setoresUsuario) {
            $subQuery->where('responsavel_atual_id', $usuario->id);

            if (!empty($setoresUsuario)) {
                $subQuery->orWhereIn('setor_atual', $setoresUsuario);

                $subQuery->orWhereHas('tipoProcesso', function ($tp) use ($setoresUsuario, $usuario) {
                    $tp->where(function ($tpq) use ($setoresUsuario, $usuario) {
                        $tpq->whereHas('tipoSetor', function ($ts) use ($setoresUsuario) {
                            $ts->whereIn('codigo', $setoresUsuario);
                        });

                        if ($usuario->isMunicipal() && $usuario->municipio_id) {
                            $tpq->orWhereHas('setoresMunicipais', function ($sm) use ($setoresUsuario, $usuario) {
                                $sm->where('municipio_id', $usuario->municipio_id)
                                    ->whereHas('tipoSetor', function ($ts) use ($setoresUsuario) {
                                        $ts->whereIn('codigo', $setoresUsuario);
                                    });
                            });
                        }
                    });
                });
            }
        };

        $docsPendentesQuery->whereHas('processo', $aplicarFiltroSetor);
        $respostasPendentesQuery->whereHas('documentoDigital.processo', $aplicarFiltroSetor);
    }

    // Filtrar por competência
    if ($usuario->isEstadual()) {
        // Estadual - filtra por competência manual ou null, depois filtra em memória
        $docsPendentesQuery->whereHas('processo.estabelecimento', function($q) {
            $q->where('competencia_manual', 'estadual')
              ->orWhereNull('competencia_manual');
        });
        $respostasPendentesQuery->whereHas('documentoDigital.processo.estabelecimento', function($q) {
            $q->where('competencia_manual', 'estadual')
              ->orWhereNull('competencia_manual');
        });
    } elseif ($usuario->isMunicipal()) {
        $municipioId = $usuario->municipio_id;
        $docsPendentesQuery->whereHas('processo.estabelecimento', function($q) use ($municipioId) {
            $q->where('municipio_id', $municipioId);
        });
        $respostasPendentesQuery->whereHas('documentoDigital.processo.estabelecimento', function($q) use ($municipioId) {
            $q->where('municipio_id', $municipioId);
        });
    }
    
    $totalDocsPendentes = $docsPendentesQuery->count() + $respostasPendentesQuery->count();
    
    if ($totalDocsPendentes > 0) {
        $alertas[] = [
            'tipo' => 'aprovacao',
            'titulo' => 'Documentos para Aprovar',
            'mensagem' => $totalDocsPendentes . ' documento(s) enviado(s) por empresas aguardando aprovação',
            'quantidade' => $totalDocsPendentes,
            'cor' => 'purple',
            'icone' => 'check-circle',
            'link' => route('admin.documentos-pendentes.index'),
        ];
    }
    
    // 4. Processos com movimentação recente (últimas 24h)
    // Comentado temporariamente - necessário implementar relação 'acompanhantes' no model Processo
    /*
    $processosAtualizados = \App\Models\Processo::whereHas('acompanhantes', function($q) use ($usuario) {
            $q->where('usuario_interno_id', $usuario->id);
        })
        ->where('updated_at', '>=', now()->subDay())
        ->count();
    
    if ($processosAtualizados > 0) {
        $alertas[] = [
            'tipo' => 'processo',
            'titulo' => 'Processos Atualizados',
            'mensagem' => $processosAtualizados . ' processo(s) com movimentação recente',
            'quantidade' => $processosAtualizados,
            'cor' => 'green',
            'icone' => 'clipboard',
            'link' => route('admin.processos.index-geral'),
        ];
    }
    */
    
    // Total de alertas
    $totalAlertas = collect($alertas)->sum('quantidade');
@endphp

<div class="relative" x-data="{ notificacoesOpen: false }" @click.away="notificacoesOpen = false">
    {{-- Botão de Notificações --}}
    <button @click="notificacoesOpen = !notificacoesOpen"
            class="relative p-2 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg hover:bg-gray-100 transition-colors">
        {{-- Ícone do Sino --}}
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        
        {{-- Badge de Contagem --}}
        @if($totalAlertas > 0)
        <span class="absolute -top-1 -right-1 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full border-2 border-white">
            {{ $totalAlertas > 99 ? '99+' : $totalAlertas }}
        </span>
        @endif
    </button>

    {{-- Dropdown de Notificações --}}
    <div x-show="notificacoesOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="origin-top-right absolute right-0 mt-2 w-80 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 overflow-hidden z-50">
        
        {{-- Header --}}
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Notificações</h3>
                @if($totalAlertas > 0)
                <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">
                    {{ $totalAlertas }}
                </span>
                @endif
            </div>
        </div>

        {{-- Lista de Alertas --}}
        <div class="max-h-96 overflow-y-auto">
            @forelse($alertas as $alerta)
            <a href="{{ $alerta['link'] }}" 
               class="block px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-b-0"
               @click="notificacoesOpen = false"
               @if(isset($alerta['id']))
               onclick="marcarNotificacaoLida({{ $alerta['id'] }})"
               @endif>
                <div class="flex items-start gap-3">
                    {{-- Ícone --}}
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                        @if($alerta['cor'] === 'yellow') bg-yellow-100
                        @elseif($alerta['cor'] === 'blue') bg-blue-100
                        @elseif($alerta['cor'] === 'orange') bg-orange-100
                        @elseif($alerta['cor'] === 'green') bg-green-100
                        @elseif($alerta['cor'] === 'red') bg-red-100
                        @elseif($alerta['cor'] === 'purple') bg-purple-100
                        @else bg-gray-100
                        @endif">
                        @if($alerta['icone'] === 'clipboard-check')
                        <svg class="w-4 h-4 @if($alerta['cor'] === 'purple') text-purple-600 @elseif($alerta['cor'] === 'blue') text-blue-600 @elseif($alerta['cor'] === 'red') text-red-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        @elseif($alerta['icone'] === 'edit')
                        <svg class="w-4 h-4 @if($alerta['cor'] === 'yellow') text-yellow-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        @elseif($alerta['icone'] === 'document')
                        <svg class="w-4 h-4 @if($alerta['cor'] === 'blue') text-blue-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        @elseif($alerta['icone'] === 'building')
                        <svg class="w-4 h-4 @if($alerta['cor'] === 'orange') text-orange-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        @elseif($alerta['icone'] === 'clipboard')
                        <svg class="w-4 h-4 @if($alerta['cor'] === 'green') text-green-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        @elseif($alerta['icone'] === 'check-circle')
                        <svg class="w-4 h-4 @if($alerta['cor'] === 'purple') text-purple-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        @endif
                    </div>
                    
                    {{-- Conteúdo --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $alerta['titulo'] }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $alerta['mensagem'] }}
                        </p>
                    </div>
                    
                    {{-- Badge de quantidade --}}
                    <span class="flex-shrink-0 px-2 py-0.5 text-xs font-bold rounded-full
                        @if($alerta['cor'] === 'yellow') bg-yellow-100 text-yellow-800
                        @elseif($alerta['cor'] === 'blue') bg-blue-100 text-blue-800
                        @elseif($alerta['cor'] === 'orange') bg-orange-100 text-orange-800
                        @elseif($alerta['cor'] === 'green') bg-green-100 text-green-800
                        @elseif($alerta['cor'] === 'purple') bg-purple-100 text-purple-800
                        @elseif($alerta['cor'] === 'red') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ $alerta['quantidade'] }}
                    </span>
                </div>
            </a>
            @empty
            <div class="px-4 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Nenhuma notificação</p>
                <p class="text-xs text-gray-400 mt-1">Você está em dia!</p>
            </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if($totalAlertas > 0)
        <div class="px-4 py-2 bg-gray-50 border-t border-gray-200">
            <a href="{{ route('admin.dashboard') }}" 
               class="block text-center text-xs font-medium text-blue-600 hover:text-blue-700"
               @click="notificacoesOpen = false">
                Ver todas no Dashboard →
            </a>
        </div>
        @endif
    </div>
</div>

<script>
// Função para marcar notificação como lida
function marcarNotificacaoLida(notificacaoId) {
    const baseUrl = window.APP_BASE_URL || '';
    fetch(`${baseUrl}/admin/notificacoes/${notificacaoId}/marcar-lida`, {
        method: 'POST',
        credentials: 'same-origin',
        keepalive: true,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    }).catch(err => console.error('Erro ao marcar notificação:', err));
}
</script>
