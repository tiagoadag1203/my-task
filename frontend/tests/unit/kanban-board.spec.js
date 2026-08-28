import { mountSuspended } from '@nuxt/test-utils/runtime'
import { describe, expect, it } from 'vitest'
import KanbanBoard from '../../app/components/kanban/KanbanBoard.vue'

describe('KanbanBoard', () => {
  const tasks = [
    { id: 1, title: 'Planejar semana', description: null, status: 'TODO' },
    { id: 2, title: 'Executar tarefa', description: null, status: 'IN_PROGRESS' },
  ]

  it('reutiliza as três colunas e troca status só por arrastar', async () => {
    const wrapper = await mountSuspended(KanbanBoard, { props: { tasks } })

    expect(wrapper.text()).toContain('A fazer')
    expect(wrapper.text()).toContain('Em progresso')
    expect(wrapper.text()).toContain('Concluído')
    expect(wrapper.text()).not.toContain('Iniciar')
    expect(wrapper.text()).not.toContain('Concluir')

    wrapper.findComponent({ name: 'KanbanColumn' }).vm.$emit('drop-task', 1, 'IN_PROGRESS')
    await wrapper.vm.$nextTick()

    expect(wrapper.emitted('move')?.[0]).toEqual([tasks[0], 'IN_PROGRESS'])
  })

  it('guarda editar e excluir no menu de ações', async () => {
    const wrapper = await mountSuspended(KanbanBoard, { props: { tasks } })
    const menuButton = wrapper.get('button[aria-haspopup="menu"]')

    await menuButton.trigger('click')

    expect(wrapper.text()).toContain('Editar')
    expect(wrapper.text()).toContain('Excluir')
  })

  it('não oferece ações quando o board é somente leitura', async () => {
    const wrapper = await mountSuspended(KanbanBoard, {
      props: { tasks, readonly: true },
    })

    expect(wrapper.findAll('button')).toHaveLength(0)
  })
})
