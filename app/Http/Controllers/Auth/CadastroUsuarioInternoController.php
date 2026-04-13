<?php

namespace App\Http\Controllers\Auth;

use App\Enums\NivelAcesso;
use App\Http\Controllers\Controller;
use App\Models\Municipio;
use App\Models\UsuarioInterno;
use App\Models\UsuarioInternoConvite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CadastroUsuarioInternoController extends Controller
{
    public function show(string $token)
    {
        $convite = $this->buscarConviteDisponivel($token);
        $municipios = Municipio::orderBy('nome')->get(['id', 'nome']);

        // Carregar setores ativos com seus municípios vinculados
        $tipoSetores = \App\Models\TipoSetor::where('ativo', true)
            ->with('municipios:id')
            ->orderBy('nome')
            ->get()
            ->map(function ($setor) {
                $setor->municipio_ids = $setor->municipios->pluck('id')->toArray();
                unset($setor->municipios);
                return $setor;
            });

        // Gerar QR Code da página atual
        $qrCodeBase64 = null;
        try {
            $link = route('cadastro-interno.show', $convite->token);
            $qr = new \Endroid\QrCode\QrCode($link);
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qr);
            $qrCodeBase64 = base64_encode($result->getString());
        } catch (\Throwable $e) {
            // silencia erro
        }

        return view('auth.cadastro-usuario-interno', [
            'convite' => $convite,
            'municipio' => $convite->municipio,
            'nivelAcesso' => NivelAcesso::from($convite->nivel_acesso),
            'municipios' => $municipios,
            'tipoSetores' => $tipoSetores,
            'qrCodeBase64' => $qrCodeBase64,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $convite = $this->buscarConviteDisponivel($token);

        $request->merge([
            'cpf' => preg_replace('/[^0-9]/', '', (string) $request->cpf),
            'telefone' => $request->telefone ? preg_replace('/[^0-9]/', '', (string) $request->telefone) : null,
        ]);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:usuarios_internos,cpf',
            'email' => 'required|email|max:255|unique:usuarios_internos,email',
            'telefone' => 'nullable|string|max:11',
            'data_nascimento' => 'nullable|date|before_or_equal:today',
            'matricula' => 'nullable|string|max:50|unique:usuarios_internos,matricula',
            'cargo' => 'nullable|string|max:100',
            'setor' => 'nullable|string|max:100',
            'municipio_id' => 'nullable|exists:municipios,id',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'email.unique' => 'Este email já está cadastrado.',
            'matricula.unique' => 'Esta matrícula já está cadastrada.',
        ]);

        if (in_array($convite->nivel_acesso, ['gestor_municipal', 'tecnico_municipal'], true) && empty($validated['municipio_id'])) {
            return back()
                ->withErrors(['municipio_id' => 'Selecione o município para concluir o cadastro.'])
                ->withInput();
        }

        $usuario = UsuarioInterno::create([
            ...$this->normalizarDados($validated),
            'nivel_acesso' => $convite->nivel_acesso,
            'municipio_id' => $validated['municipio_id'] ?? $convite->municipio_id,
            'convite_id' => $convite->id,
            'password' => bcrypt($validated['password']),
            'ativo' => false,
            'status_cadastro' => 'pendente',
            'aprovado_por' => null,
            'aprovado_em' => null,
            'observacao_aprovacao' => null,
        ]);

        $convite->forceFill([
            'ultimo_uso_em' => now(),
        ])->save();

        return redirect()
            ->route('cadastro-interno.show', $convite->token)
            ->with('success', 'Cadastro enviado com sucesso! Aguarde a aprovação de um administrador para acessar o sistema.');
    }

    private function buscarConviteDisponivel(string $token): UsuarioInternoConvite
    {
        $convite = UsuarioInternoConvite::with('municipio')
            ->where('token', $token)
            ->firstOrFail();

        abort_unless($convite->isDisponivel(), 404);

        return $convite;
    }

    private function normalizarDados(array $data): array
    {
        foreach (['nome', 'matricula', 'cargo'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = Str::upper(trim((string) $data[$field]));
            }
        }

        $data['email'] = Str::lower(trim((string) $data['email']));

        return $data;
    }
}
