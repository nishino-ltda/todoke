import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import Nodes from '../Admin/Nodes.vue'
import adminService from '../../services/admin'

// Mock admin service
vi.mock('../../services/admin', () => ({
  default: {
    getNodes: vi.fn(),
    updateNodeStatus: vi.fn()
  }
}))

const messages = {
  'pt-BR': {
    admin: {
      nodes: {
        title: 'Aprovação e Gerenciamento de Nós',
        table: {
          name: 'Nome',
          status: 'Status'
        },
        actions: {
          approve: 'Aprovar',
          reject: 'Rejeitar'
        },
        status: {
          pending: 'Pendente',
          approved: 'Aprovado'
        }
      }
    }
  },
  'en': {
    admin: {
      nodes: {
        title: 'Node Approval & Management',
        table: {
          name: 'Name',
          status: 'Status'
        },
        actions: {
          approve: 'Approve',
          reject: 'Reject'
        },
        status: {
          pending: 'Pending',
          approved: 'Approved'
        }
      }
    }
  }
}

const stubs = {
  AdminLayout: { template: '<div class="admin-layout"><slot /></div>' },
  DataTable: { 
    template: `
      <table>
        <thead>
          <tr><th v-for="h in headers">{{ h.title }}</th></tr>
        </thead>
        <tbody>
          <tr v-for="item in items">
            <td>{{ item.id }}</td>
            <td>{{ item.name }}</td>
            <td><slot name="item.status" :item="item">{{ item.status }}</slot></td>
            <td><slot name="item.actions" :item="item"></slot></td>
          </tr>
        </tbody>
      </table>
    `,
    props: ['headers', 'items', 'loading']
  },
  VBtn: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
  VChip: { template: '<span :class="color"><slot /></span>', props: ['color'] }
}

function createWrapper(locale = 'pt-BR') {
  const i18n = createI18n({
    legacy: false,
    locale,
    messages
  })
  return mount(Nodes, {
    global: {
      plugins: [i18n],
      stubs
    }
  })
}

describe('Admin Nodes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    
    adminService.getNodes.mockResolvedValue({
      data: [
        { id: 1, name: 'Main Hub', status: 'pending', owner_name: 'Owner 1', type: 'hub' }
      ]
    })
  })

  it('renders in pt-BR by default', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(wrapper.text()).toContain('Aprovação e Gerenciamento de Nós')
    expect(wrapper.text()).toContain('Pendente')
    expect(wrapper.text()).toContain('Aprovar')
  })

  it('renders in en when locale is switched', async () => {
    const wrapper = createWrapper('en')
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(wrapper.text()).toContain('Node Approval & Management')
    expect(wrapper.text()).toContain('Pending')
    expect(wrapper.text()).toContain('Approve')
  })

  it('can approve a pending node', async () => {
    adminService.updateNodeStatus.mockResolvedValue({ success: true })
    
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    
    const approveBtn = wrapper.find('[data-cy="approve-node-btn"]')
    await approveBtn.trigger('click')
    
    expect(adminService.updateNodeStatus).toHaveBeenCalledWith(1, 'approved')
  })
})
