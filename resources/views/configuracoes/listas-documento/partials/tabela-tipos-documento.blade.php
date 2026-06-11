<table class="w-full">
    <thead class="bg-gray-50 border-y border-gray-200">
        <tr>
            <th class="px-3 py-3 text-center w-10">
                <input type="checkbox" class="doc-check-all w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500"
                       title="Selecionar todos desta seção" onclick="toggleCheckAllDocs(this)">
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nome</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Descrição</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Tipo</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Escopo</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Setor</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @foreach($tiposTabela as $tipo)
        <tr class="hover:bg-gray-50" data-doc-row="{{ $tipo->id }}">
            <td class="px-3 py-3 text-center">
                <input type="checkbox" class="doc-check w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500"
                       value="{{ $tipo->id }}" onclick="atualizarBarraExclusao()">
            </td>
            <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-900">{{ $tipo->nome }}</span>
                    @if($tipo->prazo_validade_dias)
                    <span class="px-1.5 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded" title="Validade: {{ $tipo->prazo_validade_dias }} dias">
                        {{ $tipo->prazo_validade_dias }}d
                    </span>
                    @endif
                </div>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm text-gray-600">{{ Str::limit($tipo->descricao, 40) ?: '-' }}</span>
            </td>
            <td class="px-4 py-3 text-center">
                @if($tipo->documento_comum)
                <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full" title="Documento comum a todos os serviços">
                    Comum
                </span>
                @else
                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full" title="Documento específico por atividade">
                    Específico
                </span>
                @endif
            </td>
            <td class="px-4 py-3 text-center">
                <span class="px-2 py-1 text-xs font-medium rounded-full
                    @if($tipo->escopo_competencia === 'estadual') bg-blue-100 text-blue-800
                    @elseif($tipo->escopo_competencia === 'municipal') bg-green-100 text-green-800
                    @else bg-gray-100 text-gray-600 @endif">
                    {{ $tipo->escopo_competencia_label }}
                </span>
            </td>
            <td class="px-4 py-3 text-center">
                <span class="px-2 py-1 text-xs font-medium rounded-full
                    @if($tipo->tipo_setor === 'publico') bg-indigo-100 text-indigo-800
                    @elseif($tipo->tipo_setor === 'privado') bg-orange-100 text-orange-800
                    @else bg-gray-100 text-gray-600 @endif">
                    {{ $tipo->tipo_setor_label }}
                </span>
            </td>
            <td class="px-4 py-3 text-center">
                @if($tipo->ativo)
                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Ativo</span>
                @else
                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Inativo</span>
                @endif
            </td>
            <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.edit', $tipo) }}" 
                       class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <button type="button" onclick="excluirDocumentoTipo({{ $tipo->id }})"
                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Excluir">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
