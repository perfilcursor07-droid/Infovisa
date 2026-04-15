# Requirements Document

## Introduction

Redesign da dashboard administrativa do sistema InfoVISA — sistema de vigilância sanitária do estado do Tocantins. A dashboard atual (`/admin/dashboard`) é considerada poluída e confusa pelos usuários internos (gestores e técnicos). O objetivo é reorganizar a interface para que cada perfil de usuário encontre rapidamente as ações que precisa tomar, com separação clara entre demandas pessoais, demandas do setor e informações de acompanhamento.

O sistema é construído em Laravel + Blade + Alpine.js + Tailwind CSS. A dashboard atende 5 perfis de usuário com diferentes níveis de visibilidade.

## Glossary

- **Dashboard**: Tela principal do painel administrativo (`/admin/dashboard`) que exibe um resumo das demandas e ações pendentes do usuário logado.
- **InfoVISA**: Sistema de vigilância sanitária do estado do Tocantins.
- **Usuário_Interno**: Servidor público (gestor ou técnico) que acessa o painel administrativo do InfoVISA.
- **Administrador**: Perfil com acesso completo ao sistema, incluindo gestão de usuários e visibilidade total.
- **Gestor_Estadual**: Perfil de gestão de processos e estabelecimentos em nível estadual, com visibilidade sobre o setor.
- **Gestor_Municipal**: Perfil de gestão de processos e estabelecimentos em nível municipal, com visibilidade sobre o setor do seu município.
- **Técnico_Estadual**: Perfil de análise técnica de processos em nível estadual, com visibilidade apenas sobre suas demandas diretas.
- **Técnico_Municipal**: Perfil de análise técnica de processos em nível municipal, com visibilidade apenas sobre suas demandas diretas.
- **Gestor**: Referência genérica a Gestor_Estadual ou Gestor_Municipal.
- **Técnico**: Referência genérica a Técnico_Estadual ou Técnico_Municipal.
- **Setor**: Unidade organizacional (gerência) à qual o Usuário_Interno pertence, identificada por código.
- **Ordem_de_Serviço (OS)**: Tarefa de fiscalização ou inspeção atribuída a técnicos, com prazo de execução e prazo de finalização (15 dias após encerramento).
- **Processo**: Procedimento administrativo de vigilância sanitária vinculado a um estabelecimento (licenciamento, fiscalização, etc.).
- **Documento_Pendente_Aprovação**: Documento enviado por empresa (usuário externo) que aguarda análise e aprovação de um Usuário_Interno.
- **Resposta_Pendente_Aprovação**: Resposta a um documento com prazo, enviada por empresa, que aguarda análise.
- **Documento_Pendente_Assinatura**: Documento digital que aguarda assinatura do Usuário_Interno logado.
- **Documento_Rascunho**: Documento digital em estado de rascunho que tem o Usuário_Interno como assinante.
- **Documento_Com_Prazo**: Documento assinado que possui data de vencimento próxima (até 5 dias) ou já vencida.
- **Processo_Acompanhado**: Processo que o Usuário_Interno optou por acompanhar (favoritar) para monitoramento.
- **Atalho_Rápido**: Link personalizado criado pelo Usuário_Interno para acesso rápido a funcionalidades do sistema.
- **Cadastro_Pendente**: Estabelecimento cadastrado por empresa que aguarda aprovação de um Gestor ou Administrador.
- **Competência**: Escopo de atuação (estadual ou municipal) que determina quais processos e estabelecimentos são visíveis para cada perfil.
- **Barra_de_Resumo**: Faixa horizontal no topo da Dashboard com contadores numéricos das principais pendências do Usuário_Interno.
- **Aba_de_Navegação**: Componente de interface que permite alternar entre diferentes visualizações de conteúdo dentro de uma mesma seção.
- **Card_Colapsável**: Componente de interface que pode ser expandido ou recolhido pelo Usuário_Interno para mostrar ou ocultar detalhes.

## Requirements

### Requirement 1: Barra de Resumo com Contadores de Pendências

**User Story:** Como Usuário_Interno, quero ver um resumo numérico das minhas pendências no topo da Dashboard, para que eu saiba imediatamente quantas ações preciso tomar.

#### Acceptance Criteria

1. WHEN a Dashboard é carregada, THE Barra_de_Resumo SHALL exibir contadores numéricos para: Ordens de Serviço ativas, Documentos pendentes de assinatura, Documentos com prazo vencendo, e Processos sob responsabilidade direta do Usuário_Interno logado.
2. WHILE o Usuário_Interno possui perfil de Gestor ou Administrador, THE Barra_de_Resumo SHALL exibir contadores adicionais para: Documentos pendentes de aprovação do Setor e Cadastros pendentes de aprovação.
3. WHEN o Usuário_Interno clica em um contador da Barra_de_Resumo, THE Dashboard SHALL rolar a página até a seção correspondente àquele contador.
4. WHEN qualquer contador possui valor zero, THE Barra_de_Resumo SHALL exibir o contador com estilo visual atenuado (opacidade reduzida) para indicar ausência de pendências.
5. WHEN qualquer contador possui itens com prazo vencido ou atrasados, THE Barra_de_Resumo SHALL destacar o contador com indicador visual de urgência (cor vermelha).

### Requirement 2: Seção "Minhas Ações" com Abas de Navegação

**User Story:** Como Usuário_Interno, quero que todas as minhas demandas pessoais estejam agrupadas em uma única seção com abas, para que eu encontre rapidamente o que preciso fazer sem percorrer múltiplos cards espalhados.

#### Acceptance Criteria

1. THE Dashboard SHALL exibir uma seção "Minhas Ações" contendo abas de navegação para: "Ordens de Serviço", "Assinaturas", "Prazos" e "Processos".
2. WHEN o Usuário_Interno seleciona a aba "Ordens de Serviço", THE Dashboard SHALL exibir a lista de Ordens_de_Serviço atribuídas ao Usuário_Interno logado, ordenadas por urgência (atrasadas primeiro, depois por data de encerramento mais próxima).
3. WHEN o Usuário_Interno seleciona a aba "Assinaturas", THE Dashboard SHALL exibir os Documentos_Pendentes_Assinatura e os Documentos_Rascunho do Usuário_Interno logado, separados por subseções visuais.
4. WHEN o Usuário_Interno seleciona a aba "Prazos", THE Dashboard SHALL exibir os Documentos_Com_Prazo visíveis para o Usuário_Interno logado, ordenados por data de vencimento (vencidos primeiro).
5. WHEN o Usuário_Interno seleciona a aba "Processos", THE Dashboard SHALL exibir os Processos sob responsabilidade direta do Usuário_Interno logado (responsavel_atual_id), com paginação via AJAX.
6. WHEN uma aba contém itens com urgência (prazo vencido ou atrasado), THE Aba_de_Navegação SHALL exibir um indicador visual (badge vermelho) ao lado do nome da aba com a quantidade de itens urgentes.
7. THE Dashboard SHALL manter a aba selecionada ao recarregar a página utilizando o hash da URL ou armazenamento local do navegador.

### Requirement 3: Seção "Demandas do Setor" para Gestores e Administradores

**User Story:** Como Gestor ou Administrador, quero ver as demandas do meu setor separadas das minhas demandas pessoais, para que eu consiga distinguir claramente o que é minha responsabilidade direta e o que é responsabilidade da equipe.

#### Acceptance Criteria

1. WHILE o Usuário_Interno possui perfil de Gestor ou Administrador, THE Dashboard SHALL exibir uma seção "Demandas do Setor" separada da seção "Minhas Ações".
2. WHILE o Usuário_Interno possui perfil de Técnico, THE Dashboard SHALL ocultar a seção "Demandas do Setor".
3. THE seção "Demandas do Setor" SHALL conter abas para: "Aprovações Pendentes", "Processos do Setor" e "Cadastros Pendentes".
4. WHEN o Usuário_Interno seleciona a aba "Aprovações Pendentes", THE Dashboard SHALL exibir os Documentos_Pendentes_Aprovação e as Respostas_Pendentes_Aprovação do Setor, agrupados por processo.
5. WHEN o Usuário_Interno seleciona a aba "Processos do Setor", THE Dashboard SHALL exibir os Processos atribuídos ao Setor do Usuário_Interno (excluindo os que já aparecem em "Minhas Ações" como responsabilidade direta), com paginação via AJAX.
6. WHEN o Usuário_Interno seleciona a aba "Cadastros Pendentes", THE Dashboard SHALL exibir os estabelecimentos aguardando aprovação, filtrados pela Competência do Usuário_Interno.
7. WHEN um documento pendente de aprovação pertence a um processo de licenciamento e está pendente há mais de 5 dias, THE Dashboard SHALL destacar o item com indicador visual de atraso (cor vermelha e badge com dias de atraso).

### Requirement 4: Seção "Acompanhamento" com Informações Secundárias Colapsáveis

**User Story:** Como Usuário_Interno, quero que informações secundárias (processos acompanhados, atalhos rápidos) fiquem em uma seção separada e colapsável, para que a Dashboard não fique poluída com informações que não exigem ação imediata.

#### Acceptance Criteria

1. THE Dashboard SHALL exibir uma seção "Acompanhamento" contendo Cards_Colapsáveis para: "Processos Acompanhados" e "Atalhos Rápidos".
2. WHEN a Dashboard é carregada, THE seção "Acompanhamento" SHALL exibir os Cards_Colapsáveis no estado recolhido por padrão, mostrando apenas o título e a contagem de itens.
3. WHEN o Usuário_Interno expande o Card_Colapsável "Processos Acompanhados", THE Dashboard SHALL exibir a lista de Processos_Acompanhados com número do processo, nome do estabelecimento e status atual.
4. WHEN o Usuário_Interno expande o Card_Colapsável "Atalhos Rápidos", THE Dashboard SHALL exibir os Atalhos_Rápidos configurados pelo Usuário_Interno, com ícone, título e link.
5. THE Dashboard SHALL preservar o estado (expandido ou recolhido) de cada Card_Colapsável entre sessões utilizando armazenamento local do navegador.

### Requirement 5: Filtragem de Dados por Competência e Perfil

**User Story:** Como Usuário_Interno, quero que a Dashboard exiba apenas os dados relevantes para meu perfil e competência, para que eu não veja informações que não são da minha responsabilidade.

#### Acceptance Criteria

1. WHILE o Usuário_Interno possui perfil de Administrador, THE Dashboard SHALL exibir dados de todos os setores e competências.
2. WHILE o Usuário_Interno possui perfil de Gestor_Estadual, THE Dashboard SHALL exibir na seção "Demandas do Setor" apenas processos de competência estadual pertencentes ao Setor do Usuário_Interno.
3. WHILE o Usuário_Interno possui perfil de Gestor_Municipal, THE Dashboard SHALL exibir na seção "Demandas do Setor" apenas processos de competência municipal do município do Usuário_Interno pertencentes ao Setor do Usuário_Interno.
4. WHILE o Usuário_Interno possui perfil de Técnico_Estadual ou Técnico_Municipal, THE Dashboard SHALL exibir apenas demandas atribuídas diretamente ao Usuário_Interno (responsavel_atual_id ou tecnicos_ids).
5. THE Dashboard SHALL aplicar filtros de Competência de forma consistente em todas as seções (Barra_de_Resumo, Minhas Ações, Demandas do Setor, Acompanhamento).

### Requirement 6: Layout Responsivo com Duas Colunas para Gestores

**User Story:** Como Usuário_Interno, quero que a Dashboard tenha um layout organizado que se adapte ao meu perfil, para que eu consiga visualizar as informações de forma clara em diferentes tamanhos de tela.

#### Acceptance Criteria

1. WHILE o Usuário_Interno possui perfil de Gestor ou Administrador, THE Dashboard SHALL exibir o layout em duas colunas em telas grandes (lg): coluna principal com "Minhas Ações" e coluna lateral com "Demandas do Setor".
2. WHILE o Usuário_Interno possui perfil de Técnico, THE Dashboard SHALL exibir o layout em coluna única com "Minhas Ações" ocupando a largura total.
3. WHEN a Dashboard é acessada em tela pequena (mobile), THE Dashboard SHALL empilhar todas as seções em coluna única, com "Minhas Ações" acima de "Demandas do Setor".
4. THE Dashboard SHALL posicionar a Barra_de_Resumo acima do layout de colunas, ocupando a largura total da tela.
5. THE Dashboard SHALL posicionar a seção "Acompanhamento" abaixo do layout de colunas, ocupando a largura total da tela.

### Requirement 7: Alertas e Notificações Contextuais

**User Story:** Como Usuário_Interno, quero que alertas importantes (OS vencidas, documentos atrasados) sejam exibidos de forma destacada e não misturados com informações regulares, para que eu identifique rapidamente situações que exigem ação urgente.

#### Acceptance Criteria

1. WHILE o Usuário_Interno possui perfil de Gestor ou Administrador e existem Ordens_de_Serviço com mais de 15 dias sem encerramento, THE Dashboard SHALL exibir um banner de alerta no topo (abaixo dos avisos do sistema) com a contagem de OS atrasadas e link para a lista completa.
2. WHEN existem Documentos_Com_Prazo vencidos visíveis para o Usuário_Interno, THE Dashboard SHALL exibir um indicador de alerta na aba "Prazos" da seção "Minhas Ações" com a contagem de documentos vencidos.
3. WHEN existem Documentos_Pendentes_Aprovação atrasados (mais de 5 dias em processos de licenciamento), THE Dashboard SHALL exibir um indicador de alerta na aba "Aprovações Pendentes" da seção "Demandas do Setor".
4. THE Dashboard SHALL exibir avisos do sistema (Aviso model) no topo da página, acima de todas as seções, ordenados por tipo (urgente primeiro) e data de criação.
5. IF nenhum aviso do sistema está ativo, THEN THE Dashboard SHALL ocultar a área de avisos sem deixar espaço vazio.

### Requirement 8: Elementos Informativos e Sociais

**User Story:** Como Usuário_Interno, quero que informações sociais (aniversariantes) e avisos do sistema sejam exibidos de forma discreta e não concorram visualmente com as demandas de trabalho, para que a Dashboard mantenha o foco nas ações prioritárias.

#### Acceptance Criteria

1. THE Dashboard SHALL exibir o banner de aniversariantes do mês como um Card_Colapsável na seção "Acompanhamento", recolhido por padrão.
2. WHEN um colega faz aniversário no dia atual, THE Dashboard SHALL exibir uma notificação discreta no Card_Colapsável de aniversariantes indicando o nome do aniversariante.
3. THE Dashboard SHALL filtrar os aniversariantes conforme o escopo do Usuário_Interno: estadual exibe colegas estaduais e administradores; municipal exibe colegas do mesmo município e administradores.
4. THE Dashboard SHALL exibir a saudação personalizada (nome do Usuário_Interno e data atual) no cabeçalho da página.

### Requirement 9: Carregamento Assíncrono e Performance

**User Story:** Como Usuário_Interno, quero que a Dashboard carregue rapidamente e exiba os dados de forma progressiva, para que eu não precise esperar todo o conteúdo carregar antes de começar a trabalhar.

#### Acceptance Criteria

1. THE Dashboard SHALL carregar as listas de Ordens_de_Serviço, Processos e Documentos pendentes via requisições AJAX assíncronas após o carregamento inicial da página.
2. WHILE uma requisição AJAX está em andamento, THE Dashboard SHALL exibir um indicador de carregamento (spinner) na seção correspondente.
3. IF uma requisição AJAX falha, THEN THE Dashboard SHALL exibir uma mensagem de erro na seção correspondente com opção de tentar novamente.
4. THE Dashboard SHALL utilizar paginação via AJAX para listas de processos, com no máximo 10 itens por página.
5. WHEN o Usuário_Interno navega entre abas, THE Dashboard SHALL carregar os dados da aba selecionada sob demanda (lazy loading), evitando carregar todas as abas simultaneamente.

### Requirement 10: Indicadores Visuais de Status e Urgência

**User Story:** Como Usuário_Interno, quero que cada item da Dashboard tenha indicadores visuais claros de status e urgência, para que eu consiga priorizar minhas ações sem precisar abrir cada item individualmente.

#### Acceptance Criteria

1. THE Dashboard SHALL exibir badges coloridos em cada item de lista indicando o status: verde para "em dia", amarelo para "atenção" (prazo próximo), laranja para "urgente" (prazo hoje), e vermelho para "atrasado" (prazo vencido).
2. WHEN uma Ordem_de_Serviço está no período de finalização (entre data_fim e data_fim + 15 dias), THE Dashboard SHALL exibir o badge com texto indicando os dias restantes para finalização.
3. WHEN um Processo possui documentos obrigatórios, THE Dashboard SHALL exibir um indicador de progresso (ex: "3/5") mostrando documentos aprovados sobre o total esperado.
4. WHEN um Processo está aguardando ciência do responsável, THE Dashboard SHALL exibir um badge indicando "aguardando ciência" com a data de tramitação.
5. THE Dashboard SHALL utilizar um esquema de cores consistente em todas as seções para representar os mesmos níveis de urgência.
