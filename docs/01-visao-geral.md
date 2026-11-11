# 01. Visão Geral

## 1. Propósito

O MyTask é o artefato prático do TCC. Seu objetivo é permitir avaliar, com dados reais de uso, se a aplicação de conceitos ágeis ao cotidiano reduz a sobrecarga cognitiva e aumenta a eficácia pessoal.

O sistema se apoia em três pilares:

| Pilar | Finalidade | Horizonte |
|-------|-----------|-----------|
| **Personal Kanban** (Kanban Geral) | Fluxo contínuo de tarefas de rotina, com limite de trabalho em andamento (WIP = 3) | Operacional, diário |
| **Scrum Pessoal** | Metas complexas organizadas em projetos, backlog e sprints com prazo | Estratégico, médio e longo prazo |
| **Painel do Pesquisador** | Métricas agregadas e exportação pseudonimizada para análise estatística | Científico |

## 2. Escopo

**Dentro do escopo**

- Cadastro e login de participantes (somente apelido, e-mail e senha).
- Kanban Geral com três colunas e WIP máximo de 3.
- Projetos, backlog, sprints (planejamento, execução e encerramento) e migração de tarefas pendentes.
- Painel inicial do participante com gráficos de distribuição e conclusões semanais.
- Painel do pesquisador: indicadores, tempo de ciclo, taxa de conclusão de sprints, lista de participantes, redefinição de senha e exportação CSV.
- Convite para o questionário pós-uso (TAM), hospedado externamente (Google Forms).

**Fora do escopo** (decisões conscientes)

- Coleta de nome completo ou dados sensíveis (minimização de dados, LGPD).
- Questionário implementado dentro da plataforma (usa-se formulário externo).
- Colaboração entre usuários, compartilhamento de projetos, notificações, aplicativo mobile nativo.
- Recuperação de senha por e-mail (a redefinição é feita pelo administrador).

## 3. Atores e papéis

| Papel (`role`) | Quem é | O que pode fazer |
|----------------|--------|------------------|
| `user` | Participante voluntário da pesquisa | Usar Kanban, projetos, backlog, sprints e o próprio painel |
| `admin` | Pesquisador (autor do TCC) | Ver métricas agregadas, listar participantes, redefinir senhas, exportar CSV |

Os papéis são mutuamente exclusivos. As regras de acesso estão em [03, RN-01 a RN-05](03-regras-de-negocio.md#1-papéis-e-acesso).

## 4. Stack e versões (fonte única)

Este é o único lugar onde as versões são mantidas. Os demais documentos não repetem números de versão.

| Camada | Tecnologia | Versão em uso | Onde está definida |
|--------|-----------|---------------|--------------------|
| Linguagem backend | PHP | 8.4.1 (FPM Alpine) | `backend/Dockerfile*` (`composer.json` aceita `^8.3`) |
| Framework backend | Laravel | 13.x (`^13.8`) | `backend/composer.json` |
| Autenticação | tymon/jwt-auth | 2.3 | `backend/composer.json` |
| Testes backend | PHPUnit | 12.5 | `backend/composer.json` |
| Qualidade backend | Laravel Pint, Larastan (PHPStan) | 1.27 / 3.x | `backend/composer.json` |
| Runtime frontend | Node.js | 22 (Alpine) | `frontend/Dockerfile*` |
| Framework frontend | Nuxt (modo SPA, `ssr: false`) | 4.5 | `frontend/package.json` |
| UI | Vue 3.5, Vue Router 5, Tailwind CSS 4 | | `frontend/package.json` |
| Estado | Pinia (`@pinia/nuxt`) | 4 | `frontend/package.json` |
| Gráficos | Chart.js 4 + vue-chartjs 5 | | `frontend/package.json` |
| Drag and drop | vue-draggable-plus | 0.6 | `frontend/package.json` |
| Testes frontend | Vitest 4, Playwright 1.62 | | `frontend/package.json` |
| Banco de dados | MySQL | 8.0.36 | `docker-compose*.yml` |
| Proxy HTTP | Nginx | 1.25.3 Alpine | `docker-compose*.yml` |
| Orquestração | Docker Compose v2 | | raiz do repositório |

**Fuso horário oficial:** `America/Sao_Paulo` em todas as camadas (Laravel `APP_TIMEZONE`, MySQL `TZ`, Nuxt `NUXT_PUBLIC_APP_TIMEZONE`). Isso garante exatidão nos registros temporais usados pela pesquisa.

## 5. Glossário

| Termo | Significado no MyTask |
|-------|----------------------|
| **Kanban Geral** | Quadro de tarefas de rotina sem projeto (`project_id = null`). |
| **Projeto** | Objetivo estratégico do participante. Agrupa backlog e sprints. Não tem status próprio. |
| **Backlog** | Tarefas de um projeto ainda não alocadas em sprint (`sprint_id = null`, status `BACKLOG`). |
| **Sprint** | Ciclo com início e fim (datas) dentro de um projeto. Estados `PLANNING`, `ACTIVE`, `FINISHED`. |
| **WIP** (*Work in Progress*) | Quantidade de tarefas simultâneas em `IN_PROGRESS`. Limite: 3 por quadro. |
| **Quadro** | Conjunto de tarefas sobre o qual o WIP é contado: o Kanban Geral do usuário ou uma sprint. |
| **Auto-close** | Encerramento automático de sprints ativas cujo prazo (`ended_at`) já passou. |
| **Migração** | Mover uma tarefa do backlog para uma sprint, ou de uma sprint finalizada para a sprint ativa, com reset para `TODO`. |
| **Tempo de ciclo** (*cycle time*) | `completed_at - started_at` de uma tarefa concluída. |
| **TAM** | *Technology Acceptance Model*: modelo do questionário pós-uso. |
| **`user_hash`** | Identificador pseudonimizado do participante no CSV, válido somente dentro de um arquivo exportado. |

## 6. Repositório e localização da documentação

```text
mytask-tcc/                  Repositório do código (monorepo)
├── backend/                 API Laravel
├── frontend/                SPA Nuxt
├── docker/                  Configurações de Nginx e PHP
├── docs/                    Esta documentação
├── README.md                Visão geral e início rápido
├── docker-compose.yml       Base de desenvolvimento
├── docker-compose.override.yml
├── docker-compose.prod.yml  Produção (autônomo)
└── .env.prod.example        Modelo de variáveis de produção
```

Esta documentação fica na pasta `docs/` do repositório; o índice geral está no [README.md](../README.md) da raiz.
