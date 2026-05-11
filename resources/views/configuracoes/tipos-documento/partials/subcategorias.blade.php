{{--
    Repeater de Subcategorias do tipo de documento.
    Ex.: Alvará Sanitário -> Provisório, Administrativo, Definitivo.

    Variáveis esperadas:
      $subcategorias (Collection|array|null) - subcategorias já existentes (no edit). No create, pode ser null.
--}}
@php
    $subcategoriasIniciais = collect(old('subcategorias', isset($subcategorias) ? collect($subcategorias)->map(fn($s) => [
        'id' => $s->id,
        'nome' => $s->nome,
        'codigo' => $s->codigo,
        'ordem' => $s->ordem,
        'ativo' => $s->ativo,
    ])->values()->all() : []));
@endphp

<div class="border border-purple-200 rounded-lg p-4 bg-purple-50"
     x-data="subcategoriasRepeater({{ $subcategoriasIniciais->toJson() }})">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-4H5m14 8H5m14 4H5"/>
            </svg>
            <label class="text-sm font-semibold text-gray-900">Subcategorias</label>
        </div>
        <button type="button" @click="adicionar()"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-purple-600 rounded hover:bg-purple-700 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova subcategoria
        </button>
    </div>

    <p class="text-xs text-gray-600 mb-3">
        Use subcategorias para diferenciar variações do mesmo tipo (ex.: Alvará Sanitário -> Provisório, Administrativo, Definitivo).
        Cada subcategoria pode ter seus próprios modelos de documento.
    </p>

    <template x-if="itens.length === 0">
        <div class="text-xs text-gray-500 italic bg-white border border-dashed border-gray-300 rounded p-3 text-center">
            Nenhuma subcategoria cadastrada. Clique em "Nova subcategoria" para adicionar.
        </div>
    </template>

    <div class="space-y-2">
        <template x-for="(item, index) in itens" :key="index">
            <div class="grid grid-cols-12 gap-2 items-center bg-white border border-gray-200 rounded-lg p-2">
                <input type="hidden" :name="`subcategorias[${index}][id]`" :value="item.id || ''">

                <div class="col-span-5">
                    <input type="text"
                           :name="`subcategorias[${index}][nome]`"
                           x-model="item.nome"
                           placeholder="Nome (ex: Provisório)"
                           class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>

                <div class="col-span-4">
                    <input type="text"
                           :name="`subcategorias[${index}][codigo]`"
                           x-model="item.codigo"
                           placeholder="Código (opcional)"
                           class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>

                <div class="col-span-1">
                    <input type="number"
                           :name="`subcategorias[${index}][ordem]`"
                           x-model.number="item.ordem"
                           min="0"
                           placeholder="Ordem"
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>

                <div class="col-span-1 flex justify-center">
                    <label class="inline-flex items-center cursor-pointer" title="Ativa">
                        <input type="hidden" :name="`subcategorias[${index}][ativo]`" :value="item.ativo ? 1 : 0">
                        <input type="checkbox" x-model="item.ativo"
                               class="w-4 h-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500">
                    </label>
                </div>

                <div class="col-span-1 flex justify-end">
                    <button type="button" @click="remover(index)"
                            class="text-red-500 hover:text-red-700 p-1"
                            title="Remover subcategoria">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
    function subcategoriasRepeater(iniciais) {
        return {
            itens: (iniciais || []).map(function (i) {
                return {
                    id: i.id || '',
                    nome: i.nome || '',
                    codigo: i.codigo || '',
                    ordem: i.ordem ?? 0,
                    ativo: i.ativo === undefined ? true : !!Number(i.ativo),
                };
            }),
            adicionar() {
                this.itens.push({
                    id: '',
                    nome: '',
                    codigo: '',
                    ordem: this.itens.length,
                    ativo: true,
                });
            },
            remover(index) {
                this.itens.splice(index, 1);
            },
        };
    }
</script>
