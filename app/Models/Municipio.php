<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Municipio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'codigo_ibge',
        'uf',
        'slug',
        'logomarca',
        'rodape_documento',
        'rodape_texto',
        'ativo',
        'usa_infovisa',
        'data_adesao_infovisa',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'usa_infovisa' => 'boolean',
        'data_adesao_infovisa' => 'date',
    ];

    public function getLogomarcaUrlAttribute(): ?string
    {
        return $this->gerarUrlArquivoPublico($this->logomarca);
    }

    public function getRodapeDocumentoUrlAttribute(): ?string
    {
        return $this->gerarUrlArquivoPublico($this->rodape_documento);
    }

    /**
     * Relacionamento com estabelecimentos
     */
    public function estabelecimentos()
    {
        return $this->hasMany(Estabelecimento::class);
    }

    /**
     * Relacionamento com pactuações municipais
     */
    public function pactuacoes()
    {
        return $this->hasMany(Pactuacao::class);
    }

    /**
     * Relacionamento com usuários internos vinculados ao município
     */
    public function usuariosInternos()
    {
        return $this->hasMany(UsuarioInterno::class, 'municipio_id');
    }

    /**
     * Relacionamento com setores vinculados ao município
     */
    public function tipoSetores()
    {
        return $this->belongsToMany(TipoSetor::class, 'municipio_tipo_setor')
            ->withTimestamps();
    }

    /**
     * Busca ou cria um município baseado no nome
     * Normaliza o nome para evitar duplicatas
     */
    public static function buscarOuCriarPorNome($nome, $codigoIbge = null)
    {
        if (empty($nome)) {
            return null;
        }

        $slug = Str::slug($nome);
        
        // Tenta buscar pelo slug primeiro
        $municipio = self::where('slug', $slug)->first();
        
        if ($municipio) {
            return $municipio;
        }

        // Se não encontrou, tenta buscar por código IBGE
        if ($codigoIbge) {
            $municipio = self::where('codigo_ibge', $codigoIbge)->first();
            if ($municipio) {
                return $municipio;
            }
        }

        // Se não encontrou, cria um novo
        return self::create([
            'nome' => mb_strtoupper(trim($nome)),
            'codigo_ibge' => $codigoIbge ?? '0000000',
            'uf' => 'TO',
            'slug' => $slug,
            'ativo' => true
        ]);
    }

    /**
     * Busca município por código IBGE
     */
    public static function buscarPorCodigoIbge($codigoIbge)
    {
        return self::where('codigo_ibge', $codigoIbge)->first();
    }

    /**
     * Busca município por nome (case-insensitive)
     */
    public static function buscarPorNome($nome)
    {
        $slug = Str::slug($nome);
        return self::where('slug', $slug)->first();
    }

    private function gerarUrlArquivoPublico(?string $caminho): ?string
    {
        if (!$caminho) {
            return null;
        }

        if (Str::startsWith($caminho, ['http://', 'https://', '//'])) {
            return $caminho;
        }

        $caminhoNormalizado = Str::startsWith($caminho, 'storage/')
            ? $caminho
            : 'storage/' . ltrim($caminho, '/');

        return asset($caminhoNormalizado);
    }

    /**
     * Scope para municípios ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para municípios do Tocantins
     */
    public function scopeDoTocantins($query)
    {
        return $query->where('uf', 'TO');
    }

    /**
     * Scope para municípios que usam o InfoVISA
     */
    public function scopeUsaInfovisa($query)
    {
        return $query->where('usa_infovisa', true);
    }

    /**
     * Verifica se o município aceita cadastros de estabelecimentos municipais
     */
    public function aceitaCadastroMunicipal(): bool
    {
        return $this->usa_infovisa === true;
    }

    /**
     * Conta as descentralizações (atividades estaduais delegadas para este município)
     */
    public function countDescentralizacoes()
    {
        // Busca pactuações estaduais onde este município está na lista de exceções
        // O campo municipios_excecao é um array JSON com os nomes dos municípios
        
        $nomeMunicipio = mb_strtolower(trim($this->nome));
        
        // Busca todas as pactuações estaduais ativas e filtra em PHP
        // para garantir comparação exata (evitar "ALMAS" encontrar "PALMAS")
        return Pactuacao::where('tipo', 'estadual')
            ->where('ativo', true)
            ->whereNotNull('municipios_excecao')
            ->get()
            ->filter(function($pactuacao) use ($nomeMunicipio) {
                if (!$pactuacao->municipios_excecao || !is_array($pactuacao->municipios_excecao)) {
                    return false;
                }
                
                // Verifica se o nome do município está no array (case-insensitive)
                foreach ($pactuacao->municipios_excecao as $municipioExcecao) {
                    if (mb_strtolower(trim($municipioExcecao)) === $nomeMunicipio) {
                        return true;
                    }
                }
                
                return false;
            })
            ->count();
    }

    /**
     * Retorna o total de pactuações (municipais + descentralizações)
     */
    public function getTotalPactuacoesAttribute()
    {
        $municipais = $this->pactuacoes()->where('tipo', 'municipal')->count();
        $descentralizacoes = $this->countDescentralizacoes();
        return $municipais + $descentralizacoes;
    }

    /**
     * Retorna as descentralizações (pactuações estaduais onde este município é exceção)
     */
    public function descentralizacoes()
    {
        $nomeMunicipio = mb_strtolower(trim($this->nome));
        
        return Pactuacao::where('tipo', 'estadual')
            ->where('ativo', true)
            ->whereNotNull('municipios_excecao')
            ->get()
            ->filter(function($pactuacao) use ($nomeMunicipio) {
                if (!$pactuacao->municipios_excecao || !is_array($pactuacao->municipios_excecao)) {
                    return false;
                }
                
                foreach ($pactuacao->municipios_excecao as $municipioExcecao) {
                    if (mb_strtolower(trim($municipioExcecao)) === $nomeMunicipio) {
                        return true;
                    }
                }
                
                return false;
            });
    }
}
