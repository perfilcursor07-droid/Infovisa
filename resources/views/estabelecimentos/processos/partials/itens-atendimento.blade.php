@php
    $itensAtendimento = $docDigital->itensAtendimento ?? collect();
    $totalItensAtendimento = $itensAtendimento->count();
    $itensComUpload = $itensAtendimento->filter(fn ($item) => in_array($item->respostaAtual?->status, ['pendente', 'aprovado'], true))->count();
    $itensAprovados = $itensAtendimento->filter(fn ($item) => $item->respostaAtual?->status === 'aprovado')->count();
@endphp

@if($totalItensAtendimento > 0)
    <div class="mx-3 mb-3 border border-amber-200 rounded-lg bg-amber-50/50">
        <div class="px-3 py-2 border-b border-amber-200 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold text-amber-900">Acompanhamento dos itens exigidos</p>
                <p class="text-[10px] text-amber-700">{{ $itensComUpload }}/{{ $totalItensAtendimento }} com upload · {{ $itensAprovados }} aprovado(s) · {{ $totalItensAtendimento - $itensComUpload }} faltante(s)</p>
            </div>
            <div class="w-full sm:w-40 h-1.5 bg-amber-100 rounded-full overflow-hidden">
                <div class="h-full bg-amber-500 rounded-full" style="width: {{ $totalItensAtendimento > 0 ? round(($itensComUpload / $totalItensAtendimento) * 100) : 0 }}%"></div>
            </div>
        </div>

        <div class="divide-y divide-amber-100">
            @foreach($itensAtendimento as $itemAtendimento)
                @php
                    $respostaItem = $itemAtendimento->respostaAtual;
                    $statusItem = $respostaItem?->status ?? 'nao_enviado';
                    $classesStatusItem = [
                        'nao_enviado' => 'bg-gray-100 text-gray-700',
                        'pendente' => 'bg-yellow-100 text-yellow-800',
                        'aprovado' => 'bg-green-100 text-green-800',
                        'rejeitado' => 'bg-red-100 text-red-800',
                    ];
                    $textosStatusItem = [
                        'nao_enviado' => 'Aguardando upload',
                        'pendente' => 'Aguardando análise',
                        'aprovado' => 'Atendido',
                        'rejeitado' => 'Rejeitado',
                    ];
                @endphp
                <div class="p-3" x-data="{ rejeitarAberto: false }">
                    <div class="flex items-start gap-2">
                        <span class="flex-shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">{{ $itemAtendimento->ordem }}</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                <p class="text-xs font-semibold text-gray-900 whitespace-pre-line">{{ $itemAtendimento->descricao }}</p>
                                <span class="self-start px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $classesStatusItem[$statusItem] }}">{{ $textosStatusItem[$statusItem] }}</span>
                            </div>
                            @if($itemAtendimento->embasamento_legal)
                                <p class="mt-1.5 text-[11px] text-gray-600 whitespace-pre-line"><span class="font-semibold">Embasamento legal:</span> {{ $itemAtendimento->embasamento_legal }}</p>
                            @endif

                            @if($respostaItem)
                                <div class="mt-2 p-2 bg-white border border-gray-200 rounded-lg">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <button type="button"
                                                    @click="abrirModalRespostas({{ $docDigital->id }}, '{{ addslashes($docDigital->nome ?? $docDigital->tipoDocumento->nome) }}', '{{ $docDigital->numero_documento }}', '{{ route('admin.estabelecimentos.processos.visualizar', [$estabelecimento->id, $processo->id, $docDigital->id]) }}', {{ $respostaItem->id }})"
                                                    class="text-[11px] font-semibold text-blue-700 hover:underline truncate max-w-full">
                                                📎 {{ $respostaItem->nome_original }}
                                            </button>
                                            <p class="text-[10px] text-gray-500 mt-0.5">Enviado em {{ $respostaItem->created_at->format('d/m/Y H:i') }} por {{ $respostaItem->usuarioExterno->nome ?? 'N/D' }}</p>
                                            @if($statusItem === 'rejeitado' && $respostaItem->motivo_rejeicao)
                                                <p class="text-[10px] text-red-700 mt-1"><span class="font-semibold">Motivo:</span> {{ $respostaItem->motivo_rejeicao }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <a href="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.download', [$estabelecimento->id, $processo->id, $docDigital->id, $respostaItem->id]) }}" class="px-2 py-1 text-[10px] font-semibold text-gray-700 bg-gray-100 rounded hover:bg-gray-200">Baixar</a>
                                            @if($statusItem === 'pendente')
                                                <form method="POST" action="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.aprovar', [$estabelecimento->id, $processo->id, $docDigital->id, $respostaItem->id]) }}" onsubmit="return confirm('Aprovar a resposta deste item?')">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 text-[10px] font-semibold text-green-700 bg-green-100 rounded hover:bg-green-200">Aprovar</button>
                                                </form>
                                                <button type="button" @click="rejeitarAberto = !rejeitarAberto" class="px-2 py-1 text-[10px] font-semibold text-red-700 bg-red-100 rounded hover:bg-red-200">Rejeitar</button>
                                            @else
                                                <form method="POST" action="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.revalidar', [$estabelecimento->id, $processo->id, $docDigital->id, $respostaItem->id]) }}">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 text-[10px] font-semibold text-gray-700 bg-gray-100 rounded hover:bg-gray-200">Reavaliar</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    @if($statusItem === 'pendente')
                                        <form x-show="rejeitarAberto" x-cloak method="POST" action="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.rejeitar', [$estabelecimento->id, $processo->id, $docDigital->id, $respostaItem->id]) }}" class="mt-2 pt-2 border-t border-red-100">
                                            @csrf
                                            <label class="block text-[10px] font-semibold text-red-700 mb-1">Motivo da rejeição</label>
                                            <div class="flex flex-col gap-2 sm:flex-row">
                                                <textarea name="motivo_rejeicao" rows="2" required maxlength="1000" class="flex-1 px-2 py-1.5 text-xs border border-red-200 rounded focus:ring-red-500 focus:border-red-500" placeholder="Explique o que precisa ser corrigido"></textarea>
                                                <button type="submit" class="self-end px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded hover:bg-red-700">Confirmar rejeição</button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <p class="mt-2 text-[11px] font-medium text-gray-500">A empresa ainda não anexou resposta para este item.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
