# Módulo Unidade Móvel — InfoVISA

**Resumo para a equipe da Vigilância Sanitária**

---

## O que foi desenvolvido

Foi desenvolvido no InfoVISA o **Módulo de Unidade Móvel**, que permite o credenciamento de empresas que prestam serviços de saúde itinerantes em veículos adaptados (vans, micro-ônibus, ônibus e carretas) atuando em municípios do Tocantins.

O módulo cobre desde o cadastro pela empresa até a aprovação pela vigilância e o acompanhamento pós-aprovação, integrado às regras de **pactuação** já existentes no sistema.

---

## Como funciona

### 1. Configuração (Admin)

- **Pactuação de Unidade Móvel** (`Configurações → Pactuação`): aba dedicada onde o administrador marca quais CNAEs são contemplados pela Unidade Móvel e suas competências (estadual/municipal, com exceções por município).
- **Documentos obrigatórios** (`Configurações → Listas de Documentos → Unidade Móvel`): aba específica para definir quais documentos são exigidos por CNAE de Unidade Móvel, separados em documentos gerais e por município.

### 2. Cadastro pela Empresa

A empresa pode entrar no módulo de duas formas:

**a) Cadastro novo de Unidade Móvel**
Empresa de fora do estado (ou nova) faz o cadastro completo escolhendo "Unidade Móvel". Preenche tipo de unidade, atividades contempladas, municípios de atuação no Tocantins e contato.

**b) Estabelecimento já existente que vira Unidade Móvel**
Empresa já aprovada no InfoVISA com ao menos um CNAE contemplado vê o botão **"Unidade Móvel"** no painel do estabelecimento e preenche um formulário simplificado (tipo de unidade + atividades + municípios). Não duplica o cadastro: o estabelecimento ganha o módulo como uma extensão.

Em ambos os casos, o sistema:
- Valida em tempo real se cada município é de competência **estadual** ou **municipal**, e se o município **utiliza ou não o InfoVISA**.
- Bloqueia o envio se nenhum município for válido (todos sem InfoVISA).
- Exibe a competência (estadual/municipal) automaticamente, conforme as regras de pactuação.

### 3. Aprovação pela Vigilância Sanitária

- A solicitação aparece como **alerta na dashboard** do admin/gestor e também no `show` do estabelecimento.
- O analista revisa os dados, atividades e municípios de atuação.
- Pode **Aprovar** ou **Rejeitar** com motivo.

Ao **aprovar**, o sistema cria automaticamente os processos de credenciamento:
- **1 processo estadual** com pastas separadas para cada município de competência estadual.
- **1 processo municipal** para cada município de competência municipal que utiliza o InfoVISA.
- Municípios que não utilizam o InfoVISA recebem orientação para procurar a vigilância municipal diretamente.

Ao **rejeitar**, o módulo é desativado e o estabelecimento mantém seu cadastro normal, podendo refazer a solicitação.

### 4. Pós-Aprovação

No painel da empresa, quando o módulo está aprovado, são exibidos:
- Menu lateral **"Municípios de Atuação"** — página dedicada com todos os municípios, períodos de atuação e competência.
- Botão **"Adicionar Município"** — permite incluir novos municípios mesmo após a aprovação. O sistema recalcula a competência automaticamente e:
  - Se for novo município **estadual**: adiciona uma pasta no processo estadual existente (ou cria um novo, se não houver).
  - Se for **municipal com InfoVISA**: cria um novo processo municipal.
  - Se for **municipal sem InfoVISA**: registra o município com aviso para procurar a vigilância local.

No painel do admin, a mesma página dedicada de **Municípios de Atuação** está disponível para acompanhamento.

### 5. Documentos Obrigatórios nos Processos

Os processos de credenciamento de Unidade Móvel exibem automaticamente, no painel da empresa, os documentos obrigatórios configurados pelo admin para os CNAEs daquela unidade. A empresa faz o upload normalmente, como em qualquer outro processo.

---

## Identificação visual

- Estabelecimentos de Unidade Móvel são identificados em todas as listagens com a marcação **"— UNIDADE MÓVEL"** ao lado do nome (em fúcsia).
- Solicitações pendentes ganham badge **"Aguardando aprovação"**.
- A dashboard do admin exibe um alerta dedicado em fúcsia listando todas as solicitações pendentes de aprovação.

---

## Resumo do fluxo

```
Empresa solicita
       ↓
Sistema valida CNAEs e competência por município
       ↓
Vigilância Sanitária aprova ou rejeita
       ↓
[Aprovado] Processos criados automaticamente
       ↓
Empresa envia documentos obrigatórios
       ↓
Pós-aprovação: empresa pode adicionar novos municípios a qualquer momento
```

---

*Documento gerado para apresentação interna do módulo.*
