<?php

namespace Database\Seeders;

use App\Enums\SprintStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Popula a conta usada na apresentação do TCC com dados realistas.
 *
 * Execução (produção):
 *   php artisan db:seed --class=DemoPresentationSeeder --force
 *
 * O seeder é idempotente: remove o projeto de demonstração e as tarefas
 * gerais criadas por ele antes de recriá-los. Todas as datas são relativas
 * ao momento da execução, então rode-o próximo da data da apresentação.
 */
class DemoPresentationSeeder extends Seeder
{
    private const PROJECT_NAME = 'Clínica Bem-Estar — Portal de Agendamentos';

    private CarbonImmutable $now;

    private User $user;

    public function run(): void
    {
        $email = env('DEMO_USER_EMAIL', 'tiago.adag@gmail.com');

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            throw new RuntimeException("Usuário {$email} não encontrado. Crie a conta antes de rodar o seeder.");
        }

        $this->user = $user;
        $this->now = CarbonImmutable::now();

        DB::transaction(function (): void {
            $this->cleanup();
            $this->seedGeneralKanban();
            $this->seedProject();
        });

        $this->command?->info("Dados de demonstração criados para {$email}.");
    }

    private function cleanup(): void
    {
        $this->user->projects()->where('name', self::PROJECT_NAME)->get()->each->delete();

        $this->user->tasks()
            ->whereNull('project_id')
            ->whereIn('title', array_column($this->generalTasks(), 'title'))
            ->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Kanban Geral (tarefas sem projeto)
    |--------------------------------------------------------------------------
    */

    private function seedGeneralKanban(): void
    {
        foreach ($this->generalTasks() as $task) {
            $this->createTask(null, null, $task);
        }
    }

    /**
     * Cada tarefa descreve seu ciclo em dias/horas relativos a "agora":
     * - created: dias atrás em que foi criada
     * - started: dias atrás em que entrou em andamento
     * - completed: dias atrás em que foi concluída
     *
     * @return list<array<string, mixed>>
     */
    private function generalTasks(): array
    {
        return [
            // A FAZER
            ['title' => 'Revisar referências bibliográficas do TCC', 'description' => 'Conferir se todas as citações seguem a ABNT NBR 10520 e se constam nas referências.', 'status' => TaskStatus::Todo, 'created' => 3],
            ['title' => 'Agendar consulta no dentista', 'description' => null, 'status' => TaskStatus::Todo, 'created' => 2],
            ['title' => 'Renovar matrícula do próximo semestre', 'description' => 'Prazo no portal do aluno até o fim do mês.', 'status' => TaskStatus::Todo, 'created' => 5],
            ['title' => 'Estudar para a prova de Engenharia de Software', 'description' => 'Capítulos 4 a 7: requisitos, arquitetura e testes.', 'status' => TaskStatus::Todo, 'created' => 1],
            ['title' => 'Organizar arquivos do Google Drive', 'description' => null, 'status' => TaskStatus::Todo, 'created' => 8],

            // EM ANDAMENTO (3 = limite de WIP, útil para demonstrar o bloqueio)
            ['title' => 'Preparar slides da apresentação do TCC', 'description' => 'Estrutura: problema, objetivos, metodologia, resultados e conclusão.', 'status' => TaskStatus::InProgress, 'created' => 6, 'started' => 2],
            ['title' => 'Escrever capítulo de resultados', 'description' => 'Incluir os gráficos de cycle time e a taxa de conclusão das sprints.', 'status' => TaskStatus::InProgress, 'created' => 10, 'started' => 4],
            ['title' => 'Responder e-mails pendentes do orientador', 'description' => null, 'status' => TaskStatus::InProgress, 'created' => 1, 'started' => 0.2],

            // CONCLUÍDAS — distribuídas nos últimos 7 dias (gráfico semanal)
            ['title' => 'Aplicar questionário TAM com usuários', 'description' => 'Enviar o formulário para os participantes da validação.', 'status' => TaskStatus::Done, 'created' => 9, 'started' => 6, 'completed' => 0.1],
            ['title' => 'Pagar boleto da faculdade', 'description' => null, 'status' => TaskStatus::Done, 'created' => 2, 'started' => 1.2, 'completed' => 1],
            ['title' => 'Revisar resumo e abstract', 'description' => null, 'status' => TaskStatus::Done, 'created' => 4, 'started' => 2.5, 'completed' => 2],
            ['title' => 'Fazer backup do banco de produção', 'description' => 'Exportar dump do MySQL antes do deploy final.', 'status' => TaskStatus::Done, 'created' => 3, 'started' => 2.2, 'completed' => 2],
            ['title' => 'Atualizar currículo no LinkedIn', 'description' => null, 'status' => TaskStatus::Done, 'created' => 6, 'started' => 3.4, 'completed' => 3],
            ['title' => 'Comprar material de escritório', 'description' => null, 'status' => TaskStatus::Done, 'created' => 5, 'started' => 4.3, 'completed' => 4],
            ['title' => 'Ler artigo sobre Lei de Little', 'description' => 'Base teórica para a seção sobre limite de WIP.', 'status' => TaskStatus::Done, 'created' => 8, 'started' => 6, 'completed' => 5],
            ['title' => 'Configurar domínio e certificado SSL', 'description' => null, 'status' => TaskStatus::Done, 'created' => 7, 'started' => 6.5, 'completed' => 5],
            ['title' => 'Reunião com orientador', 'description' => 'Alinhar ajustes finais da monografia.', 'status' => TaskStatus::Done, 'created' => 7, 'started' => 6.1, 'completed' => 6],

            // CONCLUÍDAS — mais antigas (histórico / cycle time)
            ['title' => 'Definir tema e objetivos do TCC', 'description' => null, 'status' => TaskStatus::Done, 'created' => 60, 'started' => 58, 'completed' => 52],
            ['title' => 'Montar cronograma do semestre', 'description' => null, 'status' => TaskStatus::Done, 'created' => 45, 'started' => 44, 'completed' => 43],
            ['title' => 'Entregar relatório de estágio', 'description' => null, 'status' => TaskStatus::Done, 'created' => 30, 'started' => 25, 'completed' => 20],
            ['title' => 'Renovar livros da biblioteca', 'description' => null, 'status' => TaskStatus::Done, 'created' => 14, 'started' => 12, 'completed' => 12],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Projeto Scrum
    |--------------------------------------------------------------------------
    */

    private function seedProject(): void
    {
        /** @var Project $project */
        $project = $this->user->projects()->forceCreate([
            'name' => self::PROJECT_NAME,
            'description' => 'Sistema web para a Clínica Bem-Estar: agendamento online de consultas, '
                .'cadastro de pacientes, painel do profissional e lembretes automáticos.',
            'created_at' => $this->daysAgo(45),
            'updated_at' => $this->daysAgo(45),
        ]);

        $today = $this->now->startOfDay();

        // Sprint 1 — finalizada com 100% de conclusão
        $sprint1 = $this->createSprint($project, [
            'name' => 'Sprint 1 — Fundação',
            'goal' => 'Estruturar o projeto, autenticação e cadastro de pacientes.',
            'started_at' => $today->subDays(42),
            'ended_at' => $today->subDays(29),
            'status' => SprintStatus::Finished,
        ]);

        foreach ([
            ['title' => 'Configurar repositório e pipeline de CI', 'description' => 'GitHub Actions rodando testes e lint a cada push.', 'created' => 43, 'started' => 42, 'completed' => 41],
            ['title' => 'Modelar banco de dados inicial', 'description' => 'Tabelas de pacientes, profissionais e consultas.', 'created' => 43, 'started' => 41.5, 'completed' => 39],
            ['title' => 'Implementar login e cadastro de usuários', 'description' => 'Autenticação via JWT com recuperação de senha.', 'created' => 43, 'started' => 39, 'completed' => 35],
            ['title' => 'CRUD de pacientes', 'description' => null, 'created' => 43, 'started' => 36, 'completed' => 32],
            ['title' => 'Layout base e navegação', 'description' => 'Header, menu lateral e tema claro/escuro.', 'created' => 43, 'started' => 34, 'completed' => 30],
        ] as $task) {
            $this->createTask($project, $sprint1, [...$task, 'status' => TaskStatus::Done]);
        }

        // Sprint 2 — finalizada com pendências (demonstra migração de tarefas)
        $sprint2 = $this->createSprint($project, [
            'name' => 'Sprint 2 — Agendamentos',
            'goal' => 'Permitir que pacientes agendem consultas e profissionais gerenciem sua agenda.',
            'started_at' => $today->subDays(28),
            'ended_at' => $today->subDays(15),
            'status' => SprintStatus::Finished,
        ]);

        foreach ([
            ['title' => 'Cadastro de profissionais e especialidades', 'description' => null, 'status' => TaskStatus::Done, 'created' => 29, 'started' => 28, 'completed' => 26],
            ['title' => 'Tela de agenda do profissional', 'description' => 'Visualização semanal com horários livres e ocupados.', 'status' => TaskStatus::Done, 'created' => 29, 'started' => 26, 'completed' => 22],
            ['title' => 'Fluxo de agendamento pelo paciente', 'description' => 'Escolha de especialidade, profissional, data e horário.', 'status' => TaskStatus::Done, 'created' => 29, 'started' => 24, 'completed' => 19],
            ['title' => 'Validação de conflito de horários', 'description' => null, 'status' => TaskStatus::Done, 'created' => 29, 'started' => 20, 'completed' => 17],
            ['title' => 'Cancelamento e reagendamento de consultas', 'description' => 'Regra: cancelamento permitido até 24h antes.', 'status' => TaskStatus::InProgress, 'created' => 29, 'started' => 17],
            ['title' => 'Exportar agenda para PDF', 'description' => null, 'status' => TaskStatus::Todo, 'created' => 29],
        ] as $task) {
            $this->createTask($project, $sprint2, $task);
        }

        // Sprint 3 — ativa (quadro Kanban da sprint)
        $sprint3 = $this->createSprint($project, [
            'name' => 'Sprint 3 — Notificações e Painel',
            'goal' => 'Enviar lembretes automáticos e entregar o painel de indicadores da clínica.',
            'started_at' => $today->subDays(7),
            'ended_at' => $today->addDays(10),
            'status' => SprintStatus::Active,
        ]);

        foreach ([
            ['title' => 'Lembrete de consulta por e-mail', 'description' => 'Envio automático 24h antes da consulta.', 'status' => TaskStatus::Done, 'created' => 8, 'started' => 7, 'completed' => 5],
            ['title' => 'Confirmação de presença pelo paciente', 'description' => 'Link no e-mail para confirmar ou cancelar.', 'status' => TaskStatus::Done, 'created' => 8, 'started' => 5, 'completed' => 3],
            ['title' => 'Página de perfil do paciente', 'description' => null, 'status' => TaskStatus::Done, 'created' => 8, 'started' => 3, 'completed' => 1],
            ['title' => 'Integração com WhatsApp para lembretes', 'description' => 'Avaliar API oficial do WhatsApp Business.', 'status' => TaskStatus::InProgress, 'created' => 8, 'started' => 2],
            ['title' => 'Painel de indicadores da clínica', 'description' => 'Consultas por dia, taxa de faltas e profissionais mais procurados.', 'status' => TaskStatus::InProgress, 'created' => 8, 'started' => 1],
            ['title' => 'Testes automatizados do fluxo de agendamento', 'description' => null, 'status' => TaskStatus::Todo, 'created' => 8],
            ['title' => 'Histórico de consultas do paciente', 'description' => null, 'status' => TaskStatus::Todo, 'created' => 8],
            ['title' => 'Ajustes de acessibilidade (contraste e teclado)', 'description' => 'Seguir checklist WCAG 2.1 AA.', 'status' => TaskStatus::Todo, 'created' => 6],
        ] as $task) {
            $this->createTask($project, $sprint3, $task);
        }

        // Sprint 4 — em planejamento
        $sprint4 = $this->createSprint($project, [
            'name' => 'Sprint 4 — Pagamentos',
            'goal' => 'Permitir pagamento online da consulta e emissão de recibos.',
            'started_at' => $today->addDays(11),
            'ended_at' => $today->addDays(24),
            'status' => SprintStatus::Planning,
        ]);

        foreach ([
            ['title' => 'Integrar gateway de pagamento (Pix e cartão)', 'description' => null, 'created' => 4],
            ['title' => 'Emitir recibo em PDF após pagamento', 'description' => null, 'created' => 4],
            ['title' => 'Relatório financeiro mensal', 'description' => 'Receita por profissional e por especialidade.', 'created' => 3],
        ] as $task) {
            $this->createTask($project, $sprint4, [...$task, 'status' => TaskStatus::Todo]);
        }

        // Backlog do produto (sem sprint)
        foreach ([
            ['title' => 'Aplicativo mobile para pacientes', 'description' => 'Versão PWA ou app nativo — a definir.', 'created' => 40],
            ['title' => 'Teleconsulta por vídeo', 'description' => null, 'created' => 35],
            ['title' => 'Prontuário eletrônico', 'description' => 'Registro de evolução clínica com controle de acesso (LGPD).', 'created' => 30],
            ['title' => 'Lista de espera para horários lotados', 'description' => null, 'created' => 20],
            ['title' => 'Avaliação do atendimento pelo paciente', 'description' => 'Nota de 1 a 5 e comentário após a consulta.', 'created' => 12],
            ['title' => 'Login com conta Google', 'description' => null, 'created' => 6],
        ] as $task) {
            $this->createTask($project, null, [...$task, 'status' => TaskStatus::Backlog]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<string, mixed>  $data
     */
    private function createSprint(Project $project, array $data): Sprint
    {
        $createdAt = CarbonImmutable::parse($data['started_at'])->subDays(1)->setTime(10, 0);

        if ($createdAt->greaterThan($this->now)) {
            $createdAt = $this->now->subHours(2);
        }

        /** @var Sprint */
        return $project->sprints()->forceCreate([
            ...$data,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createTask(?Project $project, ?Sprint $sprint, array $data): Task
    {
        $status = $data['status'];

        $createdAt = $this->daysAgo($data['created']);
        $startedAt = in_array($status, [TaskStatus::InProgress, TaskStatus::Done], true)
            ? $this->daysAgo($data['started'])
            : null;
        $completedAt = $status === TaskStatus::Done ? $this->daysAgo($data['completed']) : null;

        /** @var Task */
        return $this->user->tasks()->forceCreate([
            'project_id' => $project?->getKey(),
            'sprint_id' => $sprint?->getKey(),
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $status,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'created_at' => $createdAt,
            'updated_at' => $completedAt ?? $startedAt ?? $createdAt,
        ]);
    }

    /**
     * Converte "dias atrás" (aceita frações, ex.: 0.5 = 12h) em timestamp.
     * A conversão é monotônica, garantindo created <= started <= completed.
     */
    private function daysAgo(float $days): CarbonImmutable
    {
        return $this->now->subMinutes((int) round($days * 24 * 60));
    }
}
