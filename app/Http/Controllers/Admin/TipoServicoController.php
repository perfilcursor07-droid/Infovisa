<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Atividade;
use App\Models\Municipio;
use App\Models\Pactuacao;
use App\Models\TipoServico;
use Illuminate\Http\Request;

class TipoServicoController extends Controller
{
    public function index(Request $request)
    {
        $query = TipoServico::withCount('atividades');

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'ilike', "%{$busca}%")
                  ->orWhere('descricao', 'ilike', "%{$busca}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        $tipos = $query->ordenado()->paginate(20)->withQueryString();

        return view('configuracoes.tipos-servico.index', compact('tipos'));
    }

    public function create()
    {
        $municipios = Municipio::orderBy('nome')->get();
        return view('configuracoes.tipos-servico.create', compact('municipios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'escopo' => 'required|in:estadual,municipal',
            'municipio_id' => 'nullable|required_if:escopo,municipal|exists:municipios,id',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
            'importar_cnaes' => 'nullable|array',
            'importar_cnaes.*' => 'string',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;
        if ($validated['escopo'] === 'estadual') {
            $validated['municipio_id'] = null;
        }

        // Remove importar_cnaes do array antes de criar o tipo
        $cnaesParaImportar = $validated['importar_cnaes'] ?? [];
        unset($validated['importar_cnaes']);

        $tipoServico = TipoServico::create($validated);

        // Se selecionou CNAEs para importar da pactuação
        if (!empty($cnaesParaImportar) && $validated['escopo'] === 'municipal') {
            $this->importarCnaesDaPactuacao($tipoServico, $cnaesParaImportar);
        }

        $countAtividades = $tipoServico->atividades()->count();
        $mensagem = 'Tipo de serviço criado com sucesso!';
        if ($countAtividades > 0) {
            $mensagem .= " {$countAtividades} atividade(s) importada(s) da pactuação.";
        }

        return redirect()
            ->route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-servico'])
            ->with('success', $mensagem);
    }

    public function edit(TipoServico $tipos_servico)
    {
        $municipios = Municipio::orderBy('nome')->get();
        return view('configuracoes.tipos-servico.edit', [
            'tipo' => $tipos_servico,
            'municipios' => $municipios,
        ]);
    }

    public function update(Request $request, TipoServico $tipos_servico)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'escopo' => 'required|in:estadual,municipal',
            'municipio_id' => 'nullable|required_if:escopo,municipal|exists:municipios,id',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ]);

        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = $validated['ordem'] ?? 0;
        if ($validated['escopo'] === 'estadual') {
            $validated['municipio_id'] = null;
        }

        $tipos_servico->update($validated);

        return redirect()
            ->route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-servico'])
            ->with('success', 'Tipo de serviço atualizado com sucesso!');
    }

    public function destroy(TipoServico $tipos_servico)
    {
        // Verifica se tem atividades vinculadas
        if ($tipos_servico->atividades()->exists()) {
            return redirect()
                ->route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-servico'])
                ->with('error', 'Este tipo de serviço possui atividades vinculadas e não pode ser excluído.');
        }

        $tipos_servico->delete();

        return redirect()
            ->route('admin.configuracoes.listas-documento.index', ['tab' => 'tipos-servico'])
            ->with('success', 'Tipo de serviço excluído com sucesso!');
    }

    /**
     * Busca os CNAEs de um município na tabela de pactuação (AJAX)
     */
    public function buscarCnaesMunicipio($municipioId)
    {
        $municipio = Municipio::findOrFail($municipioId);
        $municipioNome = $municipio->nome;

        // Busca pactuações municipais diretas
        $pactuacoesMunicipais = Pactuacao::where('ativo', true)
            ->where(function ($query) use ($municipioNome, $municipioId) {
                // Pactuações do tipo municipal com o nome do município
                $query->where(function ($q) use ($municipioNome) {
                    $q->where('tipo', 'municipal')
                      ->where('municipio', $municipioNome);
                })
                // Ou pactuações municipais vinculadas pelo ID
                ->orWhere(function ($q) use ($municipioId) {
                    $q->where('tipo', 'municipal')
                      ->where('municipio_id', $municipioId);
                });
            })
            ->select('cnae_codigo', 'cnae_descricao', 'tabela', 'classificacao_risco')
            ->orderBy('cnae_descricao')
            ->get();

        // Busca pactuações estaduais descentralizadas para o município
        $pactuacoesDescentralizadas = Pactuacao::where('ativo', true)
            ->where('tipo', 'estadual')
            ->where(function ($query) use ($municipioNome, $municipioId) {
                $query->whereJsonContains('municipios_excecao', $municipioNome)
                    ->orWhereJsonContains('municipios_excecao_ids', $municipioId);
            })
            ->select('cnae_codigo', 'cnae_descricao', 'tabela', 'classificacao_risco')
            ->orderBy('cnae_descricao')
            ->get();

        // Combina e remove duplicatas
        $cnaes = $pactuacoesMunicipais->concat($pactuacoesDescentralizadas)
            ->unique('cnae_codigo')
            ->values()
            ->map(function ($pactuacao) use ($pactuacoesMunicipais) {
                return [
                    'cnae_codigo' => $pactuacao->cnae_codigo,
                    'cnae_descricao' => $pactuacao->cnae_descricao,
                    'tabela' => $pactuacao->tabela,
                    'classificacao_risco' => $pactuacao->classificacao_risco,
                    'origem' => $pactuacoesMunicipais->contains('cnae_codigo', $pactuacao->cnae_codigo)
                        ? 'municipal'
                        : 'descentralizado',
                ];
            });

        return response()->json([
            'municipio' => $municipioNome,
            'total' => $cnaes->count(),
            'cnaes' => $cnaes,
        ]);
    }

    /**
     * Importa CNAEs da pactuação como atividades do tipo de serviço
     */
    private function importarCnaesDaPactuacao(TipoServico $tipoServico, array $cnaesCodigos)
    {
        $pactuacoes = Pactuacao::where('ativo', true)
            ->whereIn('cnae_codigo', $cnaesCodigos)
            ->get()
            ->unique('cnae_codigo');

        $ordem = 0;
        foreach ($pactuacoes as $pactuacao) {
            Atividade::create([
                'tipo_servico_id' => $tipoServico->id,
                'nome' => $pactuacao->cnae_descricao ?? 'CNAE ' . $pactuacao->cnae_codigo,
                'codigo_cnae' => $pactuacao->cnae_codigo,
                'descricao' => null,
                'ativo' => true,
                'ordem' => $ordem++,
            ]);
        }
    }
}
