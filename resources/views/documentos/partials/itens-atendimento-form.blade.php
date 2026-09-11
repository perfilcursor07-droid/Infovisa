<div x-show="exigeItensAtendimento" x-cloak
     class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden mb-3">
    <div class="px-4 py-3 bg-amber-50/70 border-b border-amber-200">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex gap-3">
                <span class="mt-0.5 inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </span>
                <div>
                <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    O que a empresa precisa atender?
                </h2>
                    <p class="text-sm text-gray-600 mt-1">Cada item vira uma exigência separada no PDF e no portal da empresa.</p>
                </div>
            </div>
            <button type="button" @click="adicionarItemAtendimento()"
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Adicionar item
            </button>
        </div>
    </div>

    <div class="p-4 space-y-3">
        <div class="grid gap-2 sm:grid-cols-[1fr_auto] sm:items-center">
            <div class="rounded-lg bg-blue-50 border border-blue-100 px-3 py-2 text-xs text-blue-900">
                <p><span class="font-semibold">Dica:</span> descreva a providência e o comprovante esperado no mesmo campo. A base legal fica ao lado, quando houver.</p>
                <p class="mt-1 text-blue-700">Ex.: “Regularizar o responsável técnico e anexar o certificado vigente.”</p>
            </div>
            <p class="inline-flex items-center justify-center rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700" x-text="itensAtendimento.length + ' item(ns)'"></p>
        </div>
        @error('itens_atendimento')
            <div class="px-3 py-2 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg">{{ $message }}</div>
        @enderror

        <template x-if="itensAtendimento.length === 0">
            <div class="py-6 text-center border-2 border-dashed border-amber-200 rounded-lg bg-amber-50/40">
                <p class="text-sm text-gray-600">Nenhum item adicionado.</p>
                <p class="mt-1 text-xs text-gray-500">Clique em “Adicionar item” para cadastrar a primeira exigência.</p>
            </div>
        </template>

        <template x-for="(item, indice) in itensAtendimento" :key="item.chave">
            <div class="border border-gray-200 rounded-xl p-3 bg-white shadow-sm">
                <input type="hidden" :name="'itens_atendimento[' + indice + '][descricao]'" :value="item.descricao">
                <input type="hidden" :name="'itens_atendimento[' + indice + '][embasamento_legal]'" :value="item.embasamento_legal">

                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800 text-xs font-bold" x-text="indice + 1"></span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900" x-text="'Exigência ' + (indice + 1)"></p>
                            <p class="text-[11px] text-gray-500">A empresa enviará um PDF para este item.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button type="button" @click="abrirModalItemAtendimento(indice)"
                                class="p-1.5 text-blue-600 rounded hover:bg-blue-50" title="Editar item" aria-label="Editar item">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                        </button>
                        <button type="button" @click="moverItemAtendimento(indice, -1)" :disabled="indice === 0"
                                class="p-1.5 text-gray-500 rounded hover:bg-gray-100 disabled:opacity-30" title="Mover para cima" aria-label="Mover item para cima">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button type="button" @click="moverItemAtendimento(indice, 1)" :disabled="indice === itensAtendimento.length - 1"
                                class="p-1.5 text-gray-500 rounded hover:bg-gray-100 disabled:opacity-30" title="Mover para baixo" aria-label="Mover item para baixo">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <button type="button" @click="if ((!item.descricao && !item.embasamento_legal) || confirm('Remover o item ' + (indice + 1) + '?')) removerItemAtendimento(indice)"
                                class="p-1.5 text-red-600 rounded hover:bg-red-50" title="Remover item" aria-label="Remover item">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <button type="button" @click="abrirModalItemAtendimento(indice)" class="mt-3 block w-full text-left">
                    <p class="rounded-lg bg-gray-50 px-3 py-2 text-sm font-medium text-gray-900 whitespace-pre-line" x-text="item.descricao || 'Sem descrição informada'"></p>
                    <p x-show="item.embasamento_legal" x-cloak class="mt-2 text-xs text-gray-600 whitespace-pre-line">
                        <span class="font-semibold">Base legal:</span>
                        <span x-text="item.embasamento_legal"></span>
                    </p>
                </button>
            </div>
        </template>

    </div>

    <div x-show="modalItemAtendimentoAberto" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
        <div class="flex min-h-screen items-center justify-center px-4 py-6">
            <div class="fixed inset-0 bg-gray-900/50" @click="fecharModalItemAtendimento()"></div>
            <div class="relative w-full max-w-4xl rounded-2xl bg-white shadow-xl">
                <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700" x-text="modalItemAtendimentoIndice === null ? 'Novas exigências' : 'Editar exigência'"></p>
                        <h3 class="mt-1 text-lg font-bold text-gray-900">O que a empresa precisa atender?</h3>
                        <p class="mt-1 text-sm text-gray-600" x-text="modalItemAtendimentoIndice === null ? 'Cadastre uma ou várias exigências de uma vez. Cada linha abaixo vira um item separado no PDF e no portal da empresa.' : 'Altere a providência e o comprovante esperado deste item.'"></p>
                    </div>
                    <button type="button" @click="fecharModalItemAtendimento()" class="rounded-full p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Fechar modal">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="max-h-[70vh] space-y-4 overflow-y-auto px-5 py-4">
                    <div x-show="modalItemAtendimentoIndice === null" x-cloak class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                        Você pode preencher várias exigências agora e salvar tudo de uma vez. Use <span class="font-semibold">Adicionar outra exigência</span> para abrir mais campos.
                    </div>

                    <template x-for="(itemModal, indiceModal) in modalItensAtendimento" :key="indiceModal">
                        <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-3">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-800" x-text="indiceModal + 1"></span>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900" x-text="modalItemAtendimentoIndice === null ? 'Exigência ' + (indiceModal + 1) : 'Exigência selecionada'"></p>
                                        <p class="text-[11px] text-gray-500">A empresa enviará um PDF para este item.</p>
                                    </div>
                                </div>
                                <button type="button"
                                        x-show="modalItemAtendimentoIndice === null && modalItensAtendimento.length > 1"
                                        @click="removerLinhaModalItemAtendimento(indiceModal)"
                                        class="rounded-lg p-1.5 text-red-600 hover:bg-red-50"
                                        title="Remover esta exigência"
                                        aria-label="Remover esta exigência">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <div class="grid gap-3 md:grid-cols-[minmax(0,1.4fr)_minmax(260px,0.9fr)]">
                                <div>
                                    <label :for="'modal-exigencia-descricao-' + indiceModal" class="mb-1 block text-sm font-semibold text-gray-700">Providência e comprovante <span class="text-red-500">*</span></label>
                                    <textarea :id="'modal-exigencia-descricao-' + indiceModal" rows="4" maxlength="2000" x-model="itemModal.descricao"
                                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500"
                                              placeholder="Ex.: Regularizar o responsável técnico e enviar o certificado vigente em PDF."></textarea>
                                </div>
                                <div>
                                    <label :for="'modal-exigencia-base-legal-' + indiceModal" class="mb-1 block text-sm font-semibold text-gray-700">Base legal <span class="font-normal text-gray-500">(opcional)</span></label>
                                    <textarea :id="'modal-exigencia-base-legal-' + indiceModal" rows="4" maxlength="5000" x-model="itemModal.embasamento_legal"
                                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500"
                                              placeholder="Ex.: Art. 10 da Lei nº ... / RDC nº ..."></textarea>
                                </div>
                            </div>
                        </div>
                    </template>

                    <button type="button"
                            x-show="modalItemAtendimentoIndice === null"
                            @click="adicionarLinhaModalItemAtendimento()"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Adicionar outra exigência
                    </button>

                    <div class="rounded-lg bg-blue-50 border border-blue-100 px-3 py-2 text-xs text-blue-900">
                        <span class="font-semibold">Dica:</span> escreva como uma ação clara. Ex.: “Apresentar contrato atualizado” ou “Anexar certificado vigente”.
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-gray-200 px-5 py-4 sm:flex-row sm:justify-end">
                    <button type="button" @click="fecharModalItemAtendimento()" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button type="button" @click="salvarModalItemAtendimento()" :disabled="!modalTemItensValidos()"
                            class="px-4 py-2 text-sm font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700 disabled:cursor-not-allowed disabled:bg-gray-300">
                        <span x-text="modalItemAtendimentoIndice === null ? 'Salvar exigências' : 'Salvar alteração'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
