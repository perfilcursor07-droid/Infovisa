@extends('layouts.admin')

@section('title', 'Assinar Documento')

@section('content')
@php
    $temAssinaturaFeita = $documento->assinaturas->where('status', 'assinado')->count() > 0;
    $usuarioLogado = auth('interno')->user();
    $isAdmin = $usuarioLogado->isAdmin();
    $isCriador = $documento->usuario_criador_id === $usuarioLogado->id;
    $isLote = $documento->isLote();
@endphp

<div class="max-w-8xl mx-auto py-6 space-y-4">

    {{-- Header compacto --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.assinatura.pendentes') }}" class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-base font-bold text-gray-900">{{ $isLote ? 'Assinatura em Lote' : 'Assinatura Digital' }}</h1>
                <p class="text-[11px] text-gray-400">{{ $documento->tipoDocumento->nome }} · Nº {{ $documento->numero_documento ?? $documento->id }}</p>
            </div>
        </div>
        <span class="text-[10px] font-semibold px-2 py-1 rounded-full {{ $isLote ? 'bg-purple-100 text-purple-700' : 'bg-amber-100 text-amber-700' }}">
            {{ $isLote ? 'Lote · ' . count($documento->processos_ids) . ' processos' : 'Pendente' }}
        </span>
    </div>

    {{-- Banner de outros documentos pendentes --}}
    @if(isset($outrosPendentes) && $outrosPendentes > 0)
    <a href="{{ route('admin.assinatura.pendentes') }}"
       class="flex items-center justify-between gap-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 hover:bg-amber-100 hover:border-amber-300 transition group">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center group-hover:bg-amber-200 transition">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-amber-900">
                    Você tem {{ $outrosPendentes }} {{ $outrosPendentes === 1 ? 'outro documento pendente' : 'outros documentos pendentes' }} para assinar
                </p>
                <p class="text-xs text-amber-700">Clique aqui para ver todos os documentos aguardando sua assinatura</p>
            </div>
        </div>
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>
    @endif

    {{-- Card único --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Info do documento --}}
        <div class="p-4 border-b border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-900">{{ $documento->tipoDocumento->nome }}</h2>
                <span class="text-[11px] text-gray-400">{{ $documento->created_at->format('d/m/Y H:i') }}</span>
            </div>

            @if($isLote)
            <div class="bg-purple-50 rounded-xl p-3 border border-purple-100">
                <p class="text-xs text-purple-700 mb-2">Será distribuído para {{ count($documento->processos_ids) }} processos:</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-1.5">
                    @foreach($documento->processosLote() as $proc)
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$proc->estabelecimento_id, $proc->id]) }}"
                       class="flex items-center gap-2 p-2 bg-white rounded-lg border border-purple-100 hover:border-purple-300 transition text-xs group">
                        <svg class="w-3.5 h-3.5 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <div class="min-w-0 flex-1">
                            <span class="font-medium text-purple-700">{{ $proc->numero_processo ?? 'S/N' }}</span>
                            <span class="text-gray-400 truncate block">{{ $proc->estabelecimento->nome_fantasia ?? $proc->estabelecimento->razao_social ?? 'N/A' }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @if($documento->os_id)
                <a href="{{ route('admin.ordens-servico.show', $documento->os_id) }}" class="inline-flex items-center gap-1 text-[11px] text-purple-600 hover:text-purple-800 font-medium mt-2">
                    OS #{{ $documento->ordemServico->numero ?? $documento->os_id }}
                </a>
                @endif
            </div>
            @elseif($documento->processo)
            <div class="flex items-center gap-4 text-xs bg-gray-50 rounded-xl p-3">
                <div>
                    <span class="text-gray-400">Processo</span>
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}"
                       class="block font-medium text-blue-600 hover:text-blue-700 hover:underline">{{ $documento->processo->numero_processo ?? 'S/N' }}</a>
                </div>
                <div class="w-px h-8 bg-gray-200"></div>
                <div class="min-w-0">
                    <span class="text-gray-400">Estabelecimento</span>
                    @if($documento->processo->estabelecimento)
                    <a href="{{ route('admin.estabelecimentos.show', $documento->processo->estabelecimento_id) }}"
                       class="block font-medium text-blue-600 hover:text-blue-700 hover:underline truncate">
                        {{ $documento->processo->estabelecimento->nome_fantasia ?? $documento->processo->estabelecimento->razao_social ?? 'N/A' }}
                    </a>
                    @else
                    <p class="font-medium text-gray-700 truncate">N/A</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Assinantes --}}
        <div class="p-4 border-b border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wide">Assinantes ({{ $assinatura->ordem }}º de {{ $documento->assinaturas->count() }})</h3>
                @if(($isCriador || $isAdmin) && (!$temAssinaturaFeita || $isAdmin) && $documento->status !== 'assinado')
                <button type="button" onclick="abrirModalGerenciarAssinantes()" class="px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Gerenciar Assinantes
                </button>
                @endif
            </div>
            <div class="flex flex-wrap gap-1.5">
                @foreach($documento->assinaturas->sortBy('ordem') as $ass)
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border text-xs
                    {{ $ass->status === 'assinado' ? 'bg-green-50 border-green-200 text-green-700' : ($ass->usuario_interno_id === $usuarioLogado->id ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-gray-50 border-gray-200 text-gray-500') }}">
                    @if($ass->status === 'assinado')
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @elseif($ass->usuario_interno_id === $usuarioLogado->id)
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                    @else
                        <span class="w-3.5 h-3.5 rounded-full border-2 border-gray-300 flex-shrink-0"></span>
                    @endif
                    <span class="font-medium">{{ $ass->usuarioInterno->nome ?? 'Usuário' }}</span>
                    @if($ass->status === 'assinado')
                        <span class="opacity-60">✓</span>
                    @elseif($ass->usuario_interno_id === $usuarioLogado->id)
                        <span class="opacity-60">· Você</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Formulário de assinatura + Visualizar --}}
        <form action="{{ route('admin.assinatura.processar', $documento->id) }}" method="POST" class="p-4">
            @csrf
            <input type="hidden" name="acao" value="assinar">

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Senha de Assinatura Digital</label>
                <input type="password"
                       name="senha_assinatura"
                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('senha_assinatura') border-red-400 ring-2 ring-red-200 @enderror"
                       placeholder="Digite sua senha"
                       required
                       autofocus>
                @error('senha_assinatura')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-white rounded-xl transition shadow-sm
                    {{ $isLote ? 'bg-purple-600 hover:bg-purple-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $isLote ? 'Assinar Lote' : 'Assinar Documento' }}
                </button>
                <button type="button" onclick="abrirModalPdf()" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Visualizar Documento
                </button>
            </div>
        </form>

        {{-- Rodapé segurança --}}
        <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-center gap-1.5 text-[10px] text-gray-400">
            <svg class="w-3 h-3 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            Protegido por criptografia
        </div>
    </div>
</div>

{{-- Modal PDF --}}
<div id="modalPdf" class="hidden fixed inset-0 bg-gray-900/95 z-50">
    <div class="flex items-center justify-center min-h-screen p-6">
        <div class="bg-white rounded-xl w-full max-w-7xl h-[95vh] overflow-hidden flex flex-col shadow-2xl">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <span class="text-sm font-medium text-gray-700">{{ $documento->numero_documento ?? 'Documento #' . $documento->id }}</span>
                <div class="flex items-center gap-2">
                    <button onclick="abrirPdfNovaAba()" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1 rounded hover:bg-gray-100 transition">Nova aba</button>
                    <button onclick="fecharModalPdf()" class="p-1.5 text-gray-400 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-hidden bg-gray-50">
                <iframe id="pdfFrame" src="" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>
</div>

{{-- Modal Gerenciar Assinantes --}}
<div id="modalGerenciarAssinantes" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Gerenciar Assinantes</h3>
                <button onclick="fecharModalGerenciarAssinantes()" class="p-1.5 text-gray-400 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.documentos.gerenciar-assinantes', $documento->id) }}" method="POST">
                @csrf
                <div class="p-4">
                    @php
                        $modalUsuarioLogado = auth('interno')->user();
                        $usuariosInternosQuery = \App\Models\UsuarioInterno::where('ativo', true);
                        if ($modalUsuarioLogado->isAdmin()) {
                            // Admin vê todos
                        } elseif ($modalUsuarioLogado->isEstadual()) {
                            $usuariosInternosQuery->where(function($q) {
                                $q->whereNull('municipio_id')
                                  ->orWhereIn('nivel_acesso', ['administrador', 'gestor_estadual', 'tecnico_estadual']);
                            });
                        } elseif ($modalUsuarioLogado->isMunicipal() && $modalUsuarioLogado->municipio_id) {
                            $usuariosInternosQuery->where('municipio_id', $modalUsuarioLogado->municipio_id);
                        }
                        $usuariosInternos = $usuariosInternosQuery->orderBy('nome')->get();
                        $assinantesAtuais = $documento->assinaturas->pluck('usuario_interno_id')->toArray();
                    @endphp

                    <input type="text" placeholder="Buscar por nome..." oninput="filtrarAssinantes(this.value)"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 mb-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                    <div class="space-y-0.5 max-h-64 overflow-y-auto" id="listaAssinantes">
                        @foreach($usuariosInternos as $usuario)
                        <label class="assinante-item flex items-center gap-2.5 p-2 cursor-pointer hover:bg-gray-50 rounded-lg transition" data-nome="{{ strtolower($usuario->nome) }}">
                            <input type="checkbox" name="assinantes[]" value="{{ $usuario->id }}" {{ in_array($usuario->id, $assinantesAtuais) ? 'checked' : '' }}
                                   class="w-3.5 h-3.5 text-blue-600 rounded border-gray-300 focus:ring-2 focus:ring-blue-500">
                            <div class="flex-1 min-w-0">
                                <span class="text-xs text-gray-700 block truncate">{{ $usuario->nome }}</span>
                                <span class="text-[10px] text-gray-400">{{ $usuario->nivel_acesso->label() }}@if($usuario->setor) · {{ $usuario->setor }}@endif</span>
                            </div>
                            @if(in_array($usuario->id, $assinantesAtuais))
                            <span class="text-[9px] px-1.5 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">Atual</span>
                            @endif
                        </label>
                        @endforeach
                    </div>
                    <p class="text-[10px] text-gray-400 mt-2">{{ $usuariosInternos->count() }} disponíveis</p>
                </div>
                <div class="px-4 py-3 border-t border-gray-100 flex gap-2">
                    <button type="button" onclick="fecharModalGerenciarAssinantes()" class="flex-1 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 rounded-lg transition">Cancelar</button>
                    <button type="submit" class="flex-1 px-3 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const pdfUrl = "{{ route('admin.assinatura.visualizar-pdf', $documento->id) }}";
function abrirModalPdf() { document.getElementById('pdfFrame').src = pdfUrl; document.getElementById('modalPdf').classList.remove('hidden'); }
function fecharModalPdf() { document.getElementById('modalPdf').classList.add('hidden'); document.getElementById('pdfFrame').src = ''; }
function abrirPdfNovaAba() { window.open(pdfUrl, '_blank'); }
function abrirModalGerenciarAssinantes() { document.getElementById('modalGerenciarAssinantes').classList.remove('hidden'); }
function fecharModalGerenciarAssinantes() { document.getElementById('modalGerenciarAssinantes').classList.add('hidden'); }
function filtrarAssinantes(t) { document.querySelectorAll('.assinante-item').forEach(i => { i.style.display = !t.trim() || i.dataset.nome.includes(t.toLowerCase()) ? '' : 'none'; }); }
document.getElementById('modalPdf')?.addEventListener('click', e => { if (e.target.id === 'modalPdf') fecharModalPdf(); });
document.getElementById('modalGerenciarAssinantes')?.addEventListener('click', e => { if (e.target.id === 'modalGerenciarAssinantes') fecharModalGerenciarAssinantes(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { fecharModalPdf(); fecharModalGerenciarAssinantes(); } });
</script>
@endsection
