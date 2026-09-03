<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoItemAtendimento extends Model
{
    protected $table = 'documento_itens_atendimento';

    protected $fillable = [
        'documento_digital_id',
        'ordem',
        'descricao',
        'embasamento_legal',
    ];

    protected $casts = [
        'ordem' => 'integer',
    ];

    public function documentoDigital()
    {
        return $this->belongsTo(DocumentoDigital::class);
    }

    public function respostas()
    {
        return $this->hasMany(DocumentoResposta::class, 'documento_item_atendimento_id');
    }

    public function respostaAtual()
    {
        return $this->hasOne(DocumentoResposta::class, 'documento_item_atendimento_id')->latestOfMany();
    }

    public function getStatusAttribute(): string
    {
        return $this->respostaAtual?->status ?? 'nao_enviado';
    }
}
