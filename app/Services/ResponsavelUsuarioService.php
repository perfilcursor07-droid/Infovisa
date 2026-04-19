<?php

namespace App\Services;

use App\Models\Estabelecimento;
use App\Models\Responsavel;
use App\Models\UsuarioExterno;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ResponsavelUsuarioService
{
    /**
     * Ao cadastrar um responsável, cria (ou reutiliza) um UsuarioExterno
     * e vincula ao estabelecimento automaticamente.
     */
    public static function vincularResponsavelComoUsuario(
        Responsavel $responsavel,
        Estabelecimento $estabelecimento,
        string $tipoVinculo // 'legal' ou 'tecnico'
    ): ?UsuarioExterno {
        if (empty($responsavel->cpf) || empty($responsavel->email)) {
            return null;
        }

        $cpfLimpo = preg_replace('/\D/', '', $responsavel->cpf);
        $senhaGerada = null;
        $usuarioCriado = false;

        // Busca ou cria o usuário externo
        $usuario = UsuarioExterno::where('cpf', $cpfLimpo)->first();

        if (!$usuario) {
            $senhaGerada = Str::random(8);
            $usuario = UsuarioExterno::create([
                'nome' => mb_strtoupper($responsavel->nome),
                'cpf' => $cpfLimpo,
                'email' => $responsavel->email,
                'telefone' => $responsavel->telefone,
                'vinculo_estabelecimento' => $tipoVinculo === 'legal' ? 'responsavel_legal' : 'responsavel_tecnico',
                'password' => bcrypt($senhaGerada),
                'ativo' => true,
            ]);
            $usuarioCriado = true;
        }

        // Vincula ao estabelecimento (se ainda não estiver vinculado)
        $tipoVinculoUsuario = $tipoVinculo === 'legal' ? 'responsavel_legal' : 'responsavel_tecnico';

        $jaVinculado = $estabelecimento->usuariosVinculados()
            ->where('usuario_externo_id', $usuario->id)
            ->exists();

        // Também verifica se é o criador do estabelecimento
        $ehCriador = $estabelecimento->usuario_externo_id === $usuario->id;

        if (!$jaVinculado && !$ehCriador) {
            $estabelecimento->usuariosVinculados()->attach($usuario->id, [
                'tipo_vinculo' => $tipoVinculoUsuario,
                'nivel_acesso' => 'gestor',
                'observacao' => 'Vinculado automaticamente como ' . ($tipoVinculo === 'legal' ? 'Responsável Legal' : 'Responsável Técnico'),
            ]);
        }

        // Envia email com credenciais se o usuário foi criado agora
        if ($usuarioCriado && $senhaGerada) {
            try {
                self::enviarEmailCredenciais($usuario, $senhaGerada, $estabelecimento, $tipoVinculo);
            } catch (\Throwable $e) {
                Log::warning('Falha ao enviar email de credenciais para responsável', [
                    'usuario_id' => $usuario->id,
                    'email' => $usuario->email,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        return $usuario;
    }

    /**
     * Envia email com as credenciais de acesso.
     */
    private static function enviarEmailCredenciais(
        UsuarioExterno $usuario,
        string $senha,
        Estabelecimento $estabelecimento,
        string $tipoVinculo
    ): void {
        $nomeEstabelecimento = $estabelecimento->nome_fantasia ?: $estabelecimento->razao_social ?: 'Estabelecimento';
        $tipoLabel = $tipoVinculo === 'legal' ? 'Responsável Legal' : 'Responsável Técnico';
        $loginUrl = route('login');

        Mail::send([], [], function ($message) use ($usuario, $senha, $nomeEstabelecimento, $tipoLabel, $loginUrl) {
            $message->to($usuario->email, $usuario->nome)
                ->subject('InfoVISA - Seu acesso foi criado automaticamente')
                ->html("
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <div style='background: linear-gradient(135deg, #2563eb, #0ea5e9); padding: 24px; border-radius: 12px 12px 0 0;'>
                            <h1 style='color: white; margin: 0; font-size: 20px;'>InfoVISA - Acesso Criado</h1>
                        </div>
                        <div style='background: #ffffff; padding: 24px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 12px 12px;'>
                            <p style='color: #374151; font-size: 14px;'>Olá, <strong>{$usuario->nome}</strong>!</p>
                            <p style='color: #374151; font-size: 14px;'>Você foi cadastrado como <strong>{$tipoLabel}</strong> do estabelecimento <strong>{$nomeEstabelecimento}</strong> no sistema InfoVISA.</p>
                            <p style='color: #374151; font-size: 14px;'>Um acesso foi criado automaticamente para você:</p>
                            <div style='background: #f3f4f6; padding: 16px; border-radius: 8px; margin: 16px 0;'>
                                <p style='margin: 4px 0; font-size: 14px;'><strong>CPF:</strong> {$usuario->cpf_formatado}</p>
                                <p style='margin: 4px 0; font-size: 14px;'><strong>Senha:</strong> {$senha}</p>
                            </div>
                            <p style='color: #dc2626; font-size: 13px;'><strong>Importante:</strong> Recomendamos que altere sua senha após o primeiro acesso.</p>
                            <div style='text-align: center; margin: 24px 0;'>
                                <a href='{$loginUrl}' style='background: #2563eb; color: white; padding: 12px 32px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px;'>Acessar o Sistema</a>
                            </div>
                            <p style='color: #9ca3af; font-size: 12px; margin-top: 24px;'>Este é um email automático do sistema InfoVISA. Não responda.</p>
                        </div>
                    </div>
                ");
        });
    }
}
