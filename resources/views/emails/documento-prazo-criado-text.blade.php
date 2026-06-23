InfoVISA - Vigilancia Sanitaria do Tocantins

Ola, {{ $nomeDestinatario }}!

@if($comPrazo)
A Vigilancia Sanitaria emitiu um novo documento com prazo para o estabelecimento {{ $nomeEstabelecimento }}.
@else
A Vigilancia Sanitaria emitiu um novo documento para o estabelecimento {{ $nomeEstabelecimento }}.
@endif

Tipo: {{ $tipoDocumento }}
Numero: {{ $numeroDocumento }}
Processo: {{ $numeroProcesso }}
@if($comPrazo && $prazoDias)
Prazo: {{ $prazoDias }} dias
@endif

Acesse o sistema para visualizar o documento:
{{ $linkDocumento }}

---
Este e um e-mail automatico. Nao responda a esta mensagem.
