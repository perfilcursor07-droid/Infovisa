<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoUnidadeMovelCnae extends Model
{
    protected $table = 'documento_unidade_movel_cnae';

    public $timestamps = false;

    protected $fillable = [
        'documento_unidade_movel_id',
        'cnae_codigo',
    ];

    public function documentoUnidadeMovel(): BelongsTo
    {
        return $this->belongsTo(DocumentoUnidadeMovel::class, 'documento_unidade_movel_id');
    }
}
