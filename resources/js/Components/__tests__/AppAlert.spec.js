import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import AppAlert from '../AppAlert.vue'

// Mock Vuetify components
const vuetifyStubs = {
  VAlert: {
    template: '<div class="v-alert"><slot /><button v-if="closable" class="close-btn" @click="$emit(\'click:close\')"></button></div>',
    props: ['closable', 'type', 'title', 'text']
  }
}

describe('AppAlert', () => {
  it('renders correctly when visible', () => {
    const wrapper = mount(AppAlert, {
      props: {
        modelValue: true,
        type: 'success',
        title: 'Success!',
        text: 'Action completed'
      },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    expect(wrapper.find('[data-cy="app-alert"]').exists()).toBe(true)
  })

  it('emits update:modelValue false when closed', async () => {
    const wrapper = mount(AppAlert, {
      props: {
        modelValue: true,
        closable: true
      },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    await wrapper.find('.close-btn').trigger('click')
    expect(wrapper.emitted('update:modelValue')[0]).toEqual([false])
  })

  it('starts timer if autoDismiss is true', async () => {
    vi.useFakeTimers()
    const wrapper = mount(AppAlert, {
      props: {
        modelValue: true,
        autoDismiss: 1000
      },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    vi.advanceTimersByTime(1000)
    expect(wrapper.emitted('update:modelValue')[0]).toEqual([false])
    vi.useRealTimers()
  })
})
