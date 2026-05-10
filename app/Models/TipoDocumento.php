<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDocumento extends Model
{
    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'ativo',
        'visibilidade',
        'ordem',
        'tem_prazo',
        'prazo_padrao_dias',
        'prazo_notificacao',
        'permite_resposta',
        'prazo_analise_dias',
        'tipo_prazo_analise',
        'abrir_processo_automaticamente',
        'tipo_processo_codigo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'tem_prazo' => 'boolean',
        'prazo_notificacao' => 'boolean',
        'permite_resposta' => 'boolean',
        'abrir_processo_automaticamente' => 'boolean',
        'prazo_padrao_dias' => 'integer',
        'prazo_analise_dias' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com modelos de documentos
     */
    public function modelosDocumento(): HasMany
    {
        return $this->hasMany(ModeloDocumento::class);
    }

    /**
     * Tipos de documento de resposta vinculados
     */
    public function tiposDocumentoResposta(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TipoDocumentoResposta::class, 'tipo_documento_tipo_resposta')
            ->withPivot('obrigatorio', 'ordem')
            ->withTimestamps()
            ->orderByPivot('ordem');
    }

    /**
     * Scope para buscar apenas tipos ativos
     */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para ordenar por ordem
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    /**
     * Scope para filtrar por visibilidade do usuário logado
     */
    public function scopeVisivelParaUsuario($query, $usuario = null)
    {
        if (!$usuario) {
            $usuario = auth('interno')->user();
        }
        if (!$usuario || $usuario->isAdmin()) {
            return $query; // Admin vê todos
        }
        if ($usuario->isEstadual()) {
            return $query->whereIn('visibilidade', ['todos', 'estadual']);
        }
        if ($usuario->isMunicipal()) {
            return $query->whereIn('visibilidade', ['todos', 'municipal']);
        }
        return $query;
    }
}
