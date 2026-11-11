# 11. Métricas, Pesquisa e LGPD

Como o MyTask coleta, calcula e exporta dados de uso, e como protege a privacidade dos usuários.

> **Escopo no TCC:** a validação do protótipo no TCC foi feita exclusivamente pelo uso do próprio autor, sem participantes externos e sem análise estatística. O painel do administrador, a exportação CSV e o convite para o questionário TAM descritos aqui foram implementados para viabilizar estudos futuros com uma amostra de usuários (mediante submissão a comitê de ética) e **não foram utilizados** na validação do trabalho.

## 1. Dados coletados

| Dado | Origem | Finalidade | Visível ao pesquisador? |
|------|--------|-----------|------------------------|
| Apelido (`nickname`) | Cadastro | Personalizar a interface | Sim, na lista de participantes |
| E-mail | Cadastro | Login e suporte (redefinição de senha) | Sim, na lista de participantes |
| Senha | Cadastro | Autenticação | Não (somente hash) |
| Data de cadastro | Automático | Convite TAM, contexto da amostra | Sim |
| Título e descrição das tarefas, nomes de projetos e sprints | Participante | Uso da ferramenta | **Não**: não aparecem no painel nem no CSV |
| Status e timestamps das tarefas (`created_at`, `started_at`, `completed_at`) | Automático | Métricas de processo | Sim, agregados no painel e por tarefa no CSV (pseudonimizado) |
| Status e datas das sprints | Participante / automático | Taxa de conclusão | Sim, agregados |

Não são coletados nome completo, documento, telefone, endereço ou qualquer dado sensível (minimização, art. 6º, III da LGPD).

## 2. Métricas

| ID | Métrica | Definição | Onde aparece |
|----|---------|-----------|--------------|
| RN-29 | **Tempo de ciclo** | `completed_at - started_at`, apenas para tarefas `DONE` com ambos os campos preenchidos. Médias separadas: **Kanban** (`project_id` nulo), **Scrum** (`project_id` preenchido) e **Global** (ambas). O painel converte para horas (2 casas); o CSV mantém segundos. `sample_size` = número de tarefas consideradas. | `GET /admin/analytics`, CSV |
| RN-30 | **Taxa de conclusão de sprints** | Para cada sprint `FINISHED` com ao menos uma tarefa: `tarefas DONE / total de tarefas × 100`. O painel mostra a média simples dessas taxas. | `GET /admin/analytics` |
| | Distribuição do participante | Contagem atual de `TODO`, `IN_PROGRESS`, `DONE` no Kanban Geral do próprio participante. | `GET /dashboard/analytics` |
| | Conclusões da semana | Tarefas com `completed_at` em cada um dos últimos 7 dias (fuso de São Paulo), todos os contextos. | `GET /dashboard/analytics` |

### 2.1 Limitações metodológicas (declarar no TCC)

Estes comportamentos são consequência direta das regras atuais e afetam a interpretação dos números:

1. **Retrabalho entra no tempo de ciclo.** `started_at` registra a *primeira* entrada em `IN_PROGRESS` e nunca é reiniciado ao reabrir uma tarefa ou migrá-la (RN-22, RN-25). Uma tarefa iniciada na Sprint 1 e concluída na Sprint 3 terá o tempo de ciclo das três sprints.
2. **Tarefas que pulam `IN_PROGRESS` ficam fora da amostra.** A API permite mover `TODO → DONE` diretamente; essas tarefas não têm `started_at` e não entram no tempo de ciclo (aparecem no CSV com `cycle_time_seconds` vazio).
3. **Migração altera a taxa de conclusão da sprint de origem.** Ao migrar uma pendência, ela deixa de pertencer à sprint finalizada; o denominador diminui e a taxa daquela sprint sobe (no limite, 100%). A taxa reflete o estado *atual* do banco, não o estado no momento do encerramento.
4. **Exclusões apagam histórico.** Excluir tarefa ou projeto remove os registros definitivamente; excluir sprint devolve as tarefas ao backlog e limpa seus timestamps (RN-19, RN-26).
5. **`SCRUM` inclui o backlog.** No CSV, qualquer tarefa com projeto é `SCRUM`, mesmo que nunca tenha entrado em sprint.
6. **Sem recorte temporal.** Painel e exportação consideram toda a base; recortes por período devem ser feitos na análise (Excel, R, SPSS) usando os timestamps.

Esses pontos devem ser tratados antes de um estudo com participantes (por exemplo, registrando o resultado da sprint no momento do encerramento).

## 3. Exportação CSV

`GET /api/admin/export` (somente `admin`). Arquivo UTF-8, separador vírgula, uma linha por tarefa, ordenado por `id`.

| Coluna | Conteúdo |
|--------|----------|
| `user_hash` | HMAC-SHA256 do ID do usuário com salt efêmero (ver RN-31) |
| `task_type` | `KANBAN` (sem projeto) ou `SCRUM` (com projeto) |
| `created_at` | ISO 8601 |
| `started_at` | ISO 8601 ou vazio |
| `completed_at` | ISO 8601 ou vazio |
| `cycle_time_seconds` | Inteiro, ou vazio se faltar início ou conclusão |
| `status_final` | `BACKLOG`, `TODO`, `IN_PROGRESS` ou `DONE` no momento da exportação |

| ID | Regra de privacidade |
|----|---------------------|
| RN-31 | O CSV **nunca** contém e-mail, apelido, títulos ou descrições. O participante é identificado apenas por `user_hash`. |
| RN-32 | A cada exportação é gerado um salt aleatório de 64 caracteres, que **não é salvo nem exportado**. O `user_hash` é consistente dentro de um arquivo (permite agrupar por participante) mas muda entre exportações, então arquivos diferentes não podem ser cruzados entre si. |

Consequência prática: em um estudo futuro, a análise estatística deve usar **uma única exportação**, feita ao término da coleta.

## 4. Questionário pós-uso (TAM)

- Hospedado no Google Forms; a URL é configurada em `NUXT_PUBLIC_TAM_FORM_URL` (vazia desliga o convite).
- O participante vê um cartão fixo no dashboard quando o cadastro completa **15 dias**; o link abre em nova aba.
- O descarte do cartão fica no `localStorage` do navegador (por usuário). Em outro navegador ou dispositivo o cartão reaparece.
- As respostas não ficam no banco do MyTask e **não há vínculo automático** entre resposta do formulário e dados de uso. Se a análise exigir esse vínculo, é preciso planejar um identificador.

## 5. Medidas de proteção

| Medida | Implementação |
|--------|---------------|
| Minimização | Cadastro com apelido, e-mail e senha apenas |
| Pseudonimização | `user_hash` com salt efêmero (RN-31, RN-32) |
| Controle de acesso | Papéis estritos (RN-02, RN-03) e isolamento por usuário (RN-05) |
| Confidencialidade | Senhas com hash; JWT; HTTPS em produção; banco sem porta pública |
| Segregação do pesquisador | O `admin` não acessa conteúdo das tarefas, apenas agregados e a lista de contas |

## 6. Requisitos para um estudo futuro com participantes

- **Comitê de ética e consentimento:** submeter o estudo a comitê de ética em pesquisa e apresentar Termo de Consentimento Livre e Esclarecido (TCLE) e aviso de privacidade no cadastro, recursos que a plataforma ainda não possui.
- **Senha padrão na redefinição:** `123456` é conhecida; o participante não é obrigado a trocá-la e não existe tela de troca de senha. Recomenda-se implementar a troca de senha pelo próprio usuário antes de um estudo com participantes.
- **Encerramento da coleta:** definir o que acontece com as contas e dados após a coleta (retenção e eliminação, art. 15 e 16 da LGPD).
