<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoExigenciaColaborador extends Model
{
    protected $table = 'documento_exigencia_colaboradores';

    protected $fillable = [
        'documento_digital_id',
        'usuario_interno_id',
        'atribuido_por',
        'area',
        'prazo_interno',
        'status',
        'concluido_em',
    ];

    protected $casts = [
        'prazo_interno' => 'date',
        'concluido_em' => 'datetime',
    ];

    public function documentoDigital()
    {
        return $this->belongsTo(DocumentoDigital::class);
    }

    public function usuarioInterno()
    {
        return $this->belongsTo(UsuarioInterno::class, 'usuario_interno_id');
    }

    public function atribuidor()
    {
        return $this->belongsTo(UsuarioInterno::class, 'atribuido_por');
    }
}
