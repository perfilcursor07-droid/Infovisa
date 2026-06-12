{{-- Barra flutuante de exclusão em lote (tipos de documento) --}}
<div id="barra-exclusao-docs" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 hidden">
    <div class="flex items-center gap-4 bg-gray-900 text-white px-5 py-3 rounded-xl shadow-2xl">
        <span class="text-sm font-medium">
            <span id="contador-docs-selecionados">0</span> documento(s) selecionado(s)
        </span>
        <button type="button" onclick="excluirSelecionadosDocs()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Excluir selecionados
        </button>
        <button type="button" onclick="limparSelecaoDocs()" class="text-gray-400 hover:text-white text-sm">
            Cancelar
        </button>
    </div>
</div>

<script>
    const CSRF_DOCS = document.querySelector('meta[name="csrf-token"]').content;
    const URL_DESTROY_DOC = "{{ url('admin/configuracoes/tipos-documento-obrigatorio') }}";
    const URL_DESTROY_MULTIPLE_DOC = "{{ route('admin.configuracoes.tipos-documento-obrigatorio.destroy-multiple') }}";

    function atualizarBarraExclusao() {
        const selecionados = document.querySelectorAll('.doc-check:checked');
        const barra = document.getElementById('barra-exclusao-docs');
        const contador = document.getElementById('contador-docs-selecionados');
        contador.textContent = selecionados.length;
        barra.classList.toggle('hidden', selecionados.length === 0);
    }

    function toggleCheckAllDocs(checkbox) {
        const tabela = checkbox.closest('table');
        tabela.querySelectorAll('.doc-check').forEach(cb => cb.checked = checkbox.checked);
        atualizarBarraExclusao();
    }

    function limparSelecaoDocs() {
        document.querySelectorAll('.doc-check, .doc-check-all').forEach(cb => cb.checked = false);
        atualizarBarraExclusao();
    }

    async function excluirDocumentoTipo(id) {
        if (!confirm('Excluir este tipo de documento?')) return;
        try {
            const resp = await fetch(`${URL_DESTROY_DOC}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_DOCS, 'Accept': 'application/json' }
            });
            const data = await resp.json();
            if (data.success) {
                document.querySelector(`[data-doc-row="${id}"]`)?.remove();
                atualizarBarraExclusao();
            } else {
                alert(data.message || 'Não foi possível excluir.');
            }
        } catch (e) {
            alert('Erro ao excluir documento.');
        }
    }

    async function excluirSelecionadosDocs() {
        const ids = Array.from(document.querySelectorAll('.doc-check:checked')).map(cb => cb.value);
        if (ids.length === 0) return;
        if (!confirm(`Excluir ${ids.length} documento(s) selecionado(s)?`)) return;
        try {
            const resp = await fetch(URL_DESTROY_MULTIPLE_DOC, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_DOCS,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids })
            });
            const data = await resp.json();
            if (data.success) {
                (data.ids_excluidos || []).forEach(id => {
                    document.querySelector(`[data-doc-row="${id}"]`)?.remove();
                });
                limparSelecaoDocs();
                if (data.vinculados > 0) {
                    alert(data.message);
                }
            } else {
                alert(data.message || 'Não foi possível excluir.');
            }
        } catch (e) {
            alert('Erro ao excluir documentos.');
        }
    }
</script>
