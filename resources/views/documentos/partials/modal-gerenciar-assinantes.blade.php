@php
    $usuarioLogadoModal = auth('interno')->user();
    $usuariosAssinantes = \App\Models\UsuarioInterno::paraSelecaoAssinantes($usuarioLogadoModal)
        ->with('municipioRelacionado')
        ->orderBy('nome')
        ->get();
    $assinantesAtuais = $documento->assinaturas->pluck('usuario_interno_id')->toArray();
    $usuariosEstaduais = $usuariosAssinantes->filter(fn ($u) => $u->municipio_id === null)->values();
    $usuariosMunicipais = $usuariosAssinantes->filter(fn ($u) => $u->municipio_id !== null)->values();
    $modalId = $modalId ?? 'modalGerenciarAssinantes';
@endphp

<div id="{{ $modalId }}" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-2xl w-full max-w-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Gerenciar Assinantes</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Selecione os usuários que devem assinar</p>
                </div>
                <button type="button" onclick="fecharModalGerenciarAssinantes()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.documentos.gerenciar-assinantes', $documento->id) }}" method="POST">
                @csrf
                <div class="p-5">
                    {{-- Busca e filtro --}}
                    <div class="flex flex-col sm:flex-row gap-2 mb-4">
                        <div class="relative flex-1">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" id="buscaAssinantes" placeholder="Buscar por nome, cargo ou setor..."
                                   oninput="filtrarAssinantesModal()"
                                   class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <select id="filtroEscopoAssinantes" onchange="filtrarAssinantesModal()"
                                class="w-full sm:w-40 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="todos">Todos</option>
                            @if($usuariosEstaduais->isNotEmpty())
                            <option value="estadual">Estadual ({{ $usuariosEstaduais->count() }})</option>
                            @endif
                            @if($usuariosMunicipais->isNotEmpty())
                            <option value="municipal">Municipal ({{ $usuariosMunicipais->count() }})</option>
                            @endif
                        </select>
                    </div>

                    <div class="max-h-80 overflow-y-auto border border-gray-200 rounded-xl p-2 space-y-3" id="listaAssinantes">
                        @if($usuariosEstaduais->isNotEmpty())
                        <div class="assinantes-grupo" data-grupo="estadual">
                            <p class="sticky top-0 z-10 px-2 py-1.5 text-[10px] font-bold uppercase tracking-wide text-purple-700 bg-purple-50 rounded-md mb-1">
                                Equipe Estadual ({{ $usuariosEstaduais->count() }})
                            </p>
                            @foreach($usuariosEstaduais as $usuario)
                            @include('documentos.partials.item-assinante-modal', ['usuario' => $usuario, 'escopo' => 'estadual'])
                            @endforeach
                        </div>
                        @endif

                        @if($usuariosMunicipais->isNotEmpty())
                        <div class="assinantes-grupo" data-grupo="municipal">
                            <p class="sticky top-0 z-10 px-2 py-1.5 text-[10px] font-bold uppercase tracking-wide text-blue-700 bg-blue-50 rounded-md mb-1">
                                Equipe Municipal ({{ $usuariosMunicipais->count() }})
                                @if($usuarioLogadoModal->municipio_id && $usuarioLogadoModal->municipioRelacionado)
                                · {{ $usuarioLogadoModal->municipioRelacionado->nome }}
                                @endif
                            </p>
                            @foreach($usuariosMunicipais as $usuario)
                            @include('documentos.partials.item-assinante-modal', ['usuario' => $usuario, 'escopo' => 'municipal'])
                            @endforeach
                        </div>
                        @endif

                        @if($usuariosAssinantes->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-6">Nenhum usuário disponível para assinatura.</p>
                        @endif

                        <p id="assinantesNenhumResultado" class="hidden text-sm text-gray-500 text-center py-6">Nenhum usuário encontrado para a busca.</p>
                    </div>

                    <p class="text-xs text-gray-400 mt-2">
                        {{ $usuariosAssinantes->count() }} usuário(s) disponíveis
                        · {{ count($assinantesAtuais) }} selecionado(s)
                    </p>
                </div>

                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex gap-3 justify-end">
                    <button type="button" onclick="fecharModalGerenciarAssinantes()"
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filtrarAssinantesModal() {
    const busca = (document.getElementById('buscaAssinantes')?.value || '').toLowerCase().trim();
    const escopo = document.getElementById('filtroEscopoAssinantes')?.value || 'todos';
    let visiveis = 0;

    document.querySelectorAll('.assinantes-grupo').forEach(grupo => {
        const grupoEscopo = grupo.dataset.grupo;
        const mostrarGrupo = escopo === 'todos' || escopo === grupoEscopo;
        let grupoVisivel = false;

        grupo.querySelectorAll('.assinante-item').forEach(item => {
            const matchBusca = !busca || (item.dataset.search || '').includes(busca);
            const show = mostrarGrupo && matchBusca;
            item.style.display = show ? '' : 'none';
            if (show) {
                visiveis++;
                grupoVisivel = true;
            }
        });

        grupo.style.display = grupoVisivel ? '' : 'none';
    });

    document.getElementById('assinantesNenhumResultado')?.classList.toggle('hidden', visiveis > 0);
}
</script>
