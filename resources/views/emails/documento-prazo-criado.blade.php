<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="500" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#d97706,#b45309);padding:30px 40px;text-align:center;">
                            <h1 style="color:#ffffff;font-size:22px;margin:0;">📄 InfoVISA</h1>
                            <p style="color:#fde68a;font-size:13px;margin:8px 0 0;">{{ ($comPrazo ?? true) ? 'Novo Documento com Prazo' : 'Novo Documento Disponivel' }}</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:30px 40px;">
                            <p style="color:#374151;font-size:15px;margin:0 0 15px;">Olá, <strong>{{ $nomeDestinatario }}</strong>!</p>
                            <p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0 0 20px;">
                                @if($comPrazo ?? true)
                                A Vigilância Sanitária emitiu um novo documento com prazo para o estabelecimento <strong>{{ $nomeEstabelecimento }}</strong>.
                                @else
                                A Vigilância Sanitária emitiu um novo documento para o estabelecimento <strong>{{ $nomeEstabelecimento }}</strong>.
                                @endif
                            </p>

                            <!-- Info do documento -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#fffbeb;border:1px solid #fde68a;border-radius:8px;margin:0 0 20px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <p style="color:#92400e;font-size:13px;margin:0 0 8px;"><strong>Tipo:</strong> {{ $tipoDocumento }}</p>
                                        <p style="color:#92400e;font-size:13px;margin:0 0 8px;"><strong>Nº:</strong> {{ $numeroDocumento }}</p>
                                        <p style="color:#92400e;font-size:13px;margin:0 0 8px;"><strong>Processo:</strong> {{ $numeroProcesso }}</p>
                                        @if(($comPrazo ?? true) && !empty($prazoDias))
                                        <p style="color:#92400e;font-size:13px;margin:0;"><strong>Prazo:</strong> {{ $prazoDias }} dias</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <!-- Alerta -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;margin:0 0 25px;">
                                <tr>
                                    <td style="padding:12px 20px;">
                                        <p style="color:#1e40af;font-size:12px;margin:0;line-height:1.5;">
                                            📌 <strong>Ação necessária:</strong> Acesse o sistema para visualizar o documento e tomar as providências cabíveis.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:10px 0 25px;">
                                        <a href="{{ $linkDocumento }}" style="display:inline-block;background-color:#d97706;color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:8px;font-size:14px;font-weight:bold;">
                                            Visualizar Documento
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <hr style="border:none;border-top:1px solid #e5e7eb;margin:20px 0;">
                            <p style="color:#9ca3af;font-size:11px;margin:0;">
                                Se o botão não funcionar, copie e cole este link no navegador:<br>
                                <a href="{{ $linkDocumento }}" style="color:#d97706;word-break:break-all;">{{ $linkDocumento }}</a>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f9fafb;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;">
                            <p style="color:#9ca3af;font-size:11px;margin:0;">InfoVISA - Vigilância Sanitária do Tocantins</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
