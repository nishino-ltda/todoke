import { mount } from '@vue/test-utils'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import AddressManagerModal from '../AddressManagerModal.vue'

vi.mock('@/services/api', () => ({
  default: {
    get: vi.fn().mockResolvedValue({ data: { data: [] } }),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
    patch: vi.fn(),
  }
}))

describe('AddressManagerModal', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  const globalMocks = {
    $t: (key) => key,
  }

  it('renders when modelValue is true', () => {
    const wrapper = mount(AddressManagerModal, {
      props: {
        modelValue: true,
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
          'v-btn': { template: '<button><slot /></button>' },
          'v-icon': { template: '<span />' },
          'v-spacer': { template: '<span />' },
          'v-list': { template: '<div><slot /></div>' },
          'v-list-item': { template: '<div><slot /></div>' },
          'v-list-item-title': { template: '<div><slot /></div>' },
          'v-list-item-subtitle': { template: '<div><slot /></div>' },
          'v-alert': { template: '<div><slot /></div>' },
          'v-form': { template: '<div><slot /></div>' },
          'v-select': { template: '<div />' },
          'v-text-field': { template: '<div />' },
          'v-row': { template: '<div><slot /></div>' },
          'v-col': { template: '<div><slot /></div>' },
          'v-switch': { template: '<div />' },
        }
      }
    })

    expect(wrapper.exists()).toBe(true)
  })
})
