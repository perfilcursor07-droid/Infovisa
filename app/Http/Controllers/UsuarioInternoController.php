<?php

namespace App\Http\Controllers;

use App\Enums\NivelAcesso;
use App\Models\UsuarioInterno;
use App\Models\UsuarioInternoConvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsuarioInternoController extends Controller
{
    /**
     * Retorna os níveis de acesso permitidos para o usuário logado criar/gerenciar
     */
    private function getNiveisPermitidos(): array
    {
        $usuarioLogado = auth('interno')->user();
        
        if ($usuarioLogado->isAdmin()) {
            // Admin pode criar todos os níveis
            return NivelAcesso::cases();
        }
        
        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorEstadual) {
            // Gestor Estadual pode criar: Gestor Estadual e Técnico Estadual
            return [
                NivelAcesso::GestorEstadual,
                NivelAcesso::TecnicoEstadual,
            ];
        }
        
        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal) {
            // Gestor Municipal pode criar: Gestor Municipal e Técnico Municipal
            return [
                NivelAcesso::GestorMunicipal,
                NivelAcesso::TecnicoMunicipal,
            ];
        }
        
        // Outros níveis não podem criar usuários
        return [];
    }

    /**
     * Verifica se o usuário logado pode gerenciar usuários internos
     */
    private function podeGerenciarUsuarios(): bool
    {
        $usuarioLogado = auth('interno')->user();
        return $usuarioLogado->isAdmin() || $usuarioLogado->isGestor();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        $usuarioLogado = auth('interno')->user();
        $aba = $request->get('aba', 'usuarios');
        $query = $this->aplicarEscopoUsuarioLogado(UsuarioInterno::query(), $usuarioLogado);
        $queryEscopoRanking = $this->aplicarEscopoUsuarioLogado(UsuarioInterno::query(), $usuarioLogado);

        // Filtro por nome (case-insensitive usando ILIKE)
        if ($request->filled('nome')) {
            $query->whereRaw("nome ILIKE ?", ['%' . $request->nome . '%']);
        }

        // Filtro por CPF
        if ($request->filled('cpf')) {
            $cpf = preg_replace('/\D/', '', $request->cpf);
            $query->where('cpf', 'like', '%' . $cpf . '%');
        }

        // Filtro por email (case-insensitive usando ILIKE)
        if ($request->filled('email')) {
            $query->whereRaw("email ILIKE ?", ['%' . $request->email . '%']);
        }

        // Filtro por município (case-insensitive usando ILIKE)
        if ($request->filled('municipio')) {
            $query->whereRaw("municipio ILIKE ?", ['%' . $request->municipio . '%']);
        }

        // Filtro por nível de acesso
        if ($request->filled('nivel_acesso')) {
            $query->where('nivel_acesso', $request->nivel_acesso);
        }

        // Filtro por status
        if ($request->filled('ativo')) {
            $query->where('ativo', $request->ativo === '1');
        }

        // Ordenação
        $sortField = $request->get('sort', 'nome');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Paginação com relacionamento
        $usuarios = $query
            ->with(['municipioRelacionado', 'convite', 'aprovador'])
            ->paginate(15)
            ->withQueryString();

        // Filtro de escopo para aba de atividade
        $filtroEscopoAtividade = $request->get('escopo_atividade');
        if ($filtroEscopoAtividade === 'estadual') {
            $queryEscopoRanking->whereIn('nivel_acesso', ['administrador', 'gestor_estadual', 'tecnico_estadual']);
        } elseif ($filtroEscopoAtividade === 'municipal') {
            $queryEscopoRanking->whereIn('nivel_acesso', ['gestor_municipal', 'tecnico_municipal']);
            if ($request->filled('municipio_id_atividade')) {
                $queryEscopoRanking->where('municipio_id', $request->municipio_id_atividade);
            }
        }

        $resumoAtividadeUsuarios = $this->montarRankingAtividadeUsuarios($queryEscopoRanking);
        $rankingAtividadeUsuarios = $resumoAtividadeUsuarios
            ->filter(fn($usuario) => $usuario->total_acoes > 0)
            ->sortByDesc('total_acoes')
            ->take(10)
            ->values();

        $usuariosSemAtividadeSemLogin = $resumoAtividadeUsuarios
            ->filter(fn($usuario) => $usuario->total_acoes === 0 && empty($usuario->ultimo_login_em))
            ->values();

        // Lista completa de todos os usuários com métricas (para aba atividade)
        $todosUsuariosAtividade = $resumoAtividadeUsuarios->sortByDesc('total_acoes')->values();
        
        // Níveis permitidos para filtro
        $niveisPermitidos = $this->getNiveisPermitidos();

        // Municípios para filtro
        $municipios = \App\Models\Municipio::orderBy('nome')->get();

        $pendentesAprovacao = $this->aplicarEscopoUsuarioLogado(
            UsuarioInterno::query()->where('status_cadastro', 'pendente'),
            $usuarioLogado
        )
            ->with(['municipioRelacionado', 'convite'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $convitesCadastro = $this->aplicarEscopoConvitesUsuarioLogado(
            UsuarioInternoConvite::query()->with(['municipio', 'criador'])->withCount('usuarios'),
            $usuarioLogado
        )
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Gerar QR Codes para cada convite
        $qrCodes = [];
        foreach ($convitesCadastro as $convite) {
            try {
                $link = route('cadastro-interno.show', $convite->token);
                $qr = new \Endroid\QrCode\QrCode($link);
                $writer = new \Endroid\QrCode\Writer\PngWriter();
                $result = $writer->write($qr);
                $qrCodes[$convite->id] = base64_encode($result->getString());
            } catch (\Throwable $e) {
                $qrCodes[$convite->id] = null;
            }
        }

        return view('admin.usuarios-internos.index', compact(
            'usuarios',
            'niveisPermitidos',
            'rankingAtividadeUsuarios',
            'usuariosSemAtividadeSemLogin',
            'todosUsuariosAtividade',
            'municipios',
            'aba',
            'pendentesAprovacao',
            'convitesCadastro',
            'qrCodes'
        ));
    }

    /**
     * Aplica escopo de visibilidade de usuários conforme o perfil do usuário logado.
     */
    private function aplicarEscopoUsuarioLogado($query, UsuarioInterno $usuarioLogado)
    {
        if ($usuarioLogado->isAdmin()) {
            return $query;
        }

        $niveisPermitidos = array_map(fn($n) => $n->value, $this->getNiveisPermitidos());
        $query->whereIn('nivel_acesso', $niveisPermitidos);

        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal && $usuarioLogado->municipio_id) {
            $query->where('municipio_id', $usuarioLogado->municipio_id);
        }

        return $query;
    }

    private function aplicarEscopoConvitesUsuarioLogado($query, UsuarioInterno $usuarioLogado)
    {
        if ($usuarioLogado->isAdmin()) {
            return $query;
        }

        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal && $usuarioLogado->municipio_id) {
            return $query->where('municipio_id', $usuarioLogado->municipio_id);
        }

        $niveisPermitidos = array_map(fn($n) => $n->value, $this->getNiveisPermitidos());

        return $query->whereIn('nivel_acesso', $niveisPermitidos);
    }

    /**
     * Monta ranking de usuários que mais realizaram ações no sistema.
     */
    private function montarRankingAtividadeUsuarios($queryEscopo)
    {
        $usuariosEscopo = (clone $queryEscopo)
            ->select('id', 'nome', 'nivel_acesso', 'ativo', 'ultimo_login_em')
            ->get();

        if ($usuariosEscopo->isEmpty()) {
            return collect();
        }

        $idsUsuarios = $usuariosEscopo->pluck('id')->all();

        $documentosCriados = DB::table('documentos_digitais')
            ->select('usuario_criador_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('usuario_criador_id')
            ->whereIn('usuario_criador_id', $idsUsuarios)
            ->groupBy('usuario_criador_id')
            ->pluck('total', 'usuario_criador_id');

        $documentosAprovados = DB::table('processo_documentos')
            ->select('aprovado_por', DB::raw('COUNT(*) as total'))
            ->whereNotNull('aprovado_por')
            ->where('status_aprovacao', 'aprovado')
            ->whereIn('aprovado_por', $idsUsuarios)
            ->groupBy('aprovado_por')
            ->pluck('total', 'aprovado_por');

        $uploadsDocumentos = DB::table('processo_documentos')
            ->select('usuario_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('usuario_id')
            ->where('tipo_usuario', 'interno')
            ->whereIn('usuario_id', $idsUsuarios)
            ->groupBy('usuario_id')
            ->pluck('total', 'usuario_id');

        $acoesProcessos = DB::table('processo_eventos')
            ->select('usuario_interno_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('usuario_interno_id')
            ->whereIn('usuario_interno_id', $idsUsuarios)
            ->groupBy('usuario_interno_id')
            ->pluck('total', 'usuario_interno_id');

        $acoesEstabelecimentos = DB::table('estabelecimento_historicos')
            ->select('usuario_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('usuario_id')
            ->whereIn('usuario_id', $idsUsuarios)
            ->groupBy('usuario_id')
            ->pluck('total', 'usuario_id');

        return $usuariosEscopo
            ->map(function ($usuario) use ($documentosCriados, $documentosAprovados, $uploadsDocumentos, $acoesProcessos, $acoesEstabelecimentos) {
                $documentosCriadosCount = (int) ($documentosCriados[$usuario->id] ?? 0);
                $documentosAprovadosCount = (int) ($documentosAprovados[$usuario->id] ?? 0);
                $uploadsDocumentosCount = (int) ($uploadsDocumentos[$usuario->id] ?? 0);
                $acoesProcessosCount = (int) ($acoesProcessos[$usuario->id] ?? 0);
                $acoesEstabelecimentosCount = (int) ($acoesEstabelecimentos[$usuario->id] ?? 0);

                $usuario->documentos_criados_count = $documentosCriadosCount;
                $usuario->documentos_aprovados_count = $documentosAprovadosCount;
                $usuario->uploads_documentos_count = $uploadsDocumentosCount;
                $usuario->acoes_processos_count = $acoesProcessosCount;
                $usuario->acoes_estabelecimentos_count = $acoesEstabelecimentosCount;
                $usuario->total_acoes = $documentosCriadosCount + $documentosAprovadosCount + $uploadsDocumentosCount + $acoesProcessosCount + $acoesEstabelecimentosCount;

                return $usuario;
            })
            ->values();
    }

    public function storeConvite(Request $request)
    {
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para gerar links de cadastro.');
        }

        $usuarioLogado = auth('interno')->user();
        $niveisPermitidos = array_map(fn($n) => $n->value, $this->getNiveisPermitidos());

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'nivel_acesso' => ['required', 'string', 'in:' . implode(',', $niveisPermitidos)],
            'municipio_id' => 'nullable|exists:municipios,id',
            'expira_em' => 'nullable|date|after:now',
        ], [
            'nivel_acesso.in' => 'Você não tem permissão para gerar links para este nível de acesso.',
            'expira_em.after' => 'A expiração precisa ser futura.',
        ]);

        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal && $usuarioLogado->municipio_id) {
            $validated['municipio_id'] = $usuarioLogado->municipio_id;
        }

        UsuarioInternoConvite::create([
            'titulo' => Str::upper(trim((string) $validated['titulo'])),
            'token' => $this->gerarSlugConvite($validated['titulo']),
            'nivel_acesso' => $validated['nivel_acesso'],
            'municipio_id' => $validated['municipio_id'] ?? null,
            'criado_por' => $usuarioLogado->id,
            'expira_em' => $validated['expira_em'] ?? null,
            'ativo' => true,
        ]);

        return redirect()
            ->route('admin.usuarios-internos.index', ['aba' => 'cadastros'])
            ->with('success', 'Link de cadastro gerado com sucesso.');
    }

    public function aprovarCadastro(UsuarioInterno $usuarioInterno)
    {
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para aprovar usuários.');
        }

        $this->verificarPermissaoUsuario($usuarioInterno);

        if (!$usuarioInterno->isPendenteAprovacao()) {
            return redirect()
                ->route('admin.usuarios-internos.index', ['aba' => 'cadastros'])
                ->with('success', 'Este cadastro já foi processado.');
        }

        $usuarioInterno->update([
            'ativo' => true,
            'status_cadastro' => 'aprovado',
            'aprovado_por' => auth('interno')->id(),
            'aprovado_em' => now(),
            'observacao_aprovacao' => null,
        ]);

        return redirect()
            ->route('admin.usuarios-internos.index', ['aba' => 'cadastros'])
            ->with('success', 'Cadastro aprovado com sucesso.');
    }

    public function rejeitarCadastro(Request $request, UsuarioInterno $usuarioInterno)
    {
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para rejeitar usuários.');
        }

        $this->verificarPermissaoUsuario($usuarioInterno);

        if (!$usuarioInterno->isPendenteAprovacao()) {
            return redirect()
                ->route('admin.usuarios-internos.index', ['aba' => 'cadastros'])
                ->with('success', 'Este cadastro já foi processado.');
        }

        $validated = $request->validate([
            'observacao_aprovacao' => 'nullable|string|max:1000',
        ]);

        $usuarioInterno->update([
            'ativo' => false,
            'status_cadastro' => 'rejeitado',
            'aprovado_por' => auth('interno')->id(),
            'aprovado_em' => now(),
            'observacao_aprovacao' => $validated['observacao_aprovacao'] ?? 'Cadastro rejeitado pelo administrador.',
        ]);

        return redirect()
            ->route('admin.usuarios-internos.index', ['aba' => 'cadastros'])
            ->with('success', 'Cadastro rejeitado com sucesso.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para criar usuários.');
        }

        $usuarioLogado = auth('interno')->user();
        $municipios = \App\Models\Municipio::orderBy('nome')->get();
        
        // Tenta buscar os tipos de setor, retorna coleção vazia se a tabela não existir
        try {
            $tipoSetores = \App\Models\TipoSetor::where('ativo', true)
                ->with('municipios:id')
                ->orderBy('nome')
                ->get()
                ->map(function ($setor) {
                    $setor->municipio_ids = $setor->municipios->pluck('id')->toArray();
                    unset($setor->municipios);
                    return $setor;
                });
        } catch (\Exception $e) {
            $tipoSetores = collect([]);
        }
        
        // Níveis de acesso permitidos para o usuário logado
        $niveisPermitidos = $this->getNiveisPermitidos();
        
        // Se for Gestor Municipal, pré-seleciona o município
        $municipioPreSelecionado = null;
        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal && $usuarioLogado->municipio_id) {
            $municipioPreSelecionado = $usuarioLogado->municipio_id;
        }
        
        return view('admin.usuarios-internos.create', compact('municipios', 'tipoSetores', 'niveisPermitidos', 'municipioPreSelecionado'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para criar usuários.');
        }

        $usuarioLogado = auth('interno')->user();
        $niveisPermitidos = array_map(fn($n) => $n->value, $this->getNiveisPermitidos());

        // Remove máscara do CPF e telefone antes de validar
        $request->merge([
            'cpf' => preg_replace('/[^0-9]/', '', $request->cpf),
            'telefone' => $request->telefone ? preg_replace('/[^0-9]/', '', $request->telefone) : null,
        ]);

        $rules = [
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:usuarios_internos,cpf',
            'email' => 'required|email|unique:usuarios_internos,email',
            'telefone' => 'nullable|string|max:11',
            'data_nascimento' => 'nullable|date|before_or_equal:today',
            'matricula' => 'nullable|string|max:50',
            'cargo' => 'nullable|string|max:100',
            'setor' => 'nullable|string|max:100',
            'nivel_acesso' => ['required', 'string', 'in:' . implode(',', $niveisPermitidos)],
            'password' => 'required|string|min:8|confirmed',
            'ativo' => 'boolean',
        ];

        // Município é obrigatório para perfis municipais
        if (in_array($request->nivel_acesso, ['gestor_municipal', 'tecnico_municipal'])) {
            $rules['municipio_id'] = 'required|exists:municipios,id';
        } else {
            $rules['municipio_id'] = 'nullable|exists:municipios,id';
        }

        $validated = $request->validate($rules, [
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos',
            'cpf.unique' => 'Este CPF já está cadastrado',
            'telefone.max' => 'O telefone deve ter no máximo 11 dígitos',
            'municipio_id.required' => 'O município é obrigatório para usuários municipais',
            'municipio_id.exists' => 'Município inválido',
            'nivel_acesso.in' => 'Você não tem permissão para criar usuários com este nível de acesso.',
        ]);

        // Se for Gestor Municipal, força o município do usuário logado
        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal && $usuarioLogado->municipio_id) {
            $validated['municipio_id'] = $usuarioLogado->municipio_id;
        }

        $validated = $this->normalizarDadosUsuario($validated);
        $validated['password'] = bcrypt($validated['password']);
        $validated['ativo'] = $request->has('ativo');
        $validated['status_cadastro'] = 'aprovado';
        $validated['aprovado_por'] = auth('interno')->id();
        $validated['aprovado_em'] = now();
        $validated['observacao_aprovacao'] = null;

        $usuario = UsuarioInterno::create($validated);

        // Sincronizar setores na pivot
        $setoresInput = $request->input('setores', []);
        if (!empty($setoresInput)) {
            $usuario->tipoSetores()->sync($setoresInput);
        } elseif (!empty($validated['setor'])) {
            // Fallback: se veio setor único, vincular na pivot também
            $tipoSetor = \App\Models\TipoSetor::where('codigo', $validated['setor'])->first();
            if ($tipoSetor) {
                $usuario->tipoSetores()->sync([$tipoSetor->id]);
            }
        }

        return redirect()->route('admin.usuarios-internos.index')
            ->with('success', 'Usuário interno criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(UsuarioInterno $usuarioInterno)
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para visualizar usuários.');
        }

        // Verifica se pode visualizar este usuário específico
        $this->verificarPermissaoUsuario($usuarioInterno);

        $usuarioInterno->load('municipioRelacionado');
        return view('admin.usuarios-internos.show', compact('usuarioInterno'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UsuarioInterno $usuarioInterno)
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para editar usuários.');
        }

        // Verifica se pode editar este usuário específico
        $this->verificarPermissaoUsuario($usuarioInterno);

        $municipios = \App\Models\Municipio::orderBy('nome')->get();
        
        // Tenta buscar os tipos de setor, retorna coleção vazia se a tabela não existir
        try {
            $tipoSetores = \App\Models\TipoSetor::where('ativo', true)
                ->with('municipios:id')
                ->orderBy('nome')
                ->get()
                ->map(function ($setor) {
                    $setor->municipio_ids = $setor->municipios->pluck('id')->toArray();
                    unset($setor->municipios);
                    return $setor;
                });
        } catch (\Exception $e) {
            $tipoSetores = collect([]);
        }
        
        // Níveis de acesso permitidos para o usuário logado
        $niveisPermitidos = $this->getNiveisPermitidos();

        // IDs dos setores vinculados ao usuário
        $setoresVinculadosIds = $usuarioInterno->tipoSetores()->pluck('tipo_setores.id')->toArray();
        
        return view('admin.usuarios-internos.edit', compact('usuarioInterno', 'municipios', 'tipoSetores', 'niveisPermitidos', 'setoresVinculadosIds'));
    }

    /**
     * Verifica se o usuário logado pode gerenciar o usuário especificado
     */
    private function verificarPermissaoUsuario(UsuarioInterno $usuarioInterno): void
    {
        $usuarioLogado = auth('interno')->user();
        
        // Admin pode tudo
        if ($usuarioLogado->isAdmin()) {
            return;
        }
        
        $niveisPermitidos = array_map(fn($n) => $n->value, $this->getNiveisPermitidos());
        
        // Verifica se o nível do usuário está nos permitidos
        if (!in_array($usuarioInterno->nivel_acesso->value, $niveisPermitidos)) {
            abort(403, 'Você não tem permissão para gerenciar este usuário.');
        }
        
        // Gestor Municipal só pode gerenciar usuários do seu município
        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal && $usuarioLogado->municipio_id) {
            if ($usuarioInterno->municipio_id !== $usuarioLogado->municipio_id) {
                abort(403, 'Você só pode gerenciar usuários do seu município.');
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UsuarioInterno $usuarioInterno)
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para editar usuários.');
        }

        // Verifica se pode editar este usuário específico
        $this->verificarPermissaoUsuario($usuarioInterno);

        $usuarioLogado = auth('interno')->user();
        $niveisPermitidos = array_map(fn($n) => $n->value, $this->getNiveisPermitidos());

        // Remove máscara do CPF e telefone antes de validar
        $request->merge([
            'cpf' => preg_replace('/[^0-9]/', '', $request->cpf),
            'telefone' => $request->telefone ? preg_replace('/[^0-9]/', '', $request->telefone) : null,
        ]);

        $rules = [
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:usuarios_internos,cpf,' . $usuarioInterno->id,
            'email' => 'required|email|unique:usuarios_internos,email,' . $usuarioInterno->id,
            'telefone' => 'nullable|string|max:11',
            'data_nascimento' => 'nullable|date|before_or_equal:today',
            'matricula' => 'nullable|string|max:50',
            'cargo' => 'nullable|string|max:100',
            'setor' => 'nullable|string|max:100',
            'nivel_acesso' => ['required', 'string', 'in:' . implode(',', $niveisPermitidos)],
            'password' => 'nullable|string|min:8|confirmed',
            'ativo' => 'boolean',
        ];

        // Município é obrigatório para perfis municipais
        if (in_array($request->nivel_acesso, ['gestor_municipal', 'tecnico_municipal'])) {
            $rules['municipio_id'] = 'required|exists:municipios,id';
        } else {
            $rules['municipio_id'] = 'nullable|exists:municipios,id';
        }

        $validated = $request->validate($rules, [
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos',
            'cpf.unique' => 'Este CPF já está cadastrado',
            'telefone.max' => 'O telefone deve ter no máximo 11 dígitos',
            'municipio_id.required' => 'O município é obrigatório para usuários municipais',
            'municipio_id.exists' => 'Município inválido',
            'nivel_acesso.in' => 'Você não tem permissão para definir este nível de acesso.',
        ]);

        // Se for Gestor Municipal, força o município do usuário logado
        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal && $usuarioLogado->municipio_id) {
            $validated['municipio_id'] = $usuarioLogado->municipio_id;
        }

        $validated = $this->normalizarDadosUsuario($validated);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['ativo'] = $request->has('ativo');
        if ($usuarioInterno->status_cadastro !== 'pendente') {
            $validated['status_cadastro'] = $usuarioInterno->status_cadastro ?: 'aprovado';
        }

        $usuarioInterno->update($validated);

        // Sincronizar setores na pivot
        $setoresInput = $request->input('setores', []);
        if (!empty($setoresInput)) {
            // Multi-select: sincroniza com os IDs selecionados
            $usuarioInterno->tipoSetores()->sync($setoresInput);
        } elseif (!empty($validated['setor'])) {
            // Select único: sincroniza com o setor selecionado
            $tipoSetor = \App\Models\TipoSetor::where('codigo', $validated['setor'])->first();
            if ($tipoSetor) {
                $usuarioInterno->tipoSetores()->sync([$tipoSetor->id]);
            }
        }

        return redirect()->route('admin.usuarios-internos.index')
            ->with('success', 'Usuário interno atualizado com sucesso!');
    }

    private function normalizarDadosUsuario(array $dados): array
    {
        foreach (['nome', 'matricula', 'cargo'] as $campo) {
            if (!empty($dados[$campo])) {
                $dados[$campo] = Str::upper(trim((string) $dados[$campo]));
            }
        }

        if (!empty($dados['email'])) {
            $dados['email'] = Str::lower(trim((string) $dados['email']));
        }

        return $dados;
    }

    private function gerarSlugConvite(string $titulo): string
    {
        $slug = Str::slug($titulo);

        // Se já existe, adiciona um sufixo numérico curto
        if (UsuarioInternoConvite::where('token', $slug)->exists()) {
            $i = 2;
            while (UsuarioInternoConvite::where('token', $slug . '-' . $i)->exists()) {
                $i++;
            }
            $slug = $slug . '-' . $i;
        }

        return $slug;
    }

    private function gerarTokenCurtoConvite(): string
    {
        do {
            $token = strtolower(Str::random(12));
        } while (UsuarioInternoConvite::where('token', $token)->exists());

        return $token;
    }

    public function destroyConvite(UsuarioInternoConvite $convite)
    {
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para excluir links de cadastro.');
        }

        $convite->delete();

        return redirect()
            ->route('admin.usuarios-internos.index', ['aba' => 'cadastros'])
            ->with('success', 'Link de cadastro excluído com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UsuarioInterno $usuarioInterno)
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para excluir usuários.');
        }

        // Verifica se pode excluir este usuário específico
        $this->verificarPermissaoUsuario($usuarioInterno);

        $usuarioInterno->delete();

        return redirect()->route('admin.usuarios-internos.index')
            ->with('success', 'Usuário interno excluído com sucesso!');
    }
}
