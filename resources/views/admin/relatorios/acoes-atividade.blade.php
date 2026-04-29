@extends('layouts.admin')

@section('title', 'Relatório de Ações por Atividade')

@section('content')
<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <a href="{{ route('admin.relatorios.index') }}" class="hover:text-gray-700">Relatórios</a>
            <span>/</span>
            <span class="text-gray-900">Ações por Atividade</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Ações por Atividade</h1>
        <p class="text-gray-500 text-sm mt-1">Ordens de serviço por tipo de ação, município, competência e técnico</p>
    </div>

    {{-- Cards resumo --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <p class="text-xs text-gray-500 font-medium">Total OS</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($totalOS, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <p class="text-xs text-gray-500 font-medium">Concluídas</p>
            <p class="text-xl font-bold text-green-600">{{ number_format($totalConcluidas, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <p class="text-xs text-gray-500 font-medium">Em Andamento</p>
            <p class="text-xl font-bold text-amber-600">{{ number_format($totalEmAndamento, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <p class="text-xs text-gray-500 font-medium">Comp. Estadual</p>
            <p class="text-xl font-bold text-blue-600">{{ number_format($totalEstadual, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <p class="text-xs text-gray-500 font-medium">Comp. Municipal</p>
            <p class="text-xl font-bold text-emerald-600">{{ number_format($totalMunicipal, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.relatorios.acoes-atividade') }}" class="flex flex-wrap items-end gap-3">
            @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual())
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">Competência</label>
                <select name="competencia" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Todas</option>
                    <option value="estadual" @selected(request('competencia') === 'estadual')>Estadual</option>
                    <option value="municipal" @selected(request('competencia') === 'municipal')>Municipal</option>
                </select>
            </div>
            <div class="w-44">
                <label class="block text-xs font-medium text-gray-600 mb-1">Município</label>
                <select name="municipio_id" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Todos</option>
                    @foreach($municipios as $municipio)
                        <option value="{{ $municipio->id }}" @selected((string) request('municipio_id') === (string) $municipio->id)>{{ $municipio->nome }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Técnico</label>
                <select name="usuario_id" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Todos</option>
                    @foreach($usuarios as $usr)
                        <option value="{{ $usr->id }}" @selected((string) request('usuario_id') === (string) $usr->id)>{{ $usr->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Todos</option>
                    <option value="aberta" @selected(request('status') === 'aberta')>Aberta</option>
                    <option value="em_andamento" @selected(request('status') === 'em_andamento')>Em Andamento</option>
                    <option value="concluida" @selected(request('status') === 'concluida')>Concluída</option>
                    <option value="cancelada" @selected(request('status') === 'cancelada')>Cancelada</option>
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                <input type="date" name="data_inicio" value="{{ request('data_inicio') }}"
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                <input type="date" name="data_fim" value="{{ request('data_fim') }}"
                       class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-3 py-1.5 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">Filtrar</button>
                <a href="{{ route('admin.relatorios.acoes-atividade') }}" class="px-3 py-1.5 bg-gray-100 text-gray-600 text-sm rounded-lg hover:bg-gray-200">Limpar</a>
            </div>
        </form>
    </div>

    {{-- Gráficos --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Gráfico: Ações por Tipo --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">OS por Tipo de Ação</h3>
            <div style="height: 300px;">
                <canvas id="chartAcoesTipo"></canvas>
            </div>
        </div>

        {{-- Gráfico: OS por Município --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">OS por Município</h3>
            <div style="height: 300px;">
                <canvas id="chartMunicipio"></canvas>
            </div>
        </div>

        {{-- Gráfico: Evolução Mensal --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Evolução Mensal</h3>
            <div style="height: 300px;">
                <canvas id="chartMensal"></canvas>
            </div>
        </div>

        {{-- Gráfico: Competência --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Distribuição por Competência</h3>
            <div style="height: 300px;">
                <canvas id="chartCompetencia"></canvas>
            </div>
        </div>
    </div>

    {{-- Tabelas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Tabela: Ações por Tipo --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900">Detalhamento por Tipo de Ação</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ação</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($acoesPorTipoFormatado as $acao)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-900">{{ $acao['nome'] }}</td>
                            <td class="px-4 py-2 text-center font-bold text-purple-600">{{ $acao['total'] }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="px-4 py-6 text-center text-gray-400">Nenhuma ação encontrada</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabela: Por Município --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900">Detalhamento por Município</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Município</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Concluídas</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Est.</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Mun.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($porMunicipio as $mun)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-900">{{ $mun['nome'] }}</td>
                            <td class="px-4 py-2 text-center font-bold text-gray-700">{{ $mun['total'] }}</td>
                            <td class="px-4 py-2 text-center text-green-600 font-medium">{{ $mun['concluidas'] }}</td>
                            <td class="px-4 py-2 text-center text-blue-600 font-medium">{{ $mun['estadual'] }}</td>
                            <td class="px-4 py-2 text-center text-emerald-600 font-medium">{{ $mun['municipal'] }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Nenhum dado encontrado</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tabela: Por Técnico --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900">Atividades por Técnico</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Técnico</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Atividades Atribuídas</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Concluídas</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">% Conclusão</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($porUsuarioFormatado as $usr)
                    @php $pctConclusao = $usr['total'] > 0 ? round(($usr['concluidas'] / $usr['total']) * 100) : 0; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-900 font-medium">{{ $usr['nome'] }}</td>
                        <td class="px-4 py-2 text-center font-bold text-gray-700">{{ $usr['total'] }}</td>
                        <td class="px-4 py-2 text-center text-green-600 font-medium">{{ $usr['concluidas'] }}</td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-full rounded-full {{ $pctConclusao === 100 ? 'bg-green-500' : 'bg-purple-500' }}" style="width: {{ $pctConclusao }}%"></div>
                                </div>
                                <span class="text-xs font-bold {{ $pctConclusao === 100 ? 'text-green-600' : 'text-gray-600' }}">{{ $pctConclusao }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Nenhum técnico encontrado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cores = ['#7c3aed','#2563eb','#059669','#d97706','#dc2626','#0891b2','#4f46e5','#16a34a','#ea580c','#9333ea','#0284c7','#65a30d'];

    // Ações por Tipo
    const acoesTipoData = @json($acoesPorTipoFormatado);
    if (acoesTipoData.length > 0) {
        new Chart(document.getElementById('chartAcoesTipo'), {
            type: 'bar',
            data: {
                labels: acoesTipoData.map(a => a.nome.length > 40 ? a.nome.substring(0, 40) + '...' : a.nome),
                datasets: [{
                    label: 'Quantidade',
                    data: acoesTipoData.map(a => a.total),
                    backgroundColor: cores.slice(0, acoesTipoData.length),
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // Por Município
    const munData = @json($porMunicipio->take(10));
    if (munData.length > 0) {
        new Chart(document.getElementById('chartMunicipio'), {
            type: 'bar',
            data: {
                labels: munData.map(m => m.nome),
                datasets: [
                    { label: 'Estadual', data: munData.map(m => m.estadual), backgroundColor: '#3b82f6', borderRadius: 4 },
                    { label: 'Municipal', data: munData.map(m => m.municipal), backgroundColor: '#10b981', borderRadius: 4 },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // Evolução Mensal
    const mesData = @json($porMes);
    if (mesData.length > 0) {
        new Chart(document.getElementById('chartMensal'), {
            type: 'line',
            data: {
                labels: mesData.map(m => m.mes),
                datasets: [{
                    label: 'OS Abertas',
                    data: mesData.map(m => m.total),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#7c3aed',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    // Competência (Doughnut)
    const estadual = {{ $totalEstadual }};
    const municipal = {{ $totalMunicipal }};
    if (estadual + municipal > 0) {
        new Chart(document.getElementById('chartCompetencia'), {
            type: 'doughnut',
            data: {
                labels: ['Estadual', 'Municipal'],
                datasets: [{
                    data: [estadual, municipal],
                    backgroundColor: ['#3b82f6', '#10b981'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 12 } } }
                }
            }
        });
    }
});
</script>
@endsection
