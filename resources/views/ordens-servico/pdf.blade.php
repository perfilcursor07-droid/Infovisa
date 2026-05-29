<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordem de Serviço #{{ $ordemServico->numero }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            font-size: 11px;
        }
        
        .page {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
            background: white;
        }
        
        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 6px;
            margin-bottom: 8px;
            text-align: center;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-container img {
            max-height: 100px;
            max-width: 400px;
            height: auto;
            width: auto;
        }
        
        .header-title {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }
        
        .header-subtitle {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        
        .section {
            margin-bottom: 6px;
        }
        
        .section-title {
            background-color: #f0f0f0;
            padding: 4px 6px;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            border-left: 3px solid #333;
        }
        
        .section-content {
            padding: 5px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
            table-layout: fixed;
        }
        
        .info-item {
            display: table-cell;
            width: 20%;
            padding-right: 8px;
            vertical-align: top;
        }
        
        .label {
            font-weight: bold;
            color: #555;
            font-size: 8px;
            text-transform: uppercase;
        }
        
        .value {
            color: #000;
            margin-top: 1px;
            font-size: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-em-andamento {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-finalizada {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-cancelada {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 10px;
            border-left: 3px solid;
            font-size: 10px;
            line-height: 1.4;
        }
        
        .alert-green {
            background-color: #f0fdf4;
            border-color: #22c55e;
            color: #166534;
        }
        
        .alert-amber {
            background-color: #fffbeb;
            border-color: #f59e0b;
            color: #92400e;
        }
        
        .alert-red {
            background-color: #fef2f2;
            border-color: #ef4444;
            color: #991b1b;
        }
        
        .equipment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        
        .equipment-table th {
            background-color: #e5e7eb;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            border-bottom: 1px solid #999;
        }
        
        .equipment-table td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        
        .equipment-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .atividade {
            background-color: #f9fafb;
            padding: 7px;
            margin-bottom: 5px;
            border-left: 3px solid #3b82f6;
        }
        
        .atividade-titulo {
            font-weight: bold;
            color: #1f2937;
            font-size: 10px;
            margin-bottom: 3px;
        }
        
        .atividade-info {
            font-size: 9px;
            color: #666;
            margin-bottom: 2px;
        }

        .checklist-box {
            border: 1px solid #d1d5db;
            padding: 8px;
            margin-bottom: 8px;
            background-color: #fafafa;
        }

        .checklist-stats {
            margin-bottom: 6px;
            font-size: 9px;
            color: #444;
        }

        .progress-bar {
            width: 100%;
            height: 7px;
            background-color: #e5e7eb;
            margin: 4px 0 6px;
        }

        .progress-bar-fill {
            height: 7px;
            background-color: #2563eb;
        }

        .checklist-item {
            padding: 4px 6px;
            margin-bottom: 4px;
            border-left: 3px solid #9ca3af;
            background-color: #fff;
            font-size: 9px;
        }

        .checklist-item.ok {
            border-left-color: #16a34a;
            background-color: #f0fdf4;
        }

        .checklist-item.alerta {
            border-left-color: #dc2626;
            background-color: #fef2f2;
        }

        .checklist-item.aviso {
            border-left-color: #d97706;
            background-color: #fffbeb;
        }

        .badge-inline {
            display: inline-block;
            padding: 1px 5px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 10px;
            margin-left: 4px;
        }

        .badge-green {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-red {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-amber {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-gray {
            background-color: #e5e7eb;
            color: #374151;
        }

        .mini-list {
            margin-top: 4px;
            padding-left: 14px;
            font-size: 8.5px;
            color: #555;
        }

        .mini-list li {
            margin-bottom: 2px;
        }
        
        .footer {
            margin-top: 20px;
            padding: 15px 0;
            border-top: 2px solid #333;
            font-size: 7.5pt;
            color: #000;
            width: 100%;
            overflow: hidden;
        }

        .footer-content {
            width: 100%;
            overflow: hidden;
        }

        .footer-logo {
            float: left;
            width: 70px;
            padding-right: 12px;
        }

        .footer-logo img {
            max-height: 50px;
            max-width: 70px;
            height: auto;
            width: auto;
            display: block;
        }

        .footer-text {
            overflow: hidden;
            text-align: justify;
            line-height: 1.5;
            word-wrap: break-word;
        }

        .qrcode-section {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            text-align: center;
            background-color: #f9fafb;
        }

        .qrcode-section h4 {
            font-size: 10px;
            font-weight: bold;
            color: #374151;
            margin: 0 0 6px 0;
        }

        .qrcode-section img {
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }

        .qrcode-section p {
            font-size: 8px;
            color: #6b7280;
            margin: 5px 0 0 0;
        }
    </style>
</head>
<body>
    @php
        $estabelecimentoPdf = $estabelecimentoPdf ?? $ordemServico->estabelecimento;
        $processoPdf = $processoPdf ?? $ordemServico->processo;
        $checklistPdf = $checklistPdf ?? [];
        $documentosObrigatoriosPdf = collect($checklistPdf['documentos_obrigatorios'] ?? []);
        $documentosPendentesPdf = collect($checklistPdf['documentos_pendentes'] ?? []);
        $atividadesExigemRtPdf = collect($checklistPdf['atividades_exigem_rt'] ?? []);
        $totalDocumentosPdf = $checklistPdf['total_documentos'] ?? 0;
        $totalAprovadosPdf = $checklistPdf['total_aprovados'] ?? 0;
        $totalPendentesPdf = $checklistPdf['total_pendentes'] ?? 0;
        $totalRejeitadosPdf = $checklistPdf['total_rejeitados'] ?? 0;
        $totalNaoEnviadosPdf = $checklistPdf['total_nao_enviados'] ?? 0;
        $percentualChecklistPdf = $totalDocumentosPdf > 0 ? (int) round(($totalAprovadosPdf / $totalDocumentosPdf) * 100) : 0;
    @endphp
    <div class="page">
        {{-- Logomarca --}}
        @if(isset($logomarca) && $logomarca)
            <div class="logo-container">
                @php
                    $logoPathRelativo = str_replace('storage/', '', $logomarca);
                    $logoPath = public_path('storage/' . $logoPathRelativo);

                    if (file_exists($logoPath)) {
                        try {
                            $imageData = file_get_contents($logoPath);
                            $imageInfo = getimagesize($logoPath);

                            if ($imageInfo && $imageData) {
                                $mimeType = $imageInfo['mime'];
                                $base64 = base64_encode($imageData);
                                echo '<img src="data:' . $mimeType . ';base64,' . $base64 . '" alt="Logomarca" style="max-width: 100%; height: auto;">';
                            }
                        } catch (\Exception $e) {
                            // Ignora erros
                        }
                    }
                @endphp
            </div>
        @endif

        {{-- Header --}}
        <div class="header">
            <div class="header-title">Ordem de Serviço #{{ str_pad($ordemServico->numero, 5, '0', STR_PAD_LEFT) }}</div>
        </div>

        {{-- Informações da OS --}}
        <div class="section">
            <div class="section-title">INFORMAÇÕES DA ORDEM DE SERVIÇO</div>
            <div class="section-content">
                @php
                    $dataInicioOs = $ordemServico->data_inicio
                        ? $ordemServico->data_inicio->format('d/m/Y')
                        : '-';

                    $dataTerminoOs = $ordemServico->data_fim
                        ? $ordemServico->data_fim->format('d/m/Y')
                        : ($ordemServico->data_conclusao
                            ? $ordemServico->data_conclusao->format('d/m/Y')
                            : ($ordemServico->finalizada_em
                                ? $ordemServico->finalizada_em->format('d/m/Y')
                                : '-'));

                    $municipioOs = $ordemServico->municipio?->nome
                        ?? $estabelecimentoPdf?->municipio?->nome
                        ?? $estabelecimentoPdf?->municipioRelacionado?->nome
                        ?? ((is_object($estabelecimentoPdf?->municipio) && !empty($estabelecimentoPdf->municipio->nome))
                            ? $estabelecimentoPdf->municipio->nome
                            : null)
                        ?? (is_string($estabelecimentoPdf?->municipio) ? $estabelecimentoPdf->municipio : null)
                        ?? $estabelecimentoPdf?->cidade
                        ?? '-';
                @endphp

                <div class="info-row">
                    <div class="info-item" style="width: 25%;">
                        <div class="label">Número</div>
                        <div class="value">{{ str_pad($ordemServico->numero, 5, '0', STR_PAD_LEFT) }}</div>
                    </div>
                    <div class="info-item" style="width: 25%;">
                        <div class="label">Competência</div>
                        <div class="value">{{ ucfirst($ordemServico->competencia) }}</div>
                    </div>
                    <div class="info-item" style="width: 25%; padding-right: 0;">
                        <div class="label">Município</div>
                        <div class="value">{{ $municipioOs }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-item" style="width: 25%;">
                        <div class="label">Data de Emissão</div>
                        <div class="value">{{ $ordemServico->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-item" style="width: 25%;">
                        <div class="label">Início da OS</div>
                        <div class="value">{{ $dataInicioOs }}</div>
                    </div>
                    <div class="info-item" style="width: 25%;">
                        <div class="label">Término da OS</div>
                        <div class="value">{{ $dataTerminoOs }}</div>
                    </div>
                    <div class="info-item" style="width: 25%; padding-right: 0;">
                        <div class="label">Processo Vinculado</div>
                        <div class="value">{{ $processoPdf?->numero_processo ?? '-' }}</div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-item" style="width: 100%;">
                        <div class="label">Descrição da OS</div>
                        <div class="value">{{ $ordemServico->observacoes ?? $ordemServico->descricao ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informações do Estabelecimento --}}
        @if($estabelecimentoPdf)
        <div class="section">
            <div class="section-title">DADOS DO ESTABELECIMENTO</div>
            <div class="section-content">
                <div class="info-row">
                    <div class="info-item">
                        <div class="label">Razão Social / Nome</div>
                        <div class="value">{{ $estabelecimentoPdf->razao_social ?? $estabelecimentoPdf->nome_fantasia }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Nome Fantasia</div>
                        <div class="value">{{ $estabelecimentoPdf->nome_fantasia ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">CNPJ / CPF</div>
                        <div class="value">
                            @if($estabelecimentoPdf->tipo_pessoa === 'fisica')
                                {{ $estabelecimentoPdf->cpf_formatado ?? '-' }}
                            @else
                                {{ $estabelecimentoPdf->cnpj_formatado ?? '-' }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-item">
                        <div class="label">Endereço</div>
                        <div class="value">
                            {{ $estabelecimentoPdf->logradouro }}
                            @if($estabelecimentoPdf->numero), {{ $estabelecimentoPdf->numero }}@endif
                            @if($estabelecimentoPdf->complemento) - {{ $estabelecimentoPdf->complemento }}@endif
                            , {{ $estabelecimentoPdf->bairro }}
                            @if(is_object($estabelecimentoPdf->municipio)) - {{ $estabelecimentoPdf->municipio->nome }}/{{ $estabelecimentoPdf->municipio->uf }}@endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="section">
            <div class="section-title">CHECKLIST DOCUMENTAL E RESPONSÁVEIS</div>
            <div class="section-content">
                <div class="checklist-box">
                    <div class="checklist-stats">
                        <strong>{{ $checklistPdf['titulo_documentos'] ?? 'Docs. Licenciamento' }}:</strong>
                        {{ $totalAprovadosPdf }}/{{ $totalDocumentosPdf }} aprovados
                    </div>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: {{ $percentualChecklistPdf }}%;"></div>
                    </div>
                    <div class="checklist-stats">
                        {{ $percentualChecklistPdf }}% concluído
                        @if($totalPendentesPdf > 0)
                            | {{ $totalPendentesPdf }} pendente(s)
                        @endif
                        @if($totalRejeitadosPdf > 0)
                            | {{ $totalRejeitadosPdf }} rejeitado(s)
                        @endif
                        @if($totalNaoEnviadosPdf > 0)
                            | {{ $totalNaoEnviadosPdf }} não enviado(s)
                        @endif
                    </div>

                    @if($documentosPendentesPdf->isEmpty() && $totalDocumentosPdf > 0)
                        <div class="checklist-item ok">
                            <strong>Documentação obrigatória regular.</strong> Todos os documentos obrigatórios deste processo estão aprovados.
                        </div>
                    @elseif($documentosPendentesPdf->isEmpty())
                        <div class="checklist-item aviso">
                            <strong>Nenhum documento obrigatório configurado</strong> para este processo no momento.
                        </div>
                    @else
                        <div class="checklist-item alerta">
                            <strong>Documentos pendentes para análise/entrega:</strong>
                            <ul class="mini-list">
                                @foreach($documentosPendentesPdf as $documento)
                                    @php
                                        $statusDocumento = $documento['status'] ?? null;
                                        $rotuloStatus = match($statusDocumento) {
                                            'pendente' => 'Pendente de aprovação',
                                            'rejeitado' => 'Rejeitado',
                                            default => 'Não enviado',
                                        };
                                    @endphp
                                    <li>{{ $documento['nome'] }} ({{ $rotuloStatus }})</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="checklist-box">
                    <div class="checklist-item {{ ($checklistPdf['responsavel_legal_ok'] ?? false) ? 'ok' : 'alerta' }}">
                        <strong>Responsável legal:</strong>
                        @if($checklistPdf['responsavel_legal_ok'] ?? false)
                            cadastrado(s) {{ $checklistPdf['responsavel_legal_total'] ?? 0 }}
                            <span class="badge-inline badge-green">OK</span>
                        @else
                            nenhum responsável legal vinculado
                            <span class="badge-inline badge-red">PENDENTE</span>
                        @endif
                    </div>

                    <div class="checklist-item {{ ($checklistPdf['responsavel_tecnico_ok'] ?? false) || !($checklistPdf['responsavel_tecnico_obrigatorio'] ?? false) ? 'ok' : 'alerta' }}">
                        <strong>Responsável técnico:</strong>
                        @if($checklistPdf['responsavel_tecnico_obrigatorio'] ?? false)
                            obrigatório para este estabelecimento
                            @if($checklistPdf['responsavel_tecnico_ok'] ?? false)
                                , com {{ $checklistPdf['responsavel_tecnico_total'] ?? 0 }} vinculado(s)
                                <span class="badge-inline badge-green">OK</span>
                            @else
                                , mas ainda não vinculado
                                <span class="badge-inline badge-red">PENDENTE</span>
                            @endif
                        @else
                            opcional no momento
                            @if($checklistPdf['responsavel_tecnico_ok'] ?? false)
                                , com {{ $checklistPdf['responsavel_tecnico_total'] ?? 0 }} vinculado(s)
                                <span class="badge-inline badge-green">CADASTRADO</span>
                            @else
                                <span class="badge-inline badge-gray">NÃO OBRIGATÓRIO</span>
                            @endif
                        @endif

                        @if($atividadesExigemRtPdf->isNotEmpty())
                            <ul class="mini-list">
                                @foreach($atividadesExigemRtPdf as $atividadeRt)
                                    <li>{{ $atividadeRt }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Status de Equipamentos de Imagem --}}
        @php
            $codigosAtividadesRadiacao = \App\Models\AtividadeEquipamentoRadiacao::where('ativo', true)
                ->pluck('codigo_atividade')
                ->map(fn($c) => preg_replace('/[^0-9]/', '', $c))
                ->unique()
                ->filter()
                ->toArray();
            
            $exigeEquipamentos = false;
            if ($estabelecimentoPdf) {
                $atividadesEstabelecimento = $estabelecimentoPdf->getTodasAtividades();
                foreach ($atividadesEstabelecimento as $codigo) {
                    if (in_array($codigo, $codigosAtividadesRadiacao)) {
                        $exigeEquipamentos = true;
                        break;
                    }
                }
            }
        @endphp

        @if($exigeEquipamentos && $estabelecimentoPdf)
        <div class="section">
            <div class="section-title">EQUIPAMENTOS DE IMAGEM</div>
            <div class="section-content">
                @if($estabelecimentoPdf->equipamentosRadiacao()->count() > 0)
                    <div class="alert alert-green">
                        <strong>✓ Equipamentos Registrados</strong><br>
                        Este estabelecimento possui {{ $estabelecimentoPdf->equipamentosRadiacao()->count() }} equipamento(s) de imagem cadastrado(s).
                    </div>
                    <table class="equipment-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Fabricante</th>
                                <th>Modelo</th>
                                <th>Registro ANVISA</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estabelecimentoPdf->equipamentosRadiacao()->get() as $equipamento)
                            <tr>
                                <td>{{ $equipamento->tipo_equipamento }}</td>
                                <td>{{ $equipamento->fabricante }}</td>
                                <td>{{ $equipamento->modelo }}</td>
                                <td>{{ $equipamento->registro_anvisa }}</td>
                                <td>{{ ucfirst($equipamento->status) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif($estabelecimentoPdf->declaracao_sem_equipamentos_imagem)
                    <div class="alert alert-amber">
                        <strong>⚠ Declaração: Não Possui Equipamentos</strong><br>
                        O estabelecimento declarou formalmente que NÃO POSSUI equipamentos de imagem.
                        
                        @if($estabelecimentoPdf->declaracao_sem_equipamentos_opcoes)
                        @php
                            $opcoes = json_decode($estabelecimentoPdf->declaracao_sem_equipamentos_opcoes, true) ?? [];
                        @endphp
                        @if(count($opcoes) > 0)
                        <br><br><strong>Confirmações:</strong><br>
                        @if(in_array('opcao_1', $opcoes))
                        [X] Não executa atividades de diagnóstico por imagem neste estabelecimento<br>
                        @endif
                        @if(in_array('opcao_2', $opcoes))
                        [X] Não possui equipamentos de diagnóstico por imagem instalados no local<br>
                        @endif
                        @if(in_array('opcao_3', $opcoes))
                        [X] Os exames, quando necessários, são integralmente terceirizados ou realizados em outro estabelecimento regularmente licenciado<br>
                        @endif
                        @endif
                        @endif
                        
                        @if($estabelecimentoPdf->declaracao_sem_equipamentos_imagem_justificativa)
                        <br><strong>Justificativa:</strong> {{ $estabelecimentoPdf->declaracao_sem_equipamentos_imagem_justificativa }}
                        @endif
                    </div>
                @else
                    <div class="alert alert-red">
                        <strong>✗ Equipamentos Não Registrados</strong><br>
                        Este estabelecimento não possui equipamentos de imagem cadastrados e nem declaração formal.
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Ações Executadas --}}
        @if($ordemServico->atividades_tecnicos && count($ordemServico->atividades_tecnicos) > 0)
        <div class="section">
            <div class="section-title">DETALHAMENTO DAS AÇÕES E TÉCNICOS RESPONSÁVEIS</div>
            <div class="section-content">
                @foreach($ordemServico->atividades_tecnicos as $index => $atividade)
                <div class="atividade">
                    <div class="atividade-titulo">{{ $index + 1 }}. {{ $atividade['nome_atividade'] ?? 'Atividade Desconhecida' }}</div>
                    
                    {{-- Técnicos Atribuídos --}}
                    @php
                        $responsavelId = $atividade['responsavel_id'] ?? null;
                        $tecnicosIds = $atividade['tecnicos'] ?? [];
                        $tecnicos = \App\Models\UsuarioInterno::whereIn('id', $tecnicosIds)->get();
                        $tecnicosListados = [];
                        
                        // Primeiro adiciona o responsável com destaque
                        if ($responsavelId) {
                            $responsavel = $tecnicos->firstWhere('id', $responsavelId);
                            if ($responsavel) {
                                $tecnicosListados[] = $responsavel->nome . ' - Técnico Responsável';
                            }
                        }
                        
                        // Depois adiciona os outros técnicos
                        foreach ($tecnicos as $tec) {
                            if ($tec->id != $responsavelId) {
                                $tecnicosListados[] = $tec->nome;
                            }
                        }
                    @endphp
                    
                    @if(count($tecnicosListados) > 0)
                    <div class="atividade-info">
                        <strong>Técnicos:</strong> {{ implode(', ', $tecnicosListados) }}
                    </div>
                    @endif
                    
                    {{-- Status --}}
                    @php
                        $statusAtividade = $atividade['status'] ?? 'pendente';
                        $statusLabel = $statusAtividade === 'finalizada' ? 'Finalizada' : 'Pendente';
                    @endphp
                    <div class="atividade-info">
                        <strong>Status:</strong> {{ $statusLabel }}
                    </div>
                    
                    @if(isset($atividade['observacoes']) && $atividade['observacoes'])
                    <div class="atividade-info">
                        <strong>Observações:</strong> {{ $atividade['observacoes'] }}
                    </div>
                    @endif
                    
                    @if($statusAtividade === 'finalizada' && isset($atividade['finalizada_em']))
                    <div class="atividade-info">
                        <strong>Data de Finalização:</strong> {{ \Carbon\Carbon::parse($atividade['finalizada_em'])->format('d/m/Y H:i') }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Ações Executadas (Compatibilidade com formato antigo) --}}

        {{-- QR Code Pesquisa de Satisfação --}}
        @if(!empty($qrCodePesquisaBase64))
        <div class="qrcode-section">
            <h4>Pesquisa de Satisfação</h4>
            <img src="data:image/png;base64,{{ $qrCodePesquisaBase64 }}" alt="QR Code Pesquisa">
            <p>Escaneie o QR Code acima para avaliar o atendimento desta Ordem de Serviço.</p>
            @if(!empty($linkPesquisaExterna))
            <p style="font-size: 7px; color: #9ca3af; margin-top: 2px;">{{ $linkPesquisaExterna }}</p>
            @endif
        </div>
        @endif

        {{-- Assinatura do Gestor Estadual --}}
        @if($ordemServico->gestor_assinatura_id && $ordemServico->gestorAssinatura && $ordemServico->assinadaPeloGestor())
        <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
            <div style="text-align: center; max-width: 300px; margin: 0 auto;">
                <div style="border-top: 1px solid #000; padding-top: 5px; margin-top: 40px;">
                    <p style="font-size: 9pt; font-weight: bold; margin: 0;">{{ $ordemServico->gestorAssinatura->nome }}</p>
                    @if($ordemServico->gestorAssinatura->cargo)
                    <p style="font-size: 8pt; color: #4b5563; margin: 2px 0 0 0;">{{ $ordemServico->gestorAssinatura->cargo }}</p>
                    @endif
                    @php
                        $setorGestor = null;
                        if ($ordemServico->gestorAssinatura->setor) {
                            $setorGestor = \App\Models\TipoSetor::where('codigo', $ordemServico->gestorAssinatura->setor)->value('nome');
                        }
                        if (!$setorGestor) {
                            $setorGestor = $ordemServico->gestorAssinatura->tipoSetores->first()?->nome;
                        }
                    @endphp
                    @if($setorGestor)
                    <p style="font-size: 7pt; color: #6b7280; margin: 2px 0 0 0;">{{ $setorGestor }}</p>
                    @endif
                    <p style="font-size: 7pt; color: #6b7280; margin: 4px 0 0 0;">
                        Assinado digitalmente em {{ $ordemServico->gestor_assinado_em->format('d/m/Y') }} às {{ $ordemServico->gestor_assinado_em->format('H:i') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <div class="footer-content">
                <div class="footer-logo">
                    @php
                        try {
                            $rodapeDocumento = $rodapeDocumento ?? null;

                            if (!$rodapeDocumento) {
                                $estabelecimentoRodape = $estabelecimentoPdf ?? $ordemServico->estabelecimento;
                                $municipioRodape = null;

                                if ($estabelecimentoRodape && !$estabelecimentoRodape->isCompetenciaEstadual()) {
                                    $municipioRodapeObj = $estabelecimentoRodape->municipio ?? null;

                                    if (!$municipioRodapeObj && $estabelecimentoRodape->municipio_id) {
                                        $municipioRodapeObj = \App\Models\Municipio::find($estabelecimentoRodape->municipio_id);
                                    }

                                    if ($municipioRodapeObj && !empty($municipioRodapeObj->rodape_documento)) {
                                        $municipioRodape = $municipioRodapeObj->rodape_documento;
                                    }
                                }

                                $rodapeDocumento = $municipioRodape ?: \App\Models\ConfiguracaoSistema::rodapeEstadual();
                            }

                            $rodapeImagePath = $rodapeDocumento ? public_path(ltrim($rodapeDocumento, '/')) : null;

                            if ($rodapeImagePath && file_exists($rodapeImagePath)) {
                                $imageData = file_get_contents($rodapeImagePath);
                                $imageInfo = getimagesize($rodapeImagePath);

                                if ($imageInfo && $imageData) {
                                    $mimeType = $imageInfo['mime'];
                                    $base64 = base64_encode($imageData);
                                    echo '<img src="data:' . $mimeType . ';base64,' . $base64 . '" alt="Rodapé" style="max-width: 100%; height: auto;">';
                                }
                            }
                        } catch (\Exception $e) {
                            // Ignora erros
                        }
                    @endphp
                </div>
                <div class="footer-text">
                    @php
                        $rodapeTexto = $rodapeTexto ?? null;

                        if (!$rodapeTexto) {
                            $estabelecimentoTexto = $estabelecimentoPdf ?? $ordemServico->estabelecimento;
                            $municipioTexto = null;

                            if ($estabelecimentoTexto && !$estabelecimentoTexto->isCompetenciaEstadual()) {
                                $municipioTextoObj = $estabelecimentoTexto->municipio ?? null;

                                if (!$municipioTextoObj && $estabelecimentoTexto->municipio_id) {
                                    $municipioTextoObj = \App\Models\Municipio::find($estabelecimentoTexto->municipio_id);
                                }

                                if ($municipioTextoObj && !empty($municipioTextoObj->rodape_texto)) {
                                    $municipioTexto = $municipioTextoObj->rodape_texto;
                                }
                            }

                            $rodapeTexto = $municipioTexto ?: \App\Models\ConfiguracaoSistema::rodapeTextoPadrao();
                        }
                    @endphp
                    {!! nl2br(e($rodapeTexto)) !!}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
