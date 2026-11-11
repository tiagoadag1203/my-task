# 07. Backend (Laravel)

API REST stateless em Laravel. Versões em [01](01-visao-geral.md#4-stack-e-versões-fonte-única); contrato HTTP em [06](06-api.md); regras em [03](03-regras-de-negocio.md).

## 1. Organização do código

```text
backend/app/
├── Enums/            RoleEnum, SprintStatus, TaskStatus
├── Http/
│   ├── Controllers/  Auth/AuthController, ProjectController, SprintController,
│   │                 TaskController, DashboardController, Admin/DashboardController,
│   │                 Admin/UserController
│   ├── Middleware/   CheckRole
│   ├── Requests/     12 FormRequests (validação e normalização)
│   └── Resources/    UserResource, ProjectResource, SprintResource, TaskResource
├── Models/           User, Project, Sprint, Task
├── Policies/         ProjectPolicy, SprintPolicy, TaskPolicy
└── Services/         TaskService, SprintService, AnalyticsService,
                      ParticipantAnalyticsService
```

| Camada | Responsabilidade | Regra de projeto |
|--------|------------------|------------------|
| FormRequest | Validar e normalizar entrada (ex.: e-mail em minúsculas) | Controllers não validam manualmente |
| Controller | Coordenar HTTP, buscar o recurso a partir do usuário autenticado, autorizar | Sem regra de negócio |
| Policy | Confirmar que o recurso pertence ao usuário | Obrigatória para Project, Sprint e Task |
| Service | Regras de negócio, transações e bloqueios | Toda regra `RN-xx` vive aqui |
| Model | Relacionamentos e *casts* (enums, datas, `hashed`) | |
| Resource | Contrato de saída | Nunca retornar Model cru |

### 1.1 Serviços

| Serviço | Responsabilidades | Regras |
|---------|-------------------|--------|
| `TaskService` | Criar tarefa (Kanban/backlog), atualizar status com timestamps, WIP, migração, exclusão | RN-06, RN-07, RN-09, RN-10, RN-20 a RN-26 |
| `SprintService` | Criar, editar, mudar status, excluir, auto-close | RN-11 a RN-19 |
| `AnalyticsService` | Resumo, métricas e exportação CSV do pesquisador | RN-28 a RN-32 |
| `ParticipantAnalyticsService` | Distribuição do Kanban e conclusões dos últimos 7 dias | RF-17, RF-18 |

### 1.2 Isolamento por usuário

Toda consulta parte do usuário autenticado, por exemplo `$user->projects()->findOrFail($id)` e, para sprints, `Sprint::whereHas('project', fn ($q) => $q->where('user_id', $user->id))`. Um ID de outro usuário resulta em `404`. As Policies são uma segunda barreira (defesa em profundidade).

### 1.3 Concorrência

Operações que dependem de contagem ou estado são executadas em `DB::transaction` com `lockForUpdate`: atualização de tarefa (WIP), migração, mudança de status de sprint, edição e exclusão de sprint.

## 2. Autenticação e autorização

- Guard padrão `api` com driver JWT (`tymon/jwt-auth`).
- Token com validade `JWT_TTL` (padrão 60 min). Não há endpoint de renovação (*refresh*); ao expirar, o frontend recebe `401` e redireciona ao login.
- Logout invalida o token pela blacklist, que usa o cache em banco (`CACHE_STORE=database`, tabela `cache`).
- `CheckRole` (alias `role`) compara o papel exigido com `users.role`; papel diferente gera `403`.
- `trustProxies('*')` para respeitar `X-Forwarded-Proto` atrás do Nginx.

| Grupo de rotas | Middlewares |
|----------------|-------------|
| `/auth/register`, `/auth/login` | `throttle:10,1` |
| `/auth/me`, `/auth/logout`, `/health` | `auth:api` |
| Operacionais (participante) | `auth:api`, `role:user` |
| `/admin/*` | `auth:api`, `role:admin` |

## 3. Tratamento de erros

Configurado em `bootstrap/app.php`: todas as respostas de erro são JSON e mensagens padronizadas em português.

| Exceção | Código | Mensagem |
|---------|--------|----------|
| `AuthenticationException` | 401 | `Não autenticado.` |
| `AuthorizationException` | 403 | `Acesso negado.` |
| `ValidationException` | 422 | `Os dados informados são inválidos.` + `errors` |
| `ModelNotFoundException` | 404 | `Recurso não encontrado.` |
| `NotFoundHttpException` | 404 | `Rota não encontrada.` |

Regras de negócio violadas lançam `ValidationException::withMessages([...])`, por isso também chegam como `422`.

## 4. Configuração relevante

| Variável | Uso | Padrão em dev |
|----------|-----|---------------|
| `APP_TIMEZONE` | Fuso da aplicação | `America/Sao_Paulo` |
| `AUTH_GUARD` | Guard padrão | `api` |
| `JWT_SECRET` | Assinatura dos tokens (gerar com `php artisan jwt:secret`) | vazio até gerar |
| `JWT_TTL` | Validade do token em minutos | `60` |
| `CACHE_STORE` | Necessário para a blacklist JWT | `database` |
| `ADMIN_NICKNAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Administrador criado pelo seeder | `Administrador`, `admin@mytask.local`, `change-me` |
| `DB_*` | Conexão MySQL (host = serviço `mysql`) | `mysql`, `3306`, `mytask`, `root`, sem senha |

Variáveis de produção em [10](10-deploy.md#4-variáveis-de-ambiente).

## 5. Testes automatizados

Executados com PHPUnit (`php artisan test`), em `backend/tests/Feature`:

| Arquivo | Testes | Cobre |
|---------|--------|-------|
| `AuthAndRolesTest` | 2 | Fluxo cadastro → login → perfil → logout; separação de papéis |
| `BusinessRulesTest` | 4 | WIP do Kanban (RN-07); uma sprint ativa (RN-15); acesso cruzado (RN-05); migração com reset (RN-25) |
| `AdminAndAutoCloseTest` | 2 | Auto-close no login (RN-17); CSV pseudonimizado (RN-31) |
| `ParticipantDashboardAnalyticsTest` | 4 | Autenticação e papel exigidos; distribuição só do Kanban Geral; 7 dias com zeros e todos os contextos; virada de dia UTC x São Paulo |
| `ExampleTest` (Feature) | 1 | Apenas login e cadastro são públicos (`/health` e `/projects` exigem token) |

Total: 13 testes de feature. `tests/Unit/ExampleTest.php` é resquício do esqueleto do Laravel (`test_that_true_is_true`), e o `ExampleTest` de Feature merece um nome que descreva o que testa.

Comandos de qualidade em [09](09-ambiente-de-desenvolvimento.md#5-qualidade).
