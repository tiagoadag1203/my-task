# 04. Arquitetura

## 1. Visão de contexto

```text
 Participante / Pesquisador
          │  HTTPS
          ▼
 ┌───────────────────────────┐
 │ nginx-proxy + acme (TLS)  │   somente em produção
 └─────────────┬─────────────┘
               ▼
 ┌───────────────────────────┐
 │ Nginx da aplicação        │   ponto único de entrada (same-origin)
 └──────┬─────────────┬──────┘
   /api │             │ demais rotas
        ▼             ▼
 ┌─────────────┐ ┌──────────────────┐
 │ Laravel API │ │ Nuxt SPA         │
 │ (JWT, REST) │ │ (ssr: false)     │
 └──────┬──────┘ └──────────────────┘
        ▼
 ┌─────────────┐        ┌──────────────────────────┐
 │ MySQL 8     │        │ Google Forms (TAM)       │ link externo
 └─────────────┘        └──────────────────────────┘
```

O navegador conversa apenas com uma origem. `/api/*` vai para o Laravel e todo o resto para o Nuxt. Assim não há CORS entre frontend e backend.

## 2. Contêineres por ambiente

| Serviço | Desenvolvimento | Produção |
|---------|-----------------|----------|
| `nginx` | `default.conf`: `/api/` via `proxy_pass` HTTP para `backend:8000`; `/` para `frontend:3000`. Publica a porta 80 no host. | `production.conf`: `/api` via `fastcgi_pass` para `backend:9000` (`SCRIPT_FILENAME` = `public/index.php`); `/` para `frontend:3000`. Sem porta no host; exposto ao `nginx-proxy` pela rede externa `nginx-proxy-network`. |
| `backend` | `php artisan serve` na porta 8000, código montado por bind mount. | PHP-FPM na porta 9000, imagem multi-stage (`vendor` + runtime), volume `backend_storage`. |
| `frontend` | `nuxt dev` na porta 3000, bind mount e polling de arquivos. | Servidor Nitro (`node .output/server/index.mjs`) gerado por `nuxt build`. |
| `mysql` | Root sem senha, porta 3306 publicada no host. | Usuário e senha dedicados, sem porta publicada. |
| Projeto Compose / imagens | `mytask` / `mytask-*:dev` | `mytask-prod` / `mytask-*:prod` |

Os dois ambientes podem coexistir no mesmo Docker Engine (nomes de contêineres, imagens e volumes distintos). Detalhes operacionais em [09](09-ambiente-de-desenvolvimento.md) e [10](10-deploy.md).

## 3. Camadas do backend

```text
Requisição HTTP
  → throttle (apenas /auth/register e /auth/login)
  → auth:api (JWT)
  → role:user | role:admin (CheckRole)
  → FormRequest (validação e normalização)
  → Controller (consulta a partir do usuário + Policy)
  → Service (regras de negócio e transações)
  → Model Eloquent
  → API Resource (contrato de saída)
```

Detalhamento em [07](07-backend.md).

## 4. Camadas do frontend

```text
Página / componente de aba
  → useAsyncData (leituras)
  → $api (plugin)  →  repositório do domínio (funções puras)
  → $fetch com Authorization: Bearer
  → /api (Nginx → Laravel)
```

Detalhamento em [08](08-frontend.md).

## 5. Decisões de arquitetura

Registro curto das principais decisões (formato ADR simplificado), úteis para a seção de metodologia do TCC.

| # | Decisão | Motivação | Consequência |
|---|---------|-----------|--------------|
| AD-01 | **API REST + SPA desacopladas** em monorepo. | Separar responsabilidades e permitir conteinerização individual. | Dois projetos com ferramentas próprias, versionados juntos. |
| AD-02 | **Nuxt em modo SPA (`ssr: false`).** | Área logada privada: SEO irrelevante; menor consumo de CPU/RAM no servidor. | Variáveis `NUXT_PUBLIC_*` entram no bundle no build; mudá-las exige rebuild do frontend. |
| AD-03 | **Same-origin atrás de Nginx.** | Eliminar CORS e simplificar cookies e segurança. | Toda a API fica sob `/api`; o Nginx é peça obrigatória também no desenvolvimento. |
| AD-04 | **JWT stateless** (`tymon/jwt-auth`) em vez de sessão/Sanctum. | Backend sem estado de sessão; token simples no cabeçalho. | Logout depende da blacklist em cache (tabela `cache`). Não há endpoint de *refresh*: após 60 min o usuário faz login novamente. |
| AD-05 | **Tabela única `tasks`** para Kanban Geral e Scrum. | Simplificar consultas e métricas comparativas (Kanban x Scrum). | O contexto da tarefa é inferido por `project_id`/`sprint_id`, e as regras de status variam por contexto (RN-06, RN-09, RN-20). |
| AD-06 | **MySQL relacional.** | Integridade referencial e transações para as regras de concorrência e as métricas. | Uso de `lockForUpdate` nas operações críticas. |
| AD-07 | **Fuso único `America/Sao_Paulo`.** | Exatidão dos registros temporais da pesquisa. | Configurado em todas as camadas. |
| AD-08 | **Navegação do dashboard por query string** (`?tab=`, `?project=`, `?sprint=`) em rota única. | Preservar estado das abas (`KeepAlive`), histórico do navegador e links diretos. | Uma única rota protegida (`/dashboard`). |
| AD-09 | **Questionário TAM externo** (Google Forms). | Evitar construir CRUD de formulário. | Respostas do questionário ficam fora do banco do MyTask. |
| AD-10 | **Compose de produção autônomo.** | Evitar herdar configurações inseguras do desenvolvimento (portas, senha vazia, bind mounts). | Arquivos de Compose de dev e prod são mantidos separadamente. |
