import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import { createPinia, setActivePinia } from 'pinia'
import SettingsIndex from '../Admin/Settings/Index.vue'
import adminService from '../../services/admin'

vi.mock('../../services/admin', () => ({
  default: {
    getSettings: vi.fn(),
    updateSettings: vi.fn()
  }
}))

const mockSettings = {
  site_name: 'TODOKE',
  support_email: 'suporte@todoke.test',
  contact_phone: '+55 11 99999-0000',
  maintenance_mode: false,
  allow_registration: true,
  delivery_fee_base: 5.00,
  delivery_fee_per_km: 1.50
}

const messages = {
  'pt-BR': {
    admin: {
      settings: {
        title: 'Configurações do Sistema',
        save: 'Salvar',
        general: 'Configurações Gerais',
        fees: 'Taxas de Entrega',
        site_name: 'Nome do Site',
        support_email: 'Email de Suporte',
        contact_phone: 'Telefone',
        maintenance_mode: 'Modo de Manutenção',
        allow_registration: 'Permitir Cadastro',
        base_fee: 'Taxa Base',
        km_fee: 'Taxa por KM',
        notifications: {
          load_failed: 'Falha ao carregar configurações',
          update_success: 'Configurações atualizadas com sucesso',
          update_failed: 'Falha ao salvar configurações'
        }
      }
    }
  },
  'en': {
    admin: {
      settings: {
        title: 'System Configuration',
        save: 'Save',
        general: 'General Settings',
        fees: 'Delivery Fees',
        site_name: 'Site Name',
        support_email: 'Support Email',
        contact_phone: 'Phone',
        maintenance_mode: 'Maintenance Mode',
        allow_registration: 'Allow Registration',
        base_fee: 'Base Fee',
        km_fee: 'Fee per KM',
        notifications: {
          load_failed: 'Failed to load settings',
          update_success: 'Settings updated successfully',
          update_failed: 'Failed to save settings'
        }
      }
    }
  }
}

const stubs = {
  AdminLayout: { template: '<div class="admin-layout"><slot /></div>' },
  VBtn: {
    template: '<button :data-cy="$attrs[\'data-cy\']" :disabled="loading" @click="$emit(\'click\')"><slot /></button>',
    props: ['loading']
  },
  VTextField: {
    template: '<input :data-cy="$attrs[\'data-cy\']" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    props: ['modelValue'],
    emits: ['update:modelValue']
  },
  VSwitch: {
    template: '<input type="checkbox" :data-cy="$attrs[\'data-cy\']" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />',
    props: ['modelValue'],
    emits: ['update:modelValue']
  },
  VCard: { template: '<div class="v-card"><slot /></div>' },
  VCardTitle: { template: '<div class="v-card-title"><slot /></div>' },
  VCardText: { template: '<div><slot /></div>' },
  VRow: { template: '<div><slot /></div>' },
  VCol: { template: '<div><slot /></div>' },
  VDivider: { template: '<hr />' },
  VIcon: { template: '<i></i>' },
}

function createWrapper(locale = 'pt-BR') {
  const i18n = createI18n({ legacy: false, locale, messages })
  return mount(SettingsIndex, { global: { plugins: [i18n], stubs } })
}

describe('Admin Settings', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    adminService.getSettings.mockResolvedValue({ data: mockSettings })
    adminService.updateSettings.mockResolvedValue({ data: { success: true } })
  })

  it('renders in pt-BR by default', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(wrapper.text()).toContain('Configurações do Sistema')
    expect(wrapper.text()).toContain('Configurações Gerais')
    expect(wrapper.text()).toContain('Taxas de Entrega')
    expect(wrapper.text()).toContain('Salvar')
  })

  it('renders in en when locale is switched', async () => {
    const wrapper = createWrapper('en')
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(wrapper.text()).toContain('System Configuration')
    expect(wrapper.text()).toContain('General Settings')
    expect(wrapper.text()).toContain('Delivery Fees')
    expect(wrapper.text()).toContain('Save')
  })

  it('loads settings from API on mount', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(adminService.getSettings).toHaveBeenCalled()
    // form should be populated
    expect(wrapper.vm.form.site_name).toBe('TODOKE')
    expect(wrapper.vm.form.maintenance_mode).toBe(false)
  })

  it('calls updateSettings when save button is clicked', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    await wrapper.find('[data-cy="save-settings-btn"]').trigger('click')
    expect(adminService.updateSettings).toHaveBeenCalledWith(
      expect.objectContaining({ site_name: 'TODOKE' })
    )
  })

  it('maintenance mode toggle updates form state', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    const toggle = wrapper.find('[data-cy="setting-maintenance-mode"]')
    await toggle.setValue(true)
    expect(wrapper.vm.form.maintenance_mode).toBe(true)
  })

  it('shows save button visible and enabled when not saving', async () => {
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    const saveBtn = wrapper.find('[data-cy="save-settings-btn"]')
    expect(saveBtn.exists()).toBe(true)
    expect(saveBtn.attributes('disabled')).toBeFalsy()
  })

  it('handles load failure gracefully', async () => {
    adminService.getSettings.mockRejectedValue(new Error('Network Error'))
    const wrapper = createWrapper('pt-BR')
    await new Promise(resolve => setTimeout(resolve, 0))
    expect(adminService.getSettings).toHaveBeenCalled()
    // form should remain at defaults
    expect(wrapper.vm.form.site_name).toBe('')
  })
})
