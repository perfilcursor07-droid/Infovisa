<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoDigital extends Model
{
    use SoftDeletes;

    protected $table = 'documentos_digitais';

    protected $fillable = [
        'tipo_documento_id',
        'subcategoria_id',
        'processo_id',
        'pasta_id',
        'usuario_criador_id',
        'ultimo_editor_id',
        'ultima_edicao_em',
        'versao_atual',
        'numero_documento',
        'nome',
        'conteudo',
        'sigiloso',
        'status',
        'arquivo_pdf',
        'codigo_autenticidade',
        'finalizado_em',
        'prazo_dias',
        'tipo_prazo',
        'prazo_notificacao',
        'data_vencimento',
        'prazo_iniciado_em',
        'prazo_iniciado_por',
        'prazo_finalizado_em',
        'prazo_finalizado_por',
        'prazo_finalizado_motivo',
        'prazo_prorrogado_dias',
        'prazo_prorrogado_em',
        'prazo_prorrogado_por',
        'prazo_prorrogado_motivo',
        'processos_ids',
        'os_id',
        'atividade_index',
    ];

    protected $casts = [
        'sigiloso' => 'boolean',
        'prazo_notificacao' => 'boolean',
        'finalizado_em' => 'datetime',
        'ultima_edicao_em' => 'datetime',
        'prazo_dias' => 'integer',
        'data_vencimento' => 'date',
        'prazo_iniciado_em' => 'datetime',
        'prazo_finalizado_em' => 'datetime',
        'prazo_prorrogado_dias' => 'integer',
        'prazo_prorrogado_em' => 'datetime',
        'processos_ids' => 'array',
        'atividade_index' => 'integer',
    ];

    /**
     * Gera código único de autenticidade
     */
    public static function gerarCodigoAutenticidade(): string
    {
        do {
            $codigo = md5(uniqid(rand(), true));
            $existe = self::where('codigo_autenticidade', $codigo)->exists();
        } while ($existe);

        return $codigo;
    }

    /**
     * Gera o próximo número de documento no formato 000001.2025
     */
    public static function gerarNumeroDocumento(): string
    {
        $ano = date('Y');
        
        // Busca o último número de documento do ano (incluindo soft deleted)
        $ultimo = self::withTrashed()
            ->where('numero_documento', 'like', "%.{$ano}")
            ->orderByRaw("CAST(SUBSTRING(numero_documento FROM 1 FOR 6) AS INTEGER) DESC")
            ->first();

        // Extrai o número sequencial (primeiros 6 dígitos)
        $sequencial = 1;
        if ($ultimo) {
            $partes = explode('.', $ultimo->numero_documento);
            $sequencial = (int) $partes[0] + 1;
        }

        // Garante que o número seja único tentando até encontrar um disponível
        $tentativas = 0;
        do {
            $numeroDocumento = sprintf('%06d.%s', $sequencial + $tentativas, $ano);
            $existe = self::withTrashed()->where('numero_documento', $numeroDocumento)->exists();
            $tentativas++;
        } while ($existe && $tentativas < 100);

        return $numeroDocumento;
    }

    /**
     * Relacionamento com tipo de documento
     */
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    /**
     * Relacionamento com a subcategoria escolhida (opcional).
     */
    public function subcategoria()
    {
        return $this->belongsTo(TipoDocumentoSubcategoria::class, 'subcategoria_id');
    }

    /**
     * Relacionamento com processo
     */
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    /**
     * Relacionamento com OS de origem
     */
    public function ordemServico()
    {
        return $this->belongsTo(\App\Models\OrdemServico::class, 'os_id');
    }

    /**
     * Verifica se é um documento em lote (multi-processo)
     */
    public function isLote(): bool
    {
        return !empty($this->processos_ids) && count($this->processos_ids) > 1;
    }

    /**
     * Retorna os processos vinculados ao lote
     */
    public function processosLote()
    {
        if (!$this->isLote()) {
            return collect();
        }

        return Processo::with('estabelecimento')->whereIn('id', $this->processos_ids)->get();
    }

    /**
     * Relacionamento com usuário criador
     */
    public function usuarioCriador()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_criador_id');
    }

    /**
     * Relacionamento com assinaturas
     */
    public function assinaturas()
    {
        return $this->hasMany(DocumentoAssinatura::class);
    }

    /**
     * Relacionamento com visualizações
     */
    public function visualizacoes()
    {
        return $this->hasMany(DocumentoVisualizacao::class);
    }

    /**
     * Primeira visualização do documento (para mostrar quem visualizou primeiro)
     */
    public function primeiraVisualizacao()
    {
        return $this->hasOne(DocumentoVisualizacao::class)->oldestOfMany();
    }

    /**
     * Relacionamento com pasta
     */
    public function pasta()
    {
        return $this->belongsTo(ProcessoPasta::class, 'pasta_id');
    }

    /**
     * Relacionamento com usuário que finalizou o prazo
     */
    public function usuarioFinalizouPrazo()
    {
        return $this->belongsTo(UsuarioInterno::class, 'prazo_finalizado_por');
    }

    /**
     * Relacionamento com usuário que prorrogou o prazo
     */
    public function usuarioProrrogouPrazo()
    {
        return $this->belongsTo(UsuarioInterno::class, 'prazo_prorrogado_por');
    }

    /**
     * Finaliza o prazo do documento (marca como respondido/resolvido)
     */
    public function finalizarPrazo($usuarioInternoId, $motivo = null): void
    {
        $this->update([
            'prazo_finalizado_em' => now(),
            'prazo_finalizado_por' => $usuarioInternoId,
            'prazo_finalizado_motivo' => $motivo,
        ]);
    }

    /**
     * Reabre o prazo do documento
     */
    public function reabrirPrazo(): void
    {
        $this->update([
            'prazo_finalizado_em' => null,
            'prazo_finalizado_por' => null,
            'prazo_finalizado_motivo' => null,
        ]);
    }

    /**
     * Verifica se o prazo foi finalizado
     */
    public function isPrazoFinalizado(): bool
    {
        return $this->prazo_finalizado_em !== null;
    }

    /**
     * Quantidade de dias ainda disponíveis para prorrogação
     */
    public function getDiasProrrogacaoDisponiveisAttribute(): int
    {
        return max(0, 30 - ((int) ($this->prazo_prorrogado_dias ?? 0)));
    }

    /**
     * Verifica se o prazo ainda pode ser prorrogado
     */
    public function podeProrrogarPrazo(): bool
    {
        return $this->temPrazo()
            && $this->data_vencimento !== null
            && !$this->isPrazoFinalizado()
            && $this->todasAssinaturasCompletas()
            && $this->status === 'assinado'
            && $this->dias_prorrogacao_disponiveis > 0;
    }

    /**
     * Prorroga o prazo do documento respeitando o limite total de 30 dias
     */
    public function prorrogarPrazo(int $dias, int $usuarioInternoId, string $motivo): array
    {
        if ($dias < 1) {
            throw new \InvalidArgumentException('A prorrogação deve ser de pelo menos 1 dia.');
        }

        if (!$this->podeProrrogarPrazo()) {
            throw new \RuntimeException('Este documento não pode mais ter o prazo prorrogado.');
        }

        if ($dias > $this->dias_prorrogacao_disponiveis) {
            throw new \RuntimeException('A prorrogação excede o limite máximo de 30 dias para esta notificação.');
        }

        $dataAnterior = $this->data_vencimento->copy();
        $novaData = $dataAnterior->copy()->addDays($dias);

        $this->update([
            'data_vencimento' => $novaData,
            'prazo_prorrogado_dias' => ((int) ($this->prazo_prorrogado_dias ?? 0)) + $dias,
            'prazo_prorrogado_em' => now(),
            'prazo_prorrogado_por' => $usuarioInternoId,
            'prazo_prorrogado_motivo' => trim($motivo),
        ]);

        return [
            'data_anterior' => $dataAnterior,
            'data_nova' => $novaData,
            'dias' => $dias,
            'dias_total' => (int) $this->prazo_prorrogado_dias,
        ];
    }

    /**
     * Verifica se o documento tem prazo (por dias definidos ou por ser notificação)
     */
    public function temPrazo(): bool
    {
        return $this->prazo_dias || $this->prazo_notificacao;
    }

    /**
     * Relacionamento com respostas do estabelecimento
     */
    public function respostas()
    {
        return $this->hasMany(DocumentoResposta::class);
    }

    /**
     * Verifica se o tipo de documento permite resposta
     * Só permite resposta se o tipo permite E o prazo não foi finalizado
     */
    public function permiteResposta(): bool
    {
        // Verifica se o tipo de documento permite resposta
        if (!$this->tipoDocumento || !$this->tipoDocumento->permite_resposta) {
            return false;
        }
        
        // Se tem prazo e foi finalizado, não permite mais resposta
        if ($this->temPrazo() && $this->isPrazoFinalizado()) {
            return false;
        }
        
        return true;
    }

    /**
     * Registra uma visualização do documento por usuário externo
     * e inicia a contagem do prazo se for documento de notificação
     */
    public function registrarVisualizacao($usuarioExternoId, $ip = null, $userAgent = null): void
    {
        // Registra a visualização
        $this->visualizacoes()->create([
            'usuario_externo_id' => $usuarioExternoId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);

        // Se for documento de notificação e o prazo ainda não foi iniciado, inicia agora
        if ($this->prazo_notificacao && !$this->prazo_iniciado_em && $this->todasAssinaturasCompletas()) {
            $this->iniciarPrazoPorVisualizacao();
        }
    }

    /**
     * Inicia o prazo por visualização do estabelecimento
     */
    public function iniciarPrazoPorVisualizacao(): void
    {
        if ($this->prazo_iniciado_em) {
            return; // Prazo já foi iniciado
        }

        $this->prazo_iniciado_em = now();
        $this->prazo_iniciado_por = 'visualizacao';
        
        // Recalcula a data de vencimento baseada na data de início do prazo
        if ($this->prazo_dias) {
            $this->data_vencimento = $this->calcularDataVencimento($this->prazo_iniciado_em, $this->prazo_dias, $this->tipo_prazo);
        }
        
        $this->save();
    }

    /**
     * Inicia o prazo por tempo de disponibilidade (5 dias)
     * Deve ser chamado por um job/scheduler
     */
    public function iniciarPrazoPorDisponibilidade(): void
    {
        if ($this->prazo_iniciado_em) {
            return; // Prazo já foi iniciado
        }

        $this->prazo_iniciado_em = now();
        $this->prazo_iniciado_por = 'tempo_disponibilidade';
        
        // Recalcula a data de vencimento baseada na data de início do prazo
        if ($this->prazo_dias) {
            $this->data_vencimento = $this->calcularDataVencimento($this->prazo_iniciado_em, $this->prazo_dias, $this->tipo_prazo);
        }
        
        $this->save();
    }

    /**
     * Define manualmente o prazo e inicia a contagem imediatamente.
     */
    public function definirPrazoManualmente(int $prazoDias, string $tipoPrazo, bool $prazoNotificacao = false): void
    {
        if ($prazoDias < 1) {
            throw new \InvalidArgumentException('O prazo deve ser de pelo menos 1 dia.');
        }

        if ($this->status !== 'assinado' || !$this->todasAssinaturasCompletas()) {
            throw new \RuntimeException('Só é possível definir prazo manualmente em documentos totalmente assinados.');
        }

        if ($this->prazo_dias || $this->data_vencimento) {
            throw new \RuntimeException('Este documento já possui prazo configurado.');
        }

        $inicioPrazo = now();

        $this->update([
            'prazo_dias' => $prazoDias,
            'tipo_prazo' => $tipoPrazo,
            'prazo_notificacao' => $prazoNotificacao,
            'prazo_iniciado_em' => $inicioPrazo,
            'prazo_iniciado_por' => 'definicao_manual_admin',
            'data_vencimento' => $this->calcularDataVencimento($inicioPrazo, $prazoDias, $tipoPrazo),
            'prazo_finalizado_em' => null,
            'prazo_finalizado_por' => null,
            'prazo_finalizado_motivo' => null,
        ]);
    }

    /**
     * Calcula a data de vencimento baseada no tipo de prazo
     */
    private function calcularDataVencimento($dataInicio, $dias, $tipoPrazo): \Carbon\Carbon
    {
        $data = \Carbon\Carbon::parse($dataInicio);
        
        if ($tipoPrazo === 'uteis') {
            // Dias úteis - exclui finais de semana
            $diasAdicionados = 0;
            while ($diasAdicionados < $dias) {
                $data->addDay();
                if (!$data->isWeekend()) {
                    $diasAdicionados++;
                }
            }
        } else {
            // Dias corridos
            $data->addDays($dias);
        }
        
        return $data;
    }

    /**
     * Retorna a data da última assinatura obrigatória do documento
     */
    public function getDataUltimaAssinaturaAttribute(): ?\Carbon\Carbon
    {
        $ultimaAssinatura = $this->assinaturas()
            ->where('obrigatoria', true)
            ->where('status', 'assinado')
            ->whereNotNull('assinado_em')
            ->orderBy('assinado_em', 'desc')
            ->first();

        return $ultimaAssinatura?->assinado_em;
    }

    /**
     * Verifica se o documento está disponível há mais de 5 dias úteis
     * e o prazo ainda não foi iniciado.
     * 
     * O prazo de 5 dias conta a partir da ÚLTIMA ASSINATURA do documento,
     * conforme §1º da portaria.
     */
    public function verificarInicioAutomaticoPrazo(): bool
    {
        // Só aplica para documentos de notificação com prazo não iniciado
        if (!$this->prazo_notificacao || $this->prazo_iniciado_em || !$this->todasAssinaturasCompletas()) {
            return false;
        }

        // Pega a data da última assinatura (quando o documento ficou disponível)
        $dataUltimaAssinatura = $this->data_ultima_assinatura;
        
        // Se não tem data de assinatura, usa finalizado_em ou created_at como fallback
        if (!$dataUltimaAssinatura) {
            $dataUltimaAssinatura = $this->finalizado_em ?? $this->created_at;
        }

        // Calcula 5 dias úteis após a última assinatura
        $diasUteis = 0;
        $dataLimite = \Carbon\Carbon::parse($dataUltimaAssinatura)->copy();
        
        while ($diasUteis < 5) {
            $dataLimite->addDay();
            if (!$dataLimite->isWeekend()) {
                $diasUteis++;
            }
        }

        // Se já passou os 5 dias úteis, inicia o prazo automaticamente
        if (now()->startOfDay()->gte($dataLimite->startOfDay())) {
            $this->iniciarPrazoPorDisponibilidade();
            return true;
        }

        return false;
    }

    /**
     * Relacionamento com versões
     */
    public function versoes()
    {
        return $this->hasMany(DocumentoDigitalVersao::class);
    }

    /**
     * Relacionamento com último editor
     */
    public function ultimoEditor()
    {
        return $this->belongsTo(UsuarioInterno::class, 'ultimo_editor_id');
    }

    /**
     * Verifica se todas assinaturas obrigatórias foram feitas
     */
    public function todasAssinaturasCompletas(): bool
    {
        return !$this->assinaturas()
            ->where('obrigatoria', true)
            ->where('status', '!=', 'assinado')
            ->exists();
    }

    /**
     * Verifica se o documento possui pelo menos uma assinatura realizada
     */
    public function possuiAssinaturaRealizada(): bool
    {
        return $this->assinaturas()
            ->where('status', 'assinado')
            ->exists();
    }

    /**
     * Verifica se o documento pode ser editado.
     * Rascunhos sempre podem ser editados.
     * Documentos finalizados (aguardando_assinatura) podem ser editados
     * se nenhuma assinatura foi realizada ainda.
     */
    public function podeEditar(): bool
    {
        if ($this->status === 'rascunho') {
            return true;
        }

        if ($this->status === 'aguardando_assinatura' && !$this->possuiAssinaturaRealizada()) {
            return true;
        }

        return false;
    }

    /**
     * Salva uma nova versão do documento
     */
    public function salvarVersao($usuarioId, $conteudo, $alteracoes = null)
    {
        $ultimaVersao = $this->versoes()->max('versao') ?? 0;
        
        return $this->versoes()->create([
            'usuario_interno_id' => $usuarioId,
            'versao' => $ultimaVersao + 1,
            'conteudo' => $conteudo,
            'alteracoes' => $alteracoes,
        ]);
    }

    /**
     * Calcula quantos dias faltam para o vencimento
     */
    public function getDiasFaltandoAttribute(): ?int
    {
        if (!$this->data_vencimento) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->data_vencimento, false);
    }

    /**
     * Verifica se o documento está vencido
     */
    public function getVencidoAttribute(): bool
    {
        if (!$this->data_vencimento) {
            return false;
        }

        return now()->startOfDay()->gt($this->data_vencimento);
    }

    /**
     * Verifica se o documento está próximo do vencimento (7 dias ou menos)
     */
    public function getProximoVencimentoAttribute(): bool
    {
        $diasFaltando = $this->dias_faltando;
        
        if ($diasFaltando === null) {
            return false;
        }

        return $diasFaltando >= 0 && $diasFaltando <= 7;
    }

    /**
     * Retorna o texto do status do prazo
     * Só mostra os dias restantes após todas as assinaturas serem concluídas
     */
    public function getTextoStatusPrazoAttribute(): string
    {
        if (!$this->data_vencimento && !$this->prazo_dias) {
            return 'Sem prazo';
        }

        // Se o prazo foi finalizado, mostra como resolvido
        if ($this->isPrazoFinalizado()) {
            return 'Respondido';
        }

        // Verifica se todas as assinaturas obrigatórias foram feitas
        if (!$this->todasAssinaturasCompletas()) {
            $pendentes = $this->assinaturas()
                ->where('obrigatoria', true)
                ->where('status', '!=', 'assinado')
                ->count();
            $total = $this->assinaturas()->where('obrigatoria', true)->count();
            return "Aguardando {$pendentes}/{$total} assinatura(s)";
        }

        // Para documentos de notificação, verifica se o prazo já foi iniciado
        if ($this->prazo_notificacao && !$this->prazo_iniciado_em) {
            return "Clique para visualizar";
        }

        $diasFaltando = $this->dias_faltando;

        if ($diasFaltando === null) {
            return 'Sem prazo';
        }

        if ($diasFaltando < 0) {
            $diasVencidos = abs($diasFaltando);
            return "Vencido há {$diasVencidos} " . ($diasVencidos === 1 ? 'dia' : 'dias');
        }

        if ($diasFaltando === 0) {
            return 'Vence hoje';
        }

        if ($diasFaltando === 1) {
            return 'Vence amanhã';
        }

        return "Faltam {$diasFaltando} dias";
    }

    /**
     * Retorna a cor do badge de status do prazo
     * Considera se as assinaturas estão pendentes
     */
    public function getCorStatusPrazoAttribute(): string
    {
        if (!$this->data_vencimento && !$this->prazo_dias) {
            return 'gray';
        }

        // Se o prazo foi finalizado, mostra em azul (resolvido)
        if ($this->isPrazoFinalizado()) {
            return 'blue';
        }

        // Se ainda tem assinaturas pendentes, mostra em cinza/neutro
        if (!$this->todasAssinaturasCompletas()) {
            return 'gray';
        }

        // Para documentos de notificação sem prazo iniciado
        if ($this->prazo_notificacao && !$this->prazo_iniciado_em) {
            return 'yellow'; // Amarelo para indicar ação necessária
        }

        if ($this->vencido) {
            return 'red';
        }

        if ($this->proximo_vencimento) {
            return 'yellow';
        }

        return 'green';
    }
}
