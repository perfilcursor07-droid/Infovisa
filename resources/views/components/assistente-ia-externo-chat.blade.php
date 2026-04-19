@php
    $iaExternaAtiva = \App\Models\ConfiguracaoSistema::where('chave', 'ia_externa_ativa')->value('valor');
    $desativarNaPagina = isset($desativarAssistenteIAExterno) && $desativarAssistenteIAExterno === true;

    $usuarioExterno = auth('externo')->user();
    $nomeUsuario = $usuarioExterno ? $usuarioExterno->nome : 'Usuário';
    $primeiroNome = explode(' ', $nomeUsuario)[0];

    $documentosAjudaLinks = \App\Models\DocumentoAjuda::ativos()->genericosGlobais()->ordenado()->get(['id', 'titulo'])
        ->map(function ($doc) {
            return [
                'titulo' => $doc->titulo,
                'url' => route('company.documentos-ajuda.visualizar', $doc->id),
            ];
        })
        ->values()
        ->all();
@endphp

@if($iaExternaAtiva === 'true' && !$desativarNaPagina)
<div x-data="assistenteIAExterno()" x-init="init()" class="fixed bottom-5 right-5 z-[9999]" x-cloak>
    <button id="btn-ia-chat"
            @click="toggleChat()"
            x-show="!chatAberto"
            class="group bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-full px-4 py-3 shadow-xl hover:shadow-2xl transition-all duration-300 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <span class="text-sm font-semibold hidden sm:inline">Ajuda com IA</span>
    </button>

    <div x-show="chatAberto"
         x-transition.duration.250ms
         :class="maximizado ? 'fixed inset-4' : 'w-[360px] sm:w-[390px] h-[560px]'"
         class="bg-white border border-gray-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col">

        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white px-4 py-3 flex items-center justify-between">
            <div class="min-w-0">
                <h3 class="font-semibold text-sm truncate">Assistente do Usuário Externo</h3>
                <p class="text-[11px] text-white/85 truncate">Olá, {{ $primeiroNome }}! Posso te orientar passo a passo.</p>
            </div>
            <div class="flex items-center gap-1">
                <button @click="toggleMaximizar()" class="p-1.5 rounded-md hover:bg-white/20 transition" :title="maximizado ? 'Minimizar' : 'Expandir'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3H5a2 2 0 00-2 2v3m16-5h-3m3 0v3M8 21H5a2 2 0 01-2-2v-3m16 5h-3a2 2 0 01-2-2v-3"/>
                    </svg>
                </button>
                <button @click="limparConversa()" x-show="mensagens.length > 0" class="p-1.5 rounded-md hover:bg-white/20 transition" title="Limpar conversa">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
                <button @click="toggleChat()" class="p-1.5 rounded-md hover:bg-white/20 transition" title="Fechar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-3 bg-gray-50 space-y-2" x-ref="messagesContainer">
            <div x-show="mensagens.length === 0" class="space-y-3">
                <div class="bg-white border border-indigo-100 rounded-xl p-3">
                    <p class="text-xs text-gray-700 leading-relaxed">
                        Posso te ajudar a usar o sistema de forma simples. Escolha um tema abaixo ou digite sua dúvida.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-2">
                    <button @click="enviarSugestao('Como cadastrar um novo estabelecimento?')" class="text-left px-3 py-2 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 transition text-xs text-gray-700">
                        🏢 Como cadastrar um novo estabelecimento?
                    </button>
                    <button @click="enviarSugestao('Como abrir um processo no sistema?')" class="text-left px-3 py-2 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 transition text-xs text-gray-700">
                        📂 Como abrir um processo no sistema?
                    </button>
                    <button @click="enviarSugestao('Como enviar documentos no processo?')" class="text-left px-3 py-2 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 transition text-xs text-gray-700">
                        📎 Como enviar documentos no processo?
                    </button>
                    <button @click="enviarSugestao('Como acompanhar minhas pendências e prazos?')" class="text-left px-3 py-2 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 transition text-xs text-gray-700">
                        ⏰ Como acompanhar pendências e prazos?
                    </button>
                </div>
            </div>

            <template x-for="(msg, index) in mensagens" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user'
                        ? 'max-w-[85%] bg-indigo-600 text-white rounded-2xl rounded-tr-none px-3 py-2'
                        : 'max-w-[90%] bg-white border border-gray-200 text-gray-800 rounded-2xl rounded-tl-none px-3.5 py-2.5 shadow-sm'">
                        <div x-show="msg.role === 'user'" class="text-xs leading-relaxed whitespace-pre-wrap" x-text="msg.content"></div>
                        <div x-show="msg.role !== 'user'"
                             class="text-[11px] leading-relaxed text-gray-800 [&_p]:mb-1 [&_ol]:my-1.5 [&_ol]:ml-4 [&_ol]:list-decimal [&_ul]:my-1.5 [&_ul]:ml-4 [&_ul]:list-disc [&_li]:mb-1 [&_blockquote]:my-1.5 [&_blockquote]:border-l-2 [&_blockquote]:border-indigo-200 [&_blockquote]:pl-2 [&_blockquote]:text-gray-700"
                             x-html="formatarMensagemAssistente(msg.content)"></div>
                        <p class="text-[10px] mt-1 opacity-60" x-text="msg.time"></p>
                    </div>
                </div>
            </template>

            <div x-show="carregando" class="flex justify-start">
                <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-none px-3 py-2.5 shadow-sm">
                    <div class="flex gap-1.5">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce"></span>
                        <span class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 bg-white p-3">
            <form @submit.prevent="enviarMensagem()" class="flex gap-2">
                <input type="text"
                       x-model="mensagemAtual"
                       maxlength="2000"
                       :disabled="carregando"
                       placeholder="Digite sua dúvida..."
                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-100">
                <button type="submit"
                        :disabled="!mensagemAtual.trim() || carregando"
                        class="px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function assistenteIAExterno() {
    const estadoChat = localStorage.getItem('ia_chat_externo_aberto');
    const estadoMaximizado = localStorage.getItem('ia_chat_externo_maximizado');
    const historico = localStorage.getItem('ia_chat_externo_history');

    let mensagensIniciais = [];
    if (historico) {
        try {
            mensagensIniciais = JSON.parse(historico);
        } catch (e) {
            mensagensIniciais = [];
        }
    }

    return {
        documentosAjudaLinks: @json($documentosAjudaLinks),
        chatAberto: estadoChat === 'true',
        maximizado: estadoMaximizado === 'true',
        mensagens: mensagensIniciais,
        mensagemAtual: '',
        carregando: false,

        init() {
            this.$nextTick(() => {
                if (this.chatAberto && this.mensagens.length > 0) {
                    this.scrollToBottomInstant();
                }
            });
        },

        toggleChat() {
            this.chatAberto = !this.chatAberto;
            localStorage.setItem('ia_chat_externo_aberto', this.chatAberto ? 'true' : 'false');
            if (this.chatAberto) {
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        toggleMaximizar() {
            this.maximizado = !this.maximizado;
            localStorage.setItem('ia_chat_externo_maximizado', this.maximizado ? 'true' : 'false');
            this.$nextTick(() => this.scrollToBottom());
        },

        enviarSugestao(texto) {
            this.mensagemAtual = texto;
            this.enviarMensagem();
        },

        async enviarMensagem() {
            if (!this.mensagemAtual.trim() || this.carregando) return;

            const mensagem = this.mensagemAtual.trim();
            this.mensagemAtual = '';

            this.mensagens.push({
                role: 'user',
                content: mensagem,
                time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
            });

            this.salvarHistorico();
            this.scrollToBottom();
            this.carregando = true;

            try {
                const history = this.mensagens.slice(-10).map(msg => ({
                    role: msg.role,
                    content: msg.content
                }));

                const response = await fetch('{{ route('company.ia.chat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        message: mensagem,
                        history: history.slice(0, -1)
                    })
                });

                const data = await response.json();

                this.mensagens.push({
                    role: 'assistant',
                    content: data.response || 'Não consegui responder agora. Tente novamente em instantes.',
                    time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                });
            } catch (e) {
                this.mensagens.push({
                    role: 'assistant',
                    content: 'Estou com instabilidade agora. Tente novamente em alguns segundos.',
                    time: new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
                });
            } finally {
                this.carregando = false;
                this.salvarHistorico();
                this.scrollToBottom();
            }
        },

        salvarHistorico() {
            localStorage.setItem('ia_chat_externo_history', JSON.stringify(this.mensagens.slice(-50)));
        },

        limparConversa() {
            if (confirm('Deseja limpar a conversa?')) {
                this.mensagens = [];
                localStorage.removeItem('ia_chat_externo_history');
            }
        },

        escaparHtml(texto) {
            return String(texto ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        formatarInline(texto) {
            let html = this.escaparHtml(texto);
            html = html.replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold">$1</strong>');
            html = html.replace(/`([^`]+?)`/g, '<code class="px-1 py-0.5 rounded bg-gray-100 text-[10px] font-mono">$1</code>');
            return html;
        },

        normalizarTituloFonte(texto) {
            return String(texto ?? '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/\s+/g, ' ')
                .trim();
        },

        obterLinkDocumentoPorTitulo(tituloFonte) {
            const alvo = this.normalizarTituloFonte(tituloFonte);
            const encontrado = this.documentosAjudaLinks.find(doc =>
                this.normalizarTituloFonte(doc.titulo) === alvo
            );
            return encontrado ? encontrado.url : null;
        },

        renderizarLinhaFonte(textoLinha) {
            const match = textoLinha.match(/^Fonte\(s\):\s*(.+)$/i);
            if (!match) {
                return `<p>${this.formatarInline(textoLinha)}</p>`;
            }

            const conteudoFonte = match[1].trim();
            const itensEntreColchetes = [...conteudoFonte.matchAll(/\[([^\]]+)\]/g)].map(m => m[1].trim());
            let titulos = itensEntreColchetes;

            if (titulos.length === 0) {
                titulos = conteudoFonte
                    .split(',')
                    .map(item => item.trim())
                    .filter(Boolean);
            }

            if (titulos.length === 0) {
                return `<p><strong class="font-semibold">Fonte(s):</strong> ${this.formatarInline(conteudoFonte)}</p>`;
            }

            const links = titulos.map((titulo) => {
                const url = this.obterLinkDocumentoPorTitulo(titulo);
                const tituloEscapado = this.escaparHtml(titulo);
                if (!url) {
                    return `[${tituloEscapado}]`;
                }

                return `<a href="${this.escaparHtml(url)}" target="_blank" rel="noopener noreferrer" class="text-indigo-700 hover:text-indigo-900 underline font-medium">[${tituloEscapado}]</a>`;
            });

            return `<p><strong class="font-semibold">Fonte(s):</strong> ${links.join(' ')}</p>`;
        },

        formatarMensagemAssistente(texto) {
            if (!texto) return '';

            let conteudo = String(texto)
                .replace(/<think>[\s\S]*?<\/think>/gi, '')
                .replace(/\r\n/g, '\n')
                .trim();

            if (!conteudo) return '';

            const linhas = conteudo.split('\n');
            let html = '';
            let emListaOrdenada = false;
            let emListaNaoOrdenada = false;
            let emCitacao = false;

            const fecharEstruturas = () => {
                if (emListaOrdenada) {
                    html += '</ol>';
                    emListaOrdenada = false;
                }
                if (emListaNaoOrdenada) {
                    html += '</ul>';
                    emListaNaoOrdenada = false;
                }
                if (emCitacao) {
                    html += '</blockquote>';
                    emCitacao = false;
                }
            };

            for (const linha of linhas) {
                const textoLinha = linha.trim();

                if (!textoLinha) {
                    fecharEstruturas();
                    continue;
                }

                const itemOrdenado = textoLinha.match(/^\d+\.\s+(.+)$/);
                if (itemOrdenado) {
                    if (emListaNaoOrdenada) {
                        html += '</ul>';
                        emListaNaoOrdenada = false;
                    }
                    if (emCitacao) {
                        html += '</blockquote>';
                        emCitacao = false;
                    }
                    if (!emListaOrdenada) {
                        html += '<ol>';
                        emListaOrdenada = true;
                    }
                    html += `<li>${this.formatarInline(itemOrdenado[1])}</li>`;
                    continue;
                }

                const itemNaoOrdenado = textoLinha.match(/^[\-•]\s+(.+)$/);
                if (itemNaoOrdenado) {
                    if (emListaOrdenada) {
                        html += '</ol>';
                        emListaOrdenada = false;
                    }
                    if (emCitacao) {
                        html += '</blockquote>';
                        emCitacao = false;
                    }
                    if (!emListaNaoOrdenada) {
                        html += '<ul>';
                        emListaNaoOrdenada = true;
                    }
                    html += `<li>${this.formatarInline(itemNaoOrdenado[1])}</li>`;
                    continue;
                }

                const citacao = textoLinha.match(/^>\s?(.+)$/);
                if (citacao) {
                    if (emListaOrdenada) {
                        html += '</ol>';
                        emListaOrdenada = false;
                    }
                    if (emListaNaoOrdenada) {
                        html += '</ul>';
                        emListaNaoOrdenada = false;
                    }
                    if (!emCitacao) {
                        html += '<blockquote>';
                        emCitacao = true;
                    }
                    html += `<p>${this.formatarInline(citacao[1])}</p>`;
                    continue;
                }

                fecharEstruturas();
                html += this.renderizarLinhaFonte(textoLinha);
            }

            fecharEstruturas();
            return html;
        },

        scrollToBottom() {
            this.$nextTick(() => {
                setTimeout(() => {
                    const container = this.$refs.messagesContainer;
                    if (container) {
                        container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
                    }
                }, 50);
            });
        },

        scrollToBottomInstant() {
            const container = this.$refs.messagesContainer;
            if (container) container.scrollTop = container.scrollHeight;
        }
    }
}
</script>
@endif
