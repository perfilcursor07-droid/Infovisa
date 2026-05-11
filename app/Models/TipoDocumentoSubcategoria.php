<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDocumentoSubcategoria extends Model
{
    protected $table = 'tipo_documento_subcategorias';

    protected $fillable = [
        'tipo_documento_id',
        'nome',
        'codigo',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    public function modelosDocumento(): HasMany
    {
        return $this->hasMany(ModeloDocumento::class, 'subcategoria_id');
    }

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }
}
