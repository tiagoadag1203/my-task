# 08. Frontend (Nuxt SPA)

SPA em Nuxt sem SSR. Versões em [01](01-visao-geral.md#4-stack-e-versões-fonte-única); endpoints consumidos em [06](06-api.md). A API é sempre a fonte da verdade para autorização e regras: o frontend apenas reflete as regras na interface (ex.: bloqueia visualmente o WIP).

## 1. Organização do código

```text
frontend/
├── nuxt.config.ts          ssr: false, módulos, runtimeConfig, script anti-flash do tema, fontes
├── app/
│   ├── app.vue             Casca: <NuxtPage> + hosts globais (toast, confirm, modal, loader)
│   ├── pages/              index, login, register, dashboard, em-breve
│   ├── layouts/            default, dashboard
│   ├── middleware/         auth.global.js, guest.js
│   ├── plugins/            api.js (cliente HTTP), theme.client.js
│   ├── stores/auth.js      Única store Pinia (sessão)
│   ├── repositories/       auth, dashboard, tasks, projects, sprints, admin (+ index)
│   ├── composables/        useToast, useConfirm, useModal, useLoader, useTheme, useChartTheme
│   └── components/
│       ├── ui/             Primitivas do design system (Ui*)
│       ├── landing/        Seções da página inicial
│       ├── auth/           AuthShell (casca de login/cadastro)
│       ├── dashboard/      Sidebar, navegação mobile, aba Início, Kanban Geral, TAM, BaseModal, api.js
│       ├── kanban/         KanbanBoard, KanbanColumn, TaskCard, TaskFormModal, WipIndicator
│       ├── projects/       ProjectsTab, ProjectFormModal, ProjectWorkspace
│       ├── backlog/        BacklogPanel
│       ├── sprints/        SprintsPanel, SprintBoard, SprintFormModal
│       ├── admin/          ResearcherOverviewTab, ResearcherParticipantsTab
│       └── global/         Hosts globais e GlobalThemeToggle
└── tests/
    ├── unit/               Vitest
    └── e2e/                Playwright
```

## 2. Fluxo de dados

```text
Componente de aba
  → useAsyncData (leituras)
  → $api.<repositório>.<método>()      repositórios são funções puras, sem composables do Nuxt
  → $fetch (baseURL /api, Authorization: Bearer)
  → em 401: limpa a sessão e navega para /login
```

- `components/dashboard/api.js` reúne utilitários de normalização das respostas (`unwrapData`, `unwrapList`, `paginationOf`, `errorMessage`), usados por várias abas.
- O mesmo conjunto de componentes de quadro (`KanbanBoard`, `KanbanColumn`, `TaskCard`) atende o Kanban Geral e a sprint.

## 3. Sessão e estado

Store `stores/auth.js`:

| Campo | Descrição |
|-------|-----------|
| `token` | JWT no cookie `mytask_token` (`sameSite: lax`, `secure` em produção) |
| `user` | Objeto retornado pela API (`id`, `nickname`, `email`, `role`, `created_at`) |
| `expires_at` | Calculado a partir de `expires_in` |
| `isAuthenticated`, `isUser`, `isAdmin` | Getters derivados |

`hydrate()` chama `/auth/me` quando há token e limpa a sessão se falhar. O logout chama a API e depois limpa o cookie.

## 4. Rotas e proteção

| Rota | Acesso | Layout | Função |
|------|--------|--------|--------|
| `/` | Público | default | Página inicial (landing) |
| `/login` | Visitante (`guest`) | próprio | Entrar |
| `/register` | Visitante (`guest`) | próprio | Criar conta |
| `/dashboard` | Autenticado (`auth.global`) | dashboard | Painel conforme o papel |
| `/em-breve` | Público | default | Placeholder, fora do fluxo principal |

- `auth.global.js`: em qualquer rota `/dashboard*`, hidrata a sessão e redireciona para `/login` se não autenticado.
- `guest.js`: usuário autenticado em `/login` ou `/register` vai para `/dashboard`.

### 4.1 Navegação interna do dashboard

Rota única com abas por *query string* (decisão AD-08). Conteúdo em `KeepAlive` para preservar o estado ao trocar de aba.

| Papel | Query | Aba | Componente |
|-------|-------|-----|------------|
| `user` | `?tab=inicio` | Início | `DashboardHomeTab` |
| `user` | `?tab=tarefas` | Minhas tarefas | `GeneralKanbanTab` |
| `user` | `?tab=projetos` | Projetos | `ProjectsTab` |
| `user` | `?tab=projetos&project=12` | Workspace do projeto | `ProjectWorkspace` |
| `user` | `?tab=projetos&project=12&sprint=34` | Sprint selecionada | `SprintsPanel` + `SprintBoard` |
| `admin` | `?tab=visao-geral` | Visão geral | `ResearcherOverviewTab` |
| `admin` | `?tab=participantes` | Participantes | `ResearcherParticipantsTab` |

Aba inválida para o papel é substituída pela primeira permitida (`router.replace`).

## 5. Telas do participante

### 5.1 Landing (`/`)

Navbar (âncoras, Entrar, Criar conta, menu mobile), Hero ("Menos caos. Mais foco." com mock de sprint, Kanban e indicador WIP 2/3), Como funciona (clareza, menos sobrecarga, evolução contínua), Personal Kanban, Scrum Pessoal, CTA final e rodapé acadêmico. SEO via `useSeoMeta`.

### 5.2 Login e cadastro

`AuthShell` com validação local, erros da API e estado de carregamento. Cadastro pede apelido (até 50), e-mail, senha (mínimo 8) e confirmação; aplica a sessão e abre o dashboard.

### 5.3 Início

Consome `GET /dashboard/analytics`: gráfico de rosca (A fazer, Em progresso, Concluídas do Kanban Geral) e gráfico de linha (concluídas nos últimos 7 dias). Estados de carregamento (skeleton), erro com nova tentativa e vazio. Cores seguem o tema (`useChartTheme`).

### 5.4 Minhas tarefas (Kanban Geral)

- Três colunas com arrastar e soltar; criar, editar e excluir por modal com confirmação.
- WIP: a coluna Em progresso exibe `WIP n/3` e o frontend bloqueia o quarto movimento antes de chamar a API.
- Atualização otimista do status com rollback se a API falhar; feedback por toast e alerta.

### 5.5 Projetos e workspace

- Lista paginada em cards (nome, descrição, contagem de sprints e tarefas); criar, editar, excluir com confirmação; estado vazio.
- Workspace: cabeçalho com totais, **Backlog** à esquerda (criar, editar, excluir, alocar em sprint `PLANNING`/`ACTIVE`) e **Sprints** à direita (criar; em planejamento: editar, ativar, excluir; ativa: finalizar).
- Sprint selecionada via `?sprint=`; sem seleção, usa a ativa ou a primeira.
- Atualização periódica a cada 60 s e ao recuperar o foco da janela (captura o auto-close).
- Quadro da sprint: somente a sprint ativa permite mover, editar e excluir; em planejamento fica bloqueado; finalizada é somente leitura, com opção de migrar pendências para a sprint ativa.

### 5.6 Convite TAM

`TamNudgeCard` aparece para participantes quando `NUXT_PUBLIC_TAM_FORM_URL` está definida e o cadastro tem 15 dias ou mais. O descarte é guardado no `localStorage` por usuário. Detalhes em [11](11-pesquisa-e-lgpd.md#4-questionário-pós-uso-tam).

## 6. Telas do pesquisador

### 6.1 Visão geral

Carrega `GET /admin/summary` e `GET /admin/analytics` em paralelo. Cards (participantes, projetos, tarefas, concluídas, sprints ativas, sprints finalizadas), gráfico de barras do tempo médio de ciclo (global, Kanban, Scrum, em horas) e rosca da taxa média de conclusão de sprints. Botão **Exportar CSV** baixa `mytask-export-AAAA-MM-DD.csv`, com aviso de que o arquivo é pseudonimizado.

### 6.2 Participantes

Tabela paginada (ID, apelido, e-mail, data de inscrição), busca com *debounce* de 350 ms e ação **Resetar senha** (padrão `123456`) com confirmação.

## 7. Aspectos transversais

| Tema | Implementação |
|------|---------------|
| Tema claro/escuro | Preferência `light`, `dark` ou `system` em `localStorage` (`mytask-theme`); script no `<head>` aplica a classe `dark` antes da pintura. Toggle no dashboard; landing e autenticação em visual claro. |
| Feedback | Toast (sucesso, erro, aviso, info; 5 s), confirmação baseada em Promise (`useConfirm().ask`), modal e loader globais. |
| Design system | `UiButton`, `UiInput`, `UiTextarea`, `UiSelect`, `UiCard`, `UiBadge`, `UiModal`, `UiMenu`, `UiEmptyState`, `UiAvatar`, `UiIcon`, `UiSpinner`, `UiProgress`. Acento `#00ffbf`, bordas pretas, sombras deslocadas; fontes Rubik, Spinnaker e Hind; tokens CSS `--app-*`; cores de status (A fazer laranja, Em progresso azul, Concluído acento). |
| Acessibilidade | *Skip link*, rótulos, `role="alert"`/`status`, `aria-label` em menus e colunas, cards de projeto e sprint acionáveis por Enter/Espaço, `focus-visible` nos CTAs. |
| Responsividade | Sidebar a partir de `lg`; barra de navegação inferior no mobile; header fixo com saudação. |

## 8. Variáveis de ambiente

| Variável | Padrão | Uso |
|----------|--------|-----|
| `NUXT_PUBLIC_API_BASE` | `/api` | Base do cliente HTTP |
| `NUXT_PUBLIC_APP_TIMEZONE` | `America/Sao_Paulo` | Formatação de datas |
| `NUXT_PUBLIC_TAM_FORM_URL` | vazio | URL do questionário; vazio desativa o convite |

Por ser SPA, os valores entram no bundle no build (AD-02).

## 9. Testes

| Tipo | Arquivo | Cobre |
|------|---------|-------|
| Unitário | `dashboard-api.spec.js` | Normalização de respostas (`unwrapData`, paginação) |
| Unitário | `repositories.spec.js` | Repositórios chamam os endpoints corretos |
| Unitário | `kanban-board.spec.js` | Comportamento do quadro Kanban |
| E2E | `auth-dashboard.spec.js` | Login do participante e preservação das abas na URL |
| E2E | `admin-dashboard.spec.js` | Navegação e dados exclusivos do pesquisador |

Comandos em [09](09-ambiente-de-desenvolvimento.md#5-qualidade).
