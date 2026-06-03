@extends('layouts.admin')

@section('title', 'Municípios de Atuação')
@section('page-title', 'Municípios de Atuação')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" 
               class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900">Municípios de Atuação</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $estabelecimento->nome_razao_social }}
                    <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-fuchsia-100 text-fuchsia-700">Unidade Móvel</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">{{ $municipiosAtuacao->count() }} município(s)</span>
        </div>
    </div>

    {{-- Tipo de Unidade --}}
    @if($estabelecimento->tipo_unidade_movel)
    <div class="bg-fuchsia-50 border border-fuchsia-200 rounded-lg p-4">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-fuchsia-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/>
            </svg>
            <div>
                <span class="text-sm font-medium text-fuchsia-900">Tipo de Unidade:</span>
                <span class="text-sm text-fuchsia-700 ml-1">{{ $estabelecimento->tipo_unidade_movel }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Lista de Municípios --}}
    @if($municipiosAtuacao->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p class="text-sm text-gray-500">Nenhum município de atuação cadastrado para esta unidade móvel.</p>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Município</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Competência</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">InfoVISA</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($municipiosAtuacao as $mun)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-medium text-gray-900">{{ $mun->municipio_nome }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $mun->competencia === 'estadual' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($mun->competencia) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($mun->competencia === 'estadual')
                            <span class="text-xs text-gray-400">—</span>
                        @elseif($mun->usa_infovisa)
                            <span class="inline-flex items-center gap-1 text-xs text-green-700">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Utiliza
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs text-amber-700">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                Não utiliza
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($mun->data_inicio)->format('d/m/Y') }}
                            <span class="text-gray-400 mx-1">a</span>
                            {{ \Carbon\Carbon::parse($mun->data_fim)->format('d/m/Y') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Resumo --}}
    <div class="grid grid-cols-3 gap-4">
        @php
            $totalEstaduais = $municipiosAtuacao->where('competencia', 'estadual')->count();
            $totalMunicipaisInfovisa = $municipiosAtuacao->where('competencia', 'municipal')->where('usa_infovisa', true)->count();
            $totalSemInfovisa = $municipiosAtuacao->where('competencia', 'municipal')->where('usa_infovisa', false)->count();
        @endphp
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-purple-700">{{ $totalEstaduais }}</p>
            <p class="text-xs text-purple-600 mt-1">Estadual</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-blue-700">{{ $totalMunicipaisInfovisa }}</p>
            <p class="text-xs text-blue-600 mt-1">Municipal (InfoVISA)</p>
        </div>
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-amber-700">{{ $totalSemInfovisa }}</p>
            <p class="text-xs text-amber-600 mt-1">Sem InfoVISA</p>
        </div>
    </div>
    @endif
</div>
@endsection
