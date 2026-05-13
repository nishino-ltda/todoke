import { mount } from '@vue/test-utils'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import MapPinpointModal from '../MapPinpointModal.vue'

vi.mock('@/services/map', () => ({
  default: {
    reverseGeocode: vi.fn().mockResolvedValue({ data: { address: 'Test Address, SP' } }),
  }
}))

describe('MapPinpointModal', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  const globalMocks = {
    $t: (key) => key,
  }

  it('renders when modelValue is true', () => {
    const wrapper = mount(MapPinpointModal, {
      props: {
        modelValue: true,
        lat: -23.5505,
        lng: -46.6333,
      },
      global: {
        mocks: globalMocks,
        stubs: {
          'v-dialog': {
            template: '<div><slot /></div>',
            props: ['modelValue'],
          },
          'v-card': { template: '<div><slot /></div>' },
          'v-card-title': { template: '<div><slot /></div>' },
          'v-card-text': { template: '<div><slot /></div>' },
          'v-card-actions': { template: '<div><slot /></div>' },
          'v-btn': { template: '<button @click="$emit(\'click\')"><slot /></button>' },
          'v-icon': { template: '<span />' },
          'v-spacer': { template: '<span />' },
          'v-row': { template: '<div><slot /></div>' },
          'v-col': { template: '<div><slot /></div>' },
          'v-text-field': { template: '<div><input /></div>' },
        }
      }
    })

    expect(wrapper.exists()).toBe(true)
  })
})
