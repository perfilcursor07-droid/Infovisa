@extends('layouts.admin')

@section('title', 'Usuários Externos')
@section('page-title', 'Usuários Externos')

@section('content')
@php
    $inputClasse = 'w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-sm transition';
    $labelClasse = 'block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1';
@endphp
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-900 tracking-tight leading-tight">Usuários Externos</h1>
                <p class="text-xs text-slate-400">Gerencie os usuários externos do sistema</p>
            </div>
        </div>
        @if(auth('interno')->user()->isAdmin())
            <a href="{{ route('admin.usuarios-externos.create') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-semibold shadow-sm shadow-blue-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/>
                </svg>
                Novo Usuário
            </a>
        @endif
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80">
        <form method="GET" action="{{ route('admin.usuarios-externos.index') }}">
            <div class="p-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
                {{-- Nome --}}
                <div>
                    <label class="{{ $labelClasse }}">Nome</label>
                    <input type="text" name="nome" value="{{ request('nome') }}"
                           placeholder="Buscar por nome"
                           class="{{ $inputClasse }}">
                </div>

                {{-- CPF --}}
                <div>
                    <label class="{{ $labelClasse }}">CPF</label>
                    <input type="text" name="cpf" value="{{ request('cpf') }}"
                           placeholder="000.000.000-00"
                           class="{{ $inputClasse }}">
                </div>

                {{-- Email --}}
                <div>
                    <label class="{{ $labelClasse }}">Email</label>
                    <input type="email" name="email" value="{{ request('email') }}"
                           placeholder="email@exemplo.com"
                           class="{{ $inputClasse }}">
                </div>

                {{-- Vínculo --}}
                <div>
                    <label class="{{ $labelClasse }}">Vínculo</label>
                    <select name="vinculo_estabelecimento" class="{{ $inputClasse }}">
                        <option value="">Todos</option>
                        @foreach(\App\Enums\VinculoEstabelecimento::cases() as $vinculo)
                            <option value="{{ $vinculo->value }}" {{ request('vinculo_estabelecimento') == $vinculo->value ? 'selected' : '' }}>
                                {{ $vinculo->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="{{ $labelClasse }}">Status</label>
                    <select name="ativo" class="{{ $inputClasse }}">
                        <option value="">Todos</option>
                        <option value="1" {{ request('ativo') === '1' ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ request('ativo') === '0' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>

                {{-- Aceite de Termos --}}
                <div>
                    <label class="{{ $labelClasse }}">Aceite de Termos</label>
                    <select name="aceite_termos" class="{{ $inputClasse }}">
                        <option value="">Todos</option>
                        <option value="1" {{ request('aceite_termos') === '1' ? 'selected' : '' }}>Aceito</option>
                        <option value="0" {{ request('aceite_termos') === '0' ? 'selected' : '' }}>Não Aceito</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 px-3 py-2.5 border-t border-slate-100">
                <a href="{{ route('admin.usuarios-externos.index') }}" class="px-3.5 py-1.5 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition text-sm font-semibold">
                    Limpar Filtros
                </a>
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-sm transition text-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100">
            <p class="text-xs text-slate-500">
                Mostrando <span class="font-semibold text-slate-700">{{ $usuarios->firstItem() ?? 0 }}</span> a <span class="font-semibold text-slate-700">{{ $usuarios->lastItem() ?? 0 }}</span> de <span class="font-semibold text-slate-700">{{ $usuarios->total() }}</span> usuários
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                            <a href="{{ route('admin.usuarios-externos.index', array_merge(request()->all(), ['sort' => 'nome', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-blue-600">
                                Nome
                                @if(request('sort') === 'nome')
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('direction') === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
                                    </svg>
                                @endif
                            </a>
                        </th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">CPF</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Telefone</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Vínculo</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Termos</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($usuarios as $usuario)
                        <tr class="hover:bg-blue-50/40 transition-colors">
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-purple-600 font-bold text-[11px]">{{ strtoupper(substr($usuario->nome, 0, 2)) }}</span>
                                    </div>
                                    <div class="text-[13px] font-semibold text-slate-800">{{ $usuario->nome }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-[13px] text-slate-600 tabular-nums whitespace-nowrap">{{ $usuario->cpf_formatado }}</td>
                            <td class="px-4 py-2.5 text-[13px] text-slate-600">{{ $usuario->email }}</td>
                            <td class="px-4 py-2.5 text-[13px] text-slate-600 whitespace-nowrap">{{ $usuario->telefone_formatado ?? '-' }}</td>
                            <td class="px-4 py-2.5">
                                @if($usuario->vinculo_estabelecimento)
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-blue-50 text-blue-700">
                                        {{ $usuario->vinculo_estabelecimento->label() }}
                                    </span>
                                @else
                                    <span class="text-slate-300 text-sm">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @if($usuario->aceitouTermos())
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-700" title="Aceito em {{ $usuario->aceite_termos_em->format('d/m/Y H:i') }}">
                                        ✓ Aceito
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-50 text-amber-700">
                                        Pendente
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @if($usuario->ativo)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-700"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Ativo</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-red-50 text-red-700"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Inativo</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.usuarios-externos.show', $usuario) }}"
                                       class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Visualizar">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.usuarios-externos.edit', $usuario) }}"
                                       class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Editar">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @if(auth('interno')->user()->isAdmin())
                                        <form action="{{ route('admin.usuarios-externos.destroy', $usuario) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="Excluir">
                                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-14 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-600">Nenhum usuário encontrado</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($usuarios->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
