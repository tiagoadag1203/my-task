# Documentação das APIs MyTask

## Convenções

- Base URL: `/api`
- Content-Type: `application/json`
- Datas e horas: ISO 8601, timezone `America/Sao_Paulo`
- Autenticação: `Authorization: Bearer TOKEN`
- Somente `POST /auth/register` e `POST /auth/login` são públicos.
- Recursos Eloquent são retornados dentro de `data`.
- Coleções paginadas incluem `data`, `links` e `meta`.

## Respostas de erro

```json
{
  "message": "Os dados informados são inválidos.",
  "errors": {
    "status": ["Descrição do erro."]
  }
}
```

Códigos comuns:

- `200 OK`: consulta ou atualização.
- `201 Created`: cadastro.
- `204 No Content`: exclusão.
- `401 Unauthorized`: token ausente, inválido ou credenciais incorretas.
- `403 Forbidden`: cargo ou autorização insuficiente.
- `404 Not Found`: recurso não encontrado.
- `422 Unprocessable Entity`: validação ou regra de negócio.

## Autenticação

### POST `/auth/register`

Pública. Cria exclusivamente usuários com cargo `user`.

Request:

```json
{
  "nickname": "participante",
  "email": "participante@example.com",
  "password": "password123"
}
```

Resposta `201`:

```json
{
  "access_token": "JWT",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "nickname": "participante",
    "email": "participante@example.com",
    "role": "user"
  }
}
```

Validação: nickname obrigatório e máximo 50; e-mail válido e único; senha entre 8 e 255 caracteres.

### POST `/auth/login`

Pública.

```json
{
  "email": "participante@example.com",
  "password": "password123"
}
```

Retorna `200` no mesmo formato do cadastro. Credenciais inválidas retornam `401`. O login também encerra automaticamente as sprints ativas expiradas do usuário.

### GET `/auth/me`

Requer `auth:api`; aceita `user` e `admin`. Retorna os dados do usuário autenticado.

### POST `/auth/logout`

Requer `auth:api`; aceita `user` e `admin`. Invalida o token atual.

### GET `/health`

Requer `auth:api`; aceita ambos os cargos.

```json
{"status":"ok"}
```

## Projetos

Todas as rotas desta seção exigem `auth:api` e `role:user`.

### GET `/projects`

Lista paginada dos projetos do usuário, com contadores de sprints e tarefas.

### POST `/projects`

```json
{
  "name": "TCC",
  "description": "Projeto de conclusão"
}
```

Retorna `201`.

### GET `/projects/{project}`

Retorna um projeto pertencente ao usuário. Também executa o auto-close de sprints expiradas.

### PUT `/projects/{project}`

```json
{
  "name": "TCC atualizado",
  "description": "Descrição opcional"
}
```

### DELETE `/projects/{project}`

Retorna `204`. A exclusão remove em cascata suas sprints e tarefas.

## Sprints

Exigem `auth:api` e `role:user`.

### GET `/projects/{project}/sprints`

Lista as sprints do projeto com contagem de tarefas.

### POST `/projects/{project}/sprints`

```json
{
  "name": "Sprint 1",
  "goal": "Finalizar o capítulo inicial",
  "started_at": "2026-08-24",
  "ended_at": "2026-08-31"
}
```

`ended_at` deve ser posterior a `started_at`. A sprint nasce como `PLANNING`. Retorna `201`.

### GET `/sprints/{sprint}`

Retorna os metadados e a contagem de tarefas.

### PUT `/sprints/{sprint}`

Aceita o mesmo payload da criação. Somente sprints em `PLANNING` podem ser editadas.

### DELETE `/sprints/{sprint}`

Retorna `204`. As tarefas são desvinculadas, retornam a `BACKLOG` e têm os timestamps de execução resetados.

### PUT `/sprints/{sprint}/status`

```json
{
  "status": "ACTIVE"
}
```

Valores aceitos: `ACTIVE` e `FINISHED`.

Transições:

- `PLANNING -> ACTIVE`
- `ACTIVE -> FINISHED`

Somente uma sprint pode estar ativa em cada projeto.

## Kanban Geral

Exige `auth:api` e `role:user`.

### GET `/kanban/tasks`

Lista todas as tarefas do usuário sem projeto.

### POST `/kanban/tasks`

```json
{
  "title": "Responder e-mails",
  "description": "Descrição opcional"
}
```

Cria a tarefa em `TODO` e retorna `201`.

## Backlog do projeto

Exige `auth:api` e `role:user`.

### GET `/projects/{project}/backlog`

Lista tarefas do projeto com `sprint_id = null`.

### POST `/projects/{project}/backlog`

```json
{
  "title": "Escrever introdução",
  "description": "Descrição opcional"
}
```

Cria a tarefa em `BACKLOG` e retorna `201`.

## Tarefas

Exigem `auth:api` e `role:user`.

### GET `/sprints/{sprint}/tasks`

Lista as tarefas vinculadas à sprint.

### PUT `/tasks/{task}`

```json
{
  "title": "Título atualizado",
  "description": "Descrição opcional",
  "status": "IN_PROGRESS"
}
```

Valores de status: `BACKLOG`, `TODO`, `IN_PROGRESS`, `DONE`, respeitando o contexto:

- Kanban Geral: `TODO`, `IN_PROGRESS`, `DONE`.
- Backlog sem sprint: `BACKLOG`.
- Tarefa de sprint ativa: `TODO`, `IN_PROGRESS`, `DONE`.

Ao entrar pela primeira vez em `IN_PROGRESS`, registra `started_at`. Ao entrar em `DONE`, registra `completed_at`. Ao reabrir uma tarefa concluída, limpa `completed_at`.

A quarta tarefa simultânea em `IN_PROGRESS` no mesmo quadro retorna `422`.

### DELETE `/tasks/{task}`

Exclui a tarefa e retorna `204`.

### PUT `/tasks/{task}/migrate`

```json
{
  "current_sprint_id": 10
}
```

Usos:

- Alocar uma tarefa do backlog em uma sprint planejada ou ativa.
- Migrar tarefa pendente de sprint finalizada para a sprint ativa do mesmo projeto.

O status é obrigatoriamente redefinido para `TODO`.

## Administração

Todas exigem `auth:api` e `role:admin`.

### GET `/admin/summary`

Retorna:

```json
{
  "data": {
    "users": 20,
    "projects": 30,
    "tasks": 200,
    "completed_tasks": 120,
    "sprints": 40,
    "active_sprints": 10,
    "finished_sprints": 25
  }
}
```

### GET `/admin/analytics`

Retorna médias de tempo de ciclo global, Kanban e Scrum em horas no objeto `cycle_time_hours`, tamanho da amostra, quantidade de sprints finalizadas e taxa média de conclusão. A exportação CSV mantém a duração bruta em segundos.

### GET `/admin/users`

Lista paginada de participantes. Query opcional:

```text
?search=apelido-ou-email
```

Administradores não aparecem nessa listagem.

### PUT `/admin/users/{user}/reset-password`

Redefine a senha de um participante para `123456`. Não permite redefinir outro administrador.

### GET `/admin/export`

Baixa CSV UTF-8 com:

```text
user_hash,task_type,created_at,started_at,completed_at,cycle_time_seconds,status_final
```

E-mail e nickname nunca são exportados. `user_hash` é pseudonimizado com salt efêmero para cada arquivo.
