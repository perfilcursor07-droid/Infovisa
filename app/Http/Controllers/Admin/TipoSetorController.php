<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoSetor;
use App\Models\Municipio;
use App\Enums\NivelAcesso;
use Illuminate\Http\Request;

class TipoSetorController extends Controller
{
    public function index(Request $request)
    {
        $query = TipoSetor::with('municipios')->orderBy('nome');

        if ($request->filled('escopo')) {
            if ($request->escopo === 'global') {
                $query->whereDoesntHave('municipios');
            } elseif ($request->escopo === 'municipal') {
                $query->whereHas('municipios', function ($q) use ($request) {
                    if ($request->filled('municipio_id')) {
                        $q->where('municipios.id', $request->municipio_id);
                    }
                });
            }
        }

        $tipoSetores = $query->paginate(20)->withQueryString();
        $municipios = Municipio::orderBy('nome')->get(['id', 'nome']);

        return view('admin.configuracoes.tipo-setores.index', compact('tipoSetores', 'municipios'));
    }

    public function create()
    {
        $niveisAcesso = NivelAcesso::cases();
        $municipios = Municipio::orderBy('nome')->get(['id', 'nome']);

        return view('admin.configuracoes.tipo-setores.create', compact('niveisAcesso', 'municipios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'codigo' => 'required|string|max:50|unique:tipo_setores,codigo',
            'descricao' => 'nullable|string',
            'niveis_acesso' => 'nullable|array',
            'niveis_acesso.*' => 'string|in:' . implode(',', array_map(fn($case) => $case->value, NivelAcesso::cases())),
            'municipios' => 'nullable|array',
            'municipios.*' => 'exists:municipios,id',
            'ativo' => 'boolean',
        ]);

        if (empty($validated['niveis_acesso'])) {
            $validated['niveis_acesso'] = null;
        }

        $validated['ativo'] = $request->has('ativo');

        $setor = TipoSetor::create([
            'nome' => $validated['nome'],
            'codigo' => $validated['codigo'],
            'descricao' => $validated['descricao'] ?? null,
            'niveis_acesso' => $validated['niveis_acesso'],
            'ativo' => $validated['ativo'],
        ]);

        if (!empty($validated['municipios'])) {
            $setor->municipios()->sync($validated['municipios']);
        }

        return redirect()
            ->route('admin.configuracoes.tipo-setores.index')
            ->with('success', 'Tipo de setor criado com sucesso!');
    }

    public function show(TipoSetor $tipoSetor)
    {
        $tipoSetor->load('municipios');
        return view('admin.configuracoes.tipo-setores.show', compact('tipoSetor'));
    }

    public function edit(TipoSetor $tipoSetor)
    {
        $tipoSetor->load('municipios');
        $niveisAcesso = NivelAcesso::cases();
        $municipios = Municipio::orderBy('nome')->get(['id', 'nome']);

        return view('admin.configuracoes.tipo-setores.edit', compact('tipoSetor', 'niveisAcesso', 'municipios'));
    }

    public function update(Request $request, TipoSetor $tipoSetor)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'codigo' => 'required|string|max:50|unique:tipo_setores,codigo,' . $tipoSetor->id,
            'descricao' => 'nullable|string',
            'niveis_acesso' => 'nullable|array',
            'niveis_acesso.*' => 'string|in:' . implode(',', array_map(fn($case) => $case->value, NivelAcesso::cases())),
            'municipios' => 'nullable|array',
            'municipios.*' => 'exists:municipios,id',
            'ativo' => 'boolean',
        ]);

        if (empty($validated['niveis_acesso'])) {
            $validated['niveis_acesso'] = null;
        }

        $validated['ativo'] = $request->has('ativo');

        $tipoSetor->update([
            'nome' => $validated['nome'],
            'codigo' => $validated['codigo'],
            'descricao' => $validated['descricao'] ?? null,
            'niveis_acesso' => $validated['niveis_acesso'],
            'ativo' => $validated['ativo'],
        ]);

        $tipoSetor->municipios()->sync($validated['municipios'] ?? []);

        return redirect()
            ->route('admin.configuracoes.tipo-setores.index')
            ->with('success', 'Tipo de setor atualizado com sucesso!');
    }

    public function destroy(TipoSetor $tipoSetor)
    {
        $tipoSetor->municipios()->detach();
        $tipoSetor->delete();

        return redirect()
            ->route('admin.configuracoes.tipo-setores.index')
            ->with('success', 'Tipo de setor excluído com sucesso!');
    }

    public function toggleStatus(TipoSetor $tipoSetor)
    {
        $tipoSetor->update(['ativo' => !$tipoSetor->ativo]);

        return redirect()->back()->with('success', 'Status atualizado com sucesso!');
    }
}
