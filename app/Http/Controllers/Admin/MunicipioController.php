<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NivelAcesso;
use App\Http\Controllers\Controller;
use App\Models\Municipio;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MunicipioController extends Controller
{
    /**
     * Lista todos os municípios
     */
    public function index(Request $request)
    {
        $usuario = auth('interno')->user();
        $query = Municipio::query();

        if ($this->isGestorMunicipal($usuario)) {
            if (!$usuario->municipio_id) {
                abort(403, 'Gestor municipal sem município vinculado.');
            }

            $query->where('id', $usuario->municipio_id);
        }

        // Filtro de busca
        if ($request->filled('search') && !$this->isGestorMunicipal($usuario)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('codigo_ibge', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filtro de status
        if ($request->filled('ativo') && !$this->isGestorMunicipal($usuario)) {
            $query->where('ativo', $request->ativo === '1');
        }

        // Filtro de UF
        if ($request->filled('uf') && !$this->isGestorMunicipal($usuario)) {
            $query->where('uf', $request->uf);
        }

        $municipios = $query->withCount('usuariosInternos')->orderBy('nome')->paginate(20);

        $municipiosIds = (clone $query)->pluck('id');

        // Estatísticas
        $stats = [
            'total' => Municipio::whereIn('id', $municipiosIds)->count(),
            'ativos' => Municipio::whereIn('id', $municipiosIds)->where('ativo', true)->count(),
            'inativos' => Municipio::whereIn('id', $municipiosIds)->where('ativo', false)->count(),
            'com_estabelecimentos' => Municipio::whereIn('id', $municipiosIds)->has('estabelecimentos')->count(),
            'com_pactuacoes' => Municipio::whereIn('id', $municipiosIds)->has('pactuacoes')->count(),
            'com_usuarios' => Municipio::whereIn('id', $municipiosIds)->has('usuariosInternos')->count(),
        ];

        $modoMunicipal = $this->isGestorMunicipal($usuario);

        return view('admin.municipios.index', compact('municipios', 'stats', 'modoMunicipal'));
    }

    /**
     * Exibe formulário de criação
     */
    public function create()
    {
        return view('admin.municipios.create');
    }

    /**
     * Salva novo município
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:100',
            'codigo_ibge' => 'required|string|size:7|unique:municipios,codigo_ibge',
            'uf' => 'required|string|size:2',
            'logomarca' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            // ativo não precisa de validação pois usamos $request->has('ativo')
        ], [
            'nome.required' => 'O nome do município é obrigatório',
            'codigo_ibge.required' => 'O código IBGE é obrigatório',
            'codigo_ibge.size' => 'O código IBGE deve ter 7 dígitos',
            'codigo_ibge.unique' => 'Este código IBGE já está cadastrado',
            'uf.required' => 'A UF é obrigatória',
            'uf.size' => 'A UF deve ter 2 caracteres',
            'logomarca.image' => 'O arquivo deve ser uma imagem',
            'logomarca.mimes' => 'A logomarca deve ser um arquivo: jpeg, png, jpg ou svg',
            'logomarca.max' => 'A logomarca não pode ser maior que 2MB',
        ]);

        $dados = [
            'nome' => mb_strtoupper(trim($request->nome)),
            'codigo_ibge' => $request->codigo_ibge,
            'uf' => mb_strtoupper($request->uf),
            'slug' => Str::slug($request->nome),
            'ativo' => $request->has('ativo'),
        ];

        // Upload da logomarca
        if ($request->hasFile('logomarca')) {
            // Garante que o diretório existe
            if (!\Storage::disk('public')->exists('municipios/logomarcas')) {
                \Storage::disk('public')->makeDirectory('municipios/logomarcas');
            }
            
            $arquivo = $request->file('logomarca');
            $nomeArquivo = 'logomarca_' . Str::slug($request->nome) . '_' . time() . '.' . $this->determinarExtensaoImagem($arquivo);
            $caminho = $arquivo->storeAs('municipios/logomarcas', $nomeArquivo, 'public');
            $dados['logomarca'] = 'storage/' . $caminho;
        }

        $municipio = Municipio::create($dados);

        return redirect()
            ->route('admin.configuracoes.municipios.index')
            ->with('success', 'Município cadastrado com sucesso!');
    }

    /**
     * Exibe detalhes do município
     */
    public function show($id)
    {
        $municipio = Municipio::with(['estabelecimentos', 'pactuacoes'])->findOrFail($id);

        // Busca pactuações municipais (Tabela I - atividades de competência de TODOS os municípios)
        // Essas pactuações não têm municipio_id porque são para todos os 139 municípios
        $pactuacoesMunicipais = \App\Models\Pactuacao::where('tipo', 'municipal')
            ->where('tabela', 'I')
            ->where('ativo', true)
            ->orderBy('cnae_codigo')
            ->get();
        
        // Busca descentralizações (Tabela III - atividades estaduais delegadas ao município)
        $descentralizacoes = $municipio->descentralizacoes();

        // Estatísticas do município
        $stats = [
            'estabelecimentos_total' => $municipio->estabelecimentos()->count(),
            'estabelecimentos_ativos' => $municipio->estabelecimentos()->where('ativo', true)->count(),
            'estabelecimentos_pendentes' => $municipio->estabelecimentos()->where('status', 'pendente')->count(),
            'pactuacoes_municipais' => $pactuacoesMunicipais->count(),
            'pactuacoes_excecoes' => $descentralizacoes->count(),
        ];

        return view('admin.municipios.show', compact('municipio', 'stats', 'pactuacoesMunicipais', 'descentralizacoes'));
    }

    /**
     * Exibe formulário de edição
     */
    public function edit($id)
    {
        $usuario = auth('interno')->user();
        $this->autorizarAcessoMunicipio($usuario, (int) $id);

        $municipio = Municipio::findOrFail($id);
        $modoMunicipal = $this->isGestorMunicipal($usuario);

        return view('admin.municipios.edit', compact('municipio', 'modoMunicipal'));
    }

    /**
     * Atualiza município
     */
    public function update(Request $request, $id)
    {
        $usuario = auth('interno')->user();
        $this->autorizarAcessoMunicipio($usuario, (int) $id);

        $municipio = Municipio::findOrFail($id);

        if ($this->isGestorMunicipal($usuario)) {
            $request->validate([
                'logomarca' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
                'rodape_documento' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
                'rodape_texto' => 'nullable|string|max:4000',
                'remover_logomarca' => 'nullable|in:1,on',
                'remover_rodape_documento' => 'nullable|in:1,on',
            ], [
                'logomarca.image' => 'O arquivo deve ser uma imagem',
                'logomarca.mimes' => 'A logomarca deve ser um arquivo: jpeg, png, jpg ou svg',
                'logomarca.max' => 'A logomarca não pode ser maior que 2MB',
                'rodape_documento.image' => 'O arquivo deve ser uma imagem',
                'rodape_documento.mimes' => 'O rodapé deve ser um arquivo: jpeg, png, jpg ou svg',
                'rodape_documento.max' => 'O rodapé não pode ser maior que 4MB',
                'rodape_texto.max' => 'O texto do rodapé não pode ter mais que 4000 caracteres',
            ]);

            $dados = [];

            if ($request->hasFile('logomarca')) {
                $dados['logomarca'] = $this->armazenarImagemMunicipio(
                    $request->file('logomarca'),
                    'municipios/logomarcas',
                    'logomarca_' . Str::slug($municipio->nome),
                    $municipio->logomarca
                );
            } elseif ($request->has('remover_logomarca') && $municipio->logomarca) {
                $this->removerImagemMunicipio($municipio->logomarca);
                $dados['logomarca'] = null;
            }

            if ($request->hasFile('rodape_documento')) {
                $dados['rodape_documento'] = $this->armazenarImagemMunicipio(
                    $request->file('rodape_documento'),
                    'municipios/rodapes',
                    'rodape_' . Str::slug($municipio->nome),
                    $municipio->rodape_documento
                );
            } elseif ($request->has('remover_rodape_documento') && $municipio->rodape_documento) {
                $this->removerImagemMunicipio($municipio->rodape_documento);
                $dados['rodape_documento'] = null;
            }

            if ($request->has('rodape_texto')) {
                $textoRodape = trim((string) $request->input('rodape_texto'));
                $dados['rodape_texto'] = $textoRodape !== '' ? $textoRodape : null;
            }

            if (!empty($dados)) {
                $municipio->update($dados);
            }

            return redirect()
                ->route('admin.configuracoes.municipios.index')
                ->with('success', 'Identidade visual do município atualizada com sucesso!');
        }

        // Debug: Log todos os dados recebidos
        \Log::info('=== ATUALIZAÇÃO DE MUNICÍPIO ===', [
            'municipio_id' => $id,
            'has_file_logomarca' => $request->hasFile('logomarca'),
            'all_files' => array_keys($request->allFiles()),
            'all_input' => array_keys($request->all()),
            'content_type' => $request->header('Content-Type'),
        ]);

        // Validação com log de erros
        $validator = \Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
            'codigo_ibge' => 'required|string|size:7|unique:municipios,codigo_ibge,' . $id,
            'uf' => 'required|string|size:2',
            'logomarca' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'rodape_documento' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'rodape_texto' => 'nullable|string|max:4000',
            'remover_logomarca' => 'nullable|in:1,on',
            'remover_rodape_documento' => 'nullable|in:1,on',
            // ativo não precisa de validação pois usamos $request->has('ativo')
        ], [
            'nome.required' => 'O nome do município é obrigatório',
            'codigo_ibge.required' => 'O código IBGE é obrigatório',
            'codigo_ibge.size' => 'O código IBGE deve ter 7 dígitos',
            'codigo_ibge.unique' => 'Este código IBGE já está cadastrado',
            'uf.required' => 'A UF é obrigatória',
            'uf.size' => 'A UF deve ter 2 caracteres',
            'logomarca.image' => 'O arquivo deve ser uma imagem',
            'logomarca.mimes' => 'A logomarca deve ser um arquivo: jpeg, png, jpg ou svg',
            'logomarca.max' => 'A logomarca não pode ser maior que 2MB',
            'rodape_documento.image' => 'O arquivo deve ser uma imagem',
            'rodape_documento.mimes' => 'O rodapé deve ser um arquivo: jpeg, png, jpg ou svg',
            'rodape_documento.max' => 'O rodapé não pode ser maior que 4MB',
            'rodape_texto.max' => 'O texto do rodapé não pode ter mais que 4000 caracteres',
        ]);

        if ($validator->fails()) {
            \Log::error('❌ Validação falhou', [
                'errors' => $validator->errors()->toArray(),
            ]);
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        \Log::info('✅ Validação passou!');

        $dados = [
            'nome' => mb_strtoupper(trim($request->nome)),
            'codigo_ibge' => $request->codigo_ibge,
            'uf' => mb_strtoupper($request->uf),
            'slug' => Str::slug($request->nome),
            'ativo' => $request->has('ativo'),
            'usa_infovisa' => $request->has('usa_infovisa'),
            'documentos_manuais' => $request->has('documentos_manuais'),
            'data_adesao_infovisa' => $request->has('usa_infovisa') ? $request->data_adesao_infovisa : null,
            'rodape_texto' => filled($request->rodape_texto) ? trim((string) $request->rodape_texto) : null,
        ];

        // Upload da nova logomarca (tem prioridade sobre remoção)
        if ($request->hasFile('logomarca')) {
            $arquivo = $request->file('logomarca');
            
            \Log::info('Upload de logomarca iniciado', [
                'municipio_id' => $id,
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'arquivo_valido' => $arquivo->isValid(),
                'arquivo_erro' => $arquivo->getError(),
            ]);
            
            // Verifica se o arquivo é válido
            if (!$arquivo->isValid()) {
                \Log::error('Arquivo inválido', [
                    'erro_codigo' => $arquivo->getError(),
                    'erro_mensagem' => $arquivo->getErrorMessage(),
                ]);
                return redirect()
                    ->back()
                    ->with('error', 'Erro no upload do arquivo: ' . $arquivo->getErrorMessage());
            }
            
            // Garante que o diretório existe
            if (!\Storage::disk('public')->exists('municipios/logomarcas')) {
                \Storage::disk('public')->makeDirectory('municipios/logomarcas');
                \Log::info('Diretório criado: municipios/logomarcas no disco public');
            }
            
            // Remove logomarca antiga se existir
            if ($municipio->logomarca) {
                $caminhoAntigo = str_replace('storage/', '', $municipio->logomarca);
                \Storage::disk('public')->delete($caminhoAntigo);
                \Log::info('Logomarca antiga removida', ['caminho' => $caminhoAntigo]);
            }
            
            try {
                $dados['logomarca'] = $this->armazenarImagemMunicipio(
                    $arquivo,
                    'municipios/logomarcas',
                    'logomarca_' . Str::slug($request->nome),
                    $municipio->logomarca
                );
                
                \Log::info('Logomarca salva com sucesso!', [
                    'caminho_public' => $dados['logomarca'],
                    'nome_arquivo' => basename((string) $dados['logomarca']),
                ]);
            } catch (\Exception $e) {
                \Log::error('Erro ao salvar logomarca', [
                    'erro' => $e->getMessage(),
                ]);
                return redirect()
                    ->back()
                    ->with('error', 'Erro ao salvar logomarca: ' . $e->getMessage());
            }
        } elseif ($request->has('remover_logomarca') && $municipio->logomarca) {
            // Remove logomarca apenas se não houver upload de nova imagem
            $this->removerImagemMunicipio($municipio->logomarca);
            $dados['logomarca'] = null;
            \Log::info('Logomarca removida', ['caminho' => $municipio->logomarca]);
        } else {
            \Log::warning('Nenhum arquivo de logomarca recebido', [
                'municipio_id' => $id,
                'has_file' => $request->hasFile('logomarca'),
                'all_files' => $request->allFiles(),
            ]);
        }

        if ($request->hasFile('rodape_documento')) {
            try {
                $dados['rodape_documento'] = $this->armazenarImagemMunicipio(
                    $request->file('rodape_documento'),
                    'municipios/rodapes',
                    'rodape_' . Str::slug($request->nome),
                    $municipio->rodape_documento
                );
            } catch (\Exception $e) {
                \Log::error('Erro ao salvar rodapé do município', [
                    'erro' => $e->getMessage(),
                ]);

                return redirect()
                    ->back()
                    ->with('error', 'Erro ao salvar o rodapé do município: ' . $e->getMessage());
            }
        } elseif ($request->has('remover_rodape_documento') && $municipio->rodape_documento) {
            $this->removerImagemMunicipio($municipio->rodape_documento);
            $dados['rodape_documento'] = null;
        }

        $municipio->update($dados);
        
        $mensagem = 'Município atualizado com sucesso!';
        if (isset($dados['logomarca'])) {
            $mensagem .= ' Logomarca salva em: ' . $dados['logomarca'];
        }

        return redirect()
            ->route('admin.configuracoes.municipios.index')
            ->with('success', $mensagem);
    }

    /**
     * Ativa/Desativa município
     */
    public function toggleStatus($id)
    {
        try {
            $municipio = Municipio::findOrFail($id);
            $municipio->ativo = !$municipio->ativo;
            $municipio->save();

            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso!',
                'ativo' => $municipio->ativo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove município
     */
    public function destroy($id)
    {
        try {
            $municipio = Municipio::findOrFail($id);

            // Verifica se há estabelecimentos vinculados
            if ($municipio->estabelecimentos()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível excluir este município pois existem estabelecimentos vinculados.');
            }

            // Verifica se há pactuações vinculadas
            if ($municipio->pactuacoes()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível excluir este município pois existem pactuações vinculadas.');
            }

            $municipio->delete();

            return redirect()
                ->route('admin.configuracoes.municipios.index')
                ->with('success', 'Município excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao excluir município: ' . $e->getMessage());
        }
    }

    /**
     * Busca municípios (para autocomplete)
     */
    public function buscar(Request $request)
    {
        $termo = $request->get('termo', '');

        $municipios = Municipio::where('ativo', true)
            ->where(function($q) use ($termo) {
                $q->where('nome', 'like', "%{$termo}%")
                  ->orWhere('codigo_ibge', 'like', "%{$termo}%");
            })
            ->orderBy('nome')
            ->limit(20)
            ->get(['id', 'nome', 'codigo_ibge', 'uf']);

        return response()->json($municipios);
    }

    private function isGestorMunicipal($usuario): bool
    {
        return $usuario->nivel_acesso === NivelAcesso::GestorMunicipal;
    }

    private function autorizarAcessoMunicipio($usuario, int $municipioId): void
    {
        if ($usuario->isAdmin() || $usuario->isEstadual()) {
            return;
        }

        if ($this->isGestorMunicipal($usuario) && (int) $usuario->municipio_id === $municipioId) {
            return;
        }

        abort(403, 'Você não tem permissão para acessar este município.');
    }

    private function armazenarImagemMunicipio(UploadedFile $arquivo, string $diretorio, string $prefixo, ?string $caminhoAnterior = null): string
    {
        if (!Storage::disk('public')->exists($diretorio)) {
            Storage::disk('public')->makeDirectory($diretorio);
        }

        if ($caminhoAnterior) {
            $this->removerImagemMunicipio($caminhoAnterior);
        }

        $nomeArquivo = $prefixo . '_' . time() . '.' . $this->determinarExtensaoImagem($arquivo);
        $caminho = $arquivo->storeAs($diretorio, $nomeArquivo, 'public');

        if (!$caminho) {
            throw new \RuntimeException('Não foi possível armazenar a imagem no disco público.');
        }

        return 'storage/' . $caminho;
    }

    private function removerImagemMunicipio(?string $caminhoPublico): void
    {
        if (!$caminhoPublico) {
            return;
        }

        $caminhoRelativo = str_replace('storage/', '', $caminhoPublico);
        Storage::disk('public')->delete($caminhoRelativo);
    }

    private function determinarExtensaoImagem(UploadedFile $arquivo): string
    {
        return match ($arquivo->getMimeType()) {
            'image/jpeg', 'image/pjpeg' => 'jpg',
            'image/png' => 'png',
            'image/svg+xml' => 'svg',
            default => strtolower($arquivo->guessExtension() ?: $arquivo->extension() ?: $arquivo->getClientOriginalExtension()),
        };
    }
}
