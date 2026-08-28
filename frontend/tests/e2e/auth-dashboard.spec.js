import { expect, test } from '@playwright/test'

test.beforeEach(async ({ page }) => {
  await page.route('**/api/auth/login', async (route) => {
    await route.fulfill({
      json: {
        access_token: 'test-token',
        token_type: 'bearer',
        expires_in: 3600,
        user: {
          id: 1,
          nickname: 'Participante',
          email: 'participante@example.com',
          role: 'user',
          created_at: new Date().toISOString(),
        },
      },
    })
  })
  await page.route('**/api/dashboard/analytics', async (route) => {
    await route.fulfill({
      json: {
        data: {
          task_distribution: { todo: 2, in_progress: 1, done: 3, total: 6 },
          weekly_completed: [
            { date: '2026-08-21', count: 0 },
            { date: '2026-08-22', count: 1 },
            { date: '2026-08-23', count: 0 },
            { date: '2026-08-24', count: 2 },
            { date: '2026-08-25', count: 1 },
            { date: '2026-08-26', count: 0 },
            { date: '2026-08-27', count: 3 },
          ],
        },
      },
    })
  })
  await page.route('**/api/kanban/tasks', async (route) => {
    await route.fulfill({
      json: {
        data: [
          { id: 1, title: 'Tarefa de teste', description: null, status: 'TODO' },
        ],
      },
    })
  })
})

test('autentica e preserva as abas na URL', async ({ page }) => {
  await page.goto('/login')
  await page.getByLabel('E-mail').fill('participante@example.com')
  await page.getByLabel('Senha').fill('secret123')
  await page.getByRole('button', { name: 'Entrar' }).click()

  await expect(page).toHaveURL(/\/dashboard\?tab=inicio/)
  await expect(page.getByRole('heading', { name: 'Seu progresso' })).toBeVisible()

  await page.getByRole('link', { name: 'Minhas tarefas' }).first().click()
  await expect(page).toHaveURL(/tab=tarefas/)
  await expect(page.getByRole('heading', { name: 'Minhas tarefas' })).toBeVisible()
  await expect(page.getByText('Tarefa de teste')).toBeVisible()
})
