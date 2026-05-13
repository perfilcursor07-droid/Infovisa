# PJ Unidade Móvel — Especificação Completa

## 1. O que é

Funcionalidade para credenciar **estabelecimentos de outros estados** que prestam serviço itinerante/temporário no Tocantins com unidades móveis (carretas, vans, UTI Móvel, etc.).

**Exemplos de serviços:**
- UTI Móvel
- Oftalmologia / Cirurgia de Catarata
- Diagnóstico por Imagem
- Saúde da Mulher

---

## 2. Fluxo Geral

```
1. PJ Unidade Móvel acessa o InfoVisa (CNPJ de fora do TO)
2. Cadastro como "PJ Unidade Móvel" (tipo separado do PJ normal)
3. Escolha da atividade (ex: Serviço Móvel de Urgência)
4. Questionário dinâmico (P1, P2, P4)
5. Escolha dos municípios de atuação + período
6. Sistema verifica pactuação em tempo real → mostra competência por município
   - Se municipal e NÃO usa InfoVisa → aviso já aparece no cadastro
7. Estabelecimento envia o cadastro (competência já definida)
8. Admin/gestor aprova o cadastro
9. Processos de credenciamento criados automaticamente (competência já calculada)
10. Vigilância analisa documentação (gestores e técnicos)
11. Decisão: Deferido (autorização) ou Indeferido (notificação)
```

---

## 3. Cadastro

### Tipo de cadastro
- Novo tipo: `pj_unidade_movel` (separado de `juridica` e `fisica`)
- Permite UF diferente de TO no endereço da sede
- Formulário próprio acessível na tela de cadastro

### Questionário Dinâmico

| # | Pergunta | Opções | Observação |
|---|----------|--------|------------|
| P1 | Atendimento em unidade móvel (carreta/veículo adaptado)? | Sim / Não | - |
| P2 | Qual o tipo de unidade? | UTI Móvel / Carreta / Van / Outro | - |
| P3 | ~~Removida~~ | - | A UF vem automaticamente da API ao informar o CNPJ. Se for de outro estado, o sistema já sabe. Não precisa de pergunta nem upload. |
| P4 | Período de atuação no Tocantins | Tabela: Município + Data início + Data fim | Competência calculada pela pactuação |

**Nota sobre P3:** O CNPJ é informado no início do cadastro e a API retorna os dados da empresa incluindo a UF. Se a UF for diferente de TO, o sistema já identifica automaticamente que é de outro estado — sem necessidade de pergunta ou upload adicional.

### Municípios de Atuação (P4)
- O estabelecimento escolhe 1 ou mais municípios onde vai atuar
- Para cada município informa: data início e data fim
- A **competência** (estadual/municipal) é determinada automaticamente pela **pactuação existente** — não precisa criar nada novo na pactuação
- Interface com botão "+ Adicionar município"

---

## 4. Competência — Regras

A competência é definida **por município** usando a pactuação que já existe no sistema, **antes da aprovação** (no momento do cadastro):

| Situação | Competência | Ação |
|----------|-------------|------|
| Atividade é ESTADUAL naquele município (pactuação) | Estadual | Processo estadual |
| Atividade é MUNICIPAL + município USA InfoVisa | Municipal | Processo municipal (aparece pro município) |
| Atividade é MUNICIPAL + município NÃO usa InfoVisa | Municipal | Aviso: "Procure a VISA municipal de X" |

**Importante:** Um mesmo estabelecimento pode ter processos estaduais E municipais ao mesmo tempo, dependendo dos municípios escolhidos.

---

## 5. Processos Gerados Automaticamente (ao aprovar cadastro)

### Cenário: Municípios de competência ESTADUAL

- Cria **1 processo de credenciamento** com competência estadual
- Dentro do processo, cria **1 pasta por município** de competência estadual
- Documentos obrigatórios divididos em:
  - **Raiz do processo** (documentos gerais — pedidos uma vez)
  - **Dentro de cada pasta/município** (documentos específicos — mais leves)

### Cenário: Município de competência MUNICIPAL (usa InfoVisa)

- Cria **1 processo de credenciamento por município**
- Competência municipal — aparece para o gestor/técnico daquele município
- Documentos obrigatórios específicos do credenciamento municipal

### Cenário: Município de competência MUNICIPAL (NÃO usa InfoVisa)

- **Não cria processo**
- Mostra aviso ao estabelecimento: "Para o município X, procure diretamente a Vigilância Sanitária Municipal"

---

## 6. Estrutura de Documentos Obrigatórios

### Processo Estadual (1 processo com pastas por município)

```
Processo de Credenciamento (Estadual)
│
├── 📄 Documentos Gerais (raiz — pedidos uma vez)
│   ├── Alvará Sanitário do estado de origem
│   ├── Contrato Social / Ato Constitutivo
│   ├── CNES atualizado
│   ├── Responsável Técnico (CRM/COREN/etc)
│   ├── Documento do veículo (CRLV)
│   ├── Fotos da unidade móvel
│   └── Certificado de conformidade do veículo
│
├── 📁 Palmas
│   ├── Cronograma de atendimento
│   ├── Local de estacionamento/atendimento
│   └── Autorização do município (se aplicável)
│
├── 📁 Araguaína
│   ├── Cronograma de atendimento
│   ├── Local de estacionamento/atendimento
│   └── Autorização do município (se aplicável)
│
└── 📁 ...outros municípios estaduais
```

### Processo Municipal (1 por município que usa InfoVisa)

```
Processo de Credenciamento Municipal (ex: Gurupi)
│
├── Cronograma de atendimento
├── Local de estacionamento/atendimento
├── Autorização municipal
└── ...documentos específicos do município
```

**Lógica:** Documentos gerais (pesados) são pedidos uma vez no processo estadual. Documentos por município (leves) são pedidos em cada pasta/processo.

---

## 7. Visibilidade

| Quem | O que vê |
|------|----------|
| Gestor Estadual | Estabelecimento + processo estadual com pastas por município |
| Técnico Estadual | Estabelecimento + processo estadual (cria documentos, OS, tramita) |
| Gestor Municipal (usa InfoVisa) | Estabelecimento + processo municipal do seu município |
| Técnico Municipal (usa InfoVisa) | Estabelecimento + processo municipal (cria documentos, OS, tramita) |
| Estabelecimento (empresa) | Todos os seus processos + aviso sobre municípios sem InfoVisa |

**O processo funciona igual aos processos que já existem no InfoVisa** — gestores e técnicos podem:
- Criar documentos digitais
- Criar ordens de serviço
- Tramitar o processo
- Atribuir responsável
- Solicitar documentos
- Tudo que já funciona hoje dentro de um processo

---

## 8. Tipo de Processo — Configuração

Criar na configuração de tipos de processo:
- **Nome:** Credenciamento de Unidade Móvel
- **Código:** `credenciamento_movel`
- **Competência:** Definida automaticamente (estadual ou municipal conforme pactuação)
- **Abertura automática:** SIM (ao aprovar o estabelecimento)
- **Documentos obrigatórios:** Configuráveis (gerais + por pasta/município)
- **Exibir na fila pública:** A definir

---

## 9. Adicionar Município Depois (Pós-Aprovação)

O estabelecimento PJ Unidade Móvel pode solicitar atuação em novos municípios a qualquer momento após a aprovação.

**Disponível apenas para:** `tipo_pessoa = pj_unidade_movel` (não afeta outros cadastros)

**Onde:** Painel da empresa logada → botão "Solicitar atuação em novo município"

### Fluxo

```
1. Estabelecimento clica "Adicionar município de atuação"
2. Escolhe município + período (data início / data fim)
3. Sistema consulta pactuação → mostra competência em tempo real
   - Se municipal e NÃO usa InfoVisa → aviso aparece
4. Envia solicitação
5. Sistema automaticamente:
   - ESTADUAL → adiciona nova pasta no processo estadual existente
   - MUNICIPAL (usa InfoVisa) → cria novo processo municipal
   - MUNICIPAL (não usa InfoVisa) → apenas aviso
6. Vigilância analisa documentos do novo município
```

### Regras
- Se já existe processo estadual aberto, apenas adiciona nova pasta — não cria processo novo
- Se não existe processo estadual (todos anteriores eram municipais), cria um novo
- Documentos obrigatórios da pasta do município são exigidos normalmente
- Documentos gerais (raiz) já foram enviados antes — não pede novamente
- Respeita a pactuação em tempo real
- Não afeta nenhum outro tipo de cadastro

---

## 10. O que NÃO muda

- Pactuação existente — usa a que já tem, sem criar nada novo
- Cadastro PJ normal — não é afetado
- Cadastro PF — não é afetado
- Processos existentes — não são afetados
- Lógica de competência para estabelecimentos fixos — não muda
- Módulo de processos — usa o que já existe (documentos, OS, tramitação, etc.)

---

## 11. Diagrama Resumo

```
PJ Unidade Móvel cadastra
        ↓
Escolhe atividade + municípios
        ↓
Pactuação verifica competência por município (em tempo real, antes de aprovar)
        ↓
┌───────────────────┬────────────────────────┬──────────────────────────┐
↓                   ↓                        ↓
ESTADUAL            MUNICIPAL                MUNICIPAL
                    (usa InfoVisa)           (NÃO usa InfoVisa)
↓                   ↓                        ↓
1 Processo          1 Processo por           Aviso: "Procure
Estadual com        município                a VISA municipal"
pasta por município (gestores + técnicos)
(gestores + técnicos)
```

---

## 12. Implementação — Etapas de Desenvolvimento

### O que JÁ EXISTE no InfoVisa (vamos reutilizar)

| Módulo | O que faz | Como usamos |
|--------|-----------|-------------|
| Consulta CNPJ (API) | Busca dados da empresa pelo CNPJ | Detecta UF automaticamente |
| Pactuação | Define competência por atividade/município | Consulta em tempo real no cadastro |
| Configuração de Municípios | Flag `usa_infovisa` por município | Decide se cria processo municipal ou mostra aviso |
| Tipos de Processo | Configuração de tipos com abertura automática | Criar tipo "Credenciamento de Unidade Móvel" |
| Processos | Módulo completo (documentos, OS, tramitação, pastas) | Usa integralmente sem alteração |
| Documentos Obrigatórios | Checklist por tipo de processo + por pasta | Configura gerais + por município |
| Pastas no Processo | Organização de documentos por pasta | 1 pasta por município estadual |
| Aprovação de Cadastro | Admin/gestor aprova estabelecimentos | Dispara criação de processos |
| Painel da Empresa | Área logada do estabelecimento | Adicionar município pós-aprovação |
| Questionários Dinâmicos | Perguntas vinculadas a atividades | P1, P2, P4 |

### O que PRECISA SER DESENVOLVIDO

| # | O que | Descrição | Complexidade |
|---|-------|-----------|--------------|
| 1 | **Migration: campos novos** | `tipo_unidade_movel`, `is_itinerante` no estabelecimento | Baixa |
| 2 | **Migration: tabela municípios de atuação** | `estabelecimento_municipios_atuacao` (município, período, competência, status) | Baixa |
| 3 | **Tipo de processo via seeder** | "Credenciamento de Unidade Móvel" com docs obrigatórios | Baixa |
| 4 | **Formulário de cadastro PJ Unidade Móvel** | Nova view + controller, consulta CNPJ, questionário, tabela de municípios com pactuação em tempo real | Alta |
| 5 | **Lógica de aprovação específica** | Ao aprovar PJ Unidade Móvel: criar processos + pastas conforme competência | Média |
| 6 | **Aviso para municípios sem InfoVisa** | Mensagem no cadastro e no painel da empresa | Baixa |
| 7 | **Botão "Adicionar município" (pós-aprovação)** | No painel da empresa, só para PJ Unidade Móvel | Média |
| 8 | **Ajuste de visibilidade** | Garantir que processo estadual/municipal aparece para os corretos | Baixa (já funciona) |

### Ordem de Desenvolvimento

```
Etapa 1 → Migrations (#1 e #2)
Etapa 2 → Tipo de processo + docs obrigatórios (#3)
Etapa 3 → Formulário de cadastro (#4) — a mais complexa
Etapa 4 → Lógica de aprovação (#5 e #6)
Etapa 5 → Adicionar município pós-aprovação (#7)
Etapa 6 → Testes e ajustes de visibilidade (#8)
```

---

*Documento elaborado para planejamento — Sistema InfoVisa / VISA-TO*
*Data: 13/05/2026*
