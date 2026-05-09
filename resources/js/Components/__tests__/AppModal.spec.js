import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import AppModal from '../AppModal.vue'

// Mock Vuetify components
const vuetifyStubs = {
  VDialog: {
    template: '<div class="v-dialog" v-if="modelValue"><slot /></div>',
    props: {
      modelValue: Boolean
    }
  },
  VCard: {
    template: '<div class="v-card"><slot /></div>'
  },
  VCardTitle: {
    template: '<div class="v-card-title"><slot /></div>'
  },
  VCardText: {
    template: '<div class="v-card-text"><slot /></div>'
  },
  VCardActions: {
    template: '<div class="v-card-actions"><slot /></div>'
  },
  VDivider: {
    template: '<hr />'
  },
  VBtn: {
    template: '<button class="v-btn" @click="$emit(\'click\')"><slot /></button>'
  },
  VSpacer: {
    template: '<div class="v-spacer"></div>'
  }
}

describe('AppModal', () => {
  it('renders correctly when visible', () => {
    const wrapper = mount(AppModal, {
      props: {
        modelValue: true,
        title: 'Test Modal'
      },
      slots: {
        default: 'Modal content'
      },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    expect(wrapper.find('[data-cy="app-modal"]').exists()).toBe(true)
    expect(wrapper.find('[data-cy="modal-title"]').text()).toBe('Test Modal')
    expect(wrapper.find('[data-cy="modal-content"]').text()).toBe('Modal content')
  })

  it('emits close event when close button is clicked', async () => {
    const wrapper = mount(AppModal, {
      props: {
        modelValue: true,
        showClose: true
      },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    await wrapper.find('[data-cy="modal-close-btn"]').trigger('click')
    expect(wrapper.emitted('update:modelValue')[0]).toEqual([false])
    expect(wrapper.emitted('close')).toBeTruthy()
  })

  it('renders actions slot', () => {
    const wrapper = mount(AppModal, {
      props: {
        modelValue: true
      },
      slots: {
        actions: '<button id="action-btn">Action</button>'
      },
      global: {
        stubs: vuetifyStubs
      }
    })
    
    expect(wrapper.find('#action-btn').exists()).toBe(true)
  })
})
