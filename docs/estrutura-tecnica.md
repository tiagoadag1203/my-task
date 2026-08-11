# Documentação Técnica da Estrutura do MyTask

## 1. Visão Geral

O **MyTask** foi estruturado como um **monorepo** com dois serviços principais:

- `backend/`: base do backend em Laravel, preparada exclusivamente como API REST.
- `frontend/`: aplicação Nuxt em modo SPA, preparada para consumir a API via **same-origin**.

A aplicação foi organizada para rodar com **Docker** em desenvolvimento e produção, com Nginx atuando como proxy reverso na borda da aplicação.

## 2. Stack Base

- Backend: PHP 8.3 e Laravel 13
- Frontend: Nuxt 4.5 em SPA
- Estado global: Pinia
- Estilo e utilitários: Tailwind CSS
- Qualidade de código: ESLint, Prettier, Pint e Larastan
- Banco: MySQL 8.0.36
- Proxy e entrega HTTP: Nginx 1.25.3 Alpine

## 3. Estrutura de Pastas

```text
mytask-tcc/
├── backend/
├── frontend/
│   ├── app/
│   │   ├── app.vue
│   │   └── assets/css/main.css
│   ├── components/
│   ├── composables/
│   ├── layouts/
│   ├── middleware/
│   ├── pages/
│   ├── plugins/
│   ├── public/
│   └── stores/
├── docker/
│   ├── nginx/
│   │   ├── default.conf
│   │   └── production.conf
│   └── php/
│       └── local.ini
├── docker-compose.yml
├── docker-compose.override.yml
├── docker-compose.prod.yml
└── docs/
    └── estrutura-tecnica.md
```

### 3.1 Backend

- `backend/composer.json`: dependências e scripts do backend.
- `backend/.env` e `backend/.env.example`: variáveis de ambiente base.
- `backend/bootstrap/app.php`: bootstrap da aplicação.
- `backend/config/app.php`: configurações centrais, incluindo timezone.
- `backend/routes/`: rotas de API e console.
- `backend/public/index.php`: ponto de entrada HTTP.
- `backend/Dockerfile` e `backend/Dockerfile.prod`: imagens de desenvolvimento e produção.

### 3.2 Frontend

- `frontend/package.json`: scripts e dependências do Nuxt.
- `frontend/nuxt.config.ts`: modo SPA, módulos e runtime config.
- `frontend/app/app.vue`: casca base da aplicação.
- `frontend/app/assets/css/main.css`: base de estilos com Tailwind.
- `frontend/Dockerfile` e `frontend/Dockerfile.prod`: imagens de desenvolvimento e produção.
- `frontend/nginx.conf`: configuração interna para servir o SPA na imagem final.

## 4. Arquitetura Same-Origin

O acesso externo ocorre por um único ponto de entrada:

- `Nginx` recebe a requisição em `http://localhost`.
- Requisições para `/api` são encaminhadas ao backend.
- Demais rotas são encaminhadas ao frontend.

Isso evita CORS desnecessário e mantém o frontend e o backend sob a mesma origem.

## 5. Ambiente de Desenvolvimento

### Objetivo

Executar a aplicação com hot reload, volumes de trabalho e persistência de banco.

### Serviços

- `nginx`: proxy reverso local.
- `backend`: PHP-FPM do Laravel.
- `frontend`: servidor Nuxt em modo desenvolvimento.
- `mysql`: banco relacional persistente.

### Comandos principais

```bash
docker compose up -d --build
docker compose logs -f
docker compose down
docker compose down -v
```

## 6. Ambiente de Produção

### Objetivo

Gerar imagens otimizadas e executar a aplicação com separação entre build e runtime.

### Serviços

- `nginx`: proxy reverso de produção.
- `backend`: PHP-FPM em imagem enxuta.
- `frontend`: imagem final com build estático do Nuxt.
- `mysql`: banco persistente.

### Comandos principais

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml build
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f
docker compose -f docker-compose.yml -f docker-compose.prod.yml down
```

## 7. Qualidade e Padronização

- `.editorconfig` e `.gitattributes` padronizam formatação e fim de linha.
- `.gitignore` evita versionar artefatos de build, dependências e arquivos de ambiente.
- O frontend já está validado com `lint` e `build`.
- O backend foi estruturado para receber as dependências oficiais do Laravel e os containers de execução.

## 8. Resultado da Estrutura

Ao final desta etapa, o MyTask fica preparado para:

- continuar a implementação funcional por módulos;
- manter separação clara entre frontend e backend;
- operar sob same-origin em Docker;
- evoluir para testes, deploy e manutenção com menor acoplamento.
