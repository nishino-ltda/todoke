import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import Users from '../Admin/Users.vue'
import adminService from '../../services/admin'

// Mock admin service
vi.mock('../../services/admin', () => ({
  default: {
    getUsers: vi.fn(),
    manageUser: vi.fn()
  }
}))

const messages = {
  'pt-BR': {
    admin: {
      users: {
        title: 'Gerenciamento de Usuários',
        addUser: 'Adicionar Usuário',
        table: {
          name: 'Nome',
          role: 'Função',
          status: 'Status'
        },
        status: {
          active: 'Ativo',
          suspended: 'Suspenso'
        },
        roles: {
          customer: 'Cliente'
        },
        notifications: {
          update_success: 'Usuário {name} foi {action}',
          activated: 'ativado'
        }
      }
    }
  },
  'en': {
    admin: {
      users: {
        title: 'User Management',
        addUser: 'Add User',
        table: {
          name: 'Name',
          role: 'Role',
          status: 'Status'
        },
        status: {
          active: 'Active',
          suspended: 'Suspended'
        },
        roles: {
          customer: 'Customer'
        },
        notifications: {
          update_success: 'User {name} has been {action}',
          activated: 'activated'
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
            <td>{{ item.email }}</td>
            <td><slot name="item.role" :item="item">{{ item.role }}</slot></td>
            <td><slot name="item.status" :item="item">{{ item.active }}</slot></td>
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
  return mount(Users, {
    global: {
      plugins: [i18n],
      stubs
    }
  })
}

describe('Admin Users', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    
    adminService.getUsers.mockResolvedValue({
      data: [
        { id: 1, name: 'John Doe', email: 'john@example.com', role: 'customer', active: true }
      ]
    })
  })

  it('renders in pt-BR by default', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(wrapper.text()).toContain('Gerenciamento de Usuários')
    expect(wrapper.text()).toContain('Adicionar Usuário')
    expect(wrapper.text()).toContain('Ativo')
    expect(wrapper.text()).toContain('Cliente')
  })

  it('renders in en when locale is switched', async () => {
    const wrapper = createWrapper('en')
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(wrapper.text()).toContain('User Management')
    expect(wrapper.text()).toContain('Add User')
    expect(wrapper.text()).toContain('Active')
    expect(wrapper.text()).toContain('Customer')
  })

  it('can activate a suspended user', async () => {
    adminService.getUsers.mockResolvedValue({
      data: [
        { id: 1, name: 'John Doe', email: 'john@example.com', role: 'customer', active: false }
      ]
    })
    adminService.manageUser.mockResolvedValue({ success: true })
    
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    
    expect(wrapper.text()).toContain('Suspenso')
    
    const activateBtn = wrapper.find('[data-cy="activate-user-btn"]')
    await activateBtn.trigger('click')
    
    expect(adminService.manageUser).toHaveBeenCalledWith(1, 'activate')
  })
})
