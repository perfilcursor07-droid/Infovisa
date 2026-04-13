<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoSetor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_setores';

    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'niveis_acesso',
        'ativo',
    ];

    protected $casts = [
        'niveis_acesso' => 'array',
        'ativo' => 'boolean',
    ];

    /**
     * Relacionamento many-to-many com municípios
     */
    public function municipios()
    {
        return $this->belongsToMany(Municipio::class, 'municipio_tipo_setor')
            ->withTimestamps();
    }

    /**
     * Verifica se é um setor global (sem municípios vinculados)
     */
    public function isGlobal(): bool
    {
        return $this->municipios()->count() === 0;
    }

    /**
     * Verifica se o setor está disponível para um determinado nível de acesso
     */
    public function disponivelParaNivel(string $nivelAcesso): bool
    {
        if (!$this->niveis_acesso || empty($this->niveis_acesso)) {
            return true;
        }

        return in_array($nivelAcesso, $this->niveis_acesso);
    }

    /**
     * Retorna os setores disponíveis para um nível de acesso e município específicos.
     * Retorna setores globais (sem municípios) + setores vinculados ao município informado.
     */
    public static function paraNivelAcesso(string $nivelAcesso, ?int $municipioId = null)
    {
        return static::where('ativo', true)
            ->where(function ($query) use ($municipioId) {
                // Setores globais (sem nenhum município vinculado)
                $query->whereDoesntHave('municipios');
                if ($municipioId) {
                    // + setores vinculados ao município
                    $query->orWhereHas('municipios', fn($q) => $q->where('municipios.id', $municipioId));
                }
            })
            ->get()
            ->filter(fn($setor) => $setor->disponivelParaNivel($nivelAcesso));
    }

    /**
     * Retorna os labels dos níveis de acesso associados
     */
    public function getNiveisAcessoLabelsAttribute(): array
    {
        if (!$this->niveis_acesso || empty($this->niveis_acesso)) {
            return ['Todos os níveis'];
        }

        return array_map(function ($nivel) {
            return \App\Enums\NivelAcesso::from($nivel)->label();
        }, $this->niveis_acesso);
    }
}
