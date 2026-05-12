@extends('layouts.admin')

@section('title', 'Relatório de Pendências por Usuário')

@section('content')
<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.relatorios.index') }}" class="hover:text-gray-700">Relatórios</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-700">Pendências por Usuário</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Relatório de Pendências por Usuário</h1>
            <p class="text-gray-500 mt-1">Visualize todas as pendências vinculadas a cada técnico/gestor</p>
        </div>
    </div>

    {{-- Filtro de Usuário --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.relatorios.usuarios') }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label for="usuario_id" class="block text-sm font-medium text-gray-700 mb-1">Selecionar Usuário</label>
                <select name="usuario_id" id="usuario_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">-- Selecione para ver detalhes --</option>
                    @foreach($usuariosDisponiveis as $u)
                        <option value="{{ $u->id }}" {{ request('usuario_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->nome }} ({{ $u->nivel_acesso->label() }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                Ver Detalhes
            </button>
            @if(request('usuario_id'))
            <a href="{{ route('admin.relatorios.usuarios') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                Limpar
            </a>
            @endif
        </form>
    </div>

    {{-- Tabela Resumo de Todos os Usuários --}}
    @if(!$usuarioSelecionado)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Resumo de Pendências por Usuário</h2>
            <p class="text-xs text-gray-500 mt-0.5">Clique no nome para ver detalhes</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Usuário</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nível</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
                            <span title="Assinaturas Pendentes">✍️ Assinaturas</span>
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
                            <span title="Processos Abertos/Parados">📋 Processos</span>
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
                            <span title="Respostas Pendentes de Análise">📎 Respostas</span>
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
                            <span title="OS com Atividades Pendentes">🔧 OS</span>
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($resumoUsuarios as $resumo)
                    <tr class="hover:bg-gray-50 transition {{ $resumo['total_pendencias'] > 0 ? '' : 'opacity-50' }}">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.relatorios.usuarios', ['usuario_id' => $resumo['id']]) }}"
                               class="text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                                {{ $resumo['nome'] }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $resumo['nivel_acesso'] }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($resumo['assinaturas_pendentes'] > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">{{ $resumo['assinaturas_pendentes'] }}</span>
                            @else
                                <span class="text-xs text-gray-300">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($resumo['processos_abertos'] > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">{{ $resumo['processos_abertos'] }}</span>
                            @else
                                <span class="text-xs text-gray-300">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($resumo['respostas_pendentes'] > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700">{{ $resumo['respostas_pendentes'] }}</span>
                            @else
                                <span class="text-xs text-gray-300">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($resumo['os_pendentes'] > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700">{{ $resumo['os_pendentes'] }}</span>
                            @else
                                <span class="text-xs text-gray-300">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($resumo['total_pendencias'] > 0)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">{{ $resumo['total_pendencias'] }}</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">✓</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Nenhum usuário encontrado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Detalhes do Usuário Selecionado --}}
    @if($usuarioSelecionado && $pendencias)
    <div class="space-y-4">
        {{-- Info do Usuário --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-lg font-bold text-indigo-600">{{ mb_substr($usuarioSelecionado->nome, 0, 1) }}</span>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $usuarioSelecionado->nome }}</h2>
                    <p class="text-sm text-gray-500">{{ $usuarioSelecionado->nivel_acesso->label() }} • {{ $usuarioSelecionado->setor ?? 'Sem setor' }}</p>
                </div>
                <div class="ml-auto flex gap-3">
                    <div class="text-center px-3 py-1 bg-amber-50 rounded-lg">
                        <p class="text-lg font-bold text-amber-700">{{ $pendencias['assinaturas']->count() }}</p>
                        <p class="text-[10px] text-amber-600">Assinaturas</p>
                    </div>
                    <div class="text-center px-3 py-1 bg-blue-50 rounded-lg">
                        <p class="text-lg font-bold text-blue-700">{{ $pendencias['processos']->count() }}</p>
                        <p class="text-[10px] text-blue-600">Processos</p>
                    </div>
                    <div class="text-center px-3 py-1 bg-orange-50 rounded-lg">
                        <p class="text-lg font-bold text-orange-700">{{ $pendencias['respostas']->count() }}</p>
                        <p class="text-[10px] text-orange-600">Respostas</p>
                    </div>
                    <div class="text-center px-3 py-1 bg-purple-50 rounded-lg">
                        <p class="text-lg font-bold text-purple-700">{{ $pendencias['ordens_servico']->count() }}</p>
                        <p class="text-[10px] text-purple-600">OS</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Assinaturas Pendentes --}}
        @if($pendencias['assinaturas']->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-amber-50">
                <h3 class="text-sm font-semibold text-amber-800 flex items-center gap-2">
                    ✍️ Assinaturas Pendentes
                    <span class="px-2 py-0.5 bg-amber-200 text-amber-800 rounded-full text-xs">{{ $pendencias['assinaturas']->count() }}</span>
                </h3>
            </div>
            <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                @foreach($pendencias['assinaturas'] as $ass)
                <a href="{{ $ass['url'] }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-amber-50/50 transition">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $ass['tipo_documento'] }}</p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ $ass['numero_documento'] }} • {{ $ass['estabelecimento'] }}
                            @if($ass['processo_numero']) • Processo {{ $ass['processo_numero'] }} @endif
                        </p>
                    </div>
                    <span class="text-xs text-gray-400 ml-2 flex-shrink-0">{{ $ass['criado_em']?->format('d/m/Y') }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Processos sob Responsabilidade --}}
        @if($pendencias['processos']->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-blue-50">
                <h3 class="text-sm font-semibold text-blue-800 flex items-center gap-2">
                    📋 Processos sob Responsabilidade (Abertos/Parados)
                    <span class="px-2 py-0.5 bg-blue-200 text-blue-800 rounded-full text-xs">{{ $pendencias['processos']->count() }}</span>
                </h3>
            </div>
            <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                @foreach($pendencias['processos'] as $proc)
                <a href="{{ $proc['url'] }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-blue-50/50 transition">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ $proc['numero_processo'] }}
                            <span class="text-xs px-1.5 py-0.5 rounded {{ $proc['status'] === 'parado' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">{{ ucfirst($proc['status']) }}</span>
                        </p>
                        <p class="text-xs text-gray-500 truncate">{{ $proc['tipo'] }} • {{ $proc['estabelecimento'] }}</p>
                    </div>
                    <span class="text-xs text-gray-400 ml-2 flex-shrink-0">{{ $proc['criado_em']?->format('d/m/Y') }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Respostas Pendentes de Análise --}}
        @if($pendencias['respostas']->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-orange-50">
                <h3 class="text-sm font-semibold text-orange-800 flex items-center gap-2">
                    📎 Respostas Pendentes de Análise
                    <span class="px-2 py-0.5 bg-orange-200 text-orange-800 rounded-full text-xs">{{ $pendencias['respostas']->count() }}</span>
                </h3>
                <p class="text-xs text-orange-600 mt-0.5">Documentos assinados pelo usuário com respostas ainda não analisadas</p>
            </div>
            <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                @foreach($pendencias['respostas'] as $resp)
                <a href="{{ $resp['url'] }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-orange-50/50 transition">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ $resp['tipo_documento'] }} - {{ $resp['numero_documento'] }}
                            @if($resp['atrasado'])
                                <span class="text-xs px-1.5 py-0.5 rounded bg-red-100 text-red-700 font-bold">{{ abs($resp['dias_restantes']) }}d atraso</span>
                            @elseif($resp['dias_restantes'] !== null && $resp['dias_restantes'] <= 3)
                                <span class="text-xs px-1.5 py-0.5 rounded bg-amber-100 text-amber-700">{{ $resp['dias_restantes'] }}d</span>
                            @elseif($resp['dias_restantes'] !== null)
                                <span class="text-xs px-1.5 py-0.5 rounded bg-green-100 text-green-700">{{ $resp['dias_restantes'] }}d</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ $resp['arquivo'] }} • {{ $resp['estabelecimento'] }}
                            @if($resp['processo_numero']) • Processo {{ $resp['processo_numero'] }} @endif
                        </p>
                    </div>
                    <div class="text-right ml-2 flex-shrink-0">
                        <p class="text-xs text-gray-400">{{ $resp['data_resposta']?->format('d/m/Y') }}</p>
                        @if($resp['prazo_analise'])
                            <p class="text-[10px] {{ $resp['atrasado'] ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                                Limite: {{ $resp['prazo_analise']->format('d/m/Y') }}
                            </p>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Ordens de Serviço com Atividades Pendentes --}}
        @if($pendencias['ordens_servico']->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-purple-50">
                <h3 class="text-sm font-semibold text-purple-800 flex items-center gap-2">
                    🔧 Ordens de Serviço com Atividades Pendentes
                    <span class="px-2 py-0.5 bg-purple-200 text-purple-800 rounded-full text-xs">{{ $pendencias['ordens_servico']->count() }}</span>
                </h3>
            </div>
            <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                @foreach($pendencias['ordens_servico'] as $os)
                <a href="{{ $os['url'] }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-purple-50/50 transition">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            OS #{{ $os['numero'] }}
                            <span class="text-xs px-1.5 py-0.5 rounded bg-purple-100 text-purple-700">{{ $os['atividades_pendentes'] }} atividade(s)</span>
                        </p>
                        <p class="text-xs text-gray-500 truncate">{{ $os['estabelecimento'] }}</p>
                    </div>
                    <div class="text-right ml-2 flex-shrink-0">
                        <p class="text-xs text-gray-400">Aberta: {{ $os['data_abertura']?->format('d/m/Y') }}</p>
                        @if($os['data_fim'])
                            <p class="text-[10px] text-gray-400">Prazo: {{ $os['data_fim']->format('d/m/Y') }}</p>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Sem pendências --}}
        @if($pendencias['assinaturas']->count() === 0 && $pendencias['processos']->count() === 0 && $pendencias['respostas']->count() === 0 && $pendencias['ordens_servico']->count() === 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <p class="text-sm font-medium text-gray-600">{{ $usuarioSelecionado->nome }} não possui pendências</p>
            <p class="text-xs text-gray-400 mt-1">Todas as tarefas estão em dia</p>
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
