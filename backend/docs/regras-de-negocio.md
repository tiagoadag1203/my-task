# Regras de Negócio do MyTask

## 1. Cargos e permissões

Existem dois cargos mutuamente exclusivos:

- `user`: utiliza Kanban, projetos, sprints e tarefas.
- `admin`: acessa participantes, métricas e exportação científica.

Regras:

1. O cadastro público sempre cria `user`.
2. Um `admin` não acessa APIs operacionais.
3. Um `user` não acessa APIs administrativas.
4. Perfil e logout são compartilhados pelos dois cargos.
5. Cada `user` acessa somente seus próprios projetos, sprints e tarefas.

## 2. Kanban Geral

O Kanban Geral representa tarefas de rotina.

- `project_id = null`
- `sprint_id = null`
- Estado inicial `TODO`
- Estados válidos: `TODO`, `IN_PROGRESS`, `DONE`
- `BACKLOG` é proibido nesse contexto.

### Limite de WIP

Cada usuário pode manter no máximo três tarefas do Kanban Geral em `IN_PROGRESS`.

Ao tentar iniciar uma quarta tarefa, a API retorna `422` com:

> Para evitar sobrecarga mental e multitarefa ineficiente, finalize uma de suas tarefas em progresso antes de assumir um novo compromisso.

O limite é validado dentro de transação.

## 3. Projetos e backlog

Projetos representam objetivos estratégicos e não possuem status próprio.

Uma tarefa no backlog:

- pertence a um projeto;
- possui `sprint_id = null`;
- possui status `BACKLOG`.

Enquanto não for alocada em uma sprint, ela não pode mudar para `TODO`, `IN_PROGRESS` ou `DONE`.

Ao ser alocada em uma sprint planejada ou ativa:

- recebe o `sprint_id`;
- muda obrigatoriamente para `TODO`.

## 4. Ciclo de vida das sprints

Estados:

```text
PLANNING -> ACTIVE -> FINISHED
```

Regras:

1. Toda sprint nasce em `PLANNING`.
2. Apenas sprints em `PLANNING` podem ter nome, objetivo ou datas editados.
3. Apenas uma sprint pode ficar `ACTIVE` por projeto.
4. Apenas uma sprint `PLANNING` pode ser ativada.
5. Apenas uma sprint `ACTIVE` pode ser finalizada.
6. `FINISHED` é estado terminal.
7. `ended_at` deve ser posterior a `started_at`.

## 5. Fechamento de sprints

### Manual

O usuário altera uma sprint ativa para `FINISHED`.

### Automático

Ao fazer login ou acessar um projeto, o sistema procura sprints ativas do usuário cujo `ended_at` seja anterior à data atual. Elas são atualizadas para `FINISHED`.

Como o campo é uma data, a sprint permanece ativa durante todo o dia definido em `ended_at` e expira no início do dia seguinte.

### Tarefas pendentes

Finalizar uma sprint não move automaticamente suas tarefas. Tarefas incompletas permanecem vinculadas à sprint finalizada para preservar o histórico.

## 6. Exclusão de sprints

Ao excluir uma sprint:

- suas tarefas não são apagadas;
- `sprint_id` torna-se `null`;
- o status volta para `BACKLOG`;
- `started_at` e `completed_at` são limpos;
- a operação ocorre em transação.

## 7. Execução das tarefas

Tarefas de sprint podem transitar entre `TODO`, `IN_PROGRESS` e `DONE` somente quando a sprint está ativa.

### Timestamps científicos

- Ao entrar pela primeira vez em `IN_PROGRESS`, registra `started_at`.
- Mudanças posteriores para `IN_PROGRESS` preservam o primeiro início.
- Ao entrar em `DONE`, registra `completed_at`.
- Ao sair de `DONE`, limpa `completed_at`.
- Uma nova conclusão registra outro `completed_at`.

### WIP em sprint

Cada sprint possui limite independente de três tarefas simultâneas em `IN_PROGRESS`. O WIP de uma sprint não interfere no Kanban Geral nem em outra sprint.

## 8. Migração de tarefas

Uma tarefa pode ser movida quando:

1. Está no backlog e o destino é uma sprint `PLANNING` ou `ACTIVE`; ou
2. Está incompleta em uma sprint `FINISHED` e o destino é a sprint `ACTIVE`.

Restrições:

- origem e destino pertencem ao mesmo projeto;
- tarefa `DONE` não pode ser migrada;
- destino pertence ao mesmo usuário;
- destino não pode ser a sprint atual da tarefa;
- o status resultante é sempre `TODO`;
- `completed_at` fica nulo.

O reset impede entrada automática em `IN_PROGRESS` e consequente violação do WIP.

## 9. Exclusão de projetos e tarefas

- Excluir uma tarefa remove somente aquela tarefa.
- Excluir um projeto remove em cascata suas sprints e tarefas.
- Toda exclusão exige ownership validado por Policy.

## 10. Métricas científicas

### Tempo de ciclo

Calculado apenas para tarefas `DONE` com início e conclusão:

```text
cycle_time_seconds = completed_at - started_at
```

As médias são separadas em:

- Kanban: `project_id = null`.
- Scrum: `project_id != null`.
- Global: ambas.

O painel analítico converte as médias para horas. O CSV preserva `cycle_time_seconds` para permitir análises estatísticas com precisão.

### Conclusão de sprints

Para cada sprint finalizada com tarefas:

```text
taxa = tarefas DONE / total de tarefas * 100
```

O painel apresenta a média das taxas das sprints finalizadas.

## 11. Privacidade e exportação

O CSV não contém e-mail nem nickname.

Para cada exportação:

1. É criado um salt aleatório efêmero.
2. O ID do usuário é processado com HMAC-SHA256.
3. O hash identifica consistentemente o usuário somente dentro daquele arquivo.
4. O salt não é exportado nem persistido.

Campos:

```text
user_hash
task_type
created_at
started_at
completed_at
cycle_time_seconds
status_final
```

## 12. Redefinição administrativa de senha

- Somente `admin` executa a operação.
- Somente contas `user` podem ser redefinidas.
- A senha padrão definida pelo requisito é `123456`.
- O Model aplica hashing antes de persistir.
