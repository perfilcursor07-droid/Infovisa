<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracaoSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ConfiguracaoSistemaController extends Controller
{
    /**
     * Exibe a página de configurações gerais do sistema
     */
    public function index()
    {
        $logomarcaEstadual = ConfiguracaoSistema::where('chave', 'logomarca_estadual')->first();
        if ($logomarcaEstadual && $logomarcaEstadual->valor) {
            $logomarcaEstadual->valor = ConfiguracaoSistema::normalizarCaminhoArquivoPublico($logomarcaEstadual->valor);
            $relativo = $this->caminhoRelativoDiscoPublico($logomarcaEstadual->valor);
            if ($relativo && !Storage::disk('public')->exists($relativo)) {
                $logomarcaEstadual->valor = null;
            }
            $this->garantirArquivoPublicoAcessivel($logomarcaEstadual->valor);
        }

        $rodapeEstadual = ConfiguracaoSistema::where('chave', 'rodape_estadual')->first();
        if ($rodapeEstadual && $rodapeEstadual->valor) {
            $rodapeEstadual->valor = ConfiguracaoSistema::normalizarCaminhoArquivoPublico($rodapeEstadual->valor);
            $relativo = $this->caminhoRelativoDiscoPublico($rodapeEstadual->valor);
            if ($relativo && !Storage::disk('public')->exists($relativo)) {
                $rodapeEstadual->valor = null;
            }
            $this->garantirArquivoPublicoAcessivel($rodapeEstadual->valor);
        }

        $rodapeTextoPadrao = ConfiguracaoSistema::rodapeTextoPadrao();
        
        // Configurações da IA
        $iaAtiva = ConfiguracaoSistema::where('chave', 'ia_ativa')->first();
        $iaExternaAtiva = ConfiguracaoSistema::where('chave', 'ia_externa_ativa')->first();
        $iaApiKey = ConfiguracaoSistema::where('chave', 'ia_api_key')->first();
        $iaApiUrl = ConfiguracaoSistema::where('chave', 'ia_api_url')->first();
        $iaModel = ConfiguracaoSistema::where('chave', 'ia_model')->first();
        $iaBuscaWeb = ConfiguracaoSistema::where('chave', 'ia_busca_web')->first();
        
        // Configurações do Chat Interno
        $chatInternoAtivo = ConfiguracaoSistema::where('chave', 'chat_interno_ativo')->first();
        
        // Configuração do Assistente de Redação
        $assistenteRedacaoAtivo = ConfiguracaoSistema::where('chave', 'assistente_redacao_ativo')->first();
        
        // Configuração do Assistente de Pesquisa de Satisfação
        $iaPesquisaSatisfacaoAtiva = ConfiguracaoSistema::where('chave', 'ia_pesquisa_satisfacao_ativa')->first();
        $iaPesquisaSatisfacaoPrompt = ConfiguracaoSistema::where('chave', 'ia_pesquisa_satisfacao_prompt')->first();
        
        return view('admin.configuracoes.sistema.index', compact(
            'logomarcaEstadual',
            'rodapeEstadual',
            'rodapeTextoPadrao',
            'iaAtiva',
            'iaExternaAtiva',
            'iaApiKey',
            'iaApiUrl',
            'iaModel',
            'iaBuscaWeb',
            'chatInternoAtivo',
            'assistenteRedacaoAtivo',
            'iaPesquisaSatisfacaoAtiva',
            'iaPesquisaSatisfacaoPrompt'
        ));
    }

    /**
     * Atualiza as configurações do sistema
     */
    public function update(Request $request)
    {
        $request->validate([
            'logomarca_estadual' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'remover_logomarca_estadual' => 'nullable|boolean',
            'rodape_estadual' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'remover_rodape_estadual' => 'nullable|boolean',
            'rodape_texto_padrao' => 'nullable|string|max:4000',
            'ia_ativa' => 'nullable|boolean',
            'ia_externa_ativa' => 'nullable|boolean',
            'ia_api_key' => 'nullable|string',
            'ia_api_url' => 'nullable|url',
            'ia_model' => 'nullable|string',
            'chat_interno_ativo' => 'nullable|boolean',
            'assistente_redacao_ativo' => 'nullable|boolean',
            'ia_pesquisa_satisfacao_ativa' => 'nullable|boolean',
            'ia_pesquisa_satisfacao_prompt' => 'nullable|string|max:5000',
        ], [
            'logomarca_estadual.image' => 'O arquivo deve ser uma imagem',
            'logomarca_estadual.mimes' => 'A logomarca deve ser um arquivo: jpeg, png, jpg ou svg',
            'logomarca_estadual.max' => 'A logomarca não pode ser maior que 2MB',
            'rodape_estadual.image' => 'O arquivo deve ser uma imagem',
            'rodape_estadual.mimes' => 'O rodapé deve ser um arquivo: jpeg, png, jpg ou svg',
            'rodape_estadual.max' => 'O rodapé não pode ser maior que 4MB',
            'rodape_texto_padrao.max' => 'O texto padrão do rodapé não pode ter mais que 4000 caracteres',
            'ia_api_url.url' => 'A URL da API deve ser válida',
        ]);
        
        // Identifica qual formulário foi submetido baseado nos campos presentes
        $isFormularioIA = $request->has('_form_ia') || 
                          $request->filled('ia_api_key') || 
                          $request->filled('ia_api_url') || 
                          $request->filled('ia_model');
        
        $isFormularioChat = $request->has('_form_chat');
        
        $isFormularioLogomarca = $request->hasFile('logomarca_estadual') || 
                                  $request->has('remover_logomarca_estadual');
        
        // Atualiza todas as configurações de IA (formulário unificado)
        if ($isFormularioIA) {
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_ativa'],
                ['valor' => $request->has('ia_ativa') ? 'true' : 'false']
            );

            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_externa_ativa'],
                ['valor' => $request->has('ia_externa_ativa') ? 'true' : 'false']
            );
            
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_api_key'],
                ['valor' => $request->ia_api_key ?? '']
            );
            
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_api_url'],
                ['valor' => $request->ia_api_url ?? '']
            );
            
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_model'],
                ['valor' => $request->ia_model ?? '']
            );
            
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_busca_web'],
                ['valor' => $request->has('ia_busca_web') ? 'true' : 'false']
            );
            
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'assistente_redacao_ativo'],
                ['valor' => $request->has('assistente_redacao_ativo') ? 'true' : 'false']
            );
            
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_pesquisa_satisfacao_ativa'],
                ['valor' => $request->has('ia_pesquisa_satisfacao_ativa') ? 'true' : 'false']
            );
            
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_pesquisa_satisfacao_prompt'],
                ['valor' => $request->input('ia_pesquisa_satisfacao_prompt', '')]
            );
            
            return redirect()
                ->to(route('admin.configuracoes.sistema.index') . '#inteligencia-artificial')
                ->with('success', 'Configurações de Inteligência Artificial atualizadas com sucesso!');
        }
        
        // Atualiza configurações do Chat Interno apenas se for o formulário de Chat
        if ($isFormularioChat) {
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'chat_interno_ativo'],
                ['valor' => $request->has('chat_interno_ativo') ? 'true' : 'false']
            );
            
            return redirect()
                ->to(route('admin.configuracoes.sistema.index') . '#comunicacao')
                ->with('success', 'Configurações do Chat Interno atualizadas com sucesso!');
        }
        
        // Verifica se foi apenas atualização de IA (sem logomarca) - fallback para compatibilidade
        $atualizouIA = $request->has('ia_ativa') || 
                       $request->has('ia_externa_ativa') || 
                       $request->filled('ia_api_key') || 
                       $request->filled('ia_api_url') || 
                       $request->filled('ia_model') ||
                       $request->has('ia_busca_web') ||
                       $request->has('chat_interno_ativo') ||
                       $request->has('assistente_redacao_ativo');

        $configLogomarca = ConfiguracaoSistema::where('chave', 'logomarca_estadual')->first();
        $configRodape = ConfiguracaoSistema::where('chave', 'rodape_estadual')->first();
        $alteracoesIdentidadeVisual = [];

        if ($request->hasFile('logomarca_estadual')) {
            if ($configLogomarca && $configLogomarca->valor) {
                $this->removerArquivoConfiguracao($configLogomarca->valor);
            }

            $arquivo = $request->file('logomarca_estadual');
            $nomeArquivo = 'logomarca_estado_tocantins_' . time() . '.' . $arquivo->getClientOriginalExtension();
            $caminho = $arquivo->storeAs('sistema/logomarcas', $nomeArquivo, 'public');

            if (!$caminho) {
                return redirect()
                    ->route('admin.configuracoes.sistema.index')
                    ->with('error', 'Erro ao salvar o arquivo. Verifique as permissões.');
            }

            $caminhoPublico = 'storage/' . $caminho;
            $this->garantirArquivoPublicoAcessivel($caminhoPublico);

            ConfiguracaoSistema::definir(
                'logomarca_estadual',
                $caminhoPublico,
                'imagem',
                'Logomarca do Estado do Tocantins (usada em documentos de usuários estaduais)'
            );

            $alteracoesIdentidadeVisual[] = 'logomarca estadual atualizada';
        } elseif ($request->boolean('remover_logomarca_estadual') && $configLogomarca && $configLogomarca->valor) {
            $this->removerArquivoConfiguracao($configLogomarca->valor);
            $configLogomarca->update(['valor' => null]);
            $alteracoesIdentidadeVisual[] = 'logomarca estadual removida';
        }

        if ($request->hasFile('rodape_estadual')) {
            if ($configRodape && $configRodape->valor) {
                $this->removerArquivoConfiguracao($configRodape->valor);
            }

            $arquivo = $request->file('rodape_estadual');
            $nomeArquivo = 'rodape_estado_tocantins_' . time() . '.' . $arquivo->getClientOriginalExtension();
            $caminho = $arquivo->storeAs('sistema/rodapes', $nomeArquivo, 'public');

            if (!$caminho) {
                return redirect()
                    ->route('admin.configuracoes.sistema.index')
                    ->with('error', 'Erro ao salvar o arquivo. Verifique as permissões.');
            }

            $caminhoPublico = 'storage/' . $caminho;
            $this->garantirArquivoPublicoAcessivel($caminhoPublico);

            ConfiguracaoSistema::definir(
                'rodape_estadual',
                $caminhoPublico,
                'imagem',
                'Imagem de rodapé padrão do Estado do Tocantins para documentos e PDFs'
            );

            $alteracoesIdentidadeVisual[] = 'rodapé estadual atualizado';
        } elseif ($request->boolean('remover_rodape_estadual') && $configRodape && $configRodape->valor) {
            $this->removerArquivoConfiguracao($configRodape->valor);
            $configRodape->update(['valor' => null]);
            $alteracoesIdentidadeVisual[] = 'rodapé estadual removido';
        }

        if ($request->has('rodape_texto_padrao')) {
            $textoRodapePadrao = trim((string) $request->input('rodape_texto_padrao'));
            $textoRodapePadrao = $textoRodapePadrao !== ''
                ? $textoRodapePadrao
                : ConfiguracaoSistema::RODAPE_TEXTO_PADRAO;

            ConfiguracaoSistema::definir(
                'rodape_texto_padrao',
                $textoRodapePadrao,
                'texto',
                'Texto padrão do rodapé usado pelo estado e como fallback dos municípios'
            );

            $alteracoesIdentidadeVisual[] = 'texto padrão do rodapé atualizado';
        }

        if (!empty($alteracoesIdentidadeVisual)) {
            return redirect()
                ->route('admin.configuracoes.sistema.index')
                ->with('success', 'Identidade visual estadual atualizada com sucesso: ' . implode(', ', $alteracoesIdentidadeVisual) . '.');
        }
        
        // Se atualizou apenas IA, retorna com sucesso
        if ($atualizouIA) {
            return redirect()
                ->route('admin.configuracoes.sistema.index')
                ->with('success', 'Configurações do Assistente de IA atualizadas com sucesso!');
        }

        return redirect()
            ->route('admin.configuracoes.sistema.index')
            ->with('info', 'Nenhuma alteração foi realizada.');
    }

    private function caminhoRelativoDiscoPublico(?string $valor): ?string
    {
        $normalizado = ConfiguracaoSistema::normalizarCaminhoArquivoPublico($valor);
        if (!$normalizado) {
            return null;
        }

        if (str_starts_with($normalizado, 'storage/')) {
            return substr($normalizado, strlen('storage/'));
        }

        return ltrim($normalizado, '/');
    }

    private function garantirArquivoPublicoAcessivel(?string $valor): void
    {
        try {
            $relativo = $this->caminhoRelativoDiscoPublico($valor);
            if (!$relativo) {
                return;
            }

            if (!Storage::disk('public')->exists($relativo)) {
                return;
            }

            $publicStorage = public_path('storage');

            // Se public/storage for diretório real (não link), cria espelho do arquivo para evitar 404.
            if (is_dir($publicStorage) && !is_link($publicStorage)) {
                $destino = public_path('storage/' . $relativo);
                $destinoDir = dirname($destino);

                if (!is_dir($destinoDir)) {
                    File::makeDirectory($destinoDir, 0755, true);
                }

                File::copy(Storage::disk('public')->path($relativo), $destino);
            }
        } catch (\Throwable $e) {
            Log::warning('Não foi possível espelhar arquivo de configuração em public/storage', [
                'valor' => $valor,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    private function removerArquivoConfiguracao(?string $valor): void
    {
        try {
            $relativo = $this->caminhoRelativoDiscoPublico($valor);
            if (!$relativo) {
                return;
            }

            Storage::disk('public')->delete($relativo);

            $espelhoPublico = public_path('storage/' . $relativo);
            if (File::exists($espelhoPublico)) {
                File::delete($espelhoPublico);
            }
        } catch (\Throwable $e) {
            Log::warning('Não foi possível remover arquivo de configuração', [
                'valor' => $valor,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Salva as permissões de quais níveis podem marcar documentos como sigiloso
     */
    public function salvarPermissoesSigiloso(Request $request)
    {
        $niveis = $request->input('niveis', []);

        ConfiguracaoSistema::definir(
            'niveis_permitidos_sigiloso',
            json_encode($niveis),
            'json',
            'Níveis de acesso que podem marcar documentos como sigiloso'
        );

        return redirect()
            ->route('admin.configuracoes.sistema.index')
            ->with('success', 'Permissões de documento sigiloso atualizadas com sucesso!');
    }
}
