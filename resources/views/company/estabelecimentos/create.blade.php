@extends('layouts.company')

@section('title', 'Novo Estabelecimento')
@section('page-title', 'Novo Estabelecimento')

@section('content')
<div class="max-w-8xl mx-auto">

    {{-- Cabeçalho --}}
    <div class="mb-8 text-center">
        <p class="text-base text-gray-500">Selecione como deseja registrar o estabelecimento</p>
    </div>

    {{-- Cards --}}
    <div class="space-y-4">

        {{-- Pessoa Jurídica --}}
        <a href="{{ route('company.estabelecimentos.create.juridica') }}"
           class="group flex items-center gap-5 bg-white border border-gray-200 rounded-2xl p-5 hover:border-blue-400 hover:shadow-md transition-all">
            <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-blue-50 group-hover:bg-blue-100 flex items-center justify-center transition-colors">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-base font-semibold text-gray-900">Pessoa Jurídica</span>
                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">CNPJ</span>
                </div>
                <p class="text-sm text-gray-500 leading-snug">Empresas, comércios e indústrias. Dados consultados automaticamente na Receita Federal.</p>
            </div>
            <svg class="flex-shrink-0 w-5 h-5 text-gray-300 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        {{-- Pessoa Física --}}
        <a href="{{ route('company.estabelecimentos.create.fisica') }}"
           class="group flex items-center gap-5 bg-white border border-gray-200 rounded-2xl p-5 hover:border-green-400 hover:shadow-md transition-all">
            <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-green-50 group-hover:bg-green-100 flex items-center justify-center transition-colors">
                <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-base font-semibold text-gray-900">Pessoa Física</span>
                    <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">CPF</span>
                </div>
                <p class="text-sm text-gray-500 leading-snug">Profissionais autônomos. Dados preenchidos manualmente.</p>
            </div>
            <svg class="flex-shrink-0 w-5 h-5 text-gray-300 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        {{-- PJ Unidade Móvel --}}
        <a href="{{ route('company.estabelecimentos.create.unidade-movel') }}"
           class="group flex items-center gap-5 bg-white border border-gray-200 rounded-2xl p-5 hover:border-purple-400 hover:shadow-md transition-all">
            <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-purple-50 group-hover:bg-purple-100 flex items-center justify-center transition-colors">
                <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-base font-semibold text-gray-900">PJ Unidade Móvel</span>
                    <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full">Itinerante</span>
                </div>
                <p class="text-sm text-gray-500 leading-snug">Empresas que prestam serviço temporário/itinerante via unidade móvel (UTI Móvel, carreta, van, etc.) em municípios do Tocantins.</p>
            </div>
            <svg class="flex-shrink-0 w-5 h-5 text-gray-300 group-hover:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Link voltar --}}
    <div class="mt-6 text-center">
        <a href="{{ route('company.estabelecimentos.index') }}" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">
            ← Voltar para Meus Estabelecimentos
        </a>
    </div>

</div>
@endsection
