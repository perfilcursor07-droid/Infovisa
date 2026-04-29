@extends('layouts.admin')

@section('title', 'Relatório de Ações por Atividade')

@section('content')
<div class="space-y-6 max-w-[1400px] mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-400 mb-1">
                <a href="{{ route('admin.relatorios.index') }}" class="hover:text-gray-600 transition">Relatórios</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-700">Ações por Atividade</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Ações por Atividade</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $escopoVisual }}</p>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 -mr-4 -mt-4 rounded-full bg-slate-100 opacity-60"></div>
            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total OS</p>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ number_format($totalOS, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 -mr-4 -mt-4 rounded-full bg-emerald-100 opacity-60"></div>
            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Concluídas</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($totalConcluidas, 0, ',', '.') }}</p>
            @if($totalOS > 0)
            <p class="text-[10px] text-gray-400 mt-0.5">{{ round(($totalConcluidas / $totalOS) * 100) }}% do total</p>
            @endif
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 -mr-4 -mt-4 rounded-full bg-amber-100 opacity-60"></div>
            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Em Andamento</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($totalEmAndamento, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 -mr-4 -mt-4 rounded-full bg-red-100 opacity-60"></div>
            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Canceladas</p>
            <p class="text-2xl font-extrabold text-red-500 mt-1">{{ number_format($totalCanceladas, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 -mr-4 -mt-4 rounded-full bg-indigo-100 opacity-60"></div>
            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Estadual</p>
            <p class="text-2xl font-extrabold text-indigo-600 mt-1">{{ number_format($totalEstadual, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 -mr-4 -mt-4 rounded-full bg-teal-100 opacity-60"></div>
            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Municipal</p>
            <p class="text-2xl font-extrabold text-teal-600 mt-1">{{ number_format($totalMunicipal, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.relatorios.acoes-atividade') }}" class="flex flex-wrap items-end gap-3">
            @if($isAdmin || $isEstadual)
            <div class="w-32">
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Competência</label>
                <select name="competencia" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition">
                    <option value="">Todas</option>
                    <option value="estadual" @selected(request('competencia') === 'estadual')>Estadual</option>
                    <option value="municipal" @selected(request('competencia') === 'municipal')>Municipal</option>
                </select>
            </div>
            @endif
            @if($isAdmin || $isEstadual)
            <div class="w-44">
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Município</label>
                <select name="municipio_id" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition">
                    <option value="">Todos</option>
                    @foreach($municipios as $municipio)
                        <option value="{{ $municipio->id }}" @selected((string) request('municipio_id') === (string) $municipio->id)>{{ $municipio->nome }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="w-48">
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Técnico</label>
                <select name="usuario_id" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition">
                    <option value="">Todos</option>
                    @foreach($usuarios as $usr)
                        <option value="{{ $usr->id }}" @selected((string) request('usuario_id') === (string) $usr->id)>{{ $usr->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-32">
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition">
                    <option value="">Todos</option>
                    <option value="aberta" @selected(request('status') === 'aberta')>Aberta</option>
                    <option value="em_andamento" @selected(request('status') === 'em_andamento')>Em Andamento</option>
                    <option value="concluida" @selected(request('status') === 'concluida')>Concluída</option>
                    <option value="cancelada" @selected(request('status') === 'cancelada')>Cancelada</option>
                </select>
            </div>
            <div class="w-36">
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">De</label>
                <input type="date" name="data_inicio" value="{{ request('data_inicio') }}"
                       class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition">
            </div>
            <div class="w-36">
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Até</label>
                <input type="date" name="data_fim" value="{{ request('data_fim') }}"
                       class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 shadow-sm transition">Filtrar</button>
                <a href="{{ route('admin.relatorios.acoes-atividade') }}" class="px-4 py-2 bg-white text-gray-600 text-sm font-medium rounded-lg border border-gray-200 hover:bg-gray-50 transition">Limpar</a>
            </div>
        </form>
    </div>

    {{-- Gráficos Row 1 --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Evolução Mensal (maior) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Evolução Mensal
            </h3>
            <div style="height: 280px;"><canvas id="chartMensal"></canvas></div>
        </div>

        {{-- Competência (doughnut) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                Competência
            </h3>
            <div style="height: 280px;"><canvas id="chartCompetencia"></canvas></div>
        </div>
    </div>

    {{-- Gráficos Row 2 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Ações por Tipo --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                OS por Tipo de Ação
            </h3>
            <div style="height: {{ max(200, count($acoesPorTipoFormatado) * 36) }}px;"><canvas id="chartAcoesTipo"></canvas></div>
        </div>

        {{-- Por Município --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                Top 10 Municípios
            </h3>
            <div style="height: {{ max(200, min(count($porMunicipio), 10) * 36) }}px;"><canvas id="chartMunicipio"></canvas></div>
        </div>
    </div>

    {{-- Tabelas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Tabela: Ações por Tipo --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Detalhamento por Tipo de Ação</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Ação</th>
                            <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Comp.</th>
                            <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total</th>
                            <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Concl.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($acoesPorTipoFormatado as $acao)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-5 py-2.5 text-gray-800 font-medium">{{ $acao['nome'] }}</td>
                            <td class="px-3 py-2.5 text-center">
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $acao['competencia'] === 'estadual' ? 'bg-indigo-50 text-indigo-600' : ($acao['competencia'] === 'municipal' ? 'bg-teal-50 text-teal-600' : 'bg-gray-100 text-gray-500') }}">
                                    {{ ucfirst($acao['competencia']) }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-center font-bold text-gray-700">{{ $acao['total'] }}</td>
                            <td class="px-3 py-2.5 text-center font-medium text-emerald-600">{{ $acao['concluidas'] }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">Nenhuma ação encontrada</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabela: Por Município --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Detalhamento por Município</h3>
            </div>
            <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-white">
                        <tr class="border-b border-gray-100">
                            <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Município</th>
                            <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Total</th>
                            <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Concl.</th>
                            <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Est.</th>
                            <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Mun.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($porMunicipio as $mun)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-5 py-2.5 text-gray-800 font-medium">{{ $mun['nome'] }}</td>
                            <td class="px-3 py-2.5 text-center font-bold text-gray-700">{{ $mun['total'] }}</td>
                            <td class="px-3 py-2.5 text-center font-medium text-emerald-600">{{ $mun['concluidas'] }}</td>
                            <td class="px-3 py-2.5 text-center font-medium text-indigo-600">{{ $mun['estadual'] }}</td>
                            <td class="px-3 py-2.5 text-center font-medium text-teal-600">{{ $mun['municipal'] }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Nenhum dado encontrado</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tabela: Por Técnico --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-800">Produtividade por Técnico</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Técnico</th>
                        <th class="px-3 py-2.5 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Perfil</th>
                        <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Atribuídas</th>
                        <th class="px-3 py-2.5 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Concluídas</th>
                        <th class="px-5 py-2.5 text-center text-[11px] font-semibold text-gray-400 uppercase tracking-wider w-48">Conclusão</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($porUsuarioFormatado as $usr)
                    @php $pct = $usr['total'] > 0 ? round(($usr['concluidas'] / $usr['total']) * 100) : 0; @endphp
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-5 py-3 text-gray-800 font-medium">{{ $usr['nome'] }}</td>
                        <td class="px-3 py-3 text-xs text-gray-500">{{ $usr['nivel'] }}</td>
                        <td class="px-3 py-3 text-center font-bold text-gray-700">{{ $usr['total'] }}</td>
                        <td class="px-3 py-3 text-center font-medium text-emerald-600">{{ $usr['concluidas'] }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $pct === 100 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-indigo-500' : 'bg-amber-500') }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs font-bold w-10 text-right {{ $pct === 100 ? 'text-emerald-600' : 'text-gray-500' }}">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Nenhum técnico encontrado</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.color = '#64748b';

    const palette = {
        indigo: '#6366f1', indigoLight: 'rgba(99,102,241,0.12)',
        teal:   '#14b8a6', tealLight:   'rgba(20,184,166,0.12)',
        emerald:'#10b981', emeraldLight:'rgba(16,185,129,0.12)',
        amber:  '#f59e0b', amberLight:  'rgba(245,158,11,0.12)',
        rose:   '#f43f5e',
        sky:    '#0ea5e9',
        violet: '#8b5cf6',
        slate:  '#475569',
    };

    // Evolução Mensal
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
                        borderColor: palette.indigo,
                        backgroundColor: palette.indigoLight,
                        fill: true, tension: 0.4, pointRadius: 4, pointHoverRadius: 6,
                        pointBackgroundColor: '#fff', pointBorderColor: palette.indigo, pointBorderWidth: 2,
                    },
                    {
                        label: 'Concluídas',
                        data: mesData.map(m => m.concluidas),
                        borderColor: palette.emerald,
                        backgroundColor: palette.emeraldLight,
                        fill: true, tension: 0.4, pointRadius: 4, pointHoverRadius: 6,
                        pointBackgroundColor: '#fff', pointBorderColor: palette.emerald, pointBorderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { boxWidth: 10, usePointStyle: true, pointStyle: 'circle', font: { size: 11 } } } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Competência
    const est = {{ $totalEstadual }}, mun = {{ $totalMunicipal }};
    if (est + mun > 0) {
        new Chart(document.getElementById('chartCompetencia'), {
            type: 'doughnut',
            data: {
                labels: ['Estadual', 'Municipal'],
                datasets: [{ data: [est, mun], backgroundColor: [palette.indigo, palette.teal], borderWidth: 0, hoverOffset: 6 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 12 } } },
                    tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.raw} (${Math.round(ctx.raw/(est+mun)*100)}%)` } }
                }
            }
        });
    }

    // Ações por Tipo
    const acoesTipoData = @json($acoesPorTipoFormatado);
    if (acoesTipoData.length > 0) {
        const barColors = [palette.indigo, palette.teal, palette.violet, palette.sky, palette.emerald, palette.amber, palette.rose, palette.slate];
        new Chart(document.getElementById('chartAcoesTipo'), {
            type: 'bar',
            data: {
                labels: acoesTipoData.map(a => a.nome.length > 50 ? a.nome.substring(0, 50) + '…' : a.nome),
                datasets: [{
                    label: 'Quantidade',
                    data: acoesTipoData.map(a => a.total),
                    backgroundColor: acoesTipoData.map((_, i) => barColors[i % barColors.length]),
                    borderRadius: 6, barThickness: 24,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                    y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }

    // Por Município
    const munData = @json($porMunicipio->take(10));
    if (munData.length > 0) {
        new Chart(document.getElementById('chartMunicipio'), {
            type: 'bar',
            data: {
                labels: munData.map(m => m.nome.length > 25 ? m.nome.substring(0, 25) + '…' : m.nome),
                datasets: [
                    { label: 'Estadual', data: munData.map(m => m.estadual), backgroundColor: palette.indigo, borderRadius: 6, barThickness: 20 },
                    { label: 'Municipal', data: munData.map(m => m.municipal), backgroundColor: palette.teal, borderRadius: 6, barThickness: 20 },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { position: 'top', labels: { boxWidth: 10, usePointStyle: true, pointStyle: 'circle', font: { size: 11 } } } },
                scales: {
                    x: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.04)' } },
                    y: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }
});
</script>
@endsection
