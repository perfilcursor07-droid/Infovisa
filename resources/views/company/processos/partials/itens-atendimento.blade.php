@php
    $itensAtendimento = $docDigital->itensAtendimento ?? collect();
    $totalItensAtendimento = $itensAtendimento->count();
    $itensComUpload = $itensAtendimento->filter(fn ($item) => in_array($item->respostaAtual?->status, ['pendente', 'aprovado'], true))->count();
    $itensAprovados = $itensAtendimento->filter(fn ($item) => $item->respostaAtual?->status === 'aprovado')->count();
    $itensSemResposta = $itensAtendimento->filter(fn ($item) => !$item->respostaAtual)->count();
    $itensComCorrecao = $itensAtendimento->filter(fn ($item) => $item->respostaAtual?->status === 'rejeitado')->count();
    $itensFaltantes = $totalItensAtendimento - $itensComUpload;
    $compacto = $compacto ?? false;
    $urlVisualizarDocumento = route('company.processos.documento-digital.visualizar', [$processo->id, $docDigital->id]);
    $precisaVisualizarDocumento = $docDigital->prazo_notificacao && !$docDigital->prazo_iniciado_em;
@endphp

@if($totalItensAtendimento > 0)
    @if($compacto)
        <div class="mt-3 ml-11 p-2.5 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold text-amber-900">Respostas da empresa: {{ $itensComUpload }} de {{ $totalItensAtendimento }} enviadas</p>
                    <p class="text-[11px] {{ $itensFaltantes > 0 ? 'text-amber-700' : 'text-green-700' }} mt-0.5">
                        {{ $itensFaltantes > 0 ? $itensFaltantes . ' item(ns) precisam de envio ou correção' : ($itensAprovados === $totalItensAtendimento ? 'Todas as exigências foram atendidas' : 'Respostas enviadas. Aguarde a análise dos itens pendentes.') }}
                    </p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <a href="{{ $urlVisualizarDocumento }}" target="_blank" @if($precisaVisualizarDocumento) onclick="recarregarAposVisualizarDocumentoPrazo()" @endif class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-blue-700 bg-white border border-blue-200 rounded-lg hover:bg-blue-50">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                        Visualizar documento
                    </a>
                    @if(!$precisaVisualizarDocumento)
                        <a href="#itens-atendimento-doc-{{ $docDigital->id }}" class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold text-white bg-amber-700 rounded-lg hover:bg-amber-800">Responder agora</a>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div id="itens-atendimento-doc-{{ $docDigital->id }}" x-data="{ filtroItens: {{ $itensSemResposta > 0 ? "'nao_enviado'" : ($itensComCorrecao > 0 ? "'rejeitado'" : "'todos'") }} }" class="mt-3 sm:ml-12 border border-amber-200 rounded-xl bg-white scroll-mt-24 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-amber-200 bg-amber-50/60 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex gap-3">
                    <span class="mt-0.5 inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Responda às exigências deste documento</p>
                        <p class="text-sm text-gray-600 mt-1">Leia cada item, anexe o PDF correspondente e acompanhe a análise da Vigilância Sanitária.</p>
                    </div>
                </div>
                <a href="{{ $urlVisualizarDocumento }}" target="_blank" @if($precisaVisualizarDocumento) onclick="recarregarAposVisualizarDocumentoPrazo()" @endif
                   class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    Visualizar documento
                </a>
            </div>
            <div class="border-b {{ $precisaVisualizarDocumento ? 'border-amber-200 bg-amber-50 text-amber-900' : 'border-blue-100 bg-blue-50 text-blue-900' }} px-4 py-2.5 text-xs">
                @if($precisaVisualizarDocumento)
                    <span class="font-semibold">Ação necessária:</span> visualize o documento para liberar o envio das respostas. Esta página será atualizada automaticamente em seguida.
                @else
                    <span class="font-semibold">Antes de enviar:</span> confira a notificação completa e anexe um PDF para cada exigência abaixo.
                @endif
            </div>
            <x-itens-atendimento-resumo :itens="$itensAtendimento" :empresa="true" />
            <p class="px-4 py-2 text-[11px] text-gray-500">Envie um PDF por item. Aguarde o envio terminar antes de anexar outro arquivo.</p>

            <div class="divide-y divide-amber-100">
                @foreach($itensAtendimento as $itemAtendimento)
                    @php
                        $respostaItem = $itemAtendimento->respostaAtual;
                        $statusItem = $respostaItem?->status ?? 'nao_enviado';
                        $podeEnviarItem = !$precisaVisualizarDocumento && $docDigital->permiteResposta() && in_array($statusItem, ['nao_enviado', 'rejeitado'], true);
                        $classesStatusItem = [
                            'nao_enviado' => 'bg-gray-100 text-gray-700',
                            'pendente' => 'bg-yellow-100 text-yellow-800',
                            'aprovado' => 'bg-green-100 text-green-800',
                            'rejeitado' => 'bg-red-100 text-red-800',
                        ];
                        $textosStatusItem = [
                            'nao_enviado' => 'Falta enviar sua resposta',
                            'pendente' => 'Em análise',
                            'aprovado' => 'Atendido',
                            'rejeitado' => 'Correção solicitada',
                        ];
                    @endphp
                    <div class="p-4" x-show="filtroItens === 'todos' || filtroItens === '{{ $statusItem }}'">
                        <div class="flex items-start gap-2">
                            <span class="flex-shrink-0 inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">{{ $itemAtendimento->ordem }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                    <div><p class="text-xs text-gray-500 mb-1">O que você precisa atender</p><p class="text-sm font-semibold text-gray-900 whitespace-pre-line break-words">{{ $itemAtendimento->descricao }}</p></div>
                                    <span class="self-start px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $classesStatusItem[$statusItem] }}">{{ $textosStatusItem[$statusItem] }}</span>
                                </div>
                                @if($itemAtendimento->embasamento_legal)
                                    <div class="mt-2 p-2 bg-white border border-gray-200 rounded text-[11px] text-gray-600 whitespace-pre-line">
                                        <span class="font-semibold text-gray-700">Embasamento legal:</span> {{ $itemAtendimento->embasamento_legal }}
                                    </div>
                                @endif

                                @if($respostaItem)
                                    <div class="mt-3 flex flex-col gap-3 p-3 bg-white border rounded-xl shadow-sm {{ $statusItem === 'rejeitado' ? 'border-red-200' : 'border-gray-200' }} sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <a target="_blank"
                                               href="{{ route('company.processos.documento-digital.resposta.visualizar', [$processo->id, $docDigital->id, $respostaItem->id]) }}"
                                               class="group flex max-w-full items-center gap-2 rounded-lg text-left text-blue-700 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 group-hover:bg-blue-100">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-6a2.25 2.25 0 0 0-.659-1.591l-3-3A2.25 2.25 0 0 0 14.25 3H6.75A2.25 2.25 0 0 0 4.5 5.25v13.5A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-1.5M14.25 3v4.5A2.25 2.25 0 0 0 16.5 9.75H21M9 15h6m-6-3h3"/>
                                                    </svg>
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="block text-xs font-semibold text-gray-700">Abrir resposta enviada</span>
                                                    <span class="block truncate text-sm font-semibold">{{ $respostaItem->nome_original }}</span>
                                                </span>
                                            </a>
                                            <p class="text-[10px] text-gray-500">{{ $respostaItem->tamanho_formatado }} · {{ $respostaItem->created_at->format('d/m/Y H:i') }}</p>
                                            @if($statusItem === 'rejeitado' && $respostaItem->motivo_rejeicao)
                                                <p class="text-[11px] text-red-700 mt-1"><span class="font-semibold">Motivo:</span> {{ $respostaItem->motivo_rejeicao }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <a target="_blank"
                                               href="{{ route('company.processos.documento-digital.resposta.visualizar', [$processo->id, $docDigital->id, $respostaItem->id]) }}"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                               title="Visualizar comprovante"
                                               aria-label="Visualizar comprovante">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                                </svg>
                                                <span class="sr-only">Visualizar comprovante</span>
                                            </a>
                                            <a href="{{ route('company.processos.documento-digital.resposta.download', [$processo->id, $docDigital->id, $respostaItem->id]) }}"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                               title="Baixar comprovante"
                                               aria-label="Baixar comprovante">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5m0 0 4.5-4.5M12 16.5V3"/>
                                                </svg>
                                                <span class="sr-only">Baixar comprovante</span>
                                            </a>
                                            @if($statusItem === 'pendente' && (int) $respostaItem->usuario_externo_id === (int) auth('externo')->id())
                                                <form method="POST" action="{{ route('company.processos.documento-digital.resposta.excluir', [$processo->id, $docDigital->id, $respostaItem->id]) }}" onsubmit="return confirm('Excluir este arquivo enviado?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-white text-red-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                                            title="Excluir envio"
                                                            aria-label="Excluir envio">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M19.228 5.79 18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                                        </svg>
                                                        <span class="sr-only">Excluir envio</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                    @if($respostaItem->observacoes)
                                        <p class="mt-2 text-xs text-gray-600 whitespace-pre-line"><strong>Sua observação:</strong> {{ $respostaItem->observacoes }}</p>
                                    @endif
                                    @if($statusItem === 'pendente')
                                        <p class="mt-2 text-xs text-blue-700">Resposta enviada. Aguarde a análise da Vigilância Sanitária.</p>
                                    @elseif($statusItem === 'aprovado')
                                        <p class="mt-2 text-xs text-green-700">Item atendido. Nenhuma ação necessária.</p>
                                    @endif
                                @endif

                                @if($precisaVisualizarDocumento && in_array($statusItem, ['nao_enviado', 'rejeitado'], true))
                                    <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                                        <p class="font-semibold">Visualize o documento antes de enviar este item.</p>
                                        <p class="mt-1 text-xs">Ao abrir o documento, a ciência fica registrada e esta página atualiza automaticamente para liberar o envio.</p>
                                        <a href="{{ $urlVisualizarDocumento }}" target="_blank" onclick="recarregarAposVisualizarDocumentoPrazo()"
                                           class="mt-3 inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                            </svg>
                                            Visualizar documento
                                        </a>
                                    </div>
                                @elseif($podeEnviarItem)
                                    <form method="POST" enctype="multipart/form-data"
                                          x-data="{ nomeArquivo: '', erroArquivo: '', enviando: false, selecionarArquivo(event) { const arquivo = event.target.files[0]; this.erroArquivo = ''; this.nomeArquivo = ''; if (!arquivo) return; if (!/\.pdf$/i.test(arquivo.name)) this.erroArquivo = 'Selecione um arquivo PDF.'; else if (arquivo.size > 30 * 1024 * 1024) this.erroArquivo = 'O PDF deve ter no máximo 30 MB.'; if (this.erroArquivo) event.target.value = ''; else this.nomeArquivo = arquivo.name; } }"
                                          @submit="if (enviando || !nomeArquivo || erroArquivo) { $event.preventDefault(); return; } enviando = true"
                                          action="{{ route('company.processos.documento-digital.resposta', [$processo->id, $docDigital->id]) }}"
                                          class="mt-3 rounded-lg border border-blue-200 bg-blue-50/60 p-3">
                                        @csrf
                                        <input type="hidden" name="documento_item_atendimento_id" value="{{ $itemAtendimento->id }}">
                                        @if((int) old('documento_item_atendimento_id') === (int) $itemAtendimento->id)
                                            @error('arquivo')
                                                <p class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">{{ $message }} Selecione o PDF novamente para reenviar.</p>
                                            @enderror
                                            @error('observacoes')
                                                <p class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">{{ $message }}</p>
                                            @enderror
                                        @endif
                                        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(220px,0.7fr)_auto] lg:items-end">
                                            <div>
                                                <p class="mb-2 text-sm font-semibold text-blue-950">{{ $statusItem === 'rejeitado' ? 'Enviar PDF corrigido do item ' . $itemAtendimento->ordem : 'Anexar resposta do item ' . $itemAtendimento->ordem }}</p>
                                                <label :class="nomeArquivo ? 'border-blue-400 bg-white ring-1 ring-blue-300' : 'border-blue-300 bg-white hover:bg-blue-50'"
                                                       class="relative flex cursor-pointer items-center gap-3 rounded-lg border border-dashed px-3 py-2.5 transition">
                                                    <input id="resposta-arquivo-{{ $itemAtendimento->id }}" type="file" name="arquivo" accept="application/pdf,.pdf" required
                                                           @change="selecionarArquivo($event)"
                                                           class="absolute inset-0 h-full w-full cursor-pointer opacity-0">
                                                    <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path d="M12 16V4"></path>
                                                            <path d="M8 8l4-4 4 4"></path>
                                                            <path d="M20 16.5V19a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-2.5"></path>
                                                        </svg>
                                                    </span>
                                                    <span class="min-w-0">
                                                        <span class="block text-sm font-semibold text-blue-900" x-text="nomeArquivo ? 'PDF selecionado' : 'Selecionar PDF'">Selecionar PDF</span>
                                                        <span x-show="!nomeArquivo" class="block text-xs text-gray-500">Somente PDF, máximo 30 MB.</span>
                                                        <span x-show="nomeArquivo" x-cloak class="block max-w-full truncate text-xs font-semibold text-blue-800" x-text="nomeArquivo"></span>
                                                    </span>
                                                </label>
                                                <p x-show="erroArquivo" x-cloak role="alert" class="mt-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" x-text="erroArquivo"></p>
                                            </div>
                                            <div>
                                                <label for="resposta-observacao-{{ $itemAtendimento->id }}" class="block text-sm font-semibold text-gray-700 mb-1">Observação (opcional)</label>
                                                <input id="resposta-observacao-{{ $itemAtendimento->id }}" type="text" name="observacoes" maxlength="1000" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ex.: Certificado atualizado em anexo">
                                            </div>
                                            <button type="submit" :disabled="!nomeArquivo || !!erroArquivo || enviando"
                                                    class="inline-flex w-full items-center justify-center rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-600 disabled:shadow-none lg:w-auto"
                                                    x-text="enviando ? 'Enviando...' : (nomeArquivo ? 'Enviar PDF' : 'Enviar')">Enviar resposta</button>
                                        </div>
                                    </form>
                                @elseif(in_array($statusItem, ['nao_enviado', 'rejeitado'], true))
                                    <p class="mt-3 p-3 bg-amber-50 rounded-lg text-sm text-amber-900">O envio de respostas não está disponível para este documento no momento. Consulte a Vigilância Sanitária para orientação.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif
