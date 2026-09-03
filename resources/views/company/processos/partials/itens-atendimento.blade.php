@php
    $itensAtendimento = $docDigital->itensAtendimento ?? collect();
    $totalItensAtendimento = $itensAtendimento->count();
    $itensComUpload = $itensAtendimento->filter(fn ($item) => in_array($item->respostaAtual?->status, ['pendente', 'aprovado'], true))->count();
    $itensAprovados = $itensAtendimento->filter(fn ($item) => $item->respostaAtual?->status === 'aprovado')->count();
    $itensFaltantes = $totalItensAtendimento - $itensComUpload;
    $compacto = $compacto ?? false;
@endphp

@if($totalItensAtendimento > 0)
    @if($compacto)
        <div class="mt-3 ml-11 p-2.5 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold text-amber-900">Itens para atendimento: {{ $itensComUpload }}/{{ $totalItensAtendimento }} com upload</p>
                    <p class="text-[11px] {{ $itensFaltantes > 0 ? 'text-amber-700' : 'text-green-700' }} mt-0.5">
                        {{ $itensFaltantes > 0 ? $itensFaltantes . ' item(ns) ainda precisam de arquivo' : 'Todos os itens foram enviados' }}
                    </p>
                </div>
                <a href="#itens-atendimento-doc-{{ $docDigital->id }}" class="text-xs font-semibold text-amber-800 hover:text-amber-950 underline underline-offset-2">Ver e responder itens</a>
            </div>
        </div>
    @else
        <div id="itens-atendimento-doc-{{ $docDigital->id }}" class="mt-3 sm:ml-12 border border-amber-200 rounded-lg bg-amber-50/50 scroll-mt-24">
            <div class="px-3 py-2 border-b border-amber-200 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold text-amber-900">Itens a serem atendidos</p>
                    <p class="text-[11px] text-amber-700">Anexe um PDF separado em cada item.</p>
                </div>
                <div class="flex items-center gap-2 text-[10px] font-semibold">
                    <span class="px-2 py-1 bg-white border border-amber-200 rounded-full">{{ $itensComUpload }}/{{ $totalItensAtendimento }} enviados</span>
                    <span class="px-2 py-1 bg-white border border-green-200 text-green-700 rounded-full">{{ $itensAprovados }} atendidos</span>
                </div>
            </div>

            <div class="divide-y divide-amber-100">
                @foreach($itensAtendimento as $itemAtendimento)
                    @php
                        $respostaItem = $itemAtendimento->respostaAtual;
                        $statusItem = $respostaItem?->status ?? 'nao_enviado';
                        $podeEnviarItem = $docDigital->permiteResposta() && in_array($statusItem, ['nao_enviado', 'rejeitado'], true);
                        $classesStatusItem = [
                            'nao_enviado' => 'bg-gray-100 text-gray-700',
                            'pendente' => 'bg-yellow-100 text-yellow-800',
                            'aprovado' => 'bg-green-100 text-green-800',
                            'rejeitado' => 'bg-red-100 text-red-800',
                        ];
                        $textosStatusItem = [
                            'nao_enviado' => 'Upload pendente',
                            'pendente' => 'Em análise',
                            'aprovado' => 'Atendido',
                            'rejeitado' => 'Correção solicitada',
                        ];
                    @endphp
                    <div class="p-3">
                        <div class="flex items-start gap-2">
                            <span class="flex-shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">{{ $itemAtendimento->ordem }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                    <p class="text-xs font-semibold text-gray-900 whitespace-pre-line">{{ $itemAtendimento->descricao }}</p>
                                    <span class="self-start px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $classesStatusItem[$statusItem] }}">{{ $textosStatusItem[$statusItem] }}</span>
                                </div>
                                @if($itemAtendimento->embasamento_legal)
                                    <div class="mt-2 p-2 bg-white border border-gray-200 rounded text-[11px] text-gray-600 whitespace-pre-line">
                                        <span class="font-semibold text-gray-700">Embasamento legal:</span> {{ $itemAtendimento->embasamento_legal }}
                                    </div>
                                @endif

                                @if($respostaItem)
                                    <div class="mt-2 flex flex-col gap-2 p-2 bg-white border rounded {{ $statusItem === 'rejeitado' ? 'border-red-200' : 'border-gray-200' }} sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-medium text-gray-800 truncate">{{ $respostaItem->nome_original }}</p>
                                            <p class="text-[10px] text-gray-500">{{ $respostaItem->tamanho_formatado }} · {{ $respostaItem->created_at->format('d/m/Y H:i') }}</p>
                                            @if($statusItem === 'rejeitado' && $respostaItem->motivo_rejeicao)
                                                <p class="text-[11px] text-red-700 mt-1"><span class="font-semibold">Motivo:</span> {{ $respostaItem->motivo_rejeicao }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            <a target="_blank" href="{{ route('company.processos.documento-digital.resposta.visualizar', [$processo->id, $docDigital->id, $respostaItem->id]) }}" class="px-2 py-1 text-[10px] font-semibold text-blue-700 bg-blue-50 rounded hover:bg-blue-100">Ver</a>
                                            <a href="{{ route('company.processos.documento-digital.resposta.download', [$processo->id, $docDigital->id, $respostaItem->id]) }}" class="px-2 py-1 text-[10px] font-semibold text-gray-700 bg-gray-100 rounded hover:bg-gray-200">Baixar</a>
                                            @if($statusItem === 'pendente' && (int) $respostaItem->usuario_externo_id === (int) auth('externo')->id())
                                                <form method="POST" action="{{ route('company.processos.documento-digital.resposta.excluir', [$processo->id, $docDigital->id, $respostaItem->id]) }}" onsubmit="return confirm('Excluir este arquivo enviado?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2 py-1 text-[10px] font-semibold text-red-700 bg-red-50 rounded hover:bg-red-100">Excluir</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($podeEnviarItem)
                                    <form method="POST" enctype="multipart/form-data"
                                          action="{{ route('company.processos.documento-digital.resposta', [$processo->id, $docDigital->id]) }}"
                                          class="mt-2 p-2 bg-white border border-dashed border-amber-300 rounded-lg">
                                        @csrf
                                        <input type="hidden" name="documento_item_atendimento_id" value="{{ $itemAtendimento->id }}">
                                        <div class="flex flex-col gap-2 lg:flex-row lg:items-end">
                                            <div class="flex-1">
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">{{ $statusItem === 'rejeitado' ? 'Enviar arquivo corrigido' : 'Anexar resposta deste item' }}</label>
                                                <input type="file" name="arquivo" accept="application/pdf,.pdf" required
                                                       class="block w-full text-[11px] text-gray-600 file:mr-2 file:py-1.5 file:px-2 file:rounded file:border-0 file:text-[11px] file:font-semibold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200">
                                            </div>
                                            <div class="flex-1">
                                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Observação (opcional)</label>
                                                <input type="text" name="observacoes" maxlength="1000" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-amber-500 focus:border-amber-500" placeholder="Descreva o arquivo enviado">
                                            </div>
                                            <button type="submit" class="px-3 py-2 text-xs font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700">Enviar item</button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif
