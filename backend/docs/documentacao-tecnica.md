# Documentação Técnica do Backend MyTask

## 1. Visão geral

O backend do MyTask é uma API REST stateless construída com Laravel 13 e PHP 8.4. A aplicação atende dois perfis:

- `user`: participante que gerencia Kanban, projetos, sprints e tarefas.
- `admin`: pesquisador com acesso exclusivo a métricas, usuários e exportação científica.

Todas as rotas são servidas sob `/api`, sem prefixo de versão. Somente login e cadastro são públicos.

## 2. Stack

- PHP 8.4.1
- Laravel 13
- MySQL 8.0.36
- `tymon/jwt-auth` 2.3 para autenticação JWT
- PHPUnit 12
- Laravel Pint
- Larastan/PHPStan
- Docker Compose e Nginx

O timezone oficial é `America/Sao_Paulo`.

## 3. Arquitetura

O fluxo principal segue:

```text
Route
  -> auth:api
  -> CheckRole
  -> FormRequest
  -> Controller
  -> Policy
  -> Service
  -> Eloquent Model
  -> API Resource
```

Responsabilidades:

- `app/Http/Requests`: validação e normalização das entradas.
- `app/Http/Controllers`: coordenação HTTP, autorização e respostas.
- `app/Policies`: propriedade dos registros e autorização por recurso.
- `app/Services`: regras de negócio e transações.
- `app/Models`: relacionamentos e casts.
- `app/Http/Resources`: contrato de saída da API.
- `app/Enums`: valores válidos de cargos e estados.

## 4. Modelo de dados

### users

- `id`: chave primária.
- `nickname`: apelido pseudonimizado, máximo de 50 caracteres.
- `email`: único.
- `password`: hash seguro gerenciado pelo cast `hashed`.
- `role`: `user` ou `admin`.
- timestamps.

Relacionamentos: possui muitos projetos e tarefas.

### projects

- `id`
- `user_id`: FK para `users`, com exclusão em cascata.
- `name`
- `description`: opcional.
- timestamps.

Relacionamentos: pertence a um usuário; possui muitas sprints e tarefas.

### sprints

- `id`
- `project_id`: FK para `projects`, com exclusão em cascata.
- `name`
- `goal`: opcional.
- `started_at` e `ended_at`: datas do ciclo.
- `status`: `PLANNING`, `ACTIVE` ou `FINISHED`.
- timestamps.

Relacionamentos: pertence a um projeto; possui muitas tarefas.

### tasks

- `id`
- `user_id`: FK para `users`, com exclusão em cascata.
- `project_id`: FK opcional; `NULL` identifica o Kanban Geral.
- `sprint_id`: FK opcional; ao excluir a sprint, torna-se `NULL`.
- `title`
- `description`: opcional.
- `status`: `BACKLOG`, `TODO`, `IN_PROGRESS` ou `DONE`.
- `started_at`: primeira entrada em `IN_PROGRESS`.
- `completed_at`: conclusão atual da tarefa.
- timestamps.

Índices compostos suportam consultas de WIP por usuário, Kanban e Sprint.

## 5. Autenticação e autorização

O guard padrão `api` utiliza JWT. O cliente envia:

```http
Authorization: Bearer TOKEN
```

Separação de acesso:

- Públicas: `/api/auth/register` e `/api/auth/login`.
- Ambos os cargos autenticados: `/api/auth/me`, `/api/auth/logout` e `/api/health`.
- Somente `user`: projetos, sprints, Kanban e tarefas.
- Somente `admin`: `/api/admin/*`.

O middleware `CheckRole` aplica a separação por cargo. As Policies impedem que usuários diferentes acessem os mesmos recursos. Os controllers também iniciam as consultas pelo usuário autenticado para retornar 404 em tentativas de acesso cruzado.

## 6. JWT

Variáveis principais:

```dotenv
AUTH_GUARD=api
JWT_SECRET=
JWT_TTL=60
```

O token é invalidado no logout. O cargo não é aceito no cadastro público: todo cadastro recebe obrigatoriamente `user`.

## 7. Tratamento de erros

As exceções são convertidas para JSON:

- `401`: não autenticado ou credenciais inválidas.
- `403`: cargo ou Policy insuficiente.
- `404`: rota ou recurso não encontrado.
- `422`: validação de entrada ou regra de negócio.
- `500`: erro interno não tratado.

Formato de validação:

```json
{
  "message": "Os dados informados são inválidos.",
  "errors": {
    "campo": ["Descrição do erro."]
  }
}
```

## 8. Privacidade e pesquisa

- Não são coletados nomes completos.
- A exportação CSV não contém nickname ou e-mail.
- Cada usuário recebe um `user_hash` calculado com salt efêmero por exportação.
- Timestamps de processo permitem calcular tempo de ciclo.
- Queries administrativas agregam dados sem expor credenciais.

## 9. Configuração

No diretório raiz:

```bash
docker compose up -d --build
docker compose exec backend composer install
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan jwt:secret
docker compose exec backend php artisan migrate --seed
```

Para recriar o banco de desenvolvimento:

```bash
docker compose exec backend php artisan migrate:fresh --seed
```

Esse comando apaga os dados existentes.

Variáveis do administrador inicial:

```dotenv
ADMIN_NICKNAME=Administrador
ADMIN_EMAIL=admin@mytask.local
ADMIN_PASSWORD=change-me
```

Altere a senha antes de usar um ambiente compartilhado ou de produção.

## 10. Qualidade

```bash
docker compose exec backend vendor/bin/pint --test
docker compose exec backend vendor/bin/phpstan analyse --memory-limit=512M
docker compose exec backend php artisan test
docker compose exec backend php artisan route:list --path=api
```

Os testes cobrem autenticação, separação de cargos, ownership, limite de WIP, concorrência de sprints, migração de tarefas, auto-close e privacidade do CSV.
