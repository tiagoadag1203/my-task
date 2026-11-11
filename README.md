# MyTask

Plataforma web de produtividade pessoal que combina **Personal Kanban** (tarefas de rotina com limite de trabalho em andamento) e **Scrum Pessoal** (projetos, backlog e sprints).

Artefato prático do Trabalho de Conclusão de Curso *Aplicação de Metodologias Ágeis na Produtividade Pessoal por Meio do Desenvolvimento de um Protótipo de Plataforma Web*, de Tiago Augusto Dal'Acqua Gonçalves, bacharelado em Ciências da Computação do Centro Universitário de Adamantina (FAI), sob orientação da Profa. Dra. Eliane Vendramini de Oliveira, 2026.

## Funcionalidades

- **Kanban Geral:** quadro com as colunas A Fazer, Em Andamento e Concluído, arrastar e soltar, e no máximo 3 tarefas em andamento.
- **Projetos e sprints:** backlog por projeto, planejamento de sprints com datas, uma sprint ativa por projeto, quadro Kanban da sprint com limite próprio, encerramento manual ou automático e migração de pendências.
- **Painel do usuário:** distribuição das tarefas e conclusões dos últimos 7 dias.
- **Painel do administrador:** indicadores agregados, tempo médio de ciclo, taxa de conclusão de sprints, gestão de contas e exportação CSV pseudonimizada (implementado para estudos futuros).
- Interface responsiva, tema claro e escuro, recursos de acessibilidade.

## Tecnologias

API REST em **Laravel 13** (PHP 8.4, autenticação JWT), SPA em **Nuxt 4** (Vue 3, Pinia, Tailwind CSS, Chart.js), **MySQL 8.0** e **Nginx**, tudo executado com **Docker Compose**. Versões detalhadas em [docs/01-visao-geral.md](docs/01-visao-geral.md#4-stack-e-versões-fonte-única).

## Estrutura do repositório

```text
.
├── backend/                     API Laravel
├── frontend/                    SPA Nuxt
├── docker/                      Configurações de Nginx e PHP
├── docs/                        Documentação técnica
├── docker-compose.yml           Ambiente de desenvolvimento (base)
├── docker-compose.override.yml  Ambiente de desenvolvimento (ajustes locais)
├── docker-compose.prod.yml      Ambiente de produção (autônomo)
└── .env.prod.example            Modelo de variáveis de produção
```

## Início rápido (desenvolvimento)

Requisitos: Docker com Docker Compose v2 e as portas 80 e 3306 livres. Não é preciso instalar PHP, Node ou MySQL.

```bash
cp backend/.env.example backend/.env
docker compose run --rm --no-deps backend composer install
docker compose up -d --build
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan jwt:secret
docker compose exec backend php artisan migrate --seed
```

Acesse http://localhost. Administrador de desenvolvimento: `admin@mytask.local` / `change-me`. Para criar uma conta de usuário, use a opção **Criar conta** na página inicial.

Detalhes e solução de problemas em [docs/09-ambiente-de-desenvolvimento.md](docs/09-ambiente-de-desenvolvimento.md). Publicação em servidor em [docs/10-deploy.md](docs/10-deploy.md).

## Testes e qualidade

```bash
docker compose exec backend php artisan test        # testes do backend (PHPUnit)
docker compose exec backend vendor/bin/pint --test  # padrão de código
docker compose exec frontend npm run lint           # ESLint
docker compose exec frontend npm test               # testes unitários do frontend (Vitest)
```

## Documentação

| Arquivo | Finalidade |
|---------|-----------|
| [docs/01-visao-geral.md](docs/01-visao-geral.md) | Propósito, escopo, perfis de acesso, tecnologias e glossário |
| [docs/02-requisitos.md](docs/02-requisitos.md) | Requisitos funcionais e não funcionais, com rastreabilidade |
| [docs/03-regras-de-negocio.md](docs/03-regras-de-negocio.md) | Regras de Kanban, sprints, limite de trabalho em andamento e migração |
| [docs/04-arquitetura.md](docs/04-arquitetura.md) | Arquitetura, ambientes e decisões de projeto |
| [docs/05-modelo-de-dados.md](docs/05-modelo-de-dados.md) | Modelo de dados, tabelas e relacionamentos |
| [docs/06-api.md](docs/06-api.md) | Referência dos endpoints da API |
| [docs/07-backend.md](docs/07-backend.md) | Organização do backend, autenticação, erros e testes |
| [docs/08-frontend.md](docs/08-frontend.md) | Telas, componentes, estado e navegação do frontend |
| [docs/09-ambiente-de-desenvolvimento.md](docs/09-ambiente-de-desenvolvimento.md) | Instalação, configuração e execução local |
| [docs/10-deploy.md](docs/10-deploy.md) | Implantação em produção, backup e diagnóstico |
| [docs/11-pesquisa-e-lgpd.md](docs/11-pesquisa-e-lgpd.md) | Métricas, exportação de dados e privacidade |
