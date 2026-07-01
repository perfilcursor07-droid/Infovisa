<?php

namespace App\Support;

class NaturezaJuridicaHelper
{
    /**
     * Códigos CONCLA (Receita Federal) de naturezas jurídicas públicas.
     *
     * @var list<string>
     */
    private const CODIGOS_PUBLICOS = [
        '1015', '1023', '1031', '1040', '1050', '1060', '1070', '1080',
        '1104', '1112', '1120',
        '1139', '1147', '1155', '1163', '1171', '1180',
        '1210', '1228',
        '1236', '1244', '1317',
        '1252', '1260', '1279', '1287', '1295', '1309',
        '1321', '1330', '1341',
    ];

    /**
     * Palavras-chave (sem acentos) que indicam entidade pública.
     *
     * @var list<string>
     */
    private const PALAVRAS_CHAVE_PUBLICAS = [
        'orgao publico',
        'autarquia',
        'fundacao publica',
        'empresa publica',
        'sociedade de economia mista',
        'fundo publico',
        'administracao direta',
        'administracao indireta',
        'administracao publica',
        'poder executivo',
        'poder legislativo',
        'poder judiciario',
        'consorcio publico',
        'municipio',
        'estado ou distrito federal',
        'uniao',
        'ente federativo',
    ];

    public static function inferirTipoSetor(?string $naturezaJuridica): string
    {
        $natureza = trim((string) $naturezaJuridica);

        if ($natureza === '') {
            return 'privado';
        }

        if (preg_match('/^(\d{4})/', $natureza, $matches)
            && in_array($matches[1], self::CODIGOS_PUBLICOS, true)) {
            return 'publico';
        }

        $normalizada = self::normalizar($natureza);

        foreach (self::PALAVRAS_CHAVE_PUBLICAS as $palavra) {
            if (str_contains($normalizada, $palavra)) {
                return 'publico';
            }
        }

        return 'privado';
    }

    private static function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));

        return strtr($texto, [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'ê' => 'e',
            'í' => 'i',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ü' => 'u',
            'ç' => 'c',
        ]);
    }
}
