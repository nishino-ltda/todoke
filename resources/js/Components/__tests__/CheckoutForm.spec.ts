import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { createPinia } from 'pinia'
import CheckoutForm from '@/Components/CheckoutForm.vue'
import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  legacy: false,
  locale: 'pt-BR',
  messages: {
    'pt-BR': {
      checkout: {
        form_title: 'Checkout',
        address_required: 'Endereço é obrigatório',
        payment_method_required: 'Forma de pagamento é obrigatória',
        place_order: 'Fazer Pedido',
        error_submitting: 'Erro ao enviar pedido',
        validation: {
          required: 'Este campo é obrigatório',
        },
      },
    },
    en: {
      checkout: {
        form_title: 'Checkout',
        address_required: 'Address is required',
        payment_method_required: 'Payment method is required',
        place_order: 'Place Order',
        error_submitting: 'Error submitting order',
        validation: {
          required: 'This field is required',
        },
      },
    },
  },
})

const vuetify = createVuetify({ components, directives })

function mountWithVuetify(component, options = {}) {
  return mount(component, {
    ...options,
    global: {
      plugins: [vuetify, createPinia(), i18n],
      stubs: {
        'v-btn': {
          template: '<button :disabled="disabled" :loading="loading" @click="$emit(\'click\')"><slot /></button>',
          props: ['disabled', 'loading'],
        },
        AddressInput: {
          template: '<div class="address-input-stub"><input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" /></div>',
          props: ['modelValue'],
        },
        PaymentMethodInput: {
          template: '<div class="payment-method-stub"><input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" /></div>',
          props: ['modelValue'],
        },
      },
    },
  })
}

describe('CheckoutForm', () => {
  let wrapper

  beforeEach(() => {
    wrapper = mountWithVuetify(CheckoutForm, {
      props: {
        isSubmitting: false,
        errorMessage: '',
      },
    })
  })

  it('renders form with submit button and child components', () => {
    expect(wrapper.find('form').exists()).toBe(true)
    expect(wrapper.find('[data-cy="submit-order"]').exists()).toBe(true)
  })

  it('emits submit event with form data', async () => {
    await wrapper.find('form').trigger('submit.prevent')
    const emitted = wrapper.emitted('submit')
    expect(emitted).toBeTruthy()
    expect(emitted[0][0]).toHaveProperty('address')
    expect(emitted[0][0]).toHaveProperty('paymentMethod')
  })

  it('shows error message from prop', async () => {
    await wrapper.setProps({ errorMessage: 'API Error' })
    await wrapper.vm.$nextTick()
    expect(wrapper.find('[data-cy="checkout-form-error"]').text()).toContain('API Error')
  })

  it('renders text in correct language', async () => {
    i18n.global.locale.value = 'pt-BR'
    await wrapper.vm.$nextTick()
    expect(wrapper.find('h2').text()).toBe('Checkout')
    expect(wrapper.find('[data-cy="submit-order"]').text()).toBe('Fazer Pedido')

    i18n.global.locale.value = 'en'
    await wrapper.vm.$nextTick()
    expect(wrapper.find('[data-cy="submit-order"]').text()).toBe('Place Order')
  })

  it('shows loading state on submit button', async () => {
    await wrapper.setProps({ isSubmitting: true })
    await wrapper.vm.$nextTick()
    const btn = wrapper.find('[data-cy="submit-order"]')
    expect(btn.attributes('disabled')).toBeDefined()
  })
})
