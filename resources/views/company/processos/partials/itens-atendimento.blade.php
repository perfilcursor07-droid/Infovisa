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
                    <p class="text-xs font-semibold text-amber-900">Respostas da empresa: {{ $itensComUpload }} de {{ $totalItensAtendimento }} enviadas</p>
                    <p class="text-[11px] {{ $itensFaltantes > 0 ? 'text-amber-700' : 'text-green-700' }} mt-0.5">
                        {{ $itensFaltantes > 0 ? $itensFaltantes . ' item(ns) precisam de envio ou correção' : ($itensAprovados === $totalItensAtendimento ? 'Todas as exigências foram atendidas' : 'Respostas enviadas. Aguarde a análise dos itens pendentes.') }}
                    </p>
                </div>
                <a href="#itens-atendimento-doc-{{ $docDigital->id }}" class="text-xs font-semibold text-amber-800 hover:text-amber-950 underline underline-offset-2">Ver e responder itens</a>
            </div>
        </div>
    @else
        <div id="itens-atendimento-doc-{{ $docDigital->id }}" x-data="{ filtroItens: 'todos' }" class="mt-3 sm:ml-12 border border-amber-200 rounded-lg bg-gray-50 scroll-mt-24 overflow-hidden">
            <div class="px-3 py-2 border-b border-amber-200 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-900">Responda às exigências deste documento</p>
                    <p class="text-sm text-gray-600 mt-1">Leia o item, selecione o PDF e clique em “Enviar resposta”. Repita para cada exigência.</p>
                </div>
                <div class="flex items-center gap-2 text-[10px] font-semibold">
                    <span class="px-2 py-1 bg-white border border-amber-200 rounded-full">{{ $itensComUpload }}/{{ $totalItensAtendimento }} enviados</span>
                    <span class="px-2 py-1 bg-white border border-green-200 text-green-700 rounded-full">{{ $itensAprovados }} atendidos</span>
                </div>
            </div>
            <x-itens-atendimento-resumo :itens="$itensAtendimento" :empresa="true" />
            <p class="px-4 pt-3 text-xs text-gray-600">Envie um arquivo por vez. Aguarde a conclusão do envio antes de selecionar o PDF do próximo item.</p>

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
                            'nao_enviado' => 'Falta enviar sua resposta',
                            'pendente' => 'Em análise',
                            'aprovado' => 'Atendido',
                            'rejeitado' => 'Correção solicitada',
                        ];
                    @endphp
                    <div class="p-4" x-show="filtroItens === 'todos' || filtroItens === '{{ $statusItem }}'">
                        <div class="flex items-start gap-2">
                            <span class="flex-shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold">{{ $itemAtendimento->ordem }}</span>
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
                                    @if($respostaItem->observacoes)
                                        <p class="mt-2 text-xs text-gray-600 whitespace-pre-line"><strong>Sua observação:</strong> {{ $respostaItem->observacoes }}</p>
                                    @endif
                                    @if($statusItem === 'pendente')
                                        <p class="mt-2 text-xs text-blue-700">Resposta enviada. Aguarde a análise da Vigilância Sanitária.</p>
                                    @elseif($statusItem === 'aprovado')
                                        <p class="mt-2 text-xs text-green-700">Item atendido. Nenhuma ação necessária.</p>
                                    @endif
                                @endif

                                @if($podeEnviarItem)
                                    <form method="POST" enctype="multipart/form-data"
                                          x-data="{ nomeArquivo: '', erroArquivo: '', enviando: false }"
                                          @submit="if (enviando || !nomeArquivo || erroArquivo) { $event.preventDefault(); return; } enviando = true"
                                          action="{{ route('company.processos.documento-digital.resposta', [$processo->id, $docDigital->id]) }}"
                                          class="mt-3 p-4 bg-white border border-dashed border-blue-300 rounded-lg">
                                        @csrf
                                        <input type="hidden" name="documento_item_atendimento_id" value="{{ $itemAtendimento->id }}">
                                        @if((int) old('documento_item_atendimento_id') === (int) $itemAtendimento->id)
                                            @error('arquivo')
                                                <p class="mb-3 text-sm text-red-700" role="alert">{{ $message }} Selecione o PDF novamente para reenviar.</p>
                                            @enderror
                                            @error('observacoes')
                                                <p class="mb-3 text-sm text-red-700" role="alert">{{ $message }}</p>
                                            @enderror
                                        @endif
                                        <div class="flex flex-col gap-2 lg:flex-row lg:items-end">
                                            <div class="flex-1">
                                                <label for="resposta-arquivo-{{ $itemAtendimento->id }}" class="block text-sm font-semibold text-gray-700 mb-2">1. {{ $statusItem === 'rejeitado' ? 'Selecione o PDF corrigido' : 'Selecione a resposta em PDF' }}</label>
                                                <input id="resposta-arquivo-{{ $itemAtendimento->id }}" type="file" name="arquivo" accept="application/pdf,.pdf" required
                                                       @change="const arquivo = $event.target.files[0]; erroArquivo = ''; nomeArquivo = ''; if (arquivo) { if (!/\.pdf$/i.test(arquivo.name)) erroArquivo = 'Selecione um arquivo PDF.'; else if (arquivo.size > 30 * 1024 * 1024) erroArquivo = 'O PDF deve ter no máximo 30 MB.'; if (erroArquivo) $event.target.value = ''; else nomeArquivo = arquivo.name; }"
                                                       class="block w-full text-sm text-gray-600 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-800 hover:file:bg-blue-100">
                                                <p class="mt-2 text-xs text-gray-500">Um PDF por item · máximo 30 MB.</p>
                                                <p x-show="nomeArquivo" x-cloak class="mt-2 text-xs text-blue-700 break-all" x-text="'Pronto para enviar: ' + nomeArquivo"></p>
                                                <p x-show="erroArquivo" x-cloak role="alert" class="mt-2 text-xs text-red-700" x-text="erroArquivo"></p>
                                            </div>
                                            <div class="flex-1">
                                                <label for="resposta-observacao-{{ $itemAtendimento->id }}" class="block text-sm font-semibold text-gray-700 mb-1">Observação (opcional)</label>
                                                <input id="resposta-observacao-{{ $itemAtendimento->id }}" type="text" name="observacoes" maxlength="1000" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Ex.: Certificado atualizado em anexo">
                                            </div>
                                            <button type="submit" :disabled="!nomeArquivo || !!erroArquivo || enviando" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" x-text="enviando ? 'Enviando…' : '2. Enviar resposta do item {{ $itemAtendimento->ordem }}'">Enviar resposta</button>
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
