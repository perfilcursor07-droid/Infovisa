@props([
    'contadores' => [],
    'urgencias' => [],
    'isGestorOuAdmin' => false,
])

@php
    // Map existing $stats keys to summary bar items
    // The controller passes $stats with keys like 'ordens_servico_andamento', 'documentos_pendentes_assinatura', etc.
    // $urgencias will be added in Task 3; for now we handle its absence gracefully.
    $items = [
        [
            'label' => 'OS Ativas',
            'value' => $contadores['ordens_servico_andamento'] ?? $contadores['os_ativas'] ?? 0,
            'urgent' => ($urgencias['os_atrasadas'] ?? 0) > 0,
            'urgentCount' => $urgencias['os_atrasadas'] ?? 0,
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            'color' => 'blue',
            'anchor' => '#secao-minhas-acoes',
        ],
        [
            'label' => 'Assinaturas',
            'value' => $contadores['documentos_pendentes_assinatura'] ?? $contadores['docs_assinatura'] ?? 0,
            'urgent' => false,
            'urgentCount' => 0,
            'icon' => 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z',
            'color' => 'amber',
            'anchor' => '#secao-minhas-acoes',
        ],
        [
            'label' => 'Prazos',
            'value' => $contadores['documentos_vencendo'] ?? $contadores['docs_prazo'] ?? 0,
            'urgent' => ($urgencias['docs_vencidos'] ?? 0) > 0,
            'urgentCount' => $urgencias['docs_vencidos'] ?? 0,
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => 'rose',
            'anchor' => '#secao-minhas-acoes',
        ],
        [
            'label' => 'Processos',
            'value' => $contadores['processos_atribuidos'] ?? $contadores['processos_diretos'] ?? 0,
            'urgent' => false,
            'urgentCount' => 0,
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'color' => 'indigo',
            'anchor' => '#secao-minhas-acoes',
        ],
    ];

    if ($isGestorOuAdmin) {
        $items[] = [
            'label' => 'Aprovações',
            'value' => $contadores['total_pendentes_aprovacao'] ?? $contadores['docs_aprovacao_setor'] ?? 0,
            'urgent' => ($urgencias['docs_aprovacao_atrasados'] ?? 0) > 0,
            'urgentCount' => $urgencias['docs_aprovacao_atrasados'] ?? 0,
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => 'purple',
            'anchor' => '#secao-demandas-setor',
        ];
        $items[] = [
            'label' => 'Cadastros',
            'value' => $contadores['estabelecimentos_pendentes'] ?? $contadores['cadastros_pendentes'] ?? 0,
            'urgent' => false,
            'urgentCount' => 0,
            'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            'color' => 'teal',
            'anchor' => '#secao-demandas-setor',
        ];
    }

    $colorMap = [
        'blue' => ['bg' => 'bg-blue-50', 'icon_bg' => 'bg-blue-100', 'icon_text' => 'text-blue-600', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'urgent_bg' => 'bg-red-50', 'urgent_border' => 'border-red-300'],
        'amber' => ['bg' => 'bg-amber-50', 'icon_bg' => 'bg-amber-100', 'icon_text' => 'text-amber-600', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'urgent_bg' => 'bg-red-50', 'urgent_border' => 'border-red-300'],
        'rose' => ['bg' => 'bg-rose-50', 'icon_bg' => 'bg-rose-100', 'icon_text' => 'text-rose-600', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'urgent_bg' => 'bg-red-50', 'urgent_border' => 'border-red-300'],
        'indigo' => ['bg' => 'bg-indigo-50', 'icon_bg' => 'bg-indigo-100', 'icon_text' => 'text-indigo-600', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'urgent_bg' => 'bg-red-50', 'urgent_border' => 'border-red-300'],
        'purple' => ['bg' => 'bg-purple-50', 'icon_bg' => 'bg-purple-100', 'icon_text' => 'text-purple-600', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'urgent_bg' => 'bg-red-50', 'urgent_border' => 'border-red-300'],
        'teal' => ['bg' => 'bg-teal-50', 'icon_bg' => 'bg-teal-100', 'icon_text' => 'text-teal-600', 'text' => 'text-teal-700', 'border' => 'border-teal-200', 'urgent_bg' => 'bg-red-50', 'urgent_border' => 'border-red-300'],
    ];
@endphp

<div class="grid grid-cols-2 sm:grid-cols-3 {{ $isGestorOuAdmin ? 'lg:grid-cols-6' : 'lg:grid-cols-4' }} gap-2">
    @foreach($items as $item)
        @php
            $colors = $colorMap[$item['color']];
            $isEmpty = $item['value'] == 0;
            $isUrgent = $item['urgent'];
        @endphp
        <a href="{{ $item['anchor'] }}"
           class="relative flex items-center gap-2.5 px-3 py-2.5 rounded-lg border transition hover:shadow-sm {{ $isUrgent ? $colors['urgent_bg'] . ' ' . $colors['urgent_border'] : $colors['bg'] . ' ' . $colors['border'] }} {{ $isEmpty ? 'opacity-50' : '' }}"
           onclick="event.preventDefault(); document.querySelector('{{ $item['anchor'] }}')?.scrollIntoView({behavior: 'smooth', block: 'start'})">
            <div class="w-8 h-8 rounded-lg {{ $colors['icon_bg'] }} flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 {{ $colors['icon_text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-lg font-bold {{ $isUrgent ? 'text-red-700' : 'text-gray-900' }} leading-none">{{ $item['value'] }}</p>
                <p class="text-[10px] {{ $isUrgent ? 'text-red-600' : 'text-gray-500' }} truncate">{{ $item['label'] }}</p>
            </div>
            @if($isUrgent && $item['urgentCount'] > 0)
                <span class="absolute -top-1 -right-1 text-[9px] px-1.5 py-0.5 bg-red-500 text-white rounded-full font-bold leading-none">
                    {{ $item['urgentCount'] }}
                </span>
            @endif
        </a>
    @endforeach
</div>
