@php
    $assinantesPayload = collect($usuariosInternos ?? [])->map(fn ($usuario) => [
        'id' => (int) $usuario->id,
        'nome' => (string) $usuario->nome,
        'cpf' => (string) ($usuario->cpf_formatado ?? ''),
        'email' => (string) ($usuario->email ?? ''),
        'cargo' => (string) ($usuario->cargo ?? ''),
    ])->values();
    $assinantesSelecionados = collect($assinantesSelecionados ?? [])->map(fn ($id) => (int) $id)->values();
    $usuarioLogadoId = (int) auth('interno')->id();
    $formAssinaturas = $formAssinaturas ?? null;
@endphp

<div
    class="selecao-assinantes"
    x-data="buscaAssinantesDigitais({{ json_encode($assinantesPayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}, {{ json_encode($assinantesSelecionados) }}, {{ $usuarioLogadoId }})"
>
    <div class="mb-3">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   x-model="busca"
                   placeholder="Pesquisar usuário por nome, CPF, e-mail ou cargo..."
                   class="w-full pl-10 pr-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-300 transition">
        </div>
        <div class="mt-1 flex flex-wrap gap-2 text-xs text-gray-500">
            <span x-show="assinaturasSelecionadas.length > 0" class="text-purple-700 font-medium" x-text="`${assinaturasSelecionadas.length} selecionado(s)`"></span>
            <span x-show="usuariosVisiveis.length > 0" x-text="`${usuariosVisiveis.length} usuário(s) na lista`"></span>
            <span x-show="usuariosVisiveis.length === 0 && busca" class="text-orange-600">Nenhum usuário encontrado</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 max-h-64 overflow-y-auto p-1 border rounded-lg bg-gray-50">
        <template x-for="usuario in usuariosVisiveis" :key="usuario.id">
            <label class="flex items-start p-2 border-2 rounded-lg cursor-pointer hover:border-purple-300 hover:bg-purple-50 transition-all group bg-white"
                   :class="estaSelecionado(usuario.id) ? 'border-purple-400 bg-purple-50' : 'border-gray-200'">
                <input type="checkbox"
                       x-model.number="assinaturasSelecionadas"
                       :value="usuario.id"
                       class="mt-0.5 h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <div class="ml-2 flex-1 min-w-0">
                    <div class="text-xs font-semibold text-gray-900 group-hover:text-purple-900 truncate">
                        <span x-text="usuario.nome"></span>
                        <span x-show="usuario.id === usuarioLogadoId" class="ml-1 px-1.5 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full font-medium">Você</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5 truncate" x-text="usuario.cargo ? `${usuario.cpf} · ${usuario.cargo}` : usuario.cpf"></div>
                </div>
            </label>
        </template>

        <template x-if="usuarios.length === 0">
            <div class="col-span-full text-center py-4 text-gray-500">
                <p class="text-sm">Nenhum usuário disponível para assinatura</p>
            </div>
        </template>
    </div>

    <template x-for="usuarioId in assinaturasSelecionadas" :key="`assinatura-hidden-${usuarioId}`">
        <input type="hidden" name="assinaturas[]" :value="usuarioId" @if($formAssinaturas) form="{{ $formAssinaturas }}" @endif>
    </template>
</div>

@once
<script>
function buscaAssinantesDigitais(usuarios, selecionados, usuarioLogadoId) {
    const normalizar = (valor) => String(valor || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, ' ')
        .trim();

    return {
        busca: '',
        usuarios: Array.isArray(usuarios) ? usuarios : [],
        assinaturasSelecionadas: Array.isArray(selecionados) ? selecionados.map(Number) : [],
        usuarioLogadoId: Number(usuarioLogadoId) || 0,

        get usuariosVisiveis() {
            const termo = normalizar(this.busca);
            const selecionados = new Set(this.assinaturasSelecionadas.map(Number));
            const corresponde = (usuario) => {
                if (!termo) return true;
                return [usuario.nome, usuario.cpf, usuario.email, usuario.cargo]
                    .map(normalizar)
                    .some((campo) => campo.includes(termo));
            };

            return this.usuarios.filter((usuario) => selecionados.has(Number(usuario.id)) || corresponde(usuario));
        },

        estaSelecionado(id) {
            return this.assinaturasSelecionadas.map(Number).includes(Number(id));
        },
    };
}
</script>
@endonce
