@php
    $searchText = strtolower(implode(' ', array_filter([
        $usuario->nome,
        $usuario->cargo,
        $usuario->setor,
        $usuario->nivel_acesso?->label(),
        $usuario->municipioRelacionado?->nome,
    ])));
    $selecionado = in_array($usuario->id, $assinantesAtuais);
@endphp
<label class="assinante-item flex items-start gap-3 p-2.5 hover:bg-gray-50 rounded-lg cursor-pointer transition"
       data-escopo="{{ $escopo }}"
       data-search="{{ $searchText }}">
    <input type="checkbox" name="assinantes[]" value="{{ $usuario->id }}"
           {{ $selecionado ? 'checked' : '' }}
           class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 flex-shrink-0">
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
            <p class="text-sm font-medium text-gray-900">{{ $usuario->nome }}</p>
            @if($selecionado)
            <span class="text-[10px] px-1.5 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">Atual</span>
            @endif
            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium {{ $escopo === 'estadual' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                {{ $escopo === 'estadual' ? 'Estadual' : 'Municipal' }}
            </span>
        </div>
        <p class="text-xs text-gray-500 mt-0.5">{{ $usuario->cargo ?? 'Cargo não informado' }}</p>
        @if($usuario->setor || $usuario->nivel_acesso)
        <p class="text-[11px] text-gray-400 mt-0.5">
            {{ $usuario->nivel_acesso?->label() }}
            @if($usuario->setor) · {{ $usuario->setor }} @endif
            @if($usuario->municipioRelacionado) · {{ $usuario->municipioRelacionado->nome }} @endif
        </p>
        @endif
    </div>
</label>
