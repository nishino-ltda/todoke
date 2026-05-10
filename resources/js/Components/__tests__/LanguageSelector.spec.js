import { mount } from '@vue/test-utils'
import { describe, it, expect, vi, beforeEach } from 'vitest'
import LanguageSelector from '../LanguageSelector.vue'
import { createPinia, setActivePinia } from 'pinia'
import { createI18n } from 'vue-i18n'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import axios from '@/services/api'

vi.mock('@/services/api', () => ({
  default: {
    patch: vi.fn(() => Promise.resolve({}))
  }
}))

const mockAuthStore = {
  isAuthenticated: false
}
vi.mock('@/stores/auth', () => ({
  useAuthStore: () => mockAuthStore
}))

describe('LanguageSelector', () => {
  let i18n
  let vuetify

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
    vuetify = createVuetify({ components, directives })
    mockAuthStore.isAuthenticated = false
    vi.clearAllMocks()
  })

  it('renders current language code', () => {
    const wrapper = mount(LanguageSelector, {
      global: {
        plugins: [i18n, vuetify]
      }
    })

    expect(wrapper.text()).toBe('EN')
  })

  it('renders PT when locale is pt-BR', () => {
    i18n.global.locale.value = 'pt-BR'
    const wrapper = mount(LanguageSelector, {
      global: {
        plugins: [i18n, vuetify]
      }
    })

    expect(wrapper.text()).toBe('PT')
  })

  it('toggles locale on click', async () => {
    const wrapper = mount(LanguageSelector, {
      global: {
        plugins: [i18n, vuetify]
      }
    })

    expect(i18n.global.locale.value).toBe('en')
    await wrapper.find('[data-cy="language-selector"]').trigger('click')
    expect(i18n.global.locale.value).toBe('pt-BR')
  })

  it('calls API to save preference when authenticated', async () => {
    mockAuthStore.isAuthenticated = true
    const wrapper = mount(LanguageSelector, {
      global: {
        plugins: [i18n, vuetify]
      }
    })

    await wrapper.find('[data-cy="language-selector"]').trigger('click')

    expect(axios.patch).toHaveBeenCalledWith('/api/v1/user/locale', { locale: 'pt-BR' })
  })

  it('does not call API when not authenticated', async () => {
    mockAuthStore.isAuthenticated = false
    const wrapper = mount(LanguageSelector, {
      global: {
        plugins: [i18n, vuetify]
      }
    })

    await wrapper.find('[data-cy="language-selector"]').trigger('click')

    expect(axios.patch).not.toHaveBeenCalled()
  })
})
