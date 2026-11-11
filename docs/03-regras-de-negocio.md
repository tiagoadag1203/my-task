# 03. Regras de Negócio

Fonte única das regras do domínio. Cada regra tem um ID (`RN-xx`) para ser citada no texto do TCC, nos requisitos ([02](02-requisitos.md)) e nos testes. A coluna **Onde** indica a implementação no backend; o frontend apenas reflete essas regras na interface.

Valores de status: tarefas `BACKLOG | TODO | IN_PROGRESS | DONE`; sprints `PLANNING | ACTIVE | FINISHED`.

## 1. Papéis e acesso

| ID | Regra | Onde |
|----|-------|------|
| RN-01 | O cadastro público sempre cria uma conta `user`. O campo `role` não é aceito no cadastro. | `AuthController::register` |
| RN-02 | Um `admin` não acessa rotas operacionais (dashboard do participante, projetos, sprints, tarefas): resposta `403`. | middleware `role:user` |
| RN-03 | Um `user` não acessa rotas `/api/admin/*`: resposta `403`. | middleware `role:admin` |
| RN-04 | Perfil (`/auth/me`), logout e `/health` são comuns aos dois papéis. | `routes/api.php` |
| RN-05 | Cada `user` acessa somente os próprios projetos, sprints e tarefas. Consultas partem do usuário autenticado, então acesso a registro de outro usuário retorna `404` (o recurso "não existe" para ele). As Policies reforçam a verificação. | Controllers + `app/Policies` |

## 2. Kanban Geral

| ID | Regra | Onde |
|----|-------|------|
| RN-06 | Tarefa do Kanban Geral tem `project_id = null` e `sprint_id = null`, nasce em `TODO` e só aceita `TODO`, `IN_PROGRESS` e `DONE`. `BACKLOG` é proibido nesse contexto. | `TaskService::createKanban`, `validateStatusForContext` |
| RN-07 | **WIP do Kanban Geral:** cada usuário pode ter no máximo **3** tarefas do Kanban Geral em `IN_PROGRESS`. A quarta tentativa retorna `422` com a mensagem educativa abaixo. A contagem ocorre dentro de transação com bloqueio de linha (`lockForUpdate`) para evitar condição de corrida. | `TaskService::ensureWipLimit` |

Mensagem educativa (RN-07 e RN-21):

> Para evitar sobrecarga mental e multitarefa ineficiente, finalize uma de suas tarefas em progresso antes de assumir um novo compromisso.

## 3. Projetos e backlog

| ID | Regra | Onde |
|----|-------|------|
| RN-08 | Projetos representam objetivos estratégicos e não possuem status próprio. Excluir um projeto remove em cascata suas sprints e tarefas. | FK `ON DELETE CASCADE` |
| RN-09 | Tarefa criada no backlog pertence ao projeto, tem `sprint_id = null` e status `BACKLOG`. Enquanto não for alocada a uma sprint, não pode mudar para `TODO`, `IN_PROGRESS` ou `DONE`. | `TaskService::createBacklog`, `validateStatusForContext` |
| RN-10 | Tarefa vinculada a uma sprint não pode voltar diretamente para `BACKLOG` (o caminho de volta é excluir a sprint, RN-19). | `validateStatusForContext` |

## 4. Ciclo de vida das sprints

```text
            ativar (manual)            finalizar (manual ou auto-close)
PLANNING ─────────────────► ACTIVE ─────────────────────────────────► FINISHED (terminal)
```

| ID | Regra | Onde |
|----|-------|------|
| RN-11 | Toda sprint nasce em `PLANNING`. | `SprintService::create` |
| RN-12 | Somente sprints em `PLANNING` podem ter nome, objetivo ou datas editados. | `SprintService::update` |
| RN-13 | `ended_at` deve ser estritamente posterior a `started_at` (datas sem hora). | `StoreSprintRequest`, `UpdateSprintRequest` |
| RN-14 | Transições válidas: `PLANNING → ACTIVE` e `ACTIVE → FINISHED`. `FINISHED` é terminal. Pedir o status em que a sprint já está não gera erro (operação idempotente). Qualquer outra transição retorna `422`. | `SprintService::changeStatus` |
| RN-15 | Apenas **uma** sprint `ACTIVE` por projeto. Ativar uma segunda retorna `422`. Verificação feita com bloqueio de linha. | `SprintService::activate` |
| RN-16 | A ativação é manual e não depende das datas: uma sprint pode ser ativada antes de `started_at`. As datas servem para o prazo (RN-17). | `SprintService::activate` |

## 5. Encerramento de sprints

| ID | Regra | Onde |
|----|-------|------|
| RN-17 | **Auto-close:** sprints `ACTIVE` do usuário com `ended_at` anterior à data de hoje passam para `FINISHED`. A verificação ocorre no **login**, ao abrir um **projeto** (`GET /projects/{id}`) e ao **listar as sprints** de um projeto. Como `ended_at` é uma data, a sprint continua ativa durante todo o dia final e expira no início do dia seguinte. | `SprintService::autoCloseExpired` |
| RN-18 | Finalizar uma sprint (manual ou automaticamente) **não move** suas tarefas. Tarefas incompletas permanecem vinculadas à sprint finalizada, preservando o histórico, e podem ser migradas depois (RN-23). | `SprintService::changeStatus` |

## 6. Exclusão de sprints

| ID | Regra | Onde |
|----|-------|------|
| RN-19 | Ao excluir uma sprint (em qualquer estado), suas tarefas **não são apagadas**: recebem `sprint_id = null`, status `BACKLOG`, e `started_at`/`completed_at` são limpos. Tudo ocorre em uma transação. | `SprintService::delete` |

## 7. Execução de tarefas

| ID | Regra | Onde |
|----|-------|------|
| RN-20 | O **status** de uma tarefa de sprint só pode mudar quando a sprint está `ACTIVE`. Título e descrição podem ser editados pela API em qualquer estado da sprint, desde que o status enviado seja o mesmo; a interface, porém, só permite edição na sprint ativa. | `validateStatusForContext` |
| RN-21 | **WIP da sprint:** cada sprint tem limite independente de **3** tarefas em `IN_PROGRESS`. O WIP de uma sprint não interfere no Kanban Geral nem em outra sprint. | `TaskService::ensureWipLimit` |
| RN-22 | **Timestamps de processo:** na primeira entrada em `IN_PROGRESS` grava `started_at`; entradas posteriores preservam o primeiro início. Ao entrar em `DONE` grava `completed_at`; ao sair de `DONE` limpa `completed_at`; uma nova conclusão grava novo `completed_at`. | `TaskService::update` |

## 8. Migração de tarefas

| ID | Regra | Onde |
|----|-------|------|
| RN-23 | Uma tarefa pode ser migrada em dois casos: **(a)** está no backlog e o destino é uma sprint `PLANNING` ou `ACTIVE`; **(b)** está incompleta em uma sprint `FINISHED` e o destino é uma sprint `ACTIVE`. | `TaskService::validateMigrationTarget` |
| RN-24 | Restrições: a tarefa precisa pertencer a um projeto (Kanban Geral não migra); não pode estar `DONE`; origem e destino pertencem ao mesmo projeto e ao mesmo usuário; o destino não pode ser a sprint atual da tarefa. Violação retorna `422` no campo `current_sprint_id`. | `TaskService::migrate` |
| RN-25 | Resultado da migração: status sempre `TODO` e `completed_at = null`. O `started_at` é **preservado**. O reset para `TODO` impede que tarefas antigas entrem como `IN_PROGRESS` e violem o WIP da nova sprint sem planejamento consciente. | `TaskService::migrate` |

## 9. Exclusão de tarefas

| ID | Regra | Onde |
|----|-------|------|
| RN-26 | Excluir uma tarefa remove definitivamente somente aquela tarefa (não há lixeira nem *soft delete*). Exige ownership (RN-05). | `TaskService::delete` |

## 10. Administração

| ID | Regra | Onde |
|----|-------|------|
| RN-27 | Somente `admin` redefine senhas, e somente de contas `user`. Tentar redefinir a senha de um `admin` retorna `404`. A senha padrão definida pelo requisito é `123456`; o Model aplica hash antes de persistir. | `Admin\UserController::resetPassword` |
| RN-28 | A listagem de participantes nunca inclui administradores. Os contadores de usuários no painel também consideram apenas `user`. | `Admin\UserController::index`, `AnalyticsService::summary` |

## 11. Métricas e privacidade

As regras de cálculo do tempo de ciclo, da taxa de conclusão de sprints e da pseudonimização do CSV estão em [11. Pesquisa e LGPD](11-pesquisa-e-lgpd.md) (RN-29 a RN-32).
