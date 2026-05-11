$path = 'app/Http/Controllers/Admin/DashboardController.php'
$content = Get-Content -Path $path -Raw

$oldBlock = @'
        // Buscar documentos pendentes de aprovação
        // REGRAS DE VISIBILIDADE:
        // 1) Docs OBRIGATÓRIOS (tipo_documento_obrigatorio_id preenchido): aparecem para o
        //    Setor atual do processo OU Setor Responsável pela Análise Inicial do tipo de processo.
        // 2) Docs FORA da lista obrigatória (tipo_documento_obrigatorio_id NULL): aparecem para
        //    o setor onde o processo está atualmente (setor_atual).
        $documentos_pendentes_query = ProcessoDocumento::where('status_aprovacao', 'pendente')
            ->where('tipo_usuario', 'externo')
            ->with(['processo.estabelecimento']);

        $respostas_pendentes_query = DocumentoResposta::where('status', 'pendente')
            ->with(['documentoDigital.processo.estabelecimento', 'documentoDigital.tipoDocumento', 'documentoDigital.assinaturas']);

        // Filtrar por setor/responsável do processo + competência
        if (!$usuario->isAdmin()) {
            $documentos_pendentes_query->where(function($mainQuery) use ($usuario) {
                // CASO 1: Docs obrigatórios → setor atual do processo OU setor responsável pela análise inicial do tipo de processo
                $mainQuery->where(function($obrig) use ($usuario) {
                    $obrig->whereNotNull('tipo_documento_obrigatorio_id')
                          ->whereHas('processo', function($p) use ($usuario) {
                              $p->where('responsavel_atual_id', $usuario->id);
                              $setoresUsr = $usuario->getSetoresCodigos();
                              if (!empty($setoresUsr)) {
                                  // Setor atual do processo (onde o processo está agora)
                                  $p->orWhereIn('setor_atual', $setoresUsr);
                                  // Setor responsável pela análise inicial do tipo de processo
                                  $p->orWhereHas('tipoProcesso', function($tp) use ($setoresUsr) {
                                      $tp->whereHas('tipoSetor', function($ts) use ($setoresUsr) {
                                          $ts->whereIn('codigo', $setoresUsr);
                                      });
                                  });
                              }
                          });
                })
                // CASO 2: Docs fora da lista obrigatória → setor atual do processo
                ->orWhere(function($naoObrig) use ($usuario) {
                    $naoObrig->whereNull('tipo_documento_obrigatorio_id')
                             ->whereHas('processo', function($p) use ($usuario) {
                                 $p->where('responsavel_atual_id', $usuario->id);
                                 if (!empty($setoresUsr)) {
                                     $p->orWhereIn('setor_atual', $setoresUsr);
                                 }
                             });
                });
            });
            
            $this->aplicarFiltroVisibilidadeRespostasPendentes($respostas_pendentes_query, $usuario);

            // Filtrar também por competência
            if ($usuario->isEstadual()) {
                $documentos_pendentes_query->whereHas('processo.estabelecimento', fn($q) => 
                    $q->where('competencia_manual', 'estadual')->orWhereNull('competencia_manual'));
            } elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
                $documentos_pendentes_query->whereHas('processo.estabelecimento', fn($q) => 
                    $q->where('municipio_id', $usuario->municipio_id));
            }
        }
'@

$newBlock = @'
        // Buscar documentos pendentes de aprovação
        // REGRA DE VISIBILIDADE UNIFICADA (obrigatórios e fora da lista):
        // Responsável direto, OU setor_atual do processo, OU setor responsável pela análise inicial
        // do tipo de processo (tipo_processos.tipo_setor_id para estadual, tipo_processo_setor_municipio
        // para municipal do município do usuário).
        $documentos_pendentes_query = ProcessoDocumento::where('status_aprovacao', 'pendente')
            ->where('tipo_usuario', 'externo')
            ->with(['processo.estabelecimento']);

        $respostas_pendentes_query = DocumentoResposta::where('status', 'pendente')
            ->with(['documentoDigital.processo.estabelecimento', 'documentoDigital.tipoDocumento', 'documentoDigital.assinaturas']);

        // Filtrar por setor/responsável do processo + competência
        if (!$usuario->isAdmin()) {
            $this->aplicarFiltroVisibilidadeDocumentosPendentes($documentos_pendentes_query, $usuario);
            $this->aplicarFiltroVisibilidadeRespostasPendentes($respostas_pendentes_query, $usuario);

            // Filtrar também por competência
            if ($usuario->isEstadual()) {
                $documentos_pendentes_query->whereHas('processo.estabelecimento', fn($q) => 
                    $q->where('competencia_manual', 'estadual')->orWhereNull('competencia_manual'));
            } elseif ($usuario->isMunicipal() && $usuario->municipio_id) {
                $documentos_pendentes_query->whereHas('processo.estabelecimento', fn($q) => 
                    $q->where('municipio_id', $usuario->municipio_id));
            }
        }
'@

$count = ([regex]::Matches($content, [regex]::Escape($oldBlock))).Count
Write-Host "Occurrences found: $count"

$newContent = $content.Replace($oldBlock, $newBlock)
Set-Content -Path $path -Value $newContent -NoNewline

$newCount = ([regex]::Matches($newContent, [regex]::Escape($oldBlock))).Count
Write-Host "Remaining occurrences after replace: $newCount"
