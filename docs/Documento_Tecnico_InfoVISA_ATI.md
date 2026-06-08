# DOCUMENTO TÉCNICO - SISTEMA InfoVISA

## Informações Gerais

| Campo | Descrição |
|-------|-----------|
| **Sistema** | InfoVISA - Sistema de Informação em Vigilância Sanitária |
| **Versão** | 1.0 |
| **Órgão** | Vigilância Sanitária |
| **Destinatário** | ATI - Agência de Tecnologia da Informação |
| **Data** | Junho/2026 |

---

## 1. Visão Geral do Sistema

O **InfoVISA** é um sistema web integrado de gestão da Vigilância Sanitária, desenvolvido para informatizar e otimizar os processos de licenciamento sanitário, fiscalização, controle de documentos e comunicação entre a equipe técnica e os estabelecimentos regulados.

O sistema contempla dois portais principais:
- **Portal do Estabelecimento (Área da Empresa)** — para empresas e profissionais registrarem seus estabelecimentos, acompanharem processos e submeterem documentos.
- **Portal Administrativo (Área Interna)** — para servidores da Vigilância Sanitária gerenciarem processos, emitirem documentos, realizarem inspeções e administrarem o sistema.

---

## 2. Stack Tecnológica

### 2.1 Linguagem de Programação e Framework

| Componente | Tecnologia | Versão |
|------------|------------|--------|
| **Linguagem Backend** | PHP | 8.2+ |
| **Framework Backend** | Laravel | 11.31 |
| **Linguagem Frontend** | JavaScript (ES Modules) | ES2022+ |
| **Framework Frontend** | Alpine.js | 3.15.0 |
| **CSS Framework** | TailwindCSS | 3.4.18 |
| **Bundler** | Vite | 6.0.11 |
| **Plugin Laravel/Vite** | laravel-vite-plugin | 1.2.0 |

### 2.2 Banco de Dados

| Ambiente | SGBD | Observações |
|----------|------|-------------|
| **Desenvolvimento** | SQLite | Banco de dados local para desenvolvimento rápido |
| **Produção** | MySQL / MariaDB | Banco relacional principal configurável via variáveis de ambiente |
| **Compatibilidade** | PostgreSQL | Configuração disponível no framework |

O sistema utiliza o **ORM Eloquent** do Laravel para abstração de banco de dados, com mais de **180 migrations** que definem a estrutura completa do banco.

### 2.3 Servidor de Produção

| Componente | Tecnologia |
|------------|------------|
| **Servidor Web** | Apache (httpd) |
| **PHP Runtime** | PHP-FPM |
| **Sistema Operacional** | Linux (CentOS/RHEL) |
| **Fila de Processos** | Laravel Queue (Database Driver) |
| **Cache** | Database Cache (Laravel) |
| **Sessões** | Database Sessions |

### 2.4 Bibliotecas e Pacotes Principais

| Pacote | Função |
|--------|--------|
| `barryvdh/laravel-dompdf` | Geração de PDFs (alvarás, notificações, relatórios) |
| `endroid/qr-code` | Geração de QR Codes para verificação de autenticidade |
| `intervention/image-laravel` | Manipulação e redimensionamento de imagens |
| `laravel/sanctum` | Autenticação de API (app mobile) |
| `maatwebsite/excel` | Importação e exportação de planilhas Excel |
| `phpoffice/phppresentation` | Importação de apresentações PowerPoint |
| `setasign/fpdf` + `fpdi` | Manipulação avançada de PDFs existentes |
| `smalot/pdfparser` | Extração de texto de PDFs |
| `spatie/laravel-permission` | Controle de acesso baseado em papéis (RBAC) |

### 2.5 Ferramentas de Desenvolvimento

| Ferramenta | Função |
|------------|--------|
| `barryvdh/laravel-debugbar` | Barra de debug para desenvolvimento |
| `laravel/telescope` | Monitoramento de requisições, filas e exceções |
| `laravel/pint` | Formatação de código PHP (PSR-12) |
| `phpunit/phpunit` | Testes automatizados |

---

## 3. Arquitetura do Sistema

### 3.1 Padrão Arquitetural

O sistema segue o padrão **MVC (Model-View-Controller)** implementado pelo framework Laravel:

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENTE (Browser)                         │
│         Alpine.js + TailwindCSS + Vite (Assets)                 │
└──────────────────────────────┬──────────────────────────────────┘
                               │ HTTP/HTTPS
┌──────────────────────────────▼──────────────────────────────────┐
│                      SERVIDOR WEB (Apache)                       │
│                         PHP-FPM 8.2+                             │
├─────────────────────────────────────────────────────────────────┤
│                     LARAVEL FRAMEWORK 11                         │
│  ┌───────────┐  ┌──────────────┐  ┌─────────────────────────┐  │
│  │  Routes   │→ │ Middlewares  │→ │     Controllers          │  │
│  └───────────┘  └──────────────┘  └──────────┬──────────────┘  │
│                                               │                  │
│  ┌───────────────────────────────────────────▼──────────────┐   │
│  │                    Models (Eloquent ORM)                   │   │
│  └───────────────────────────────────────────┬──────────────┘   │
│                                               │                  │
│  ┌───────────────────────────────────────────▼──────────────┐   │
│  │              Views (Blade Templates)                       │   │
│  └───────────────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────────────┤
│              Banco de Dados MySQL/MariaDB                        │
│              Fila de Processos (Queue)                           │
│              Armazenamento de Arquivos (Storage)                 │
└─────────────────────────────────────────────────────────────────┘
```

### 3.2 Sistema de Autenticação

O sistema implementa **autenticação dual-guard**:

- **Guard `externo`** — Autenticação para empresas e profissionais (usuários externos)
- **Guard `interno`** — Autenticação para servidores da Vigilância Sanitária (usuários internos)

Cada guard possui login, sessão e controle de acesso completamente independentes.

### 3.3 Controle de Acesso (RBAC)

O controle de acesso é gerenciado pela biblioteca Spatie Laravel Permission com os seguintes níveis:

- **Administrador** — Acesso total ao sistema
- **Gestor Estadual** — Configurações estaduais e pactuação
- **Gestor Municipal** — Gestão do município vinculado
- **Técnico/Fiscal** — Operação de processos e inspeções
- **Usuário Externo** — Portal do estabelecimento

---

## 4. Módulos do Sistema

### 4.1 Portal Público

| Módulo | Descrição |
|--------|-----------|
| **Consulta de Processos** | Consulta pública do andamento de processos por número |
| **Fila de Processos** | Visualização pública da fila de atendimento |
| **Verificação de Autenticidade** | Validação de documentos emitidos via QR Code |
| **Pesquisa de Satisfação** | Formulários públicos de avaliação dos serviços |
| **Treinamentos** | Inscrição e participação em eventos de capacitação |

### 4.2 Portal do Estabelecimento (Área da Empresa)

| Módulo | Descrição |
|--------|-----------|
| **Dashboard** | Painel inicial com visão geral dos estabelecimentos e processos |
| **Gestão de Estabelecimentos** | Cadastro e manutenção de dados (PJ/PF), atividades CNAE, responsáveis legais e técnicos |
| **Processos** | Abertura de processos, upload de documentos, acompanhamento de status e prazos |
| **Equipamentos de Radiação** | Registro e controle de equipamentos de radiação ionizante |
| **Documentos da Vigilância** | Recebimento e resposta a documentos oficiais (notificações, intimações) |
| **Alertas** | Notificações de prazos, pendências e atualizações de processos |
| **Assistente IA** | Assistente virtual para dúvidas sobre documentação e processos |
| **Perfil** | Gerenciamento de dados pessoais e senha |

### 4.3 Portal Administrativo (Área Interna)

#### 4.3.1 Gestão de Processos

| Módulo | Descrição |
|--------|-----------|
| **Processos** | Gestão completa do ciclo de vida: abertura, análise, designação, arquivamento |
| **Designação de Responsáveis** | Atribuição de processos a técnicos/fiscais |
| **Documentos Pendentes** | Fila de documentos aguardando aprovação |
| **Alertas de Processos** | Controle de vencimentos e pendências |
| **Pastas do Processo** | Organização documental com estrutura de pastas |
| **Anotações em PDF** | Marcações e comentários diretos em documentos PDF |

#### 4.3.2 Documentos Digitais

| Módulo | Descrição |
|--------|-----------|
| **Emissão de Documentos** | Criação de alvarás, notificações, intimações, laudos com modelos configuráveis |
| **Assinatura Digital** | Assinatura eletrônica de documentos com autenticação por senha |
| **Versionamento** | Controle de versões com possibilidade de restauração |
| **Edição Colaborativa** | Edição simultânea com controle de concorrência |
| **Verificação de Autenticidade** | QR Code com código único para validação externa |

#### 4.3.3 Ordens de Serviço

| Módulo | Descrição |
|--------|-----------|
| **Criação de OS** | Registro de inspeções e atividades externas com tipos de ação e subações |
| **Designação de Técnicos** | Atribuição de múltiplos técnicos por OS |
| **Finalização por Atividade** | Cada técnico finaliza individualmente sua atividade |
| **Geração de PDF** | Documento oficial da OS para impressão/arquivo |
| **Assinatura do Gestor** | Validação hierárquica da OS finalizada |

#### 4.3.4 Estabelecimentos

| Módulo | Descrição |
|--------|-----------|
| **Aprovação de Cadastros** | Workflow de aprovação/rejeição de novos estabelecimentos |
| **Gestão de Atividades** | Configuração de atividades CNAE do estabelecimento |
| **Responsáveis Técnicos** | Gerenciamento de responsáveis legais e técnicos |
| **Histórico** | Registro completo de alterações no estabelecimento |
| **Competência** | Definição se o estabelecimento é municipal ou estadual |

#### 4.3.5 Comunicação e Colaboração

| Módulo | Descrição |
|--------|-----------|
| **Chat Interno** | Mensagens instantâneas entre servidores |
| **Broadcasts** | Comunicados gerais para toda a equipe |
| **Suporte InfoVISA** | Canal de suporte técnico do sistema |
| **Notificações** | Sistema de alertas sobre pendências, designações e prazos |
| **WhatsApp** | Integração para envio de mensagens automáticas a estabelecimentos |

#### 4.3.6 Capacitação e Treinamentos

| Módulo | Descrição |
|--------|-----------|
| **Eventos de Treinamento** | Criação e gerenciamento de eventos de capacitação |
| **Apresentações/Slides** | Sistema de apresentação interativa com slides |
| **Perguntas e Avaliação** | Quiz integrado às apresentações com pontuação |
| **Importação PowerPoint** | Importação automática de arquivos .pptx |
| **Relatórios** | Inscritos, respostas e desempenho dos participantes |

#### 4.3.7 Configurações

| Módulo | Descrição |
|--------|-----------|
| **Tipos de Processo** | Configuração dos tipos de licenciamento (Alvará, Renovação, etc.) |
| **Tipos de Documento** | Categorias de documentos e subcategorias |
| **Modelos de Documento** | Templates para emissão com variáveis dinâmicas |
| **Pactuação** | Acordos de competência entre Estado e Municípios |
| **Atividades (CNAE)** | Classificação de atividades econômicas reguladas |
| **Tipos de Ação** | Categorias de ações fiscalizatórias |
| **Municípios** | Gestão de municípios com logomarca e configurações |
| **Unidades** | Unidades organizacionais da Vigilância |
| **Setores** | Tipos de setor interno (Atendimento, Fiscalização, etc.) |
| **Documentos de Ajuda** | Manuais e guias contextuais para usuários |
| **POPs** | Procedimentos Operacionais Padrão por categoria |
| **Pesquisas de Satisfação** | Configuração de questionários de avaliação |
| **Configurações do Sistema** | Parâmetros gerais, permissões e personalização |

#### 4.3.8 Relatórios e Inteligência

| Módulo | Descrição |
|--------|-----------|
| **Relatório de Processos** | Quantitativos e indicadores de processos |
| **Relatório de Estabelecimentos por CNAE** | Distribuição por atividade econômica |
| **Relatório de Documentos Gerados** | Produtividade documental |
| **Relatório de Equipamentos de Radiação** | Inventário de equipamentos com exportação Excel |
| **Relatório de Pesquisa de Satisfação** | Resultados com análise por IA |
| **Relatório de Usuários** | Atividade e produtividade dos servidores |
| **Relatório de Ações por Atividade** | Distribuição de ações fiscalizatórias |
| **Assistente IA** | Análise de documentos, extração de PDFs e sugestões inteligentes |

#### 4.3.9 Gestão de Usuários

| Módulo | Descrição |
|--------|-----------|
| **Usuários Internos** | Cadastro, convite, aprovação e gestão de servidores |
| **Usuários Externos** | Visualização e gestão de empresas/profissionais cadastrados |
| **Perfil** | Dados pessoais, foto e alteração de senha |

### 4.4 Aplicativo Mobile (Android)

| Módulo | Descrição |
|--------|-----------|
| **Notificações Push** | Recebimento de alertas em tempo real |
| **API REST** | Comunicação via Laravel Sanctum |
| **Plataforma** | Android nativo (Java/Kotlin) |

---

## 5. Modelo de Dados (Entidades Principais)

O banco de dados do InfoVISA possui mais de **70 tabelas** organizadas nas seguintes áreas:

### 5.1 Entidades Core

| Tabela | Descrição |
|--------|-----------|
| `usuarios_externos` | Empresas e profissionais (login externo) |
| `usuarios_internos` | Servidores da Vigilância (login interno) |
| `estabelecimentos` | Estabelecimentos regulados |
| `processos` | Processos de licenciamento/fiscalização |
| `documento_digitals` | Documentos oficiais emitidos |
| `municipios` | Municípios configurados |

### 5.2 Entidades de Processo

| Tabela | Descrição |
|--------|-----------|
| `processo_documentos` | Documentos anexados ao processo |
| `processo_acompanhamentos` | Timeline/histórico do processo |
| `processo_eventos` | Eventos de mudança de status |
| `processo_designacoes` | Designação de responsáveis técnicos |
| `processo_alertas` | Alertas de prazos e pendências |
| `processo_pastas` | Estrutura organizacional de pastas |

### 5.3 Entidades de Configuração

| Tabela | Descrição |
|--------|-----------|
| `tipo_processos` | Tipos de processo configuráveis |
| `tipo_documentos` | Tipos de documento com prazos |
| `modelo_documentos` | Templates de documentos com variáveis |
| `atividades` | Atividades CNAE reguladas |
| `pactuacoes` | Acordos de competência Estado/Município |
| `tipo_acoes` / `sub_acoes` | Tipos de ação com subações |

### 5.4 Entidades de Comunicação

| Tabela | Descrição |
|--------|-----------|
| `chat_conversas` | Conversas do chat interno |
| `chat_mensagens` | Mensagens entre usuários |
| `chat_broadcasts` | Comunicados gerais |
| `notificacoes` | Notificações do sistema |
| `whatsapp_mensagens` | Log de mensagens WhatsApp |

---

## 6. Integrações

| Integração | Descrição | Tecnologia |
|------------|-----------|------------|
| **API de CNPJ** | Consulta automática de dados cadastrais de empresas | API REST (CNPJA) |
| **WhatsApp** | Envio de notificações e alertas via WhatsApp | Servidor Node.js dedicado |
| **Inteligência Artificial** | Análise de documentos, extração de PDFs e sugestões | API de IA (configurável) |
| **QR Code** | Geração para verificação de autenticidade | Biblioteca endroid/qr-code |
| **App Android** | API REST para aplicativo mobile | Laravel Sanctum |

---

## 7. Segurança

| Aspecto | Implementação |
|---------|---------------|
| **Autenticação** | Dual-guard com sessões separadas e bcrypt (12 rounds) |
| **Autorização** | RBAC com Spatie Permissions + middlewares customizados |
| **Sessão** | Armazenada em banco de dados com criptografia opcional |
| **CSRF** | Token de proteção em todos os formulários |
| **Assinatura Digital** | Senha exclusiva por usuário para assinatura de documentos |
| **Logs/Auditoria** | Registro de todas as ações relevantes no sistema |
| **Documentos Sigilosos** | Controle de acesso por permissão específica |
| **Cache de Autenticação** | Headers no-cache para páginas protegidas |

---

## 8. Infraestrutura e Deploy

### 8.1 Requisitos do Servidor

| Requisito | Especificação |
|-----------|---------------|
| **Sistema Operacional** | Linux (CentOS/RHEL ou compatível) |
| **PHP** | 8.2 ou superior com extensões: mbstring, xml, pdo, mysql, gd, zip |
| **Servidor Web** | Apache com mod_rewrite habilitado |
| **PHP-FPM** | Processamento PHP de alta performance |
| **Node.js** | 18+ (para build de assets e servidor WhatsApp) |
| **Composer** | Gerenciador de dependências PHP |
| **Espaço em Disco** | Suficiente para armazenamento de documentos |
| **RAM** | Mínimo 4GB recomendado |

### 8.2 Processo de Deploy

O deploy é realizado via Git com script automatizado:

1. Pull do código fonte via `git pull --ff-only origin main`
2. Instalação de dependências com `composer install --no-dev --optimize-autoloader`
3. Execução de migrations `php artisan migrate --force`
4. Limpeza e rebuild de caches `php artisan optimize:clear && php artisan config:cache`
5. Build dos assets frontend `npm run build`
6. Ajuste de permissões de storage e cache
7. Restart dos serviços Apache e PHP-FPM

---

## 9. Estrutura de Diretórios

```
infovisa/
├── app/                    # Código fonte da aplicação
│   ├── Console/Commands/   # Comandos artisan customizados
│   ├── Enums/             # Enumerações (NivelAcesso, TipoSetor, etc.)
│   ├── Helpers/           # Funções auxiliares
│   ├── Http/
│   │   ├── Controllers/   # Controladores (Admin, Company, Auth, Api, Public)
│   │   └── Middleware/    # Middlewares de autenticação e autorização
│   └── Models/            # 70+ modelos Eloquent
├── config/                # Configurações do framework
├── database/
│   ├── migrations/        # 180+ migrations do banco de dados
│   ├── seeders/           # Dados iniciais do sistema
│   └── factories/         # Factories para testes
├── public/                # Ponto de entrada web e assets públicos
├── resources/
│   ├── views/             # Templates Blade organizados por módulo
│   ├── css/               # Estilos (TailwindCSS)
│   └── js/                # JavaScript (Alpine.js)
├── routes/                # Definição de rotas (web, api, console)
├── storage/               # Arquivos enviados, logs e cache
├── whatsapp-server/       # Servidor Node.js para integração WhatsApp
└── android-app/           # Código fonte do aplicativo Android
```

---

## 10. Resumo Quantitativo

| Métrica | Quantidade |
|---------|------------|
| **Modelos (Entidades)** | 70+ |
| **Migrations (Tabelas)** | 180+ |
| **Controllers** | 50+ |
| **Rotas** | 300+ |
| **Views (Templates)** | 200+ |
| **Módulos Funcionais** | 20+ |

---

## 11. Considerações Finais

O InfoVISA é um sistema completo e integrado que atende todas as necessidades operacionais da Vigilância Sanitária, desde o cadastro de estabelecimentos até a emissão de documentos oficiais com assinatura digital e verificação de autenticidade.

O sistema foi desenvolvido com tecnologias modernas, amplamente suportadas pela comunidade, e segue as melhores práticas de segurança e desenvolvimento web. A arquitetura permite escalabilidade horizontal e fácil manutenção, com código bem organizado seguindo os padrões do framework Laravel.

---

*Documento elaborado para a ATI — Agência de Tecnologia da Informação*
*InfoVISA — Sistema de Informação em Vigilância Sanitária*
*Junho/2026*
