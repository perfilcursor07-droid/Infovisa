<?php

namespace App\Models;

use App\Enums\NivelAcesso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModeloDocumento extends Model
{
    protected $fillable = [
        'tipo_documento_id',
        'subcategoria_id',
        'codigo',
        'descricao',
        'conteudo',
        'variaveis',
        'escopo',
        'municipio_id',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'variaveis' => 'array',
        'ativo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com tipo de documento
     */
    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    /**
     * Relacionamento com a subcategoria do tipo de documento (opcional).
     */
    public function subcategoria(): BelongsTo
    {
        return $this->belongsTo(TipoDocumentoSubcategoria::class, 'subcategoria_id');
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Scope para buscar apenas modelos ativos
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
        return $query->orderBy('ordem');
    }

    public function scopeEstaduais($query)
    {
        return $query->where('escopo', 'estadual');
    }

    public function scopeMunicipais($query)
    {
        return $query->where('escopo', 'municipal');
    }

    public function scopeDoMunicipio($query, int $municipioId)
    {
        return $query->municipais()->where('municipio_id', $municipioId);
    }

    public function scopeVisiveisParaUsuario($query, UsuarioInterno $usuario)
    {
        if ($usuario->nivel_acesso === NivelAcesso::Administrador) {
            return $query;
        }

        if ($usuario->isMunicipal()) {
            if (!$usuario->municipio_id) {
                return $query->whereRaw('1 = 0');
            }

            return $query->doMunicipio($usuario->municipio_id);
        }

        return $query->estaduais();
    }

    public function scopeDisponiveisParaUsuario($query, UsuarioInterno $usuario)
    {
        if ($usuario->isMunicipal()) {
            if (!$usuario->municipio_id) {
                return $query->whereRaw('1 = 0');
            }

            return $query->doMunicipio($usuario->municipio_id);
        }

        return $query->estaduais();
    }

    public function isEstadual(): bool
    {
        return $this->escopo === 'estadual';
    }

    public function isMunicipal(): bool
    {
        return $this->escopo === 'municipal';
    }

    public function getEscopoLabelAttribute(): string
    {
        return $this->isEstadual() ? 'Estadual' : 'Municipal';
    }
}
