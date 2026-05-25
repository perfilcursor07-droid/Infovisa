<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessoPasta extends Model
{
    use HasFactory;

    protected $fillable = [
        'processo_id',
        'nome',
        'descricao',
        'cor',
        'ordem',
        'unidade_id',
        'protegida',
        'status',
        'motivo_parada',
        'data_parada',
        'usuario_parada_id',
        'tempo_total_parado_segundos',
    ];

    protected $casts = [
        'protegida' => 'boolean',
        'data_parada' => 'datetime',
        'tempo_total_parado_segundos' => 'integer',
    ];

    /**
     * Relacionamento com usuário que parou a pasta
     */
    public function usuarioParada()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_parada_id');
    }

    /**
     * Relacionamento com Unidade
     */
    public function unidade()
    {
        return $this->belongsTo(\App\Models\Unidade::class);
    }

    /**
     * Relacionamento com Processo
     */
    public function processo()
    {
        return $this->belongsTo(Processo::class);
    }

    /**
     * Relacionamento com Documentos (arquivos)
     */
    public function documentos()
    {
        return $this->hasMany(ProcessoDocumento::class, 'pasta_id');
    }

    /**
     * Relacionamento com Documentos Digitais
     */
    public function documentosDigitais()
    {
        return $this->hasMany(DocumentoDigital::class, 'pasta_id');
    }

    /**
     * Conta total de itens na pasta
     */
    public function getTotalItensAttribute()
    {
        return $this->documentos()->count() + $this->documentosDigitais()->count();
    }
}
