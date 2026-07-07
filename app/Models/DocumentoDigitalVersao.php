<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoDigitalVersao extends Model
{
    protected $table = 'documento_digital_versoes';

    protected $fillable = [
        'documento_digital_id',
        'usuario_interno_id',
        'versao',
        'conteudo',
    ];

    protected $casts = [
        'versao' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function documentoDigital(): BelongsTo
    {
        return $this->belongsTo(DocumentoDigital::class);
    }

    public function usuarioInterno(): BelongsTo
    {
        return $this->belongsTo(UsuarioInterno::class)->withTrashed();
    }
}
