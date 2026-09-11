@props(['itens', 'empresa' => false])
@php
    $total = $itens->count();
    $enviados = $itens->filter(fn ($item) => in_array($item->respostaAtual?->status, ['pendente', 'aprovado'], true))->count();
    $percentualEnviado = $total ? round($enviados / $total * 100) : 0;
    $faltando = $itens->filter(fn ($item) => !$item->respostaAtual)->count();
    $emAnalise = $itens->filter(fn ($item) => $item->respostaAtual?->status === 'pendente')->count();
    $atendidos = $itens->filter(fn ($item) => $item->respostaAtual?->status === 'aprovado')->count();
    $correcaoSolicitada = $itens->filter(fn ($item) => $item->respostaAtual?->status === 'rejeitado')->count();
    $filtros = [
        'todos' => ['Todos os itens', $total],
        'nao_enviado' => [$empresa ? 'Falta enviar' : 'Aguardando empresa', $faltando],
        'pendente' => [$empresa ? 'Em análise' : 'Para analisar', $emAnalise],
        'aprovado' => ['Atendidos', $atendidos],
        'rejeitado' => ['Correção solicitada', $correcaoSolicitada],
    ];
    $itensComAcao = $empresa ? ($faltando + $correcaoSolicitada) : ($faltando + $emAnalise);
    if ($empresa) {
        $acaoFiltro = $faltando > 0 ? 'nao_enviado' : 'rejeitado';
        $acaoTexto = $faltando > 0 ? 'falta enviar' : 'corrigir';
    } else {
        $acaoFiltro = $emAnalise > 0 ? 'pendente' : 'nao_enviado';
        $acaoTexto = $emAnalise > 0 ? 'para analisar' : 'aguardando empresa';
    }
@endphp
<div class="px-4 py-2.5 bg-white border-b border-gray-200">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-9 w-14 flex-shrink-0 items-center justify-center rounded-lg bg-blue-50 px-2 text-sm font-extrabold text-blue-800">
                {{ $percentualEnviado }}%
            </span>
            <div>
                <p class="text-xs font-semibold text-gray-900">{{ $enviados }}/{{ $total }} respostas enviadas</p>
                <p class="text-[11px] text-gray-500">Envio ainda depende de análise.</p>
            </div>
        </div>
        @if($itensComAcao > 0)
            <button type="button"
                    @click="filtroItens = '{{ $acaoFiltro }}'"
                    class="inline-flex items-center justify-center gap-1.5 rounded-full border border-amber-300 bg-amber-50 px-3 py-1.5 text-[11px] font-bold text-amber-900 hover:bg-amber-100">
                <span class="inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-600 px-1 text-[10px] text-white">{{ $itensComAcao }}</span>
                {{ $acaoTexto }}
            </button>
        @else
            <span class="inline-flex items-center rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-[11px] font-bold text-green-700">Tudo encaminhado</span>
        @endif
    </div>
    <div class="mt-2.5 h-1.5 bg-gray-100 rounded-full overflow-hidden" role="progressbar" aria-label="Percentual de respostas enviadas" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percentualEnviado }}">
        <div class="h-full bg-blue-600 rounded-full" style="width: {{ $percentualEnviado }}%"></div>
    </div>
    <div class="flex flex-wrap gap-1.5 mt-2.5" aria-label="Filtrar itens por situação">
        @foreach($filtros as $valor => [$rotulo, $quantidade])
            @continue($valor !== 'todos' && $quantidade === 0)
            <button type="button" @click="filtroItens = '{{ $valor }}'" :aria-pressed="filtroItens === '{{ $valor }}'"
                    :class="filtroItens === '{{ $valor }}' ? 'bg-blue-50 border-blue-500 text-blue-800 ring-1 ring-blue-500' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
                    class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1.5 border rounded-full text-[11px] font-semibold transition">
                <span>{{ $rotulo }}</span>
                <span class="inline-flex min-w-5 justify-center rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-700">{{ $quantidade }}</span>
            </button>
        @endforeach
    </div>
</div>
