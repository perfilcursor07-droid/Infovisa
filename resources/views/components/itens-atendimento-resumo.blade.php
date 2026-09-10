@props(['itens', 'empresa' => false])
@php
    $total = $itens->count();
    $enviados = $itens->filter(fn ($item) => in_array($item->respostaAtual?->status, ['pendente', 'aprovado'], true))->count();
    $filtros = [
        'todos' => ['Todos os itens', $total],
        'nao_enviado' => [$empresa ? 'Falta enviar' : 'Aguardando empresa', $itens->filter(fn ($item) => !$item->respostaAtual)->count()],
        'pendente' => [$empresa ? 'Em análise' : 'Para analisar', $itens->filter(fn ($item) => $item->respostaAtual?->status === 'pendente')->count()],
        'aprovado' => ['Atendidos', $itens->filter(fn ($item) => $item->respostaAtual?->status === 'aprovado')->count()],
        'rejeitado' => ['Correção solicitada', $itens->filter(fn ($item) => $item->respostaAtual?->status === 'rejeitado')->count()],
    ];
@endphp
<div class="p-4 bg-white border-b border-gray-200">
    <div class="flex justify-between gap-3 text-xs text-gray-600 mb-2">
        <span>Respostas enviadas: <strong>{{ $enviados }} de {{ $total }}</strong></span>
        <span>Envio não significa aprovação</span>
    </div>
    <div class="h-2 bg-gray-100 rounded-full overflow-hidden" role="progressbar" aria-label="Respostas enviadas" aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $enviados }}">
        <div class="h-full bg-blue-600 rounded-full" style="width: {{ $total ? round($enviados / $total * 100) : 0 }}%"></div>
    </div>
    <div class="flex flex-wrap gap-2 mt-4" aria-label="Filtrar itens por situação">
        @foreach($filtros as $valor => [$rotulo, $quantidade])
            <button type="button" @click="filtroItens = '{{ $valor }}'" :aria-pressed="filtroItens === '{{ $valor }}'"
                    :class="filtroItens === '{{ $valor }}' ? 'bg-blue-50 border-blue-500 text-blue-800 ring-1 ring-blue-500' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
                    class="px-3 py-2 border rounded-lg text-xs font-semibold">
                {{ $rotulo }} <span class="ml-1">{{ $quantidade }}</span>
            </button>
        @endforeach
    </div>
    @foreach($filtros as $valor => [$rotulo, $quantidade])
        @if($quantidade === 0)
            <p x-show="filtroItens === '{{ $valor }}'" x-cloak class="mt-4 text-sm text-gray-500" role="status">Nenhum item nesta situação. Selecione outro filtro para continuar.</p>
        @endif
    @endforeach
</div>
