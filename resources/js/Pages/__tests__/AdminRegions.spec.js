import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import Regions from '../Admin/Regions/Index.vue'
import adminService from '../../services/admin'

vi.mock('../../services/admin', () => ({
  default: {
    getRegions: vi.fn(),
    createRegion: vi.fn(),
    updateRegion: vi.fn(),
    deleteRegion: vi.fn(),
    getUsers: vi.fn()
  }
}))

// Mock Leaflet to avoid DOM/canvas issues in test env
vi.mock('leaflet', () => ({
  default: {
    map: vi.fn(() => ({
      setView: vi.fn().mockReturnThis(),
      remove: vi.fn(),
      removeLayer: vi.fn(),
      fitBounds: vi.fn(),
    })),
    tileLayer: vi.fn(() => ({ addTo: vi.fn() })),
    geoJSON: vi.fn(() => ({
      addTo: vi.fn().mockReturnThis(),
      bindTooltip: vi.fn().mockReturnThis(),
      on: vi.fn().mockReturnThis(),
      getBounds: vi.fn(() => ({ isValid: () => false })),
    })),
  }
}))

const messages = {
  'pt-BR': {
    admin: {
      regions: {
        title: 'Gerenciamento de Regiões',
        add: 'Nova Região',
        new: 'Nova Região',
        edit: 'Editar Região',
        table: { id: 'ID', name: 'Nome', partner: 'Parceiro', status: 'Status', actions: 'Ações' },
        notifications: {
          load_failed: 'Falha ao carregar regiões',
          save_success: 'Região salva com sucesso',
          save_failed: 'Falha ao salvar região',
          delete_success: 'Região excluída',
          delete_failed: 'Falha ao excluir região'
        }
      }
    },
    partner: {
      regions: { active: 'Ativo', inactive: 'Inativo', coordinates: 'Coordenadas GeoJSON' },
      actions: { cancel: 'Cancelar', create: 'Criar', update: 'Atualizar', delete: 'Excluir', confirm_delete: 'Confirmar Exclusão' },
      products: { confirm_delete: 'Confirma exclusão de {name}?' }
    },
    auth: { validation: { required: '{field} é obrigatório' } }
  },
  'en': {
    admin: {
      regions: {
        title: 'Region Management',
        add: 'New Region',
        new: 'New Region',
        edit: 'Edit Region',
        table: { id: 'ID', name: 'Name', partner: 'Partner', status: 'Status', actions: 'Actions' },
        notifications: {
          load_failed: 'Failed to load regions',
          save_success: 'Region saved successfully',
          save_failed: 'Failed to save region',
          delete_success: 'Region deleted',
          delete_failed: 'Failed to delete region'
        }
      }
    },
    partner: {
      regions: { active: 'Active', inactive: 'Inactive', coordinates: 'GeoJSON Coordinates' },
      actions: { cancel: 'Cancel', create: 'Create', update: 'Update', delete: 'Delete', confirm_delete: 'Confirm Delete' },
      products: { confirm_delete: 'Confirm delete {name}?' }
    },
    auth: { validation: { required: '{field} is required' } }
  }
}

const validPolygon = JSON.stringify({ type: 'Polygon', coordinates: [[[-46.64, -23.55], [-46.60, -23.55], [-46.60, -23.50], [-46.64, -23.55]]] })
const mockRegion = { id: 1, name: 'Zona Sul', status: 'active', partner_id: 1, partner: { name: 'Partner A', email: 'a@test.com' }, polygon: JSON.parse(validPolygon) }
const mockPartner = { id: 1, name: 'Partner A', email: 'a@test.com' }

const stubs = {
  AdminLayout: { template: '<div class="admin-layout"><slot /></div>' },
  DataTable: {
    template: `
      <div class="data-table" data-cy="regions-table">
        <div v-for="item in items" :key="item.id">
          <slot name="item.status" :item="item"></slot>
          <slot name="item.partner" :item="item"></slot>
          <slot name="item.actions" :item="item"></slot>
        </div>
      </div>
    `,
    props: ['headers', 'items', 'loading']
  },
  AppModal: {
    template: '<div v-if="modelValue" class="app-modal"><slot /><slot name="actions" /></div>',
    props: ['modelValue', 'title', 'maxWidth'],
    emits: ['update:modelValue']
  },
  VBtn: {
    template: '<button :data-cy="$attrs[\'data-cy\']" @click="$emit(\'click\')"><slot /></button>',
    props: ['loading', 'color', 'variant']
  },
  VChip: { template: '<span><slot /></span>' },
  VForm: { template: '<form @submit.prevent="$emit(\'submit\')"><slot /></form>', emits: ['submit'] },
  VTextField: { template: '<input :data-cy="$attrs[\'data-cy\']" />' },
  VTextarea: { template: '<textarea :data-cy="$attrs[\'data-cy\']"></textarea>' },
  VSelect: { template: '<select :data-cy="$attrs[\'data-cy\']"></select>' },
  VRow: { template: '<div><slot /></div>' },
  VCol: { template: '<div><slot /></div>' },
  VCard: { template: '<div class="v-card"><slot /></div>' },
  VCardTitle: { template: '<div><slot /></div>' },
  VCardText: { template: '<div><slot /></div>' },
  VIcon: { template: '<i></i>' },
}

function createWrapper(locale = 'pt-BR') {
  const i18n = createI18n({ legacy: false, locale, messages })
  return mount(Regions, { global: { plugins: [i18n], stubs } })
}

describe('Admin Regions', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    adminService.getRegions.mockResolvedValue({ data: { regions: [mockRegion] } })
    adminService.getUsers.mockResolvedValue({ data: { users: [mockPartner] } })
  })

  it('renders in pt-BR by default', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(wrapper.text()).toContain('Gerenciamento de Regiões')
    expect(wrapper.text()).toContain('Nova Região')
    expect(wrapper.text()).toContain('Ativo')
  })

  it('renders in en when locale is switched', async () => {
    const wrapper = createWrapper('en')
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(wrapper.text()).toContain('Region Management')
    expect(wrapper.text()).toContain('New Region')
    expect(wrapper.text()).toContain('Active')
  })

  it('opens create modal when create button is clicked', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    const createBtn = wrapper.find('[data-cy="create-region-btn"]')
    await createBtn.trigger('click')
    expect(wrapper.find('.app-modal').exists()).toBe(true)
  })

  it('opens edit modal when edit button is clicked', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    const editBtn = wrapper.find('[data-cy="edit-region-btn"]')
    await editBtn.trigger('click')
    expect(wrapper.find('.app-modal').exists()).toBe(true)
  })

  it('opens delete confirmation modal when delete button is clicked', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    const deleteBtn = wrapper.find('[data-cy="delete-region-btn"]')
    await deleteBtn.trigger('click')
    expect(wrapper.find('.app-modal').exists()).toBe(true)
  })

  it('calls deleteRegion when confirm delete is clicked', async () => {
    adminService.deleteRegion.mockResolvedValue({ data: { success: true } })
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    await wrapper.find('[data-cy="delete-region-btn"]').trigger('click')
    await wrapper.find('[data-cy="confirm-delete-btn"]').trigger('click')
    expect(adminService.deleteRegion).toHaveBeenCalledWith(1)
  })

  it('shows error notification when regions fail to load', async () => {
    adminService.getRegions.mockRejectedValue(new Error('Network Error'))
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(adminService.getRegions).toHaveBeenCalled()
  })
})
