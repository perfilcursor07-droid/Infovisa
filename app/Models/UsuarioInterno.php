<?php

namespace App\Models;

use App\Enums\NivelAcesso;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UsuarioInterno extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * O nome da tabela
     */
    protected $table = 'usuarios_internos';

    /**
     * Os atributos que podem ser atribuídos em massa
     */
    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'telefone',
        'data_nascimento',
        'matricula',
        'cargo',
        'setor',
        'nivel_acesso',
        'municipio',
        'municipio_id',
        'password',
        'senha_assinatura_digital',
        'ativo',
        'status_cadastro',
        'convite_id',
        'aprovado_por',
        'aprovado_em',
        'observacao_aprovacao',
        'email_verified_at',
    ];

    /**
     * Os atributos que devem ser escondidos na serialização
     */
    protected $hidden = [
        'password',
        'senha_assinatura_digital',
        'remember_token',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'ultimo_login_em' => 'datetime',
        'password' => 'hashed',
        'senha_assinatura_digital' => 'hashed',
        'nivel_acesso' => NivelAcesso::class,
        'ativo' => 'boolean',
        'aprovado_em' => 'datetime',
        'data_nascimento' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the name of the unique identifier for the user.
     * 
     * Este método define qual campo é usado como identificador único
     * para autenticação (login), mas o ID do usuário continua sendo 'id'
     */
    public function getAuthIdentifierName()
    {
        return 'id'; // Mantém 'id' como identificador
    }

    /**
     * Get the name of the password field for authentication.
     * 
     * Define que o campo 'cpf' será usado como username no login
     */
    public function username()
    {
        return 'cpf';
    }

    /**
     * Relacionamento com município
     */
    public function municipioRelacionado()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    public function convite(): BelongsTo
    {
        return $this->belongsTo(UsuarioInternoConvite::class, 'convite_id');
    }

    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(self::class, 'aprovado_por');
    }

    /**
     * Relacionamento many-to-many com tipos de setor
     */
    public function tipoSetores()
    {
        return $this->belongsToMany(TipoSetor::class, 'usuario_interno_tipo_setor')
            ->withTimestamps();
    }

    /**
     * Retorna array de códigos dos setores vinculados ao usuário.
     * Usa a pivot se existir, senão fallback para o campo setor (string).
     */
    public function getSetoresCodigos(): array
    {
        $codigos = $this->tipoSetores()->pluck('codigo')->toArray();
        if (!empty($codigos)) {
            return $codigos;
        }
        // Fallback: campo setor legado
        return $this->setor ? [$this->setor] : [];
    }

    /**
     * Verifica se o usuário tem acesso a um setor específico (por código)
     */
    public function temAcessoAoSetor(?string $codigoSetor): bool
    {
        if (!$codigoSetor) return false;
        return in_array($codigoSetor, $this->getSetoresCodigos());
    }

    /**
     * Accessor para nome do município
     * Se o campo municipio estiver vazio, busca do relacionamento
     */
    public function getMunicipioAttribute($value)
    {
        // Se já tem valor no campo, retorna
        if ($value) {
            return $value;
        }
        
        // Se não tem, busca do relacionamento
        if ($this->municipio_id && $this->relationLoaded('municipioRelacionado')) {
            return $this->municipioRelacionado?->nome;
        }
        
        // Se não tem relacionamento carregado, carrega agora
        if ($this->municipio_id) {
            $municipio = Municipio::find($this->municipio_id);
            return $municipio?->nome;
        }
        
        return null;
    }

    /**
     * Accessor para CPF formatado
     */
    public function getCpfFormatadoAttribute(): string
    {
        $cpf = $this->cpf;
        return substr($cpf, 0, 3) . '.' . 
               substr($cpf, 3, 3) . '.' . 
               substr($cpf, 6, 3) . '-' . 
               substr($cpf, 9, 2);
    }

    /**
     * Accessor para telefone formatado
     */
    public function getTelefoneFormatadoAttribute(): ?string
    {
        if (!$this->telefone) {
            return null;
        }

        $telefone = preg_replace('/[^0-9]/', '', $this->telefone);
        
        if (strlen($telefone) === 11) {
            return '(' . substr($telefone, 0, 2) . ') ' . 
                   substr($telefone, 2, 5) . '-' . 
                   substr($telefone, 7);
        }
        
        if (strlen($telefone) === 10) {
            return '(' . substr($telefone, 0, 2) . ') ' . 
                   substr($telefone, 2, 4) . '-' . 
                   substr($telefone, 6);
        }

        return $this->telefone;
    }

    /**
     * Verifica se o usuário é administrador
     */
    public function isAdmin(): bool
    {
        return $this->nivel_acesso === NivelAcesso::Administrador;
    }

    /**
     * Verifica se o usuário é gestor
     */
    public function isGestor(): bool
    {
        return $this->nivel_acesso->isGestor();
    }

    /**
     * Verifica se o usuário é técnico
     */
    public function isTecnico(): bool
    {
        return in_array($this->nivel_acesso, [
            NivelAcesso::TecnicoEstadual,
            NivelAcesso::TecnicoMunicipal
        ]);
    }

    /**
     * Verifica se o usuário tem acesso estadual
     */
    public function isEstadual(): bool
    {
        return $this->nivel_acesso->isEstadual();
    }

    /**
     * Verifica se o usuário tem acesso municipal
     */
    public function isMunicipal(): bool
    {
        return $this->nivel_acesso->isMunicipal();
    }

    /**
     * Verifica se o usuário está ativo
     */
    public function isAtivo(): bool
    {
        return $this->ativo === true;
    }

    public function isPendenteAprovacao(): bool
    {
        return $this->status_cadastro === 'pendente';
    }

    /**
     * Verifica se o usuário tem senha de assinatura digital cadastrada
     */
    public function temSenhaAssinatura(): bool
    {
        return !empty($this->senha_assinatura_digital);
    }

    /**
     * Scope para filtrar apenas usuários ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para filtrar apenas usuários ativos (singular)
     */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para ordenar por nome
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('nome', 'asc');
    }

    /**
     * Scope para filtrar por nível de acesso
     */
    public function scopeNivelAcesso($query, NivelAcesso|string $nivel)
    {
        $valor = $nivel instanceof NivelAcesso ? $nivel->value : $nivel;
        return $query->where('nivel_acesso', $valor);
    }

    /**
     * Scope para filtrar administradores
     */
    public function scopeAdministradores($query)
    {
        return $query->where('nivel_acesso', NivelAcesso::Administrador->value);
    }

    /**
     * Obtém a logomarca para documentos digitais
     * - Se for usuário municipal: retorna logomarca do município
     * - Se for usuário estadual: retorna logomarca estadual (configuração do sistema)
     * - Se não houver logomarca: retorna null
     */
    public function getLogomarcaDocumento()
    {
        // Se for usuário municipal e tiver município vinculado
        if ($this->isMunicipal() && $this->municipio_id) {
            $municipio = $this->municipioRelacionado;
            return $municipio?->logomarca;
        }
        
        // Se for usuário estadual ou não tiver município
        if ($this->isEstadual() || !$this->municipio_id) {
            return \App\Models\ConfiguracaoSistema::logomarcaEstadual();
        }
        
        return null;
    }

    /**
     * Verifica se o usuário tem logomarca configurada
     */
    public function temLogomarca(): bool
    {
        return !empty($this->getLogomarcaDocumento());
    }
}
