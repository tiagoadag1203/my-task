# 06. Referência da API

Contrato HTTP da API Laravel. As regras de negócio citadas (`RN-xx`) estão em [03](03-regras-de-negocio.md).

## 1. Convenções

| Item | Valor |
|------|-------|
| URL base | `/api` (sem prefixo de versão) |
| Formato | `Content-Type: application/json`; enviar também `Accept: application/json` |
| Autenticação | `Authorization: Bearer <token>`; públicas apenas `POST /auth/register` e `POST /auth/login` |
| Datas de sprint | `YYYY-MM-DD` |
| Timestamps | ISO 8601 com deslocamento, fuso `America/Sao_Paulo` (ex.: `2026-08-27T14:03:12-03:00`) |
| Recurso único | Envolvido em `data` (`{"data": {...}}`) |
| Coleção simples | `{"data": [...]}` |
| Coleção paginada | `{"data": [...], "links": {...}, "meta": {...}}`, 15 itens por página, parâmetro `?page=` |
| Limite de taxa | `/auth/register` e `/auth/login`: 10 requisições por minuto (excedente: `429`) |

### 1.1 Códigos de resposta

| Código | Quando |
|--------|--------|
| `200` | Consulta ou atualização bem-sucedida |
| `201` | Criação (cadastro, projeto, sprint, tarefa) |
| `204` | Exclusão (corpo vazio) |
| `401` | Token ausente/inválido/expirado ou credenciais incorretas |
| `403` | Papel incompatível com a rota ou Policy negada |
| `404` | Rota inexistente ou recurso que não existe **para o usuário autenticado** (RN-05) |
| `422` | Erro de validação ou violação de regra de negócio |
| `429` | Limite de tentativas de login/cadastro excedido |
| `500` | Erro interno não tratado |

### 1.2 Formato de erro

```text
// 422
{
  "message": "Os dados informados são inválidos.",
  "errors": { "status": ["Já existe uma Sprint ativa neste projeto."] }
}

// 401 / 403 / 404
{ "message": "Não autenticado." }
{ "message": "Acesso negado." }
{ "message": "Recurso não encontrado." }   // ou "Rota não encontrada."
```

Violações de regra de negócio também usam `422` com a chave do campo envolvido (`status` ou `current_sprint_id`).

## 2. Objetos retornados

**User**

```json
{ "id": 1, "nickname": "participante", "email": "p@example.com", "role": "user", "created_at": "2026-08-20T10:00:00-03:00" }
```

**Project**

```json
{ "id": 12, "name": "TCC", "description": "Projeto de conclusão", "sprints_count": 2, "tasks_count": 9,
  "created_at": "...", "updated_at": "..." }
```

`sprints_count` e `tasks_count` aparecem apenas em `GET /projects` e `GET /projects/{id}`.

**Sprint**

```json
{ "id": 34, "project_id": 12, "name": "Sprint 1", "goal": "Finalizar o capítulo inicial",
  "started_at": "2026-08-24", "ended_at": "2026-08-31", "status": "ACTIVE", "tasks_count": 5,
  "created_at": "...", "updated_at": "..." }
```

`tasks_count` aparece na listagem e em `GET /sprints/{id}`.

**Task**

```json
{ "id": 99, "project_id": 12, "sprint_id": 34, "title": "Escrever introdução", "description": null,
  "status": "IN_PROGRESS", "started_at": "2026-08-25T09:12:00-03:00", "completed_at": null,
  "created_at": "...", "updated_at": "..." }
```

## 3. Mapa de endpoints

| Método | Rota | Papel | Resumo |
|--------|------|-------|--------|
| POST | `/auth/register` | público | Cadastro de participante |
| POST | `/auth/login` | público | Login (dispara auto-close) |
| GET | `/auth/me` | ambos | Usuário autenticado |
| POST | `/auth/logout` | ambos | Invalida o token |
| GET | `/health` | ambos | Verificação autenticada |
| GET | `/dashboard/analytics` | user | Gráficos do participante |
| GET | `/projects` | user | Lista paginada de projetos |
| POST | `/projects` | user | Cria projeto |
| GET | `/projects/{project}` | user | Detalha projeto (dispara auto-close) |
| PUT | `/projects/{project}` | user | Atualiza projeto |
| DELETE | `/projects/{project}` | user | Exclui projeto (cascata) |
| GET | `/projects/{project}/sprints` | user | Lista sprints (dispara auto-close) |
| POST | `/projects/{project}/sprints` | user | Cria sprint |
| GET | `/sprints/{sprint}` | user | Detalha sprint |
| PUT | `/sprints/{sprint}` | user | Edita sprint em `PLANNING` |
| DELETE | `/sprints/{sprint}` | user | Exclui sprint (tarefas voltam ao backlog) |
| PUT | `/sprints/{sprint}/status` | user | Ativa ou finaliza |
| GET | `/kanban/tasks` | user | Tarefas do Kanban Geral |
| POST | `/kanban/tasks` | user | Cria tarefa no Kanban Geral |
| GET | `/projects/{project}/backlog` | user | Tarefas do backlog |
| POST | `/projects/{project}/backlog` | user | Cria tarefa no backlog |
| GET | `/sprints/{sprint}/tasks` | user | Tarefas da sprint |
| PUT | `/tasks/{task}` | user | Atualiza tarefa e status |
| DELETE | `/tasks/{task}` | user | Exclui tarefa |
| PUT | `/tasks/{task}/migrate` | user | Aloca ou migra tarefa para sprint |
| GET | `/admin/summary` | admin | Contadores agregados |
| GET | `/admin/analytics` | admin | Tempo de ciclo e conclusão de sprints |
| GET | `/admin/users` | admin | Lista paginada de participantes |
| PUT | `/admin/users/{user}/reset-password` | admin | Redefine senha para `123456` |
| GET | `/admin/export` | admin | Download do CSV pseudonimizado |

Fora de `/api`, o Laravel também expõe `GET /up` (verificação de saúde pública do framework). Ela só é acessível se o proxy a encaminhar ao backend, o que não ocorre na configuração atual do Nginx.

## 4. Autenticação

### POST `/auth/register`

```json
{ "nickname": "participante", "email": "participante@example.com", "password": "password123" }
```

Validação: `nickname` obrigatório, até 50; `email` obrigatório, válido, até 255, único (convertido para minúsculas); `password` de 8 a 255. Qualquer `role` enviado é ignorado (RN-01).

Resposta `201`:

```json
{
  "access_token": "<JWT>",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": { "id": 1, "nickname": "participante", "email": "participante@example.com", "role": "user", "created_at": "..." }
}
```

> Atenção: aqui `user` vem **sem** o envelope `data`, ao contrário de `GET /auth/me`.

### POST `/auth/login`

```json
{ "email": "participante@example.com", "password": "password123" }
```

`200` no mesmo formato do cadastro. Credenciais inválidas: `401` com `{"message": "Credenciais inválidas."}`. Executa o auto-close das sprints expiradas do usuário (RN-17).

### GET `/auth/me`

`200` com `{"data": User}`.

### POST `/auth/logout`

`200` com `{"message": "Logout realizado com sucesso."}`. O token entra na blacklist.

### GET `/health`

`200` com `{"status": "ok"}`. Exige token.

## 5. Dashboard do participante

### GET `/dashboard/analytics`

```json
{
  "data": {
    "task_distribution": { "todo": 3, "in_progress": 2, "done": 5, "total": 10 },
    "weekly_completed": [
      { "date": "2026-08-21", "count": 0 },
      { "date": "2026-08-22", "count": 1 },
      { "date": "2026-08-23", "count": 0 },
      { "date": "2026-08-24", "count": 2 },
      { "date": "2026-08-25", "count": 0 },
      { "date": "2026-08-26", "count": 1 },
      { "date": "2026-08-27", "count": 1 }
    ]
  }
}
```

- `task_distribution`: somente tarefas do **Kanban Geral** do usuário (`project_id` e `sprint_id` nulos).
- `weekly_completed`: sempre 7 itens (hoje e os 6 dias anteriores), do mais antigo para o mais recente, incluindo dias com zero. Conta tarefas de **todos os contextos** pelo `completed_at`, agrupando por data no fuso `America/Sao_Paulo`.

## 6. Projetos

| Endpoint | Corpo | Resposta |
|----------|-------|----------|
| `GET /projects` | | `200`, paginado (15), mais recentes primeiro, com contadores |
| `POST /projects` | `{"name": "TCC", "description": "opcional"}` | `201`, `{"data": Project}` |
| `GET /projects/{project}` | | `200`, com contadores. Executa auto-close (RN-17) |
| `PUT /projects/{project}` | `{"name": "...", "description": "..."}` (`name` obrigatório) | `200` |
| `DELETE /projects/{project}` | | `204`, cascata de sprints e tarefas (RN-08) |

Validação: `name` obrigatório, até 255; `description` opcional.

## 7. Sprints

| Endpoint | Corpo | Resposta |
|----------|-------|----------|
| `GET /projects/{project}/sprints` | | `200`, lista **não paginada**, ordenada por `started_at` decrescente, com `tasks_count`. Executa auto-close |
| `POST /projects/{project}/sprints` | ver abaixo | `201`, nasce `PLANNING` (RN-11) |
| `GET /sprints/{sprint}` | | `200`, com `tasks_count` |
| `PUT /sprints/{sprint}` | mesmo corpo da criação (todos obrigatórios exceto `goal`) | `200`; `422` se não estiver em `PLANNING` (RN-12) |
| `DELETE /sprints/{sprint}` | | `204`; tarefas voltam ao backlog com timestamps limpos (RN-19) |
| `PUT /sprints/{sprint}/status` | `{"status": "ACTIVE"}` ou `{"status": "FINISHED"}` | `200`; `422` em transição inválida ou segunda sprint ativa (RN-14, RN-15) |

Corpo de criação/edição:

```json
{ "name": "Sprint 1", "goal": "Finalizar o capítulo inicial", "started_at": "2026-08-24", "ended_at": "2026-08-31" }
```

Validação: `name` obrigatório, até 255; `goal` opcional; `started_at` e `ended_at` datas obrigatórias, com `ended_at` posterior a `started_at` (RN-13).

## 8. Tarefas

### 8.1 Criação e listagem por contexto

| Endpoint | Corpo | Resposta |
|----------|-------|----------|
| `GET /kanban/tasks` | | `200`, lista não paginada, ordem de criação |
| `POST /kanban/tasks` | `{"title": "...", "description": "opcional"}` | `201`, status `TODO` (RN-06) |
| `GET /projects/{project}/backlog` | | `200`, tarefas com `sprint_id = null` e status `BACKLOG` |
| `POST /projects/{project}/backlog` | `{"title": "...", "description": "opcional"}` | `201`, status `BACKLOG` (RN-09) |
| `GET /sprints/{sprint}/tasks` | | `200`, todas as tarefas da sprint, ordem de criação |

Validação: `title` obrigatório, até 255; `description` opcional.

### 8.2 PUT `/tasks/{task}`

Substituição completa: **`title` e `status` são obrigatórios** em toda chamada, mesmo para mudar só um deles. `description` ausente vira `null`.

```json
{ "title": "Título atualizado", "description": "opcional", "status": "IN_PROGRESS" }
```

Status aceitos por contexto:

| Contexto | Status aceitos | Erros possíveis (`422`) |
|----------|----------------|-------------------------|
| Kanban Geral | `TODO`, `IN_PROGRESS`, `DONE` | `BACKLOG` (RN-06); WIP (RN-07) |
| Backlog sem sprint | apenas `BACKLOG` | qualquer outro status (RN-09) |
| Sprint `ACTIVE` | `TODO`, `IN_PROGRESS`, `DONE` | `BACKLOG` (RN-10); WIP da sprint (RN-21) |
| Sprint `PLANNING`/`FINISHED` | apenas o status atual | qualquer mudança de status (RN-20) |

Efeitos em timestamps conforme RN-22. Resposta `200` com `{"data": Task}`.

### 8.3 DELETE `/tasks/{task}`

`204`. Remove definitivamente (RN-26).

### 8.4 PUT `/tasks/{task}/migrate`

```json
{ "current_sprint_id": 34 }
```

`current_sprint_id` é a **sprint de destino** (o nome vem da especificação original). Usos:

1. Alocar tarefa do backlog em sprint `PLANNING` ou `ACTIVE`.
2. Migrar tarefa pendente de sprint `FINISHED` para a sprint `ACTIVE` do mesmo projeto.

Resultado: status `TODO`, `completed_at = null`, `started_at` preservado (RN-25). Erros `422` em `current_sprint_id` conforme RN-23 e RN-24; sprint de destino de outro usuário: `404`.

## 9. Administração

Todas exigem papel `admin`.

### GET `/admin/summary`

```json
{ "data": { "users": 20, "projects": 30, "tasks": 200, "completed_tasks": 120,
            "sprints": 40, "active_sprints": 10, "finished_sprints": 25 } }
```

`users` conta apenas participantes (RN-28).

### GET `/admin/analytics`

```json
{
  "data": {
    "cycle_time_hours": { "global_average": 18.42, "kanban_average": 6.1, "scrum_average": 30.75, "sample_size": 120 },
    "sprints": { "finished_count": 25, "average_completion_rate": 72.5 }
  }
}
```

Valores arredondados com 2 casas; médias sem amostra retornam `0`. Definições das métricas em [11](11-pesquisa-e-lgpd.md#2-métricas).

### GET `/admin/users`

Paginado (15), mais recentes primeiro, somente participantes. Filtro opcional `?search=` (até 255 caracteres) por apelido **ou** e-mail (busca parcial). Retorna objetos `User`.

### PUT `/admin/users/{user}/reset-password`

Sem corpo. `200` com `{"message": "Senha do usuário redefinida com sucesso para o padrão: 123456"}`. Alvo `admin` ou inexistente: `404` (RN-27).

### GET `/admin/export`

Download `text/csv; charset=UTF-8`, nome `mytask-export-AAAA-MM-DD-HHMMSS.csv`. Colunas e regras de pseudonimização em [11](11-pesquisa-e-lgpd.md#3-exportação-csv).
