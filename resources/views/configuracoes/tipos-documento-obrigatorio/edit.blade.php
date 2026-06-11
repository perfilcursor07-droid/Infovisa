@extends('layouts.admin')

@section('title', 'Editar Tipo de Documento Obrigatório')
@section('page-title', 'Editar Tipo de Documento Obrigatório')

@section('content')
<div class="max-w-8xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Tipos de Documento
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.configuracoes.tipos-documento-obrigatorio.update', $tipo) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="nome" id="nome" value="{{ old('nome', $tipo->nome) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror">
                    @error('nome')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descricao') border-red-500 @enderror">{{ old('descricao', $tipo->descricao) }}</textarea>
                    @error('descricao')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ordem" class="block text-sm font-medium text-gray-700 mb-1">Ordem de Exibição</label>
                    <input type="number" name="ordem" id="ordem" value="{{ old('ordem', $tipo->ordem) }}" min="0"
                           class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Menor número aparece primeiro</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="ativo" id="ativo" value="1" {{ old('ativo', $tipo->ativo) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="ativo" class="text-sm text-gray-700">Ativo</label>
                </div>

                {{-- Seção: Documento Comum --}}
                <div class="border-t border-gray-200 pt-5">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configurações de Documento Comum</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="documento_comum" id="documento_comum" value="1" {{ old('documento_comum', $tipo->documento_comum) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5"
                                   onchange="document.getElementById('tipo_processo_container').style.display = this.checked ? 'block' : 'none'">
                            <div>
                                <label for="documento_comum" class="text-sm font-medium text-gray-700">Documento Comum a Todos os Serviços</label>
                                <p class="text-xs text-gray-500 mt-1">Se marcado, este documento será obrigatório para TODOS os serviços do tipo de processo selecionado.</p>
                            </div>
                        </div>

                        <div id="tipo_processo_container" style="display: {{ old('documento_comum', $tipo->documento_comum) ? 'block' : 'none' }}">
                            <label for="tipo_processo_id" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Processo *</label>
                            <select name="tipo_processo_id" id="tipo_processo_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione o tipo de processo...</option>
                                @foreach(\App\Models\TipoProcesso::where('ativo', true)->orderBy('nome')->get() as $tipoProcesso)
                                <option value="{{ $tipoProcesso->id }}" {{ old('tipo_processo_id', $tipo->tipo_processo_id) == $tipoProcesso->id ? 'selected' : '' }}>{{ $tipoProcesso->nome }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Selecione para qual tipo de processo este documento comum se aplica</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="escopo_competencia" class="block text-sm font-medium text-gray-700 mb-1">Escopo de Competência</label>
                                <select name="escopo_competencia" id="escopo_competencia"
                                        onchange="document.getElementById('municipio_container').style.display = this.value === 'municipal' ? 'block' : 'none'"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="todos" {{ old('escopo_competencia', $tipo->escopo_competencia) === 'todos' ? 'selected' : '' }}>Todos (Estadual + Municipal)</option>
                                    <option value="estadual" {{ old('escopo_competencia', $tipo->escopo_competencia) === 'estadual' ? 'selected' : '' }}>Apenas Estadual</option>
                                    <option value="municipal" {{ old('escopo_competencia', $tipo->escopo_competencia) === 'municipal' ? 'selected' : '' }}>Apenas Municipal</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Define se aplica a processos estaduais, municipais ou ambos</p>
                            </div>

                            <div>
                                <label for="tipo_setor" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Setor</label>
                                <select name="tipo_setor" id="tipo_setor"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="todos" {{ old('tipo_setor', $tipo->tipo_setor) === 'todos' ? 'selected' : '' }}>Todos (Público + Privado)</option>
                                    <option value="publico" {{ old('tipo_setor', $tipo->tipo_setor) === 'publico' ? 'selected' : '' }}>Apenas Público</option>
                                    <option value="privado" {{ old('tipo_setor', $tipo->tipo_setor) === 'privado' ? 'selected' : '' }}>Apenas Privado</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Define se aplica a estabelecimentos públicos, privados ou ambos</p>
                            </div>
                        </div>

                        <div id="municipio_container" style="display: {{ old('escopo_competencia', $tipo->escopo_competencia) === 'municipal' ? 'block' : 'none' }}" class="mt-4">
                            <label for="municipio_id" class="block text-sm font-medium text-gray-700 mb-1">Município *</label>
                            <select name="municipio_id" id="municipio_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione...</option>
                                @foreach(\App\Models\Municipio::orderBy('nome')->get() as $mun)
                                <option value="{{ $mun->id }}" {{ old('municipio_id', $tipo->municipio_id) == $mun->id ? 'selected' : '' }}>{{ $mun->nome }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Selecione o município ao qual este documento se aplica</p>
                        </div>

                        <div>
                            <label for="prazo_validade_dias" class="block text-sm font-medium text-gray-700 mb-1">Prazo de Validade (dias)</label>
                            <input type="number" name="prazo_validade_dias" id="prazo_validade_dias" value="{{ old('prazo_validade_dias', $tipo->prazo_validade_dias) }}" min="1"
                                   placeholder="Ex: 30 para CNPJ"
                                   class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Opcional. Ex: CNPJ com data de impressão de até 30 dias</p>
                        </div>

                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg" x-data="{ carimboModo: '{{ old('carimbo_modo', $tipo->carimbo_modo ?? 'desativado') }}' }">
                            <label class="text-sm font-medium text-gray-700">Carimbo de validação (QR Code)</label>
                            <p class="text-xs text-gray-500 mt-1 mb-3">
                                Gera uma versão carimbada do PDF com a faixa "Arquivo verificado por: [usuário] em [data/hora]"
                                e um QR Code de validação pública em todas as páginas. Indicado para pranchas de projetos
                                arquitetônicos e documentos que precisam de comprovação quando impressos.
                            </p>
                            @php $carimboModoAtual = old('carimbo_modo', $tipo->carimbo_modo ?? 'desativado'); @endphp
                            <div class="space-y-2">
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="radio" name="carimbo_modo" value="desativado" x-model="carimboModo" {{ $carimboModoAtual === 'desativado' ? 'checked' : '' }}
                                           class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 mt-0.5">
                                    <span class="text-sm text-gray-700"><strong>Desativado</strong> — não carimba este tipo de documento.</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="radio" name="carimbo_modo" value="automatico" x-model="carimboModo" {{ $carimboModoAtual === 'automatico' ? 'checked' : '' }}
                                           class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 mt-0.5">
                                    <span class="text-sm text-gray-700"><strong>Automático</strong> — carimba automaticamente ao aprovar o documento.</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <input type="radio" name="carimbo_modo" value="manual" x-model="carimboModo" {{ $carimboModoAtual === 'manual' ? 'checked' : '' }}
                                           class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500 mt-0.5">
                                    <span class="text-sm text-gray-700"><strong>Manual</strong> — o técnico carimba pelo botão na lista do documento (somente após aprovado).</span>
                                </label>
                            </div>

                            {{-- Texto customizável (apenas no modo manual) --}}
                            <div x-show="carimboModo === 'manual'" x-cloak class="mt-4 pt-4 border-t border-green-200">
                                <label for="carimbo_texto" class="block text-sm font-medium text-gray-700 mb-1">Texto do carimbo (opcional)</label>
                                <textarea name="carimbo_texto" id="carimbo_texto" rows="3"
                                          placeholder="Arquivo verificado por: {usuario} em {data}&#10;Documento aprovado pela Vigilância Sanitária - Processo {processo}"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm font-mono">{{ old('carimbo_texto', $tipo->carimbo_texto) }}</textarea>
                                <div class="text-xs text-gray-500 mt-1 space-y-1">
                                    <p>Cada linha vira uma linha no carimbo (máximo 2 linhas). Se deixar vazio, usa o texto padrão.</p>
                                    <p>Variáveis disponíveis:
                                        <code class="px-1 bg-gray-100 rounded">{usuario}</code>
                                        <code class="px-1 bg-gray-100 rounded">{data}</code>
                                        <code class="px-1 bg-gray-100 rounded">{processo}</code>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="observacao_publica" class="block text-sm font-medium text-gray-700 mb-1">Observação para Estabelecimentos Públicos</label>
                            <textarea name="observacao_publica" id="observacao_publica" rows="2"
                                      placeholder="Ex: Isento para estabelecimentos públicos"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('observacao_publica', $tipo->observacao_publica) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Observação específica mostrada para estabelecimentos públicos</p>
                        </div>

                        <div>
                            <label for="observacao_privada" class="block text-sm font-medium text-gray-700 mb-1">Observação para Estabelecimentos Privados</label>
                            <textarea name="observacao_privada" id="observacao_privada" rows="2"
                                      placeholder="Ex: Apenas para empresas privadas"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('observacao_privada', $tipo->observacao_privada) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Observação específica mostrada para estabelecimentos privados</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Seção IA — somente Admin --}}
            @if(auth('interno')->user()->isAdmin())
            <div class="bg-white rounded-xl shadow-sm border border-purple-100 overflow-hidden mt-6">
                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-purple-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.346.346a51.8 51.8 0 00-1.228 1.2 1 1 0 01-1.415 0 51.8 51.8 0 00-1.228-1.2l-.346-.346z"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-purple-800">Análise por Inteligência Artificial</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label for="criterio_ia" class="block text-sm font-medium text-gray-700 mb-1">
                            Critérios de Análise para IA
                        </label>
                        <textarea name="criterio_ia" id="criterio_ia" rows="5"
                                  placeholder="Descreva o que a IA deve verificar neste documento. Exemplo:&#10;1. O CNPJ do documento deve ser idêntico ao cadastrado no sistema&#10;2. A razão social deve corresponder ao cadastro&#10;3. O documento deve estar registrado em Junta Comercial (NIRE presente)&#10;4. A data de registro não deve ser superior a 5 anos"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">{{ old('criterio_ia', $tipo->criterio_ia) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            A IA receberá os dados do estabelecimento cadastrado e analisará o documento enviado com base nesses critérios.
                            Se vazio, o botão "Analisar com IA" não será exibido para este tipo de documento.
                        </p>
                    </div>
                    <div>
                        <label for="ia_modelo_visao" class="block text-sm font-medium text-gray-700 mb-1">
                            Modelo de Visão (para PDFs scaneados)
                        </label>
                        <input type="text" name="ia_modelo_visao" id="ia_modelo_visao"
                               value="{{ old('ia_modelo_visao', $tipo->ia_modelo_visao) }}"
                               placeholder="Padrão: Qwen/Qwen3-VL-8B-Instruct"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                        <p class="text-xs text-gray-500 mt-1">
                            Usado quando o PDF não possui texto extraível (ex: foto scaneada). Deixe em branco para usar o padrão configurado.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
