# MyTask — Frontend

SPA em Nuxt 4 para produtividade pessoal com Personal Kanban, Scrum Pessoal e painel de pesquisa.

## Requisitos e execução

- Node.js 22
- API Laravel disponível em `/api` pelo proxy Nginx

```bash
npm ci
cp .env.example .env
npm run dev
```

Também é possível iniciar a aplicação completa na raiz do projeto com Docker Compose.

## Variáveis de ambiente

```dotenv
NUXT_PUBLIC_API_BASE=/api
NUXT_PUBLIC_TAM_FORM_URL=
NUXT_PUBLIC_APP_TIMEZONE=America/Sao_Paulo
```

`NUXT_PUBLIC_TAM_FORM_URL` é opcional. Quando preenchida, participantes com 15 dias de cadastro
recebem um convite descartável para responder à pesquisa TAM.

## Arquitetura

- `app/plugins/api.js`: cliente `$fetch` global, Bearer JWT e tratamento de 401.
- `app/repositories/`: funções puras que encapsulam endpoints por domínio.
- `app/stores/auth.js`: única store Pinia; mantém token, usuário e sessão.
- `app/pages/dashboard.vue`: rota autenticada única.
- `app/components/dashboard`, `kanban`, `projects`, `backlog`, `sprints`, `admin`: abas e domínios.
- `app/components/ui`: primitivas reutilizáveis.
- `app/composables/useToast.js`, `useModal.js`, `useConfirm.js`, `useLoader.js`: UI global por estado.

Leituras são feitas com `useAsyncData` nos componentes consumidores. Repositórios não usam
composables do Nuxt. Kanban Geral e sprints compartilham os mesmos componentes de board.

## Navegação e papéis

O dashboard usa query params para preservar histórico e links diretos:

- Participante (`user`): `?tab=inicio`, `?tab=tarefas`, `?tab=projetos`.
- Pesquisador (`admin`): `?tab=visao-geral`, `?tab=participantes`.
- Workspace: `?tab=projetos&project=12&sprint=34`.

As opções aparecem na sidebar desktop e na barra inferior mobile. A API continua sendo a fonte
de verdade para autorização e ownership.

## API

A URL base real é `/api`, com papéis `user` e `admin`. O dashboard do participante consome
`GET /api/dashboard/analytics`; o painel da pesquisa usa `/api/admin/*`.

## Qualidade

```bash
npm run lint
npm test
npm run test:e2e
npm run build
```

- Vitest/Nuxt Test Utils: repositórios, normalização da API e Kanban.
- Playwright: autenticação, abas de participante e segregação administrativa.

Para instalar o navegador do Playwright pela primeira vez:

```bash
npx playwright install chromium
```

## Produção

```bash
npm run build
npm run preview
```

O `Dockerfile.prod` executa o servidor Nitro gerado em `.output/server/index.mjs`.
