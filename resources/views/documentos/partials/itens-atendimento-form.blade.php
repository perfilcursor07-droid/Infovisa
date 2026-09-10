<div x-show="exigeItensAtendimento" x-cloak
     class="bg-white rounded-lg shadow-sm border border-amber-200 overflow-hidden mb-3">
    <div class="px-3 py-2 bg-gradient-to-r from-amber-50 to-white border-b border-amber-200">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2m-4 4l2 2 4-4"/>
                    </svg>
                    O que a empresa precisa atender?
                </h2>
                <p class="text-sm text-gray-600 mt-1">Cadastre uma exigência por item. A empresa verá esta descrição e enviará um PDF como resposta para cada uma.</p>
            </div>
            <button type="button" @click="adicionarItemAtendimento()"
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Adicionar item
            </button>
        </div>
    </div>

    <div class="p-3 space-y-3">
        <div class="rounded-lg bg-blue-50 border border-blue-100 p-3 text-sm text-blue-900">
            <p class="font-semibold">1. Descreva a providência → 2. Indique o comprovante → 3. Confira os itens</p>
            <p class="mt-1">Exemplo: “Regularizar o responsável técnico e anexar o certificado de responsabilidade técnica vigente.” Informe a base legal no campo ao lado, quando houver.</p>
        </div>
        <p class="text-xs font-semibold text-gray-600" x-text="itensAtendimento.length + ' item(ns) cadastrado(s) · a numeração é automática'"></p>
        @error('itens_atendimento')
            <div class="px-3 py-2 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg">{{ $message }}</div>
        @enderror

        <template x-if="itensAtendimento.length === 0">
            <div class="py-6 text-center border-2 border-dashed border-amber-200 rounded-lg bg-amber-50/40">
                <p class="text-sm text-gray-600">Nenhum item adicionado.</p>
                <button type="button" @click="adicionarItemAtendimento()" class="mt-2 text-xs font-semibold text-amber-700 hover:text-amber-900">Adicionar o primeiro item</button>
            </div>
        </template>

        <template x-for="(item, indice) in itensAtendimento" :key="item.chave">
            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold" x-text="'Item ' + (indice + 1)"></span>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="moverItemAtendimento(indice, -1)" :disabled="indice === 0"
                                class="p-1.5 text-gray-500 rounded hover:bg-white disabled:opacity-30" title="Mover para cima">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button type="button" @click="moverItemAtendimento(indice, 1)" :disabled="indice === itensAtendimento.length - 1"
                                class="p-1.5 text-gray-500 rounded hover:bg-white disabled:opacity-30" title="Mover para baixo">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <button type="button" @click="if ((!item.descricao && !item.embasamento_legal) || confirm('Remover o item ' + (indice + 1) + '?')) removerItemAtendimento(indice)"
                                class="p-1.5 text-red-600 rounded hover:bg-red-50 flex items-center gap-1 text-xs" title="Excluir item">
                            Remover
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                    <div>
                        <label :for="'exigencia-' + item.chave" class="block text-sm font-semibold text-gray-700 mb-1">O que fazer e qual comprovante enviar? <span class="text-red-500">*</span></label>
                        <textarea rows="3" maxlength="2000" x-model="item.descricao" @input="salvarAutomaticamente()"
                                  :id="'exigencia-' + item.chave"
                                  :name="'itens_atendimento[' + indice + '][descricao]'"
                                  :disabled="!exigeItensAtendimento"
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                  placeholder="Ex.: Regularizar o responsável técnico e enviar o certificado vigente em PDF."></textarea>
                    </div>
                    <div>
                        <label :for="'base-legal-' + item.chave" class="block text-sm font-semibold text-gray-700 mb-1">Embasamento legal <span class="font-normal text-gray-500">(opcional)</span></label>
                        <textarea rows="3" maxlength="5000" x-model="item.embasamento_legal" @input="salvarAutomaticamente()"
                                  :id="'base-legal-' + item.chave"
                                  :name="'itens_atendimento[' + indice + '][embasamento_legal]'"
                                  :disabled="!exigeItensAtendimento"
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                  placeholder="Ex.: Art. 10 da Lei nº ... / RDC nº ..."></textarea>
                    </div>
                </div>
            </div>
        </template>
        <button x-show="itensAtendimento.length > 0" type="button" @click="adicionarItemAtendimento(); $nextTick(() => $el.parentElement.querySelectorAll('textarea')[($el.parentElement.querySelectorAll('textarea').length - 2)]?.focus())"
                class="w-full py-3 border-2 border-dashed border-amber-300 rounded-lg text-sm font-semibold text-amber-800 hover:bg-amber-50">+ Adicionar outra exigência</button>
    </div>
</div>
