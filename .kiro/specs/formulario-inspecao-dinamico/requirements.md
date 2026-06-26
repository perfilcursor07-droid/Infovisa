# Documento de Requisitos

## Introdução

O sistema InfoVISA atualmente utiliza um editor WYSIWYG (TinyMCE) para criação de documentos de inspeção sanitária. Técnicos preenchem tabelas manualmente no editor HTML, o que causa problemas de formatação: tabelas muito largas para A4 retrato, texto transbordando células, layout quebrado na geração de PDF, cabeçalhos de tabela não repetindo em novas páginas e mesclagem problemática de células.

Esta feature substitui a abordagem de tabelas HTML por um formulário dinâmico estruturado para documentos de inspeção. Modelos marcados como "dinâmicos" apresentam um formulário com campos definidos em vez do editor HTML livre. O formulário permite adicionar/remover setores e itens de inspeção, salva os dados como JSON e gera PDF em formato paisagem A4 com layout adequado.

## Glossário

- **Sistema**: O sistema InfoVISA de gestão de vigilância sanitária
- **Modelo_Documento**: Registro em `modelos_documentos` que define a estrutura e conteúdo padrão de um tipo de documento
- **Documento_Digital**: Registro em `documentos_digitais` que representa um documento criado por um usuário a partir de um modelo
- **Formulário_Dinâmico**: Interface de formulário estruturado com campos tipados que substitui o editor HTML para modelos dinâmicos
- **Setor_Inspecionado**: Agrupamento de itens de inspeção referente a um setor específico do estabelecimento (ex: UTI, Farmácia, Cozinha)
- **Item_Inspecao**: Linha individual dentro de um setor que descreve um ponto avaliado, sua criticidade, não-conformidade encontrada e determinação
- **Administrador**: Usuário com nível de acesso administrativo que configura modelos de documento
- **Técnico**: Usuário estadual ou municipal que realiza inspeções e preenche documentos
- **Gestor**: Usuário responsável por assinar documentos
- **DomPDF**: Biblioteca PHP utilizada para geração de arquivos PDF
- **Dados_Formulário**: Estrutura JSON que armazena os dados preenchidos no formulário dinâmico
- **Criticidade**: Classificação do nível de risco de um item: Baixa, Média, Alta ou Crítica

## Requisitos

### Requisito 1: Configuração do Modelo como Dinâmico

**User Story:** Como Administrador, eu quero marcar um modelo de documento como "dinâmico", para que técnicos que utilizem esse modelo preencham um formulário estruturado em vez de editar HTML livre.

#### Critérios de Aceitação

1. WHEN o Administrador está criando ou editando um Modelo_Documento, THE Sistema SHALL exibir uma opção de alternância (toggle) "Modelo Dinâmico" no formulário de configuração
2. WHEN o Administrador ativa a opção "Modelo Dinâmico", THE Sistema SHALL ocultar o campo de conteúdo HTML (editor TinyMCE) e exibir a interface de configuração de campos do formulário dinâmico
3. WHEN o Administrador desativa a opção "Modelo Dinâmico", THE Sistema SHALL restaurar o campo de conteúdo HTML e ocultar a interface de configuração de campos
4. THE Sistema SHALL persistir o atributo `is_dinamico` (booleano) no registro do Modelo_Documento
5. WHEN um Modelo_Documento com `is_dinamico` igual a falso é utilizado, THE Sistema SHALL manter o comportamento atual do editor TinyMCE sem alterações

### Requisito 2: Definição da Estrutura de Campos do Modelo Dinâmico

**User Story:** Como Administrador, eu quero definir quais campos e seções compõem o formulário dinâmico de um modelo, para que os técnicos tenham uma estrutura padronizada de preenchimento.

#### Critérios de Aceitação

1. WHEN o Administrador configura um modelo dinâmico, THE Sistema SHALL apresentar a estrutura padrão de campos de inspeção com três seções: Cabeçalho, Setores Inspecionados e Rodapé
2. THE Sistema SHALL definir a seção Cabeçalho com os campos: Data da Inspeção (date), Objetivo da Inspeção (text), Responsável Presente (text) e Atividades Executadas (textarea)
3. THE Sistema SHALL definir a seção Setores Inspecionados como uma lista repetível de Setor_Inspecionado, onde cada setor contém: Nome do Setor (text) e uma lista repetível de Item_Inspecao
4. THE Sistema SHALL definir cada Item_Inspecao com os campos: Número do Item (integer, auto-incrementado), Item Avaliado (text), Criticidade (select: Baixa, Média, Alta, Crítica), Não-conformidade (textarea), Determinação/Observação (textarea), Prazo (date) e Fundamentação Legal (text)
5. THE Sistema SHALL definir a seção Rodapé com os campos: Documentos Referenciados (textarea) e Observações Finais (textarea)
6. THE Sistema SHALL armazenar a configuração de campos do modelo dinâmico como JSON na coluna `estrutura_campos` do Modelo_Documento

### Requisito 3: Renderização do Formulário Dinâmico para Preenchimento

**User Story:** Como Técnico, eu quero visualizar um formulário estruturado ao criar um documento a partir de um modelo dinâmico, para que eu preencha as informações de inspeção de forma organizada sem problemas de formatação.

#### Critérios de Aceitação

1. WHEN o Técnico cria um Documento_Digital a partir de um Modelo_Documento com `is_dinamico` igual a verdadeiro, THE Sistema SHALL renderizar o Formulário_Dinâmico em vez do editor TinyMCE
2. THE Formulário_Dinâmico SHALL exibir os campos da seção Cabeçalho como campos de formulário padrão (inputs, textareas, date pickers)
3. THE Formulário_Dinâmico SHALL exibir um botão "Adicionar Setor" que permite ao Técnico adicionar novos Setor_Inspecionado
4. WHEN o Técnico adiciona um Setor_Inspecionado, THE Formulário_Dinâmico SHALL exibir o campo Nome do Setor e um botão "Adicionar Item" dentro do setor
5. WHEN o Técnico adiciona um Item_Inspecao, THE Formulário_Dinâmico SHALL exibir todos os campos do item com numeração sequencial automática dentro do setor
6. THE Formulário_Dinâmico SHALL permitir ao Técnico remover qualquer Setor_Inspecionado ou Item_Inspecao adicionado
7. WHEN o Técnico remove um Item_Inspecao, THE Formulário_Dinâmico SHALL renumerar automaticamente os itens restantes no setor de forma sequencial
8. THE Formulário_Dinâmico SHALL exibir os campos da seção Rodapé após todos os setores

### Requisito 4: Persistência dos Dados do Formulário

**User Story:** Como Técnico, eu quero salvar o formulário a qualquer momento e continuar editando depois, para que eu não perca dados durante inspeções longas.

#### Critérios de Aceitação

1. WHEN o Técnico clica em "Salvar" no Formulário_Dinâmico, THE Sistema SHALL serializar todos os dados preenchidos como JSON e armazenar no campo `conteudo` do Documento_Digital
2. WHEN o Técnico reabre um Documento_Digital que utiliza modelo dinâmico, THE Sistema SHALL desserializar os Dados_Formulário do campo `conteudo` e preencher o Formulário_Dinâmico com os valores salvos
3. WHEN o Técnico salva o formulário, THE Sistema SHALL validar os campos obrigatórios (Data da Inspeção, Nome do Setor para cada setor adicionado) e exibir mensagens de erro específicas para campos inválidos
4. WHILE o Documento_Digital está com status "rascunho", THE Sistema SHALL permitir edição completa do Formulário_Dinâmico (adicionar, remover e alterar setores e itens)
5. WHILE o Documento_Digital está com status diferente de "rascunho", THE Sistema SHALL exibir o Formulário_Dinâmico em modo somente leitura
6. FOR ALL Dados_Formulário válidos, serializar e desserializar SHALL produzir um objeto equivalente ao original (propriedade round-trip)

### Requisito 5: Geração de PDF em Formato Paisagem

**User Story:** Como Técnico, eu quero gerar um PDF do documento de inspeção em formato paisagem A4 com layout adequado, para que o documento impresso seja legível e profissional.

#### Critérios de Aceitação

1. WHEN o Documento_Digital de modelo dinâmico é finalizado (todas as assinaturas coletadas), THE Sistema SHALL gerar o arquivo PDF em orientação paisagem A4 (297mm x 210mm)
2. THE Sistema SHALL renderizar um cabeçalho fixo em cada página do PDF contendo: logotipo da instituição, título do documento, tipo do documento, número do documento e metadados da inspeção (data, objetivo, responsável)
3. THE Sistema SHALL renderizar os itens de inspeção em formato de tabela com as colunas: Nº, Item Avaliado, Criticidade, Não-conformidade, Determinação/Observação, Prazo e Fundamentação Legal
4. THE Sistema SHALL aplicar larguras proporcionais fixas às colunas da tabela de forma que o conteúdo caiba na largura da página paisagem sem transbordar
5. THE Sistema SHALL aplicar fonte com tamanho entre 8pt e 9pt no conteúdo da tabela para otimizar o uso do espaço
6. THE Sistema SHALL aplicar word-wrap em todas as células da tabela para que textos longos quebrem dentro da célula sem transbordar
7. THE Sistema SHALL repetir o cabeçalho da tabela (linha com nomes das colunas) no início de cada nova página
8. THE Sistema SHALL agrupar os itens por Setor_Inspecionado, exibindo o nome do setor como linha de cabeçalho de grupo antes dos itens
9. THE Sistema SHALL renderizar a seção de rodapé (documentos referenciados, observações) após a última tabela de itens
10. THE Sistema SHALL renderizar a área de assinaturas com nome, cargo e data de cada assinante após o rodapé

### Requisito 6: Compatibilidade com Modelos Existentes

**User Story:** Como Administrador, eu quero que modelos existentes (HTML livre) continuem funcionando sem alterações, para que a migração para formulários dinâmicos seja gradual e sem impacto em documentos em andamento.

#### Critérios de Aceitação

1. THE Sistema SHALL manter o comportamento atual para todos os Modelo_Documento com `is_dinamico` igual a falso ou nulo
2. THE Sistema SHALL manter o fluxo de assinaturas idêntico para documentos dinâmicos e documentos HTML (adicionar assinantes, assinar, finalizar)
3. WHEN um Documento_Digital de modelo dinâmico é exibido na timeline do processo, THE Sistema SHALL exibir o documento com as mesmas informações de status, data e ações disponíveis que documentos HTML
4. THE Sistema SHALL permitir download do PDF de documentos dinâmicos da mesma forma que documentos HTML (mesmo botão, mesmo endpoint)
5. WHEN um Modelo_Documento possui Documentos_Digitais já criados, THE Sistema SHALL impedir a alteração do atributo `is_dinamico` para evitar inconsistência de dados

### Requisito 7: Validação de Dados do Formulário

**User Story:** Como Técnico, eu quero receber feedback claro sobre campos inválidos ou incompletos, para que eu corrija os dados antes de enviar para assinatura.

#### Critérios de Aceitação

1. WHEN o Técnico tenta enviar o documento para assinatura, THE Sistema SHALL validar que todos os campos obrigatórios estão preenchidos: Data da Inspeção, Objetivo, pelo menos um Setor_Inspecionado com pelo menos um Item_Inspecao
2. WHEN a validação falha, THE Sistema SHALL exibir mensagem de erro específica indicando quais campos estão incompletos, sem perder dados já preenchidos
3. WHEN o campo Criticidade de um Item_Inspecao está preenchido, THE Sistema SHALL aceitar apenas os valores: "Baixa", "Média", "Alta" ou "Crítica"
4. WHEN o campo Prazo de um Item_Inspecao está preenchido, THE Sistema SHALL validar que a data é igual ou posterior à Data da Inspeção
5. IF o Técnico tenta salvar com dados em formato inválido no JSON, THEN THE Sistema SHALL rejeitar a operação e retornar erro informativo sem corromper dados existentes

### Requisito 8: Interface Responsiva do Formulário

**User Story:** Como Técnico, eu quero que o formulário dinâmico seja usável em diferentes tamanhos de tela, para que eu possa preencher dados durante a inspeção em campo se necessário.

#### Critérios de Aceitação

1. WHILE a largura da viewport é igual ou superior a 1024px, THE Formulário_Dinâmico SHALL exibir os campos de Item_Inspecao em layout de grid com múltiplas colunas
2. WHILE a largura da viewport é inferior a 1024px, THE Formulário_Dinâmico SHALL empilhar os campos de Item_Inspecao verticalmente em coluna única
3. THE Formulário_Dinâmico SHALL utilizar componentes TailwindCSS e Alpine.js consistentes com o restante do Sistema
4. THE Formulário_Dinâmico SHALL fornecer feedback visual (highlight, animação) ao adicionar ou remover setores e itens
