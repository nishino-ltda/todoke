import { mount } from '@vue/test-utils'
import PaymentMethodInput from '../PaymentMethodInput.vue'

// Stub for Vuetify's v-select
const VSelectStub = {
  template: `
    <div>
      <select 
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        class="v-select-stub"
      >
        <option v-for="item in items" :value="item.value">
          {{ item.title }}
        </option>
      </select>
      <div class="v-select-errors" v-if="errorMessages">
        {{ errorMessages.join(', ') }}
      </div>
    </div>
  `,
  props: ['modelValue', 'items', 'errorMessages']
}

describe('PaymentMethodInput', () => {
  it('renders a select for payment method', () => {
    const wrapper = mount(PaymentMethodInput, {
      props: {
        modelValue: ''
      },
      global: {
        stubs: {
          'v-select': VSelectStub
        }
      }
    })
    expect(wrapper.find('.v-select-stub').exists()).toBe(true)
  })

  it('shows payment options', () => {
    const wrapper = mount(PaymentMethodInput, {
      props: {
        modelValue: ''
      },
      global: {
        stubs: {
          'v-select': VSelectStub
        }
      }
    })
    const options = wrapper.findAll('option')
    expect(options[0].text()).toBe('Credit Card')
    expect(options[1].text()).toBe('Cash on Delivery')
  })

  it('emits update event when payment method changes', async () => {
    const wrapper = mount(PaymentMethodInput, {
      props: {
        modelValue: ''
      },
      global: {
        stubs: {
          'v-select': VSelectStub
        }
      }
    })
    await wrapper.find('.v-select-stub').setValue('credit_card')
    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')[0]).toEqual(['credit_card'])
  })

  it('displays error messages when provided', () => {
    const errorMsg = 'Payment method is required'
    const wrapper = mount(PaymentMethodInput, {
      props: {
        modelValue: '',
        errors: [errorMsg]
      },
      global: {
        stubs: {
          'v-select': VSelectStub
        }
      }
    })
    expect(wrapper.find('.v-select-errors').text()).toContain(errorMsg)
  })
})
