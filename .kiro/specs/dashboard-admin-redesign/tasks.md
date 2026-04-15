# Implementation Plan: Dashboard Admin Redesign

## Overview

Refatoração incremental da dashboard administrativa do InfoVISA, reorganizando a interface em: Barra de Resumo, seção "Minhas Ações" (abas), seção "Demandas do Setor" (abas, gestores/admin), e seção "Acompanhamento" (cards colapsáveis). Cada tarefa deixa a dashboard funcional — começamos pela estrutura, depois populamos com dados, e finalizamos com polish (persistência, error handling, testes).

## Tasks

- [x] 1. Create Blade components and restructure the dashboard layout
  - [x] 1.1 Create the `x-dashboard-summary-bar` Blade component
    - Create `resources/views/components/dashboard/summary-bar.blade.php`
    - Accept props: `$contadores` (associative array), `$urgencias` (array with urgent counts), `$isGestorOuAdmin` (boolean)
    - Render a horizontal bar with clickable counters: OS ativas, Docs pendentes assinatura, Docs com prazo, Processos diretos
    - For gestores/admin, add extra counters: Docs pendentes aprovação do setor, Cadastros pendentes
    - Each counter is an `<a href="#secao-id">` for smooth scroll to the corresponding section
    - Counters with value 0 get `opacity-50` class; counters with urgency items get red border/background
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

  - [x] 1.2 Create the `x-dashboard-tab-section` Blade component
    - Create `resources/views/components/dashboard/tab-section.blade.php`
    - Accept props: `$title`, `$icon`, `$tabs` (array of tab definitions with id, label, badge, urgent flag), `$sectionId`
    - Render a card with header and tab navigation buttons
    - Use Alpine.js `x-data="dashboardTabs('sectionId')"` for tab state management
    - Show spinner (`x-show="loadingTab === tabId"`) while loading tab data
    - Show error message with "Tentar novamente" button on AJAX failure (`x-show="errorTab === tabId"`)
    - Show empty state with icon and message when tab data is empty
    - Tabs with urgent items display a red badge with count next to the tab label
    - _Requirements: 2.1, 2.6, 9.2, 9.3_

  - [x] 1.3 Create the `x-dashboard-collapsible-card` Blade component
    - Create `resources/views/components/dashboard/collapsible-card.blade.php`
    - Accept props: `$title`, `$icon`, `$count`, `$cardId`
    - Render a card that starts collapsed by default, showing only title and item count
    - Use Alpine.js with `x-transition` for smooth expand/collapse animation
    - Persist expanded/collapsed state in `localStorage` with key `dashboard_card_{cardId}`
    - Fallback silently if `localStorage` is unavailable (start collapsed)
    - _Requirements: 4.1, 4.2, 4.5_

  - [x] 1.4 Restructure `dashboard.blade.php` with the new layout skeleton
    - Replace the current 3-column grid layout in `resources/views/admin/dashboard.blade.php`
    - Keep the existing header (saudação + data), modal de data de nascimento, and avisos do sistema sections
    - Add the `x-dashboard-summary-bar` component below avisos, above the grid
    - Add the OS atrasadas banner (existing) below summary bar, for gestores/admin only
    - Create the main grid: `lg:grid-cols-3` for gestores/admin (main 2/3 + sidebar 1/3), single column for técnicos
    - Place `x-dashboard-tab-section` for "Minhas Ações" in the main column with tabs: "Ordens de Serviço", "Assinaturas", "Prazos", "Processos"
    - Place `x-dashboard-tab-section` for "Demandas do Setor" in the sidebar (gestores/admin only) with tabs: "Aprovações Pendentes", "Processos do Setor", "Cadastros Pendentes"
    - Place "Acompanhamento" section below the grid, full width, with `x-dashboard-collapsible-card` for: "Processos Acompanhados", "Atalhos Rápidos", "Aniversariantes"
    - On mobile, all sections stack in single column: summary bar → banner → Minhas Ações → Demandas do Setor → Acompanhamento
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [x] 2. Implement Alpine.js tab management with lazy loading and persistence
  - [x] 2.1 Create the `dashboardTabs` Alpine.js component
    - Add the `dashboardTabs(sectionId)` function (inline in Blade or in a dedicated JS file)
    - Manage state: `activeTab`, `loadedTabs` (cache), `loadingTab`, `errorTab`, `tabData`
    - On `init()`, restore active tab from `localStorage` key `dashboard_tab_{sectionId}` or default to first tab
    - On `selectTab(tabId)`, save to `localStorage`, trigger `loadTab(tabId)` if not already loaded
    - `loadTab(tabId)`: fetch data from the appropriate AJAX endpoint, cache result in `tabData[tabId]`, set `loadedTabs[tabId] = true`
    - `retryTab(tabId)`: clear error state and re-fetch
    - Map tab IDs to endpoints: `os` → `/admin/dashboard/tarefas`, `assinaturas` → `/admin/dashboard/tarefas`, `prazos` → `/admin/dashboard/tarefas`, `processos` → `/admin/dashboard/processos-atribuidos?escopo=meu_direto`, `aprovacoes` → `/admin/dashboard/aprovacoes-pendentes`, `processos-setor` → `/admin/dashboard/processos-atribuidos?escopo=setor`, `cadastros` → existing data from server
    - Handle `localStorage` unavailability gracefully (try/catch around getItem/setItem)
    - Set fetch timeout to 15 seconds
    - _Requirements: 2.7, 9.1, 9.5_

- [x] 3. Refactor DashboardController to supply counters and simplify index()
  - [x] 3.1 Extract counter calculation into a dedicated method `calcularContadores()`
    - Add a private method `calcularContadores($usuario)` to `DashboardController`
    - Calculate: `os_ativas` (OS where user is in `tecnicos_ids`, status aberta/em_andamento), `docs_assinatura` (DocumentoAssinatura pendentes, excluding rascunhos), `docs_prazo` (DocumentoDigital with deadline ≤5 days, not finalized), `processos_diretos` (Processo where `responsavel_atual_id = user.id`, not archived/concluded)
    - For gestores/admin, also calculate: `docs_aprovacao_setor` (ProcessoDocumento + DocumentoResposta pendentes in user's setor), `cadastros_pendentes` (Estabelecimento pendentes filtered by competência)
    - Calculate urgency counts: `os_atrasadas` (OS >15 days past `data_fim`), `docs_vencidos` (documents with expired deadline), `docs_aprovacao_atrasados` (approval docs >5 days in licenciamento processes)
    - _Requirements: 1.1, 1.2, 1.5, 5.1, 5.2, 5.3, 5.4, 5.5_

  - [x] 3.2 Simplify the `index()` method to use `calcularContadores()` and return minimal data
    - Refactor `index()` to call `calcularContadores()` and pass `$contadores`, `$urgencias` arrays to the view
    - Keep existing logic for: avisos do sistema, aniversariantes, processos acompanhados, atalhos rápidos
    - Remove heavy data loading from `index()` that is now handled by AJAX (OS lists, process lists, document lists)
    - Pass `$isGestorOuAdmin`, `$escopoAniversariantes`, `$processos_acompanhados`, `$atalhos_rapidos` to the view
    - Ensure the view still receives all data needed for server-side rendered sections (summary bar, avisos, aniversariantes, acompanhamento cards)
    - _Requirements: 9.1_

  - [x] 3.3 Create the new `aprovacoesPendentes()` AJAX endpoint
    - Add method `aprovacoesPendentes()` to `DashboardController`
    - Register route: `GET /admin/dashboard/aprovacoes-pendentes` → `DashboardController@aprovacoesPendentes`
    - Extract logic from existing `tarefasPaginadas()` for documents/responses pending approval
    - Return JSON with documents and responses grouped by `processo_id`, including: processo number, estabelecimento name, document name, days pending, delay indicator (red badge if licenciamento >5 days)
    - Apply setor and competência filters based on user profile
    - Only accessible by gestores/admin (return 403 for técnicos)
    - Wrap in try/catch, log errors, return JSON error response on failure
    - _Requirements: 3.4, 3.6, 3.7, 5.2, 5.3_

- [x] 4. Checkpoint - Verify structural changes
  - Ensure all tests pass, ask the user if questions arise.
  - Verify the dashboard loads without errors for all 5 user profiles
  - Verify the summary bar displays correct counters
  - Verify tab navigation works and lazy loading fetches data correctly

- [x] 5. Populate tab content for "Minhas Ações" section
  - [x] 5.1 Implement the "Ordens de Serviço" tab content
    - In the tab panel for "os", render the list of OS from `tabData.os` (filtered from `tarefasPaginadas` response where `tipo === 'os'`)
    - Display each OS with: number, estabelecimento, técnicos, data_fim, urgency badge
    - Sort: overdue items first (past finalization deadline), then by `data_fim` ascending
    - Show finalization period badge with remaining days when OS is between `data_fim` and `data_fim + 15 days`
    - Show "expirado" badge when past `data_fim + 15 days`
    - Apply consistent color scheme: green (em dia), yellow (atenção), orange (urgente), red (atrasado)
    - _Requirements: 2.2, 10.1, 10.2, 10.5_

  - [x] 5.2 Implement the "Assinaturas" tab content
    - In the tab panel for "assinaturas", render documents pending signature and draft documents
    - Split into two visual subsections: "Pendentes de Assinatura" and "Rascunhos"
    - Display each document with: type name, processo number, estabelecimento, creation date
    - Data comes from `tarefasPaginadas` response filtered by `tipo === 'assinatura'` and `tipo === 'rascunho'`
    - _Requirements: 2.3_

  - [x] 5.3 Implement the "Prazos" tab content
    - In the tab panel for "prazos", render documents with deadlines
    - Sort: expired deadlines first, then by `data_vencimento` ascending
    - Display each document with: type name, processo number, deadline date, days remaining/overdue, urgency badge
    - Data comes from `tarefasPaginadas` response filtered by `tipo === 'prazo_documento'`
    - Apply urgency color scheme based on deadline proximity
    - _Requirements: 2.4, 10.1_

  - [x] 5.4 Implement the "Processos" tab content with AJAX pagination
    - In the tab panel for "processos", render processes under direct responsibility
    - Fetch from `/admin/dashboard/processos-atribuidos?escopo=meu_direto&page=N`
    - Display each process with: número_processo, estabelecimento, status badge, document progress indicator (e.g., "3/5"), "aguardando ciência" badge if applicable
    - Implement AJAX pagination with max 10 items per page, prev/next buttons
    - _Requirements: 2.5, 9.4, 10.3, 10.4_

- [x] 6. Populate tab content for "Demandas do Setor" section (gestores/admin only)
  - [x] 6.1 Implement the "Aprovações Pendentes" tab content
    - In the tab panel for "aprovacoes", render data from the new `aprovacoesPendentes` endpoint
    - Group documents by processo: show processo header with number and estabelecimento, then list pending documents/responses underneath
    - Highlight items with delay indicator (red badge + days count) for licenciamento documents pending >5 days
    - _Requirements: 3.4, 3.7_

  - [x] 6.2 Implement the "Processos do Setor" tab content with AJAX pagination
    - In the tab panel for "processos-setor", fetch from `/admin/dashboard/processos-atribuidos?escopo=setor&page=N`
    - Exclude processes that already appear in "Minhas Ações > Processos" (where `responsavel_atual_id = user.id`)
    - Display each process with: número_processo, estabelecimento, responsável atual, status badge
    - Implement AJAX pagination with max 10 items per page
    - _Requirements: 3.5, 9.4_

  - [x] 6.3 Implement the "Cadastros Pendentes" tab content
    - In the tab panel for "cadastros", render pending establishments filtered by user's competência
    - Display each establishment with: nome_fantasia/razão_social, CNPJ, data de cadastro, usuário externo
    - Data can be passed server-side from `index()` since the count is typically small
    - _Requirements: 3.6_

- [x] 7. Populate "Acompanhamento" section with collapsible cards
  - [x] 7.1 Implement the "Processos Acompanhados" collapsible card content
    - Inside the `x-dashboard-collapsible-card` for "processos-acompanhados", render the list of followed processes
    - Display: número_processo, nome do estabelecimento, status atual
    - Data passed server-side from `index()` via `$processos_acompanhados`
    - _Requirements: 4.3_

  - [x] 7.2 Implement the "Atalhos Rápidos" collapsible card content
    - Inside the `x-dashboard-collapsible-card` for "atalhos-rapidos", render user's quick links
    - Display: ícone, título, link (clickable)
    - Data passed server-side from `index()` via `$atalhos_rapidos`
    - _Requirements: 4.4_

  - [x] 7.3 Move the "Aniversariantes" section into a collapsible card
    - Migrate the existing aniversariantes UI from the current dashboard into a `x-dashboard-collapsible-card` in the "Acompanhamento" section
    - Keep existing logic: highlight today's birthdays, show month list when expanded
    - Filter by user scope: estadual sees estadual + admin colleagues; municipal sees same município + admin colleagues
    - Card starts collapsed by default; if someone has a birthday today, show a discrete notification in the card header
    - _Requirements: 4.1, 8.1, 8.2, 8.3_

- [x] 8. Checkpoint - Verify all sections populated and functional
  - Ensure all tests pass, ask the user if questions arise.
  - Verify all tabs load data correctly for each user profile
  - Verify "Demandas do Setor" is hidden for técnicos and visible for gestores/admin
  - Verify pagination works in "Processos" and "Processos do Setor" tabs
  - Verify collapsible cards expand/collapse and persist state

- [x] 9. Add alert banners, urgency indicators, and visual polish
  - [x] 9.1 Implement the OS atrasadas alert banner for gestores/admin
    - Display a red alert banner below avisos do sistema when there are OS with >15 days without closure
    - Show count of overdue OS and link to the full list
    - Only visible for gestores/admin profiles
    - _Requirements: 7.1_

  - [x] 9.2 Add urgency indicators to tab badges and summary bar
    - Add red badge to "Prazos" tab when there are expired documents (count of `docs_vencidos`)
    - Add red badge to "Aprovações Pendentes" tab when there are delayed approval docs (count of `docs_aprovacao_atrasados`)
    - Ensure summary bar counters reflect urgency with red styling when `urgencias` values > 0
    - _Requirements: 7.2, 7.3, 1.5_

  - [x] 9.3 Implement consistent urgency color scheme across all sections
    - Extract a shared helper function `classificarUrgencia(deadline)` usable in both PHP (controller) and JS (Alpine)
    - PHP: add a static method or helper that returns urgency level string based on deadline date
    - JS: add a function that classifies urgency based on days remaining
    - Apply consistently: green (em dia, >5 days), yellow (atenção, 1-5 days), orange (urgente, today), red (atrasado, past)
    - Use in all list items across all tabs
    - _Requirements: 10.1, 10.5_

  - [x] 9.4 Ensure avisos do sistema display correctly
    - Keep existing avisos rendering, ordered by tipo (urgente first) then by data de criação
    - Hide the avisos area completely (no empty space) when no active avisos exist
    - Display saudação personalizada (user name + current date) in the page header
    - _Requirements: 7.4, 7.5, 8.4_

- [ ] 10. Set up test infrastructure and write property-based tests
  - [ ]* 10.1 Set up Pest PHP test infrastructure for dashboard tests
    - Create `tests/Feature/Admin/DashboardTest.php` (or `tests/Unit/Dashboard/` for unit tests)
    - Ensure Pest PHP is properly configured (it's already in composer.json allow-plugins)
    - Create test helper for generating random dashboard data using Faker
    - Set up factory or helper methods for creating test users with different profiles (Admin, GestorEstadual, GestorMunicipal, TecnicoEstadual, TecnicoMunicipal)

  - [ ]* 10.2 Write property test for urgency classification (Property 1)
    - **Property 1: Urgency classification is deterministic and correct**
    - Test the `classificarUrgencia()` function with 100+ random deadline dates
    - Assert: past dates → "atrasado", today → "urgente", 1-5 days → "atencao", >5 days → "em_dia"
    - Assert determinism: same input always produces same output
    - **Validates: Requirements 1.5, 10.1**

  - [ ]* 10.3 Write property test for OS list ordering (Property 2)
    - **Property 2: OS list ordering preserves urgency-first invariant**
    - Generate random lists of OS with varying `data_fim` and finalization status
    - Assert: all overdue items appear before non-overdue items
    - Assert: within each group, items are ordered by `data_fim` ascending
    - **Validates: Requirements 2.2**

  - [ ]* 10.4 Write property test for documents with deadline ordering (Property 3)
    - **Property 3: Documents with deadline ordering preserves vencidos-first invariant**
    - Generate random lists of documents with varying `data_vencimento`
    - Assert: all expired-deadline documents appear before future-deadline documents
    - Assert: within each group, documents are ordered by `data_vencimento` ascending
    - **Validates: Requirements 2.4**

  - [ ]* 10.5 Write property test for pending approval grouping (Property 4)
    - **Property 4: Pending approval documents grouped by processo have consistent processo_id**
    - Generate random sets of documents with varying `processo_id`
    - Assert: every item within each group has the same `processo_id`
    - Assert: union of all groups equals the original set (no items lost or duplicated)
    - **Validates: Requirements 3.4**

  - [ ]* 10.6 Write property test for setor process exclusion (Property 5)
    - **Property 5: Setor processes exclude directly assigned processes**
    - Generate random user + process sets with mixed `responsavel_atual_id` and `setor_atual`
    - Assert: "Processos do Setor" list contains NO process where `responsavel_atual_id = user.id`
    - Assert: all items in the list have `setor_atual` matching user's setor codes
    - **Validates: Requirements 3.5**

  - [ ]* 10.7 Write property test for delay indicator logic (Property 6)
    - **Property 6: Delay indicator applies only to licenciamento documents older than 5 days**
    - Generate random documents with varying process types and creation dates
    - Assert: delay indicator shown iff `tipo === 'licenciamento'` AND `created_at` > 5 days ago
    - Assert: non-licenciamento documents NEVER show delay indicator
    - **Validates: Requirements 3.7**

  - [ ]* 10.8 Write property test for profile-based visibility filter (Property 7)
    - **Property 7: Profile-based visibility filter returns only in-scope data**
    - Generate random users (5 profiles) and random process sets
    - Assert: Admin sees all items; GestorEstadual sees only setor + estadual + direct; GestorMunicipal sees only setor + município + direct; Técnicos see only direct assignments
    - Assert: no out-of-scope item ever appears in filtered results
    - **Validates: Requirements 5.2, 5.3, 5.4**

  - [ ]* 10.9 Write property test for aniversariantes scope filter (Property 8)
    - **Property 8: Aniversariantes scope filter returns only colleagues within user's scope**
    - Generate random user sets with varying `nivel_acesso` and `municipio_id`
    - Assert: estadual users see only {GestorEstadual, TecnicoEstadual, Administrador}
    - Assert: municipal users see only {same município GestorMunicipal/TecnicoMunicipal} + Administrador
    - **Validates: Requirements 8.3**

  - [ ]* 10.10 Write property test for OS finalization period badge (Property 9)
    - **Property 9: OS finalization period badge shows correct remaining days**
    - Generate random OS with varying `data_fim` values
    - Assert: between `data_fim` and `data_fim + 15 days`, badge shows `15 - days_since_data_fim`
    - Assert: past `data_fim + 15 days`, badge shows "expirado"
    - Assert: remaining days consistent with calendar arithmetic
    - **Validates: Requirements 10.2**

- [x] 11. Final checkpoint - Full verification
  - Ensure all tests pass, ask the user if questions arise.
  - Verify dashboard works correctly for all 5 user profiles (Administrador, GestorEstadual, GestorMunicipal, TecnicoEstadual, TecnicoMunicipal)
  - Verify responsive layout: two columns on desktop for gestores, single column for técnicos, stacked on mobile
  - Verify all AJAX endpoints return correct data with proper competência filtering
  - Verify localStorage persistence for tab state and collapsible card state

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- The implementation is incremental: each task leaves the dashboard in a working state
- Existing AJAX endpoints (`tarefasPaginadas`, `processosAtribuidosPaginados`, `ordensServicoVencidas`) are reused; only `aprovacoesPendentes` is new
- The controller is refactored, not rewritten — existing private methods are preserved
