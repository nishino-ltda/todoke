import { mount } from '@vue/test-utils'
import { vi, describe, it, expect } from 'vitest'
import PaymentMethodInput from '../PaymentMethodInput.vue'

// Mock i18n
vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key) => key
  })
}))

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
    </div>
  `,
  props: ['modelValue', 'items', 'errorMessages']
}

const VTextFieldStub = {
  template: `
    <input 
      :value="modelValue" 
      @input="$emit('update:modelValue', $event.target.value)"
      class="v-text-field-stub"
    />
  `,
  props: ['modelValue', 'label']
}

describe('PaymentMethodInput', () => {
  it('renders a select for payment method', () => {
    const wrapper = mount(PaymentMethodInput, {
      props: {
        modelValue: 'cash'
      },
      global: {
        stubs: {
          'v-select': VSelectStub,
          'v-expand-transition': { template: '<div><slot /></div>' },
          'v-text-field': VTextFieldStub
        }
      }
    })
    expect(wrapper.find('.v-select-stub').exists()).toBe(true)
  })

  it('shows credit card fields only when credit_card is selected', async () => {
    const wrapper = mount(PaymentMethodInput, {
      props: {
        modelValue: 'cash'
      },
      global: {
        stubs: {
          'v-select': VSelectStub,
          'v-expand-transition': { template: '<div><slot /></div>' },
          'v-text-field': VTextFieldStub,
          'v-row': { template: '<div><slot /></div>' },
          'v-col': { template: '<div><slot /></div>' }
        }
      }
    })
    
    expect(wrapper.find('[data-cy="card-number-input"]').exists()).toBe(false)
    
    await wrapper.find('.v-select-stub').setValue('credit_card')
    
    expect(wrapper.find('[data-cy="card-number-input"]').exists()).toBe(true)
  })

  it('formats card number with spaces', async () => {
    const wrapper = mount(PaymentMethodInput, {
      props: {
        modelValue: 'credit_card'
      },
      global: {
        stubs: {
          'v-select': VSelectStub,
          'v-expand-transition': { template: '<div><slot /></div>' },
          'v-text-field': VTextFieldStub,
          'v-row': { template: '<div><slot /></div>' },
          'v-col': { template: '<div><slot /></div>' }
        }
      }
    })
    
    const numberInput = wrapper.find('[data-cy="card-number-input"]')
    await numberInput.setValue('1234567812345678')
    
    // In our stub, setValue triggers update:modelValue
    // We also need to trigger the @input handler manually or update the stub
    await numberInput.trigger('input')
    
    expect(wrapper.vm.card.number).toBe('1234 5678 1234 5678')
  })

  it('emits masked value for credit card', async () => {
    const wrapper = mount(PaymentMethodInput, {
      props: {
        modelValue: 'credit_card'
      },
      global: {
        stubs: {
          'v-select': VSelectStub,
          'v-expand-transition': { template: '<div><slot /></div>' },
          'v-text-field': VTextFieldStub,
          'v-row': { template: '<div><slot /></div>' },
          'v-col': { template: '<div><slot /></div>' }
        }
      }
    })
    
    await wrapper.find('[data-cy="card-number-input"]').setValue('1111222233334444')
    await wrapper.find('[data-cy="card-number-input"]').trigger('input')
    
    await wrapper.find('[data-cy="card-expiry-input"]').setValue('1225')
    await wrapper.find('[data-cy="card-expiry-input"]').trigger('input')
    
    await wrapper.find('[data-cy="card-cvv-input"]').setValue('123')
    await wrapper.find('[data-cy="card-holder-input"]').setValue('John Doe')
    
    const lastEmit = wrapper.emitted('update:modelValue').slice(-1)[0][0]
    
    expect(lastEmit.method).toBe('credit_card')
    expect(lastEmit.card.number).toBe('XXXX XXXX XXXX 4444')
    expect(lastEmit.card.last4).toBe('4444')
    expect(lastEmit.card.holder).toBe('John Doe')
  })
})
