# 02. Requisitos

Requisitos funcionais (RF) e não funcionais (RNF) do MyTask, com rastreabilidade até regra de negócio, endpoint e teste automatizado. Os IDs são estáveis e podem ser citados no texto do TCC.

Legenda da coluna **Teste**: `B:` teste de feature do backend (`backend/tests/Feature`), `F:` teste do frontend (`frontend/tests`), `Nenhum` sem teste automatizado.

## 1. Requisitos funcionais

### 1.1 Acesso e conta

| ID | Requisito | Regras | Endpoint(s) | Teste |
|----|-----------|--------|-------------|-------|
| RF-01 | Permitir que um visitante crie conta informando apenas apelido, e-mail e senha. | RN-01 | `POST /auth/register` | B: `AuthAndRolesTest::test_user_can_register_login_read_profile_and_logout` |
| RF-02 | Permitir login por e-mail e senha, retornando um token JWT. | RN-17 | `POST /auth/login` | B: idem RF-01; F: `auth-dashboard.spec.js` |
| RF-03 | Permitir logout, invalidando o token atual. | | `POST /auth/logout` | B: idem RF-01 |
| RF-04 | Separar estritamente as funcionalidades de participante e pesquisador. | RN-02, RN-03, RN-04 | middleware `role` | B: `AuthAndRolesTest::test_roles_are_strictly_separated`, `ExampleTest::test_only_login_and_register_are_public`; F: `admin-dashboard.spec.js` |

### 1.2 Personal Kanban

| ID | Requisito | Regras | Endpoint(s) | Teste |
|----|-----------|--------|-------------|-------|
| RF-05 | Criar, listar, editar e excluir tarefas de rotina no Kanban Geral. | RN-06, RN-26 | `GET/POST /kanban/tasks`, `PUT/DELETE /tasks/{id}` | F: `kanban-board.spec.js` (componente) |
| RF-06 | Mover tarefas entre `TODO`, `IN_PROGRESS` e `DONE` (arrastar e soltar). | RN-06, RN-22 | `PUT /tasks/{id}` | Nenhum |
| RF-07 | Impedir mais de 3 tarefas simultâneas em andamento, com mensagem educativa. | RN-07 | `PUT /tasks/{id}` | B: `BusinessRulesTest::test_kanban_wip_limit_is_three_tasks` |

### 1.3 Scrum Pessoal

| ID | Requisito | Regras | Endpoint(s) | Teste |
|----|-----------|--------|-------------|-------|
| RF-08 | Criar, listar, editar e excluir projetos. | RN-05, RN-08 | `/projects` | B: `BusinessRulesTest::test_user_cannot_access_another_users_project` |
| RF-09 | Registrar tarefas no backlog de um projeto. | RN-09, RN-10 | `GET/POST /projects/{id}/backlog` | Nenhum |
| RF-10 | Planejar sprints com nome, objetivo, data de início e data de fim. | RN-11, RN-12, RN-13 | `/projects/{id}/sprints`, `/sprints/{id}` | Nenhum |
| RF-11 | Alocar tarefas do backlog em sprints planejadas ou ativas. | RN-23, RN-24, RN-25 | `PUT /tasks/{id}/migrate` | Nenhum |
| RF-12 | Ativar uma sprint, permitindo apenas uma ativa por projeto. | RN-14, RN-15, RN-16 | `PUT /sprints/{id}/status` | B: `BusinessRulesTest::test_only_one_sprint_can_be_active_per_project` |
| RF-13 | Executar a sprint ativa em quadro Kanban com WIP próprio de 3. | RN-20, RN-21, RN-22 | `GET /sprints/{id}/tasks`, `PUT /tasks/{id}` | Nenhum |
| RF-14 | Encerrar sprints manualmente e automaticamente após o prazo. | RN-17, RN-18 | `PUT /sprints/{id}/status`, login, `GET /projects/{id}` | B: `AdminAndAutoCloseTest::test_login_automatically_closes_expired_active_sprints` |
| RF-15 | Migrar tarefas pendentes de uma sprint finalizada para a sprint ativa, reiniciando-as em `TODO`. | RN-23, RN-24, RN-25 | `PUT /tasks/{id}/migrate` | B: `BusinessRulesTest::test_pending_task_migration_resets_status_to_todo` |
| RF-16 | Excluir sprints devolvendo suas tarefas ao backlog. | RN-19 | `DELETE /sprints/{id}` | Nenhum |

### 1.4 Painel do participante

| ID | Requisito | Regras | Endpoint(s) | Teste |
|----|-----------|--------|-------------|-------|
| RF-17 | Exibir a distribuição atual das tarefas do Kanban Geral (gráfico de rosca). | | `GET /dashboard/analytics` | B: `ParticipantDashboardAnalyticsTest` (4 testes) |
| RF-18 | Exibir as tarefas concluídas por dia nos últimos 7 dias (gráfico de linha). | | `GET /dashboard/analytics` | B: idem RF-17 |
| RF-19 | Convidar o participante a responder o questionário TAM a partir do 15º dia de cadastro. | | (front, link externo) | Nenhum |

### 1.5 Painel do pesquisador

| ID | Requisito | Regras | Endpoint(s) | Teste |
|----|-----------|--------|-------------|-------|
| RF-20 | Exibir contadores agregados da base (participantes, projetos, tarefas, sprints). | RN-28 | `GET /admin/summary` | Nenhum |
| RF-21 | Exibir tempo médio de ciclo (global, Kanban, Scrum) e taxa média de conclusão de sprints. | RN-29, RN-30 | `GET /admin/analytics` | Nenhum |
| RF-22 | Listar e buscar participantes por apelido ou e-mail. | RN-28 | `GET /admin/users` | F: `admin-dashboard.spec.js` |
| RF-23 | Redefinir a senha de um participante para o padrão `123456`. | RN-27 | `PUT /admin/users/{id}/reset-password` | Nenhum |
| RF-24 | Exportar CSV pseudonimizado para análise estatística. | RN-31, RN-32 | `GET /admin/export` | B: `AdminAndAutoCloseTest::test_admin_export_is_pseudonymized` |

## 2. Requisitos não funcionais

| ID | Categoria | Requisito | Como é atendido |
|----|-----------|-----------|-----------------|
| RNF-01 | Segurança | Autenticação stateless por JWT no cabeçalho `Authorization: Bearer`, com validade de 60 min e invalidação no logout. | `tymon/jwt-auth`, blacklist na tabela `cache` |
| RNF-02 | Segurança | Senhas armazenadas apenas como hash. | cast `hashed` no Model `User` |
| RNF-03 | Segurança | Limitar tentativas de login e cadastro. | `throttle:10,1` (10 requisições por minuto) nas rotas `/auth/register` e `/auth/login` |
| RNF-04 | Segurança | Isolamento de dados por usuário (multi-tenant por dono). | RN-05: consultas a partir do usuário + Policies |
| RNF-05 | Privacidade (LGPD) | Coletar o mínimo de dados pessoais e exportar dados de pesquisa pseudonimizados. | [11](11-pesquisa-e-lgpd.md) |
| RNF-06 | Confiabilidade | Regras de concorrência (WIP, sprint ativa, migração) devem ser consistentes sob requisições simultâneas. | Transações com `lockForUpdate` |
| RNF-07 | Exatidão temporal | Todos os registros de tempo no fuso `America/Sao_Paulo`. | Configuração em Laravel, MySQL e Nuxt |
| RNF-08 | Usabilidade | Interface responsiva (sidebar no desktop, barra inferior no mobile), tema claro/escuro e feedback imediato (toast, confirmação). | [08](08-frontend.md) |
| RNF-09 | Acessibilidade | Rótulos, `aria-*`, *skip link* e navegação por teclado nos cards. | [08](08-frontend.md) |
| RNF-10 | Portabilidade | Ambiente reprodutível em qualquer máquina com Docker, sem PHP/Node/MySQL locais. | Docker Compose ([09](09-ambiente-de-desenvolvimento.md)) |
| RNF-11 | Manutenibilidade | Separação em camadas (Request, Controller, Policy, Service, Resource) e padronização automática de código. | Pint, Larastan, ESLint, Prettier |
| RNF-12 | Operação | Deploy de produção isolado, com TLS e sem exposição do banco. | [10](10-deploy.md) |
