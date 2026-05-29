<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdemServico extends Model
{
    use SoftDeletes;

    protected $table = 'ordens_servico';

    protected $fillable = [
        'numero',
        'estabelecimento_id',
        'processo_id',
        'pasta_id',
        'tipos_acao_ids',
        'atividades_tecnicos',
        'acoes_executadas_ids',
        'tecnicos_ids',
        'municipio_id',
        'observacoes',
        'documento_anexo_path',
        'documento_anexo_nome',
        'data_abertura',
        'data_inicio',
        'data_fim',
        'data_conclusao',
        'status',
        'competencia',
        'atividades_realizadas',
        'observacoes_finalizacao',
        'finalizada_por',
        'finalizada_em',
        'motivo_cancelamento',
        'cancelada_em',
        'cancelada_por',
        'gestor_assinatura_id',
        'gestor_assinatura_hash',
        'gestor_assinado_em',
    ];

    protected $casts = [
        'data_abertura' => 'date',
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'data_conclusao' => 'date',
        'finalizada_em' => 'datetime',
        'cancelada_em' => 'datetime',
        'gestor_assinado_em' => 'datetime',
        'tipos_acao_ids' => 'array',
        'atividades_tecnicos' => 'array',
        'acoes_executadas_ids' => 'array',
        'tecnicos_ids' => 'array',
    ];

    /**
     * Relacionamento com Estabelecimento (legado - único)
     */
    public function estabelecimento()
    {
        return $this->belongsTo(Estabelecimento::class);
    }

    /**
     * Relacionamento com Estabelecimentos (múltiplos - via pivot)
     */
    public function estabelecimentos()
    {
        return $this->belongsToMany(Estabelecimento::class, 'ordem_servico_estabelecimentos', 'ordem_servico_id', 'estabelecimento_id')
                    ->withPivot('processo_id')
                    ->withTimestamps();
    }

    /**
     * Retorna todos os estabelecimentos vinculados (pivot + legado)
     */
    public function getTodosEstabelecimentos()
    {
        $estabelecimentos = $this->estabelecimentos;

        // Se não tem no pivot mas tem no campo legado, inclui
        if ($estabelecimentos->isEmpty() && $this->estabelecimento_id && $this->estabelecimento) {
            return collect([$this->estabelecimento]);
        }

        return $estabelecimentos;
    }

    /**
     * Relacionamento com Gestor que assina a OS
     */
    public function gestorAssinatura()
    {
        return $this->belongsTo(\App\Models\UsuarioInterno::class, 'gestor_assinatura_id');
    }

    /**
     * Verifica se a OS está aguardando assinatura do gestor
     */
    public function aguardandoAssinaturaGestor(): bool
    {
        return $this->gestor_assinatura_id && !$this->gestor_assinado_em;
    }

    /**
     * Verifica se a OS está assinada pelo gestor
     */
    public function assinadaPeloGestor(): bool
    {
        return $this->gestor_assinatura_id && $this->gestor_assinado_em !== null;
    }

    /**
     * Relacionamento com Processo
     */
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    /**
     * Relacionamento com pasta do processo
     */
    public function pasta()
    {
        return $this->belongsTo(ProcessoPasta::class, 'pasta_id');
    }

    /**
     * Documentos digitais vinculados a esta OS
     */
    public function documentosDigitais()
    {
        return $this->hasMany(DocumentoDigital::class, 'os_id');
    }

    /**
     * Arquivos externos do processo vinculados a esta OS
     */
    public function arquivosExternos()
    {
        return $this->hasMany(ProcessoDocumento::class, 'os_id');
    }

    /**
     * Relacionamento com Tipos de Ação (múltiplos)
     */
    public function tiposAcao()
    {
        if (!$this->tipos_acao_ids) {
            return collect([]);
        }
        return TipoAcao::whereIn('id', $this->tipos_acao_ids)->get();
    }

    /**
     * Relacionamento com Ações Executadas (múltiplos)
     */
    public function acoesExecutadas()
    {
        if (!$this->acoes_executadas_ids) {
            return collect([]);
        }
        return TipoAcao::whereIn('id', $this->acoes_executadas_ids)->get();
    }

    /**
     * Relacionamento com Técnicos (múltiplos)
     */
    public function tecnicos()
    {
        if (!$this->tecnicos_ids) {
            return collect([]);
        }
        return UsuarioInterno::whereIn('id', $this->tecnicos_ids)->get();
    }

    /**
     * Relacionamento com Município
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Relacionamento com usuário que finalizou
     */
    public function finalizadaPor()
    {
        return $this->belongsTo(UsuarioInterno::class, 'finalizada_por');
    }

    /**
     * Scope para filtrar por competência
     */
    public function scopeCompetencia($query, $competencia)
    {
        return $query->where('competencia', $competencia);
    }

    /**
     * Scope para filtrar por status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por município
     */
    public function scopeMunicipio($query, $municipioId)
    {
        return $query->where('municipio_id', $municipioId);
    }

    /**
     * Scope para ordens em andamento
     */
    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'em_andamento');
    }

    /**
     * Scope para ordens finalizadas
     */
    public function scopeFinalizadas($query)
    {
        return $query->where('status', 'finalizada');
    }

    /**
     * Scope para ordens canceladas
     */
    public function scopeCanceladas($query)
    {
        return $query->where('status', 'cancelada');
    }

    /**
     * Gera número único para a OS no formato 000013.2025
     * O sequencial continua mesmo quando o ano muda
     * Usa advisory lock do PostgreSQL para evitar duplicação em requisições concorrentes
     * Considera TODOS os registros (incluindo soft deleted) para evitar conflitos
     */
    public static function gerarNumero()
    {
        return \DB::transaction(function () {
            // Usa advisory lock para garantir exclusividade na geração do número
            \DB::statement('SELECT pg_advisory_xact_lock(12345)');
            
            $ano = date('Y');
            
            // Busca o maior número sequencial existente - INCLUINDO registros deletados
            // Usa query raw para ignorar o soft delete
            $maiorNumero = \DB::table('ordens_servico')
                ->whereRaw("numero ~ '^[0-9]{6}\\.[0-9]{4}$'")
                ->selectRaw('MAX(CAST(SPLIT_PART(numero, \'.\', 1) AS INTEGER)) as max_sequencial')
                ->value('max_sequencial');
            
            if ($maiorNumero) {
                $sequencial = $maiorNumero + 1;
            } else {
                $sequencial = 1;
            }
            
            $numeroGerado = str_pad($sequencial, 6, '0', STR_PAD_LEFT) . '.' . $ano;
            
            // Verifica se o número já existe (incluindo deletados)
            $tentativas = 0;
            while (\DB::table('ordens_servico')->where('numero', $numeroGerado)->exists() && $tentativas < 100) {
                $sequencial++;
                $numeroGerado = str_pad($sequencial, 6, '0', STR_PAD_LEFT) . '.' . $ano;
                $tentativas++;
            }
            
            if ($tentativas >= 100) {
                throw new \Exception('Não foi possível gerar um número único para a Ordem de Serviço após 100 tentativas.');
            }
            
            return $numeroGerado;
        });
    }

    /**
     * Retorna label formatado do status
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'em_andamento' => 'Em Andamento',
            'finalizada' => 'Finalizada',
            'cancelada' => 'Cancelada',
            default => $this->status
        };
    }

    /**
     * Retorna badge HTML para status
     */
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'em_andamento' => 'bg-blue-100 text-blue-800',
            'finalizada' => 'bg-green-100 text-green-800',
            'cancelada' => 'bg-red-100 text-red-800',
        ];

        $color = $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
        
        return "<span class='px-2 py-1 text-xs font-medium rounded {$color}'>{$this->status_label}</span>";
    }


    /**
     * Retorna label formatado da competência
     */
    public function getCompetenciaLabelAttribute()
    {
        return match($this->competencia) {
            'estadual' => 'Estadual',
            'municipal' => 'Municipal',
            default => $this->competencia
        };
    }

    /**
     * Retorna badge HTML para competência
     */
    public function getCompetenciaBadgeAttribute()
    {
        $colors = [
            'estadual' => 'bg-blue-100 text-blue-800',
            'municipal' => 'bg-green-100 text-green-800',
        ];

        $color = $colors[$this->competencia] ?? 'bg-gray-100 text-gray-800';
        
        return "<span class='px-2 py-1 text-xs font-medium rounded {$color}'>{$this->competencia_label}</span>";
    }

    /**
     * Retorna todos os técnicos envolvidos na OS (nova estrutura)
     */
    public function getTodosTenicosAttribute()
    {
        if (!$this->atividades_tecnicos) {
            return collect([]);
        }

        $tecnicosIds = [];
        foreach ($this->atividades_tecnicos as $atividade) {
            if (isset($atividade['tecnicos']) && is_array($atividade['tecnicos'])) {
                $tecnicosIds = array_merge($tecnicosIds, $atividade['tecnicos']);
            }
        }

        $tecnicosIds = array_unique($tecnicosIds);
        return UsuarioInterno::whereIn('id', $tecnicosIds)->get();
    }

    /**
     * Verifica se um técnico está atribuído a alguma atividade
     */
    public function tecnicoEstaAtribuido($tecnicoId)
    {
        if (!$this->atividades_tecnicos) {
            return false;
        }

        foreach ($this->atividades_tecnicos as $atividade) {
            if (isset($atividade['tecnicos']) && in_array($tecnicoId, $atividade['tecnicos'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retorna as atividades pendentes para um técnico
     */
    public function getAtividadesPendentesParaTecnico($tecnicoId)
    {
        if (!$this->atividades_tecnicos) {
            return collect([]);
        }

        $atividadesPendentes = [];
        foreach ($this->atividades_tecnicos as $atividade) {
            if (isset($atividade['tecnicos']) && 
                in_array($tecnicoId, $atividade['tecnicos']) && 
                ($atividade['status'] ?? 'pendente') !== 'finalizada') {
                $atividadesPendentes[] = $atividade;
            }
        }

        return collect($atividadesPendentes);
    }

    /**
     * Verifica se todas as atividades foram finalizadas
     */
    public function todasAtividadesFinalizadas()
    {
        if (!$this->atividades_tecnicos) {
            return false;
        }

        foreach ($this->atividades_tecnicos as $atividade) {
            if (($atividade['status'] ?? 'pendente') !== 'finalizada') {
                return false;
            }
        }

        return true;
    }

    /**
     * Finaliza uma atividade específica para um técnico
     */
    public function finalizarAtividade($tipoAcaoId, $tecnicoId, $observacoes = null)
    {
        $atividades = $this->atividades_tecnicos ?? [];
        
        foreach ($atividades as $index => $atividade) {
            if ($atividade['tipo_acao_id'] == $tipoAcaoId && 
                isset($atividade['tecnicos']) && 
                in_array($tecnicoId, $atividade['tecnicos'])) {
                
                $atividades[$index]['status'] = 'finalizada';
                $atividades[$index]['finalizada_por'] = $tecnicoId;
                $atividades[$index]['finalizada_em'] = now()->toISOString();
                if ($observacoes) {
                    $atividades[$index]['observacoes'] = $observacoes;
                }
                break;
            }
        }

        $this->update(['atividades_tecnicos' => $atividades]);

        // Se todas as atividades foram finalizadas, finaliza a OS
        if ($this->todasAtividadesFinalizadas()) {
            $this->update([
                'status' => 'finalizada',
                'data_conclusao' => now(),
                'finalizada_em' => now(),
            ]);
        }
    }
}
