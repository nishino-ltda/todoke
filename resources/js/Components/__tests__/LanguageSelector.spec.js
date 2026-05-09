import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import LanguageSelector from '../LanguageSelector.vue'
import { createPinia, setActivePinia } from 'pinia'
import { createI18n } from 'vue-i18n'
import axios from '@/services/api'

// Mock axios
vi.mock('@/services/api', () => ({
  default: {
    patch: vi.fn(() => Promise.resolve({}))
  }
}))

// Mock auth store
const mockAuthStore = {
  isAuthenticated: false
}
vi.mock('@/stores/auth', () => ({
  useAuthStore: () => mockAuthStore
}))

// Mock Vuetify v-select
const VSelectStub = {
  template: `
    <select :value="modelValue" @input="$emit('update:modelValue', $event.target.value)">
      <option v-for="item in items" :key="item.code" :value="item.code">
        {{ item.name }}
      </option>
    </select>
  `,
  props: ['modelValue', 'items']
}

describe('LanguageSelector', () => {
  let i18n

  beforeEach(() => {
    setActivePinia(createPinia())
    i18n = createI18n({
      legacy: false,
      locale: 'en',
      messages: {
        en: {},
        'pt-BR': {}
      }
    })
    mockAuthStore.isAuthenticated = false
    vi.clearAllMocks()
  })

  it('renders correctly with language options', () => {
    const wrapper = mount(LanguageSelector, {
      global: {
        plugins: [i18n],
        stubs: {
          'v-select': VSelectStub
        }
      }
    })

    const select = wrapper.find('select')
    expect(select.exists()).toBe(true)
    const options = select.findAll('option')
    expect(options.length).toBe(2)
    expect(options[0].text()).toBe('English')
    expect(options[1].text()).toBe('Português (BR)')
  })

  it('updates locale when selection changes', async () => {
    const wrapper = mount(LanguageSelector, {
      global: {
        plugins: [i18n],
        stubs: {
          'v-select': VSelectStub
        }
      }
    })

    const select = wrapper.find('select')
    await select.setValue('pt-BR')
    
    expect(i18n.global.locale.value).toBe('pt-BR')
  })

  it('calls API to save preference when authenticated', async () => {
    mockAuthStore.isAuthenticated = true
    const wrapper = mount(LanguageSelector, {
      global: {
        plugins: [i18n],
        stubs: {
          'v-select': VSelectStub
        }
      }
    })

    const select = wrapper.find('select')
    await select.setValue('pt-BR')

    expect(axios.patch).toHaveBeenCalledWith('/api/v1/user/locale', { locale: 'pt-BR' })
  })

  it('does not call API when not authenticated', async () => {
    mockAuthStore.isAuthenticated = false
    const wrapper = mount(LanguageSelector, {
      global: {
        plugins: [i18n],
        stubs: {
          'v-select': VSelectStub
        }
      }
    })

    const select = wrapper.find('select')
    await select.setValue('pt-BR')

    expect(axios.patch).not.toHaveBeenCalled()
  })
})
