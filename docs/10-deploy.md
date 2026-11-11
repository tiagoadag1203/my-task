# 10. Deploy em Produção

Manual para publicar o MyTask com Docker Compose. Visão dos contêineres e diferenças em relação ao desenvolvimento em [04](04-arquitetura.md#2-contêineres-por-ambiente).

> O Compose de produção é **autônomo**. Use sempre `-f docker-compose.prod.yml` e nunca mescle com `docker-compose.yml`/override de desenvolvimento.

## 1. Topologia

```text
Internet (HTTPS)
  → nginx-proxy + acme-companion        (rede externa nginx-proxy-network, TLS Let's Encrypt)
    → mytask_nginx_prod :80
        /api   → mytask_backend_prod :9000   (PHP-FPM, FastCGI)
        demais → mytask_frontend_prod :3000  (Nitro)
      mytask_mysql_prod :3306               (somente rede interna mytask_network)
```

Volumes persistentes: `mysql_data` (banco) e `backend_storage` (storage do Laravel).

## 2. Pré-requisitos do servidor

- Linux com Docker Engine 24+ e Docker Compose v2; Git.
- DNS (A/AAAA) do domínio apontando para o servidor; portas 80 e 443 abertas.
- Rede externa `nginx-proxy-network` e um proxy TLS conectado a ela. Padrão do projeto: [nginx-proxy](https://github.com/nginx-proxy/nginx-proxy) + acme-companion, configurados **fora** deste repositório.

```bash
docker network ls | grep nginx-proxy-network || docker network create nginx-proxy-network
```

Exemplo mínimo do proxy (repositório separado):

```yaml
services:
  nginx-proxy:
    image: nginxproxy/nginx-proxy:1.6
    ports: ["80:80", "443:443"]
    volumes:
      - /var/run/docker.sock:/tmp/docker.sock:ro
      - certs:/etc/nginx/certs
      - html:/usr/share/nginx/html
    networks: [nginx-proxy-network]
  acme-companion:
    image: nginxproxy/acme-companion
    volumes_from: [nginx-proxy]
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - acme:/etc/acme.sh
    environment:
      DEFAULT_EMAIL: seu-email@exemplo.com
    networks: [nginx-proxy-network]
volumes: { certs: {}, html: {}, acme: {} }
networks:
  nginx-proxy-network:
    external: true
```

Se o TLS for feito por outro meio (Nginx do host, Traefik, Cloudflare), mantenha a rede externa e aponte o upstream para `mytask_nginx_prod:80`; as variáveis `VIRTUAL_*`/`LETSENCRYPT_*` ficam sem efeito.

## 3. Código no servidor

```bash
git clone <url-do-repositorio> mytask-tcc
cd mytask-tcc
git checkout <tag-ou-branch-de-producao>
```

| Arquivo | Função |
|---------|--------|
| `docker-compose.prod.yml` | Orquestração de produção |
| `.env.prod.example` → `.env` | Segredos e interpolação do Compose |
| `docker/nginx/production.conf` | Rotas `/` e `/api` |
| `docker/php/production.ini` | PHP e OPcache de produção |
| `backend/Dockerfile.prod`, `backend/docker/entrypoint.prod.sh` | Imagem PHP-FPM e inicialização |
| `frontend/Dockerfile.prod` | Build Nuxt + runtime Nitro |

## 4. Variáveis de ambiente

```bash
cp .env.prod.example .env
chmod 600 .env
echo "APP_KEY=base64:$(openssl rand -base64 32)"   # copie para o .env
echo "JWT_SECRET=$(openssl rand -base64 64)"        # copie para o .env
```

Gere os segredos no servidor; nunca reutilize os de `backend/.env` local. O Compose lê o `.env` **da raiz** e aborta com mensagem explícita se faltar uma variável obrigatória.

| Grupo | Variável | Obrig. | Padrão | Observação |
|-------|----------|--------|--------|-----------|
| Domínio/TLS | `VIRTUAL_HOST`, `LETSENCRYPT_HOST` | sim* | `mytask.tiagoaugusto.com` | *Se o TLS for pelo nginx-proxy |
| | `LETSENCRYPT_EMAIL` | recomendada | vazio | Contato do Let's Encrypt |
| | `APP_URL` | sim | `https://mytask.tiagoaugusto.com` | URL pública |
| Laravel | `APP_KEY`, `JWT_SECRET` | **sim** | | Trocar invalida tokens e sessões |
| | `APP_NAME` | não | `MyTask` | |
| | `JWT_TTL` | não | `60` | Minutos |
| | `LOG_LEVEL` | não | `warning` | `debug` só para diagnóstico |
| | `APP_ENV=production`, `APP_DEBUG=false`, `APP_TIMEZONE`, `APP_LOCALE=pt_BR`, `AUTH_GUARD=api`, `CACHE_STORE=database`, `QUEUE_CONNECTION=sync`, `SESSION_DRIVER=file`, `LOG_CHANNEL=stderr` | fixas | | Definidas no próprio Compose |
| Banco | `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD` | **sim** | | Alimentam o MySQL e o Laravel (`DB_HOST=mysql`) |
| | `MYSQL_DATABASE`, `MYSQL_USER` | não | `mytask` | |
| Admin | `ADMIN_NICKNAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` | para a seção 5.4 | `Administrador` / vazio / vazio | Usadas só na criação do administrador |
| Frontend | `NUXT_PUBLIC_TAM_FORM_URL` | não | vazio (desliga o TAM) | Exige rebuild do frontend |
| | `NUXT_PUBLIC_API_BASE`, `NUXT_PUBLIC_APP_TIMEZONE` | fixas | `/api`, `America/Sao_Paulo` | Definidas no Compose |

Exemplo mínimo de `.env`:

```dotenv
VIRTUAL_HOST=mytask.tiagoaugusto.com
LETSENCRYPT_HOST=mytask.tiagoaugusto.com
LETSENCRYPT_EMAIL=voce@exemplo.com
APP_URL=https://mytask.tiagoaugusto.com
APP_KEY=base64:cole-aqui
JWT_SECRET=cole-aqui
MYSQL_DATABASE=mytask
MYSQL_USER=mytask
MYSQL_PASSWORD=senha-forte-app
MYSQL_ROOT_PASSWORD=senha-forte-root
ADMIN_NICKNAME=Administrador
ADMIN_EMAIL=admin@exemplo.com
ADMIN_PASSWORD=senha-forte-admin
NUXT_PUBLIC_TAM_FORM_URL=
```

## 5. Primeiro deploy

### 5.1 Validar a configuração

```bash
docker compose -f docker-compose.prod.yml config >/dev/null && echo OK
```

### 5.2 Subir

```bash
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml ps
```

Esperados: `mytask_nginx_prod`, `mytask_backend_prod`, `mytask_frontend_prod`, `mytask_mysql_prod`. O backend só inicia após o healthcheck do MySQL.

### 5.3 Migrar o banco

```bash
docker compose -f docker-compose.prod.yml exec backend php artisan migrate --force
```

O entrypoint também aceita `RUN_MIGRATIONS=true` para migrar ao iniciar o contêiner, mas o fluxo recomendado é o comando explícito, para conferir o resultado.

### 5.4 Criar o administrador

**Não rode `db:seed` em produção:** o seeder cria 5 participantes fictícios. Crie só o pesquisador:

```bash
docker compose -f docker-compose.prod.yml exec backend php artisan tinker --execute="
\App\Models\User::query()->updateOrCreate(
  ['email' => env('ADMIN_EMAIL')],
  ['nickname' => env('ADMIN_NICKNAME', 'Administrador'), 'password' => env('ADMIN_PASSWORD'), 'role' => \App\Enums\RoleEnum::Admin]
);"
```

**Trocar a senha do administrador:** altere `ADMIN_PASSWORD` no `.env`, rode `docker compose -f docker-compose.prod.yml up -d backend` (para o contêiner ler o novo valor) e execute o mesmo comando acima. O botão "Resetar senha" do painel **não** serve para isso: ele só atua sobre participantes (RN-27). Depois, remova a senha do `.env` se quiser.

### 5.5 Teste de fumaça

```bash
curl -I https://mytask.tiagoaugusto.com/                       # SPA: 200
curl -s -o /dev/null -w "%{http_code}\n" -X POST https://mytask.tiagoaugusto.com/api/auth/login \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"email":"admin@exemplo.com","password":"senha-forte-admin"}'   # 200 (401 se errado)

TOKEN='<access_token>'
curl -H "Authorization: Bearer $TOKEN" https://mytask.tiagoaugusto.com/api/health   # {"status":"ok"}
```

## 6. Atualizar uma versão publicada

```bash
git pull
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec backend php artisan migrate --force
```

| Mudou | Comando |
|-------|---------|
| Código | Sequência acima (volumes preservados) |
| Só o `.env` do backend | `docker compose -f docker-compose.prod.yml up -d` |
| `NUXT_PUBLIC_*` | `docker compose -f docker-compose.prod.yml up -d --build frontend` |

## 7. Comandos operacionais

```bash
P="docker compose -f docker-compose.prod.yml"
$P ps
$P logs -f [backend|nginx|frontend|mysql]
$P exec backend php artisan migrate:status
$P exec backend php artisan route:list --path=api
$P restart backend
$P down            # mantém volumes
$P down -v         # CUIDADO: apaga banco e storage
```

## 8. Backup e restauração

```bash
# Backup (MYSQL_ROOT_PASSWORD exportada no shell)
docker compose -f docker-compose.prod.yml exec -T mysql \
  mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --databases mytask \
  > "backup-mytask-$(date +%F).sql"

# Restauração (sobrescreve o schema)
docker compose -f docker-compose.prod.yml exec -T mysql \
  mysql -u root -p"$MYSQL_ROOT_PASSWORD" < backup-mytask-AAAA-MM-DD.sql
```

Agende o backup diário com cron no host e copie o `.sql` para fora do servidor. Para a pesquisa, o essencial é o banco; o volume `backend_storage` guarda apenas arquivos auxiliares.

## 9. Segurança operacional

- `APP_DEBUG=false` fixo no Compose (evita vazar *stack traces*).
- Segredos apenas no `.env` do servidor, com `chmod 600`; o repositório ignora `/.env*` exceto `.env.prod.example`.
- MySQL sem porta publicada; nunca use o Compose de desenvolvimento em produção (publica 80/3306, monta o código e usa root sem senha).
- O papel `admin` não pode ser obtido pelo cadastro público; nasce somente pelo comando 5.4.
- O Laravel confia nos proxies (`trustProxies`) para respeitar `X-Forwarded-Proto`.
- O `storage/` é recriado pelo entrypoint a cada início (o diretório é excluído da imagem pelo `.dockerignore`).

## 10. Diagnóstico

| Sintoma | Verificação / solução |
|---------|----------------------|
| `network nginx-proxy-network ... could not be found` | `docker network create nginx-proxy-network` |
| `Defina APP_KEY no arquivo .env` (ou JWT/MySQL) | `.env` ausente na raiz ou variável vazia. Não use `backend/.env`. |
| Site abre, `/api` retorna 502/404 | `logs nginx backend`; backend `Up` e MySQL `healthy`; `location /api` ativo em `production.conf` |
| 502 só na API | `exec nginx ping -c 1 backend`; `exec backend php -v` |
| Erro de conexão com o banco | `DB_HOST` deve ser `mysql`. O MySQL não reaplica `MYSQL_PASSWORD` em volume já inicializado: alterar via root ou recriar o volume (apaga dados). |
| Todos deslogados após deploy | `APP_KEY` ou `JWT_SECRET` mudaram. |
| `storage/logs ... could not be opened` | Reiniciar o backend; conferir `exec backend ls -la storage/framework`. |
| Certificado não emite | DNS, portas 80/443, `LETSENCRYPT_EMAIL`, proxy e companion na mesma rede, `VIRTUAL_HOST` igual ao domínio. |
| Convite TAM não aparece | `NUXT_PUBLIC_TAM_FORM_URL` vazia ou frontend não reconstruído; o participante precisa ter 15+ dias de cadastro. |
| Página em branco após rebuild | Limpar cache do navegador; `logs frontend`. |

## 11. Checklist de go-live

- [ ] DNS e proxy TLS funcionando na rede `nginx-proxy-network`
- [ ] `.env` na raiz, `chmod 600`, sem valores de desenvolvimento
- [ ] `APP_KEY` e `JWT_SECRET` gerados no servidor
- [ ] Senhas fortes e distintas para MySQL (app e root) e administrador
- [ ] `APP_URL` e `VIRTUAL_HOST` iguais ao domínio HTTPS
- [ ] `up -d --build` e `migrate --force` executados
- [ ] Administrador criado **sem** `db:seed`
- [ ] Login, Kanban, projeto/sprint e painel do pesquisador testados
- [ ] `NUXT_PUBLIC_TAM_FORM_URL` preenchida antes do início do experimento
- [ ] Backup automático do MySQL agendado
