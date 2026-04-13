<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class UsuarioInternoConvite extends Model
{
    use HasFactory;

    protected $table = 'usuario_interno_convites';

    protected $fillable = [
        'titulo',
        'token',
        'nivel_acesso',
        'municipio_id',
        'criado_por',
        'expira_em',
        'ativo',
        'ultimo_uso_em',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'expira_em' => 'datetime',
        'ultimo_uso_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class, 'criado_por');
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(UsuarioInterno::class, 'convite_id');
    }

    public function isDisponivel(): bool
    {
        if (!$this->ativo) {
            return false;
        }

        if ($this->expira_em instanceof Carbon && $this->expira_em->isPast()) {
            return false;
        }

        return true;
    }
}
