import { expect, test } from '@playwright/test'

test('separa a navegação e os dados do pesquisador', async ({ page }) => {
  await page.route('**/api/auth/login', (route) =>
    route.fulfill({
      json: {
        access_token: 'admin-token',
        expires_in: 3600,
        user: {
          id: 2,
          nickname: 'Pesquisador',
          email: 'admin@example.com',
          role: 'admin',
          created_at: new Date().toISOString(),
        },
      },
    }),
  )
  await page.route('**/api/admin/summary', (route) =>
    route.fulfill({
      json: {
        data: {
          users: 10,
          projects: 7,
          tasks: 40,
          completed_tasks: 25,
          sprints: 8,
          active_sprints: 2,
          finished_sprints: 5,
        },
      },
    }),
  )
  await page.route('**/api/admin/analytics', (route) =>
    route.fulfill({
      json: {
        data: {
          cycle_time_hours: {
            global_average: 3,
            kanban_average: 2,
            scrum_average: 4,
            sample_size: 25,
          },
          sprints: { finished_count: 5, average_completion_rate: 72 },
        },
      },
    }),
  )
  await page.route('**/api/admin/users**', (route) =>
    route.fulfill({
      json: {
        data: [{ id: 3, nickname: 'Pessoa', email: 'pessoa@example.com', created_at: '2026-08-01T12:00:00Z' }],
        meta: { current_page: 1, last_page: 1, total: 1 },
      },
    }),
  )

  await page.goto('/login')
  await page.getByLabel('E-mail').fill('admin@example.com')
  await page.getByLabel('Senha').fill('secret123')
  await page.getByRole('button', { name: 'Entrar' }).click()

  await expect(page).toHaveURL(/tab=visao-geral/)
  await expect(page.getByRole('heading', { name: 'Visão geral' })).toBeVisible()
  await expect(page.getByRole('link', { name: 'Minhas tarefas' })).toHaveCount(0)

  await page.getByRole('link', { name: 'Participantes' }).first().click()
  await expect(page.getByText('pessoa@example.com')).toBeVisible()
})
