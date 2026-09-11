@php
    $itensAtendimento = $docDigital->itensAtendimento ?? collect();
    $totalItensAtendimento = $itensAtendimento->count();
    $itensComUpload = $itensAtendimento->filter(fn ($item) => in_array($item->respostaAtual?->status, ['pendente', 'aprovado'], true))->count();
    $itensAprovados = $itensAtendimento->filter(fn ($item) => $item->respostaAtual?->status === 'aprovado')->count();
    $itensAguardandoEmpresa = $itensAtendimento->filter(fn ($item) => !$item->respostaAtual)->count();
    $itensParaAnalise = $itensAtendimento->filter(fn ($item) => $item->respostaAtual?->status === 'pendente')->count();
@endphp

@if($totalItensAtendimento > 0)
    <div x-data="{ filtroItens: {{ $itensParaAnalise > 0 ? "'pendente'" : ($itensAguardandoEmpresa > 0 ? "'nao_enviado'" : "'todos'") }} }" class="mx-3 mb-3 border border-amber-200 rounded-xl bg-white overflow-hidden shadow-sm">
        <div class="px-4 py-3 border-b border-amber-200 bg-amber-50/60 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex gap-3">
                <span class="mt-0.5 inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-bold text-gray-900">Acompanhar e analisar as exigências</p>
                    <p class="text-sm text-gray-600 mt-1">Confira cada PDF enviado, marque o item como atendido ou solicite correção quando necessário.</p>
                </div>
            </div>
        </div>
        <x-itens-atendimento-resumo :itens="$itensAtendimento" />

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
                        'nao_enviado' => 'Aguardando empresa',
                        'pendente' => 'Aguardando análise',
                        'aprovado' => 'Atendido',
                        'rejeitado' => 'Correção solicitada',
                    ];
                @endphp
                <div class="p-4" x-data="{ rejeitarAberto: false }" x-show="filtroItens === 'todos' || filtroItens === '{{ $statusItem }}'">
                    <div class="flex items-start gap-2">
                        <span class="flex-shrink-0 inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">{{ $itemAtendimento->ordem }}</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                <p class="text-sm font-semibold text-gray-900 whitespace-pre-line break-words">{{ $itemAtendimento->descricao }}</p>
                                <span class="self-start px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $classesStatusItem[$statusItem] }}">{{ $textosStatusItem[$statusItem] }}</span>
                            </div>
                            @if($itemAtendimento->embasamento_legal)
                                <p class="mt-1.5 text-[11px] text-gray-600 whitespace-pre-line"><span class="font-semibold">Embasamento legal:</span> {{ $itemAtendimento->embasamento_legal }}</p>
                            @endif

                            @if($respostaItem)
                                <div class="mt-3 p-3 bg-white border border-gray-200 rounded-xl shadow-sm">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <button type="button"
                                                    @click="abrirModalRespostas({{ $docDigital->id }}, '{{ addslashes($docDigital->nome ?? $docDigital->tipoDocumento->nome) }}', '{{ $docDigital->numero_documento }}', '{{ route('admin.estabelecimentos.processos.visualizar', [$estabelecimento->id, $processo->id, $docDigital->id]) }}', {{ $respostaItem->id }})"
                                                    class="group flex max-w-full items-center gap-2 rounded-lg text-left text-blue-700 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 group-hover:bg-blue-100">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-6a2.25 2.25 0 0 0-.659-1.591l-3-3A2.25 2.25 0 0 0 14.25 3H6.75A2.25 2.25 0 0 0 4.5 5.25v13.5A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-1.5M14.25 3v4.5A2.25 2.25 0 0 0 16.5 9.75H21M9 15h6m-6-3h3"/>
                                                    </svg>
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="block text-xs font-semibold text-gray-700">Abrir resposta</span>
                                                    <span class="block truncate text-sm font-semibold">{{ $respostaItem->nome_original }}</span>
                                                </span>
                                            </button>
                                            <p class="text-[10px] text-gray-500 mt-0.5">Enviado em {{ $respostaItem->created_at->format('d/m/Y H:i') }} por {{ $respostaItem->usuarioExterno->nome ?? 'N/D' }}</p>
                                            @if($respostaItem->observacoes)
                                                <p class="text-xs text-gray-700 mt-2 whitespace-pre-line"><strong>Observação da empresa:</strong> {{ $respostaItem->observacoes }}</p>
                                            @endif
                                            @if($statusItem === 'rejeitado' && $respostaItem->motivo_rejeicao)
                                                <p class="text-[10px] text-red-700 mt-1"><span class="font-semibold">Motivo:</span> {{ $respostaItem->motivo_rejeicao }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <a href="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.download', [$estabelecimento->id, $processo->id, $docDigital->id, $respostaItem->id]) }}"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                               title="Baixar comprovante"
                                               aria-label="Baixar comprovante">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 12 12 16.5m0 0 4.5-4.5M12 16.5V3"/>
                                                </svg>
                                                <span class="sr-only">Baixar comprovante</span>
                                            </a>
                                            @if($statusItem === 'pendente')
                                                <form method="POST" action="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.aprovar', [$estabelecimento->id, $processo->id, $docDigital->id, $respostaItem->id]) }}" onsubmit="return confirm('Aprovar a resposta deste item?')">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-green-700 rounded-lg hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 12.75 6 6 9-13.5"/>
                                                        </svg>
                                                        Atendido
                                                    </button>
                                                </form>
                                                <button type="button" @click="rejeitarAberto = !rejeitarAberto" :aria-expanded="rejeitarAberto" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-red-700 bg-red-100 rounded-lg hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.008v.008H12V16.5Zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                                    </svg>
                                                    Corrigir
                                                </button>
                                            @else
                                                <form method="POST" action="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.revalidar', [$estabelecimento->id, $processo->id, $docDigital->id, $respostaItem->id]) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2"
                                                            title="Reavaliar resposta"
                                                            aria-label="Reavaliar resposta">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M21.015 4.356v4.992m0 0h-4.992m4.992 0-3.181-3.183a8.25 8.25 0 0 0-13.803 3.7"/>
                                                        </svg>
                                                        <span class="sr-only">Reavaliar resposta</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    @if($statusItem === 'pendente')
                                        <form x-show="rejeitarAberto" x-cloak method="POST" action="{{ route('admin.estabelecimentos.processos.documento-digital.resposta.rejeitar', [$estabelecimento->id, $processo->id, $docDigital->id, $respostaItem->id]) }}" class="mt-2 pt-2 border-t border-red-100">
                                            @csrf
                                            <label for="correcao-item-{{ $itemAtendimento->id }}" class="block text-sm font-semibold text-red-700 mb-1">O que a empresa precisa corrigir?</label>
                                            <p class="text-xs text-gray-600 mb-2">Esta orientação será exibida à empresa para que ela envie um novo PDF.</p>
                                            <div class="flex flex-col gap-2 sm:flex-row">
                                                <textarea id="correcao-item-{{ $itemAtendimento->id }}" name="motivo_rejeicao" rows="2" required maxlength="1000" class="flex-1 px-3 py-2 text-sm border border-red-200 rounded-lg focus:ring-red-500 focus:border-red-500" placeholder="Ex.: O certificado está vencido. Envie uma versão dentro da validade."></textarea>
                                                <button type="button" @click="rejeitarAberto = false" class="self-end px-3 py-2 text-xs text-gray-600">Cancelar</button>
                                                <button type="submit" class="self-end px-3 py-2 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">Confirmar solicitação</button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <p class="mt-3 p-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-600">Aguardando o comprovante da empresa. Quando ela enviar o PDF, você poderá analisar a resposta aqui.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
