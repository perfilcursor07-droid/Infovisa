@extends('layouts.admin')

@section('title', 'Relatório de Ações por Atividade')

@section('content')
<div class="space-y-5 max-w-[1400px] mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
                <a href="{{ route('admin.relatorios.index') }}" class="hover:text-gray-600 transition">Relatórios</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-600">Ações por Atividade</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Relatório de Ações por Atividade</h1>
            <p class="text-xs text-gray-500 mt-0.5">{{ $escopoVisual }}</p>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Total OS</p>
            <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ number_format($totalOS, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Concluídas</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($totalConcluidas, 0, ',', '.') }}</p>
            @if($totalOS > 0)
            <p class="text-[10px] text-gray-400 mt-0.5">{{ round(($totalConcluidas / $totalOS) * 100) }}% do total</p>
            @endif
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Em Andamento</p>
            <p class="text-2xl font-extrabold text-amber-500 mt-1">{{ number_format($totalEmAndamento, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Canceladas</p>
            <p class="text-2xl font-extrabold text-red-500 mt-1">{{ number_format($totalCanceladas, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Estadual</p>
            <p class="text-2xl font-extrabold text-indigo-600 mt-1">{{ number_format($totalEstadual, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Municipal</p>
            <p class="text-2xl font-extrabold text-teal-600 mt-1">{{ number_format($totalMunicipal, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <details class="bg-white rounded-xl border border-gray-100 shadow-sm group" {{ request()->hasAny(['competencia','municipio_id','usuario_id','status','data_inicio','data_fim']) ? 'open' : '' }}>
        <summary class="px-5 py-3 cursor-pointer flex items-center justify-between text-sm font-semibold text-gray-700 select-none">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filtros
                @if(request()->hasAny(['competencia','municipio_id','usuario_id','status','data_inicio','data_fim']))
                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-purple-100 text-purple-700 rounded-full">Ativos</span>
                @endif
            </span>
            <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <div class="px-5 pb-4 pt-2 border-t border-gray-100">
            <form method="GET" action="{{ route('admin.relatorios.acoes-atividade') }}" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 items-end">
                @if(auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual())
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Competência</label>
                    <select name="competencia" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition">
                        <option value="">Todas</option>
                        <option value="estadual" @selected(request('competencia') === 'estadual')>Estadual</option>
                        <option value="municipal" @selected(request('competencia') === 'municipal')>Municipal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Município</label>
                    <select name="municipio_id" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition">
                        <option value="">Todos</option>
                        @foreach($municipios as $mun)
                        <option value="{{ $mun->id }}" @selected((string) request('municipio_id') === (string) $mun->id)>{{ $mun->nome }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Técnico</label>
                    <select name="usuario_id" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition">
                        <option value="">Todos</option>
                        @foreach($usuarios as $usr)
                        <option value="{{ $usr->id }}" @selected((string) request('usuario_id') === (string) $usr->id)>{{ $usr->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Status</label>
                    <select name="status" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition">
                        <option value="">Todos</option>
                        <option value="aberta" @selected(request('status') === 'aberta')>Aberta</option>
                        <option value="em_andamento" @selected(request('status') === 'em_andamento')>Em Andamento</option>
                        <option value="concluida" @selected(request('status') === 'concluida')>Concluída</option>
                        <option value="cancelada" @selected(request('status') === 'cancelada')>Cancelada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">De</label>
                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Até</label>
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700 transition shadow-sm">Filtrar</button>
                    <a href="{{ route('admin.relatorios.acoes-atividade') }}" class="px-3 py-2 bg-gray-100 text-gray-500 text-sm rounded-lg hover:bg-gray-200 transition" title="Limpar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                </div>
            </form>
        </div>
    </details>

    {{-- Gráficos Row 1 --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Evolução Mensal (ocupa 2 colunas) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Evolução Mensal</h3>
            <div style="height: 260px;"><canvas id="chartMensal"></canvas></div>
        </div>
        {{-- Status (doughnut) --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Distribuição por Status</h3>
            <div style="height: 260px;"><canvas id="chartStatus"></canvas></div>
        </div>
    </div>

    {{-- Gráficos Row 2 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Ações por Tipo --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">OS por Tipo de Ação</h3>
            <div style="height: {{ max(200, count($acoesPorTipoFormatado) * 36) }}px;"><canvas id="chartAcoesTipo"></canvas></div>
        </div>
        {{-- Competência --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">OS por Município — Estadual vs Municipal</h3>
            <div style="height: {{ max(200, min(count($porMunicipio), 10) * 32) }}px;"><canvas id="chartMunicipio"></canvas></div>
        </div>
    </div>

    {{-- Tabelas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Tabela: Ações por Tipo --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Detalhamento por Tipo de Ação</h3>
            </div>
            <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80 sticky top-0">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase">Ação</th>
                            <th class="px-5 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase w-20">Comp.</th>
                            <th class="px-5 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase w-20">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($acoesPorTipoFormatado as $i => $acao)
                        <tr class="hover:bg-purple-50/40 transition-colors">
                            <td class="px-5 py-2.5 text-gray-800 font-medium">{{ $acao['nome'] }}</td>
                            <td class="px-5 py-2.5 text-center">
                                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded {{ $acao['competencia'] === 'estadual' ? 'bg-indigo-50 text-indigo-600' : ($acao['competencia'] === 'municipal' ? 'bg-teal-50 text-teal-600' : 'bg-gray-100 text-gray-500') }}">
                                    {{ ucfirst($acao['competencia']) }}
                                </span>
                            </td>
                            <td class="px-5 py-2.5 text-right font-bold text-gray-900">{{ $acao['total'] }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-gray-300 text-sm">Nenhuma ação encontrada</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabela: Por Município --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Detalhamento por Município</h3>
            </div>
            <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80 sticky top-0">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase">Município</th>
                            <th class="px-5 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase w-16">Total</th>
                            <th class="px-5 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase w-20">Concl.</th>
                            <th class="px-5 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase w-16">Est.</th>
                            <th class="px-5 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase w-16">Mun.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($porMunicipio as $mun)
                        <tr class="hover:bg-purple-50/40 transition-colors">
                            <td class="px-5 py-2.5 text-gray-800 font-medium">{{ $mun['nome'] }}</td>
                            <td class="px-5 py-2.5 text-right font-bold text-gray-900">{{ $mun['total'] }}</td>
                            <td class="px-5 py-2.5 text-right font-semibold text-emerald-600">{{ $mun['concluidas'] }}</td>
                            <td class="px-5 py-2.5 text-right font-semibold text-indigo-600">{{ $mun['estadual'] }}</td>
                            <td class="px-5 py-2.5 text-right font-semibold text-teal-600">{{ $mun['municipal'] }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-300 text-sm">Nenhum dado encontrado</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tabela: Por Técnico (full width) --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Produtividade por Técnico</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase">Técnico</th>
                        <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase w-32">Perfil</th>
                        <th class="px-5 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase w-24">Atribuídas</th>
                        <th class="px-5 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase w-24">Concluídas</th>
                        <th class="px-5 py-2.5 text-[10px] font-semibold text-gray-400 uppercase w-48">Conclusão</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($porUsuarioFormatado as $usr)
                    @php $pct = $usr['total'] > 0 ? round(($usr['concluidas'] / $usr['total']) * 100) : 0; @endphp
                    <tr class="hover:bg-purple-50/40 transition-colors">
                        <td class="px-5 py-3">
                            <span class="font-semibold text-gray-800">{{ $usr['nome'] }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $usr['nivel'] }}</td>
                        <td class="px-5 py-3 text-right font-bold text-gray-900">{{ $usr['total'] }}</td>
                        <td class="px-5 py-3 text-right font-bold text-emerald-600">{{ $usr['concluidas'] }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $pct === 100 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-purple-500' : 'bg-amber-500') }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs font-bold w-10 text-right {{ $pct === 100 ? 'text-emerald-600' : 'text-gray-600' }}">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-300 text-sm">Nenhum técnico encontrado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Paleta profissional (IBM Carbon-inspired)
    const P = {
        indigo: '#6366f1', teal: '#14b8a6', amber: '#f59e0b', rose: '#f43f5e',
        sky: '#0ea5e9', emerald: '#10b981', violet: '#8b5cf6', orange: '#f97316',
        cyan: '#06b6d4', lime: '#84cc16', fuchsia: '#d946ef', slate: '#64748b'
    };
    const palette = [P.indigo, P.teal, P.amber, P.rose, P.sky, P.emerald, P.violet, P.orange, P.cyan, P.lime, P.fuchsia, P.slate];

    Chart.defaults.font.family = "'Inter', 'Segoe UI', system-ui, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.legend.labels.boxWidth = 10;
    Chart.defaults.plugins.legend.labels.padding = 12;

    // === Evolução Mensal ===
    const mesData = @json($porMes);
    if (mesData.length > 0) {
        new Chart(document.getElementById('chartMensal'), {
            type: 'line',
            data: {
                labels: mesData.map(m => {
                    const [y, mo] = m.mes.split('-');
                    return ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'][parseInt(mo)-1] + '/' + y.slice(2);
                }),
                datasets: [
                    {
                        label: 'Total',
                        data: mesData.map(m => m.total),
                        borderColor: P.indigo,
                        backgroundColor: P.indigo + '18',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: P.indigo,
                        pointBorderWidth: 2,
                        borderWidth: 2.5,
                    },
                    {
                        label: 'Concluídas',
                        data: mesData.map(m => m.concluidas),
                        borderColor: P.emerald,
                        backgroundColor: P.emerald + '18',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: P.emerald,
                        pointBorderWidth: 2,
                        borderWidth: 2.5,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: { legend: { position: 'top' }, tooltip: { backgroundColor: '#1e293b', cornerRadius: 8, padding: 10 } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // === Status Doughnut ===
    const statusData = @json($porStatus);
    const statusLabels = ['Aberta', 'Em Andamento', 'Concluída', 'Cancelada'];
    const statusValues = [statusData.aberta, statusData.em_andamento, statusData.concluida, statusData.cancelada];
    const statusColors = ['#94a3b8', P.amber, P.emerald, P.rose];
    if (statusValues.some(v => v > 0)) {
        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: { labels: statusLabels, datasets: [{ data: statusValues, backgroundColor: statusColors, borderWidth: 0, hoverOffset: 6 }] },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 14 } },
                    tooltip: { backgroundColor: '#1e293b', cornerRadius: 8, padding: 10 }
                }
            }
        });
    }

    // === Ações por Tipo (horizontal bar) ===
    const acoesTipoData = @json($acoesPorTipoFormatado);
    if (acoesTipoData.length > 0) {
        new Chart(document.getElementById('chartAcoesTipo'), {
            type: 'bar',
            data: {
                labels: acoesTipoData.map(a => a.nome.length > 50 ? a.nome.substring(0, 50) + '…' : a.nome),
                datasets: [{
                    data: acoesTipoData.map(a => a.total),
                    backgroundColor: acoesTipoData.map((_, i) => palette[i % palette.length] + 'cc'),
                    borderColor: acoesTipoData.map((_, i) => palette[i % palette.length]),
                    borderWidth: 1,
                    borderRadius: 4,
                    barThickness: 22,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', cornerRadius: 8, padding: 10 } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }

    // === Município stacked bar ===
    const munData = @json($porMunicipio->take(12));
    if (munData.length > 0) {
        new Chart(document.getElementById('chartMunicipio'), {
            type: 'bar',
            data: {
                labels: munData.map(m => m.nome),
                datasets: [
                    { label: 'Estadual', data: munData.map(m => m.estadual), backgroundColor: P.indigo + 'cc', borderColor: P.indigo, borderWidth: 1, borderRadius: 4, barThickness: 20 },
                    { label: 'Municipal', data: munData.map(m => m.municipal), backgroundColor: P.teal + 'cc', borderColor: P.teal, borderWidth: 1, borderRadius: 4, barThickness: 20 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { position: 'top' }, tooltip: { backgroundColor: '#1e293b', cornerRadius: 8, padding: 10 } },
                scales: {
                    x: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    y: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }
});
</script>
@endsection
