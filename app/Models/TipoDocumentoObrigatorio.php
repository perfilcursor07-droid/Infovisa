<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class TipoDocumentoObrigatorio extends Model
{
    use SoftDeletes;

    protected $table = 'tipos_documento_obrigatorio';

    protected $fillable = [
        'nome',
        'nomenclatura_arquivo',
        'descricao',
        'instrucoes',
        'url_referencia',
        'ativo',
        'ordem',
        'documento_comum',
        'tipo_processo_id',
        'escopo_competencia',
        'municipio_id',
        'tipo_setor',
        'observacao_publica',
        'observacao_privada',
        'prazo_validade_dias',
        'carimbar_aprovacao',
        'carimbo_modo',
        'carimbo_texto',
        'criterio_ia',
        'ia_modelo_visao',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'documento_comum' => 'boolean',
        'ordem' => 'integer',
        'prazo_validade_dias' => 'integer',
        'carimbar_aprovacao' => 'boolean',
    ];

    /**
     * Relacionamento com tipo de processo (para documentos comuns)
     */
    public function tipoProcesso()
    {
        return $this->belongsTo(TipoProcesso::class);
    }

    /**
     * Indica se o carimbo é aplicado automaticamente ao aprovar o documento.
     */
    public function carimboAutomatico(): bool
    {
        return $this->carimbo_modo === 'automatico';
    }

    /**
     * Indica se o carimbo é manual (técnico aciona quando quiser).
     */
    public function carimboManual(): bool
    {
        return $this->carimbo_modo === 'manual';
    }

    /**
     * Indica se o carimbo está habilitado (automático ou manual).
     */
    public function carimboAtivo(): bool
    {
        return in_array($this->carimbo_modo, ['automatico', 'manual'], true);
    }

    /**
     * Relacionamento com município (para escopo municipal)
     */
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Relacionamento com listas de documento (estrutura antiga - mantida para compatibilidade)
     */
    public function listasDocumento()
    {
        return $this->belongsToMany(ListaDocumento::class, 'lista_documento_tipo', 'tipo_documento_obrigatorio_id', 'lista_documento_id')
            ->withPivot(['obrigatorio', 'observacao', 'ordem'])
            ->withTimestamps();
    }

    /**
     * Relacionamento direto com atividades (nova estrutura)
     */
    public function atividades()
    {
        return $this->belongsToMany(Atividade::class, 'atividade_documento')
            ->withPivot(['obrigatorio', 'observacao', 'ordem'])
            ->withTimestamps();
    }

    /**
     * Scope para tipos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para ordenação
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    /**
     * Scope para documentos comuns
     */
    public function scopeDocumentosComuns($query)
    {
        return $query->where('documento_comum', true);
    }

    /**
     * Scope para documentos específicos (não comuns)
     */
    public function scopeDocumentosEspecificos($query)
    {
        return $query->where('documento_comum', false);
    }

    /**
     * Scope para documentos por escopo de competência
     */
    public function scopePorEscopoCompetencia($query, $escopo)
    {
        return $query->where(function($q) use ($escopo) {
            $q->where('escopo_competencia', 'todos')
              ->orWhere('escopo_competencia', $escopo);
        });
    }

    /**
     * Scope para documentos por tipo de setor
     */
    public function scopePorTipoSetor($query, $tipoSetor)
    {
        return $query->where(function($q) use ($tipoSetor) {
            $q->where('tipo_setor', 'todos')
              ->orWhere('tipo_setor', $tipoSetor);
        });
    }

    /**
     * Verifica se é documento comum
     */
    public function isDocumentoComum(): bool
    {
        return $this->documento_comum === true;
    }

    /**
     * Verifica se se aplica ao escopo de competência
     */
    public function aplicaAoEscopo(string $escopo): bool
    {
        return $this->escopo_competencia === 'todos' || $this->escopo_competencia === $escopo;
    }

    /**
     * Verifica se se aplica ao tipo de setor
     */
    public function aplicaAoTipoSetor(string $tipoSetor): bool
    {
        return $this->tipo_setor === 'todos' || $this->tipo_setor === $tipoSetor;
    }

    /**
     * Retorna a observação apropriada baseada no tipo de setor
     */
    public function getObservacaoParaTipoSetor(string $tipoSetor): ?string
    {
        if ($tipoSetor === 'publico' && $this->observacao_publica) {
            return $this->observacao_publica;
        }
        
        if ($tipoSetor === 'privado' && $this->observacao_privada) {
            return $this->observacao_privada;
        }
        
        return $this->descricao;
    }

    /**
     * Retorna o label do escopo de competência
     */
    public function getEscopoCompetenciaLabelAttribute(): string
    {
        return match($this->escopo_competencia) {
            'estadual' => 'Estadual',
            'municipal' => 'Municipal',
            'todos' => 'Todos',
            default => 'Todos'
        };
    }

    /**
     * Retorna o label do tipo de setor
     */
    public function getTipoSetorLabelAttribute(): string
    {
        return match($this->tipo_setor) {
            'publico' => 'Público',
            'privado' => 'Privado',
            'todos' => 'Todos',
            default => 'Todos'
        };
    }

    /**
     * Retorna a nomenclatura do arquivo ou o nome como fallback
     */
    public function getNomenclaturaAttribute(): string
    {
        return $this->nomenclatura_arquivo ?: strtoupper($this->nome);
    }

    /**
     * Busca documentos comuns aplicáveis para um estabelecimento
     */
    public static function buscarDocumentosComuns(string $escopoCompetencia, string $tipoSetor): Collection
    {
        return self::ativos()
            ->documentosComuns()
            ->porEscopoCompetencia($escopoCompetencia)
            ->porTipoSetor($tipoSetor)
            ->ordenado()
            ->get();
    }

    /**
     * Busca todos os documentos aplicáveis para um estabelecimento
     * com deduplicação automática
     * 
     * @param Estabelecimento $estabelecimento
     * @return Collection
     */
    public static function getDocumentosParaEstabelecimento(Estabelecimento $estabelecimento): Collection
    {
        $escopoCompetencia = $estabelecimento->getEscopoCompetencia();
        // tipo_setor é um enum, precisa pegar o value
        $tipoSetorEnum = $estabelecimento->tipo_setor;
        $tipoSetor = $tipoSetorEnum instanceof \App\Enums\TipoSetor ? $tipoSetorEnum->value : ($tipoSetorEnum ?? 'privado');
        
        // 1. Buscar documentos comuns
        $documentosComuns = self::ativos()
            ->documentosComuns()
            ->porEscopoCompetencia($escopoCompetencia)
            ->porTipoSetor($tipoSetor)
            ->ordenado()
            ->get()
            ->map(function($doc) use ($tipoSetor) {
                $doc->origem = 'comum';
                $doc->obrigatorio_para_atividade = true;
                $doc->observacao_aplicavel = $doc->getObservacaoParaTipoSetor($tipoSetor);
                return $doc;
            });
        
        // 2. Buscar CNAEs do estabelecimento
        $cnaes = $estabelecimento->getCnaes();
        
        if (empty($cnaes)) {
            return $documentosComuns;
        }
        
        // 3. Buscar atividades correspondentes aos CNAEs
        $atividades = Atividade::buscarPorCnaes($cnaes);
        
        if ($atividades->isEmpty()) {
            return $documentosComuns;
        }
        
        $atividadeIds = $atividades->pluck('id');
        
        // 4. Buscar documentos específicos das atividades
        $documentosEspecificos = self::ativos()
            ->documentosEspecificos()
            ->whereHas('atividades', function($q) use ($atividadeIds) {
                $q->whereIn('atividades.id', $atividadeIds);
            })
            ->porEscopoCompetencia($escopoCompetencia)
            ->porTipoSetor($tipoSetor)
            ->ordenado()
            ->get()
            ->map(function($doc) use ($tipoSetor, $atividades) {
                $doc->origem = 'atividade';
                // Buscar se é obrigatório em alguma das atividades
                $doc->obrigatorio_para_atividade = $doc->atividades
                    ->whereIn('id', $atividades->pluck('id'))
                    ->contains(function($atividade) {
                        return $atividade->pivot->obrigatorio;
                    });
                $doc->observacao_aplicavel = $doc->getObservacaoParaTipoSetor($tipoSetor);
                // Coletar observações específicas das atividades
                $observacoesAtividades = $doc->atividades
                    ->whereIn('id', $atividades->pluck('id'))
                    ->pluck('pivot.observacao')
                    ->filter()
                    ->unique()
                    ->implode('; ');
                if ($observacoesAtividades) {
                    $doc->observacao_atividade = $observacoesAtividades;
                }
                return $doc;
            });
        
        // 5. Mesclar e deduplicar (documentos comuns primeiro, depois específicos)
        $todosDocumentos = $documentosComuns
            ->merge($documentosEspecificos)
            ->unique('id')
            ->sortBy('ordem')
            ->values();
        
        return $todosDocumentos;
    }

    /**
     * Busca documentos agrupados por categoria para um estabelecimento
     * 
     * @param Estabelecimento $estabelecimento
     * @return array
     */
    public static function getDocumentosAgrupadosParaEstabelecimento(Estabelecimento $estabelecimento): array
    {
        $documentos = self::getDocumentosParaEstabelecimento($estabelecimento);
        
        // tipo_setor é um enum, precisa pegar o value
        $tipoSetorEnum = $estabelecimento->tipo_setor;
        $tipoSetor = $tipoSetorEnum instanceof \App\Enums\TipoSetor ? $tipoSetorEnum->value : ($tipoSetorEnum ?? 'privado');
        
        return [
            'comuns' => $documentos->where('origem', 'comum')->values(),
            'especificos' => $documentos->where('origem', 'atividade')->values(),
            'obrigatorios' => $documentos->where('obrigatorio_para_atividade', true)->values(),
            'opcionais' => $documentos->where('obrigatorio_para_atividade', false)->values(),
            'total' => $documentos->count(),
            'escopo_competencia' => $estabelecimento->getEscopoCompetencia(),
            'tipo_setor' => $tipoSetor,
        ];
    }
}
