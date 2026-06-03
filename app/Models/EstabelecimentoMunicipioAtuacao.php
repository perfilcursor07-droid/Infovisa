<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstabelecimentoMunicipioAtuacao extends Model
{
    use HasFactory;

    protected $table = 'estabelecimento_municipios_atuacao';

    protected $fillable = [
        'estabelecimento_id',
        'municipio_id',
        'municipio_nome',
        'data_inicio',
        'data_fim',
        'competencia',
        'usa_infovisa',
        'status',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'usa_infovisa' => 'boolean',
    ];

    public function estabelecimento()
    {
        return $this->belongsTo(Estabelecimento::class);
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }
}
