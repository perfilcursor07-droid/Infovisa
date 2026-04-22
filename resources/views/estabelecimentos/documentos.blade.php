@extends('layouts.admin')

@section('title', 'Documentos - ' . ($estabelecimento->nome_fantasia ?? $estabelecimento->razao_social))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" class="hover:text-gray-700">{{ $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social }}</a>
                <span>/</span>
                <span class="text-gray-900">Documentos</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Documentos do Estabelecimento</h1>
        </div>
        <a href="{{ route('admin.documentos.create', ['estabelecimento_id' => $estabelecimento->id]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Criar Documento
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.estabelecimentos.documentos', $estabelecimento->id) }}" class="flex items-end gap-3">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-600 mb-1">Busca</label>
                <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Tipo, número..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    <option value="rascunho" @selected(request('status') === 'rascunho')>Rascunho</option>
                    <option value="aguardando_assinatura" @selected(request('status') === 'aguardando_assinatura')>Ag. Assinatura</option>
                    <option value="assinado" @selected(request('status') === 'assinado')>Assinado</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Filtrar</button>
            <a href="{{ route('admin.estabelecimentos.documentos', $estabelecimento->id) }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">Limpar</a>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Documento</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Processo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Criado por</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Data</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($documentos as $doc)
                    @php
                        $statusClass = match($doc->status) {
                            'assinado' => 'bg-green-100 text-green-700',
                            'aguardando_assinatura' => 'bg-amber-100 text-amber-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                        $statusLabel = match($doc->status) {
                            'assinado' => 'Assinado',
                            'aguardando_assinatura' => 'Ag. Assinatura',
                            default => 'Rascunho',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">
                            <a href="{{ route('admin.documentos.show', $doc->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                #{{ $doc->numero_formatado ?? $doc->id }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $doc->tipoDocumento->nome ?? $doc->nome }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($doc->processo)
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$estabelecimento->id, $doc->processo->id]) }}" class="text-blue-600 hover:underline text-xs">
                                {{ $doc->processo->numero_processo }}
                            </a>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $doc->usuarioCriador->nome ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Nenhum documento encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($documentos->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $documentos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
