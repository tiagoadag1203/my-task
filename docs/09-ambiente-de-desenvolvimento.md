# 09. Ambiente de Desenvolvimento

Tudo roda em Docker: não é preciso ter PHP, Composer, Node ou MySQL instalados na máquina. Arquitetura dos contêineres em [04](04-arquitetura.md#2-contêineres-por-ambiente).

## 1. Pré-requisitos

- Docker Engine com Docker Compose v2.
- Porta **80** (Nginx) e **3306** (MySQL) livres no host.

## 2. Primeira execução

Na raiz do repositório:

```bash
# 1. Variáveis de ambiente
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env          # opcional: só para rodar o Nuxt fora do Docker

# 2. Dependências PHP (antes do up: o backend usa bind mount e precisa de vendor/ para iniciar)
docker compose run --rm --no-deps backend composer install

# 3. Subir os serviços (nginx, backend, frontend, mysql)
docker compose up -d --build

# 4. Chaves da aplicação e do JWT
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan jwt:secret

# 5. Banco: tabelas + administrador + 5 participantes fictícios
docker compose exec backend php artisan migrate --seed
```

Acesse **http://localhost**. Administrador de desenvolvimento (definido em `backend/.env`): `admin@mytask.local` / `change-me`.

> As dependências do Node são instaladas na imagem do frontend (`npm ci`) e preservadas no volume `frontend_node_modules`. Após alterar `package.json`, reconstrua: `docker compose up -d --build frontend`.

## 3. Como os serviços se comportam em desenvolvimento

| Serviço | Comportamento |
|---------|---------------|
| `nginx` | Publica `80:80`. `/api/` vai para `backend:8000`; o resto para `frontend:3000`. |
| `backend` | `php artisan serve` na porta 8000 (definido no `docker-compose.override.yml`), código montado por bind mount: mudanças em PHP valem na próxima requisição. |
| `frontend` | `nuxt dev` com hot reload (polling habilitado por `CHOKIDAR_USEPOLLING`/`WATCHPACK_POLLING`). |
| `mysql` | Banco `mytask`, usuário `root` sem senha, porta 3306 publicada (acesso por cliente SQL local), volume `mysql_data`. |

O `docker-compose.override.yml` é aplicado automaticamente pelo `docker compose` e só deve existir no desenvolvimento.

## 4. Comandos do dia a dia

```bash
docker compose up -d                 # subir
docker compose logs -f [serviço]     # acompanhar logs
docker compose down                  # parar (mantém o banco)
docker compose down -v               # parar e APAGAR banco e node_modules

docker compose exec backend php artisan migrate:fresh --seed   # recriar o banco (apaga os dados)
docker compose exec backend php artisan route:list --path=api  # conferir rotas
docker compose exec backend php artisan tinker                 # console interativo
```

## 5. Qualidade

```bash
# Backend
docker compose exec backend vendor/bin/pint --test                          # estilo
docker compose exec backend vendor/bin/phpstan analyse --memory-limit=512M  # análise estática (Larastan)
docker compose exec backend php artisan test                                # PHPUnit

# Frontend
docker compose exec frontend npm run lint        # ESLint
docker compose exec frontend npm run format      # Prettier (escreve as correções)
docker compose exec frontend npm test            # Vitest
docker compose exec frontend npm run build       # build de produção
npm --prefix frontend run test:e2e               # Playwright (rodar no host, com navegadores do Playwright instalados)
```

Padronização compartilhada: `.editorconfig` (indentação e fim de linha), `.gitattributes` e `.gitignore` (ignora `.env*`, `vendor`, `node_modules`, `.nuxt`, `.output`).

Sugestão de rotina antes de cada commit: Pint, PHPStan, testes do backend, lint e testes do frontend.

## 6. Problemas comuns

| Sintoma | Causa provável | Solução |
|---------|----------------|---------|
| Contêiner `backend` sai logo após subir | `vendor/` ausente | Passo 2 da seção 2 |
| `/api` retorna 502 | Backend parado ou ainda subindo | `docker compose logs backend` |
| Login responde 500 mencionando JWT | `JWT_SECRET` vazio | `php artisan jwt:secret` |
| Erro de conexão com banco | `DB_HOST` diferente de `mysql` | Ajustar `backend/.env` |
| Porta 80 ou 3306 ocupada | Outro serviço local | Parar o serviço ou mudar o mapeamento no override |
