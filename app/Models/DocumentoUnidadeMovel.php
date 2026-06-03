<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class DocumentoUnidadeMovel extends Model
{
    protected $table = 'documento_unidade_movel';

    protected $fillable = [
        'tipo_documento_obrigatorio_id',
        'escopo',
        'obrigatorio',
        'ordem',
    ];

    protected $casts = [
        'obrigatorio' => 'boolean',
        'ordem' => 'integer',
    ];

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumentoObrigatorio::class, 'tipo_documento_obrigatorio_id');
    }

    /**
     * CNAEs associados a este documento (pivot simples).
     */
    public function cnaes()
    {
        return DB::table('documento_unidade_movel_cnae')
            ->where('documento_unidade_movel_id', $this->id)
            ->pluck('cnae_codigo')
            ->all();
    }

    /**
     * Retorna documentos obrigatórios para um conjunto de CNAEs.
     * Filtra pela tabela pivot e retorna agrupado por escopo.
     *
     * @param array $cnaes Códigos CNAE normalizados (somente dígitos)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function paraEstesCnaes(array $cnaes): \Illuminate\Database\Eloquent\Collection
    {
        if (empty($cnaes)) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        $cnaesNormalizados = array_map(fn($c) => preg_replace('/\D/', '', (string) $c), $cnaes);

        return self::whereHas('cnaesRelation', function ($q) use ($cnaesNormalizados) {
            $q->whereIn('cnae_codigo', $cnaesNormalizados);
        })
            ->with('tipoDocumento')
            ->orderBy('escopo')
            ->orderBy('ordem')
            ->get();
    }

    /**
     * Relação para queries whereHas (pivot como hasMany).
     */
    public function cnaesRelation()
    {
        return $this->hasMany(DocumentoUnidadeMovelCnae::class, 'documento_unidade_movel_id');
    }

    /**
     * Sincroniza CNAEs para este documento.
     */
    public function syncCnaes(array $cnaes): void
    {
        DB::table('documento_unidade_movel_cnae')
            ->where('documento_unidade_movel_id', $this->id)
            ->delete();

        $rows = array_map(fn($cnae) => [
            'documento_unidade_movel_id' => $this->id,
            'cnae_codigo' => preg_replace('/\D/', '', (string) $cnae),
        ], $cnaes);

        if (!empty($rows)) {
            DB::table('documento_unidade_movel_cnae')->insert($rows);
        }
    }
}
