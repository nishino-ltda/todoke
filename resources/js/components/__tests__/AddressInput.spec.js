import { mount } from '@vue/test-utils'
import AddressInput from '../AddressInput.vue'

// Stub for Vuetify's v-textarea
const VTextareaStub = {
  template: `
    <div>
      <textarea 
        :value="modelValue" 
        @input="$emit('update:modelValue', $event.target.value)"
        class="v-textarea-stub"
      ></textarea>
      <div class="v-textarea-errors" v-if="errorMessages">
        {{ errorMessages.join(', ') }}
      </div>
    </div>
  `,
  props: ['modelValue', 'errorMessages']
}

describe('AddressInput', () => {
  it('renders a textarea for address input', () => {
    const wrapper = mount(AddressInput, {
      props: {
        modelValue: ''
      },
      global: {
        stubs: {
          'v-textarea': VTextareaStub
        }
      }
    })
    expect(wrapper.find('.v-textarea-stub').exists()).toBe(true)
  })

  it('emits update event when address changes', async () => {
    const wrapper = mount(AddressInput, {
      props: {
        modelValue: ''
      },
      global: {
        stubs: {
          'v-textarea': VTextareaStub
        }
      }
    })
    const testAddress = '123 Main St, Anytown'
    await wrapper.find('.v-textarea-stub').setValue(testAddress)
    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')[0]).toEqual([testAddress])
  })

  it('displays error messages when provided', () => {
    const errorMsg = 'Address is required'
    const wrapper = mount(AddressInput, {
      props: {
        modelValue: '',
        errors: [errorMsg]
      },
      global: {
        stubs: {
          'v-textarea': VTextareaStub
        }
      }
    })
    expect(wrapper.find('.v-textarea-errors').text()).toContain(errorMsg)
  })
})
