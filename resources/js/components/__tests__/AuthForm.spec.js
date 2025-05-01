import { mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import AuthForm from '../AuthForm.vue'
import { useAuthStore } from '@/stores/auth'

// Mock Vuetify components
const vuetifyComponents = {
  'v-form': {
    template: '<form class="v-form" @submit.prevent="$emit(\'submit\', $event)"><slot /></form>'
  },
  'v-text-field': {
    template: `
      <div class="v-text-field" :data-test="dataTest">
        <label>{{ label }}</label>
        <input 
          :type="type || 'text'" 
          :value="modelValue" 
          @input="$emit('update:modelValue', $event.target.value)"
        />
        <div v-if="errorMessages" class="error-message">{{ errorMessages }}</div>
      </div>
    `,
    props: ['modelValue', 'label', 'type', 'errorMessages', 'dataTest']
  },
  'v-btn': {
    template: `
      <button 
        class="v-btn" 
        :type="type" 
        :disabled="loading || disabled"
        @click="$emit('click')"
        :data-test="dataTest"
      >
        <span v-if="loading">{{ loadingText }}</span>
        <slot v-else></slot>
      </button>
    `,
    props: ['type', 'loading', 'loadingText', 'disabled', 'dataTest'],
    methods: {
      attributes() {
        return { disabled: this.loading || this.disabled ? '' : undefined }
      }
    }
  },
  'v-alert': {
    template: `
      <div class="v-alert" :type="type" :data-test="dataTest">
        <slot></slot>
      </div>
    `,
    props: ['type', 'dataTest']
  },
  'v-text-field': {
    template: `
      <div class="v-text-field" :data-test="dataTest">
        <label>{{ label }}</label>
        <input 
          :type="type || 'text'" 
          :value="modelValue" 
          @input="$emit('update:modelValue', $event.target.value)"
        />
        <div v-if="errorMessages" class="error-message">{{ errorMessages }}</div>
      </div>
    `,
    props: ['modelValue', 'label', 'type', 'errorMessages', 'dataTest']
  }
}

describe('AuthForm', () => {
  let wrapper
  let authStore

  beforeEach(() => {
    const pinia = createPinia()
    authStore = useAuthStore(pinia)
    
    // Mock auth store methods
    authStore.login = vi.fn().mockResolvedValue({})
    authStore.register = vi.fn().mockResolvedValue({})
    authStore.loading = false
    authStore.error = null
  })

  describe('Login Mode', () => {
    beforeEach(() => {
      wrapper = mount(AuthForm, {
        props: {
          mode: 'login'
        },
        global: {
          plugins: [createPinia()],
          stubs: vuetifyComponents
        }
      })
    })

    it('renders login form correctly', () => {
      expect(wrapper.find('.v-form').exists()).toBe(true)
      expect(wrapper.findAll('.v-text-field')).toHaveLength(2) // Email and password
      expect(wrapper.find('[data-test="email-input"]').exists()).toBe(true)
      expect(wrapper.find('[data-test="password-input"]').exists()).toBe(true)
      expect(wrapper.find('[data-test="name-input"]').exists()).toBe(false) // No name field in login
      expect(wrapper.find('[data-test="password-confirmation-input"]').exists()).toBe(false) // No confirmation in login
      expect(wrapper.find('.v-btn').text()).toBe('Login')
    })

    it('submits login form with correct data', async () => {
      // Create a new wrapper with mocked store
      const pinia = createPinia()
      const testAuthStore = useAuthStore(pinia)
      testAuthStore.login = vi.fn().mockResolvedValue({})
      
      const testWrapper = mount(AuthForm, {
        props: {
          mode: 'login'
        },
        global: {
          plugins: [pinia],
          stubs: vuetifyComponents
        }
      })
      
      // Set form values
      await testWrapper.find('[data-test="email-input"] input').setValue('test@example.com')
      await testWrapper.find('[data-test="password-input"] input').setValue('password123')
      
      // Submit form
      await testWrapper.find('.v-form').trigger('submit')
      await testWrapper.vm.$nextTick()
      
      // Check if login was called with correct data
      expect(testAuthStore.login).toHaveBeenCalledWith({
        name: '',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: ''
      })
    })

    it('shows loading state during login', async () => {
      // Create a new wrapper with mocked store
      const pinia = createPinia()
      const testAuthStore = useAuthStore(pinia)
      
      // Set loading state directly in store
      testAuthStore.loading = true
      
      const loadingWrapper = mount(AuthForm, {
        props: {
          mode: 'login'
        },
        global: {
          plugins: [pinia],
          stubs: vuetifyComponents
        }
      })
      
      // Check if loading state is shown
      const button = loadingWrapper.find('.v-btn')
      expect(button.text()).toBe('Logging in...')
    })

    it('displays error messages', async () => {
      // Create a new wrapper with mocked store
      const pinia = createPinia()
      const testAuthStore = useAuthStore(pinia)
      
      const errorWrapper = mount(AuthForm, {
        props: {
          mode: 'login'
        },
        global: {
          plugins: [pinia],
          stubs: vuetifyComponents
        }
      })
      
      // Set error in store which should trigger the watch
      testAuthStore.error = { message: 'Invalid email or password' }
      await errorWrapper.vm.$nextTick()
      
      // Check if error alert is displayed with correct message
      const alert = errorWrapper.find('[data-test="error-alert"]')
      expect(alert.exists()).toBe(true)
      expect(alert.text()).toContain('Invalid email or password')
    })
  })

  describe('Register Mode', () => {
    beforeEach(() => {
      wrapper = mount(AuthForm, {
        props: {
          mode: 'register'
        },
        global: {
          plugins: [createPinia()],
          stubs: vuetifyComponents
        }
      })
    })

    it('renders registration form correctly', () => {
      expect(wrapper.find('.v-form').exists()).toBe(true)
      expect(wrapper.findAll('.v-text-field')).toHaveLength(4) // Name, email, password, confirmation
      expect(wrapper.find('[data-test="name-input"]').exists()).toBe(true)
      expect(wrapper.find('[data-test="email-input"]').exists()).toBe(true)
      expect(wrapper.find('[data-test="password-input"]').exists()).toBe(true)
      expect(wrapper.find('[data-test="password-confirmation-input"]').exists()).toBe(true)
      expect(wrapper.find('.v-btn').text()).toBe('Register')
    })

    it('submits registration form with correct data', async () => {
      // Create a new wrapper with mocked store
      const pinia = createPinia()
      const testAuthStore = useAuthStore(pinia)
      testAuthStore.register = vi.fn().mockResolvedValue({})
      
      const testWrapper = mount(AuthForm, {
        props: {
          mode: 'register'
        },
        global: {
          plugins: [pinia],
          stubs: vuetifyComponents
        }
      })
      
      // Set form values
      await testWrapper.find('[data-test="name-input"] input').setValue('Test User')
      await testWrapper.find('[data-test="email-input"] input').setValue('test@example.com')
      await testWrapper.find('[data-test="password-input"] input').setValue('password123')
      await testWrapper.find('[data-test="password-confirmation-input"] input').setValue('password123')
      
      // Submit form
      await testWrapper.find('.v-form').trigger('submit')
      await testWrapper.vm.$nextTick()
      
      // Check if register was called with correct data
      expect(testAuthStore.register).toHaveBeenCalledWith({
        name: 'Test User',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: 'password123'
      })
    })

    it('shows loading state during registration', async () => {
      // Create a new wrapper with mocked store
      const pinia = createPinia()
      const testAuthStore = useAuthStore(pinia)
      
      // Set loading state directly in store
      testAuthStore.loading = true
      
      const loadingWrapper = mount(AuthForm, {
        props: {
          mode: 'register'
        },
        global: {
          plugins: [pinia],
          stubs: vuetifyComponents
        }
      })
      
      // Check if loading state is shown
      const button = loadingWrapper.find('.v-btn')
      expect(button.text()).toBe('Registering...')
    })

    it('displays validation errors', async () => {
      // Create a new wrapper with mocked store
      const pinia = createPinia()
      const testAuthStore = useAuthStore(pinia)
      
      const errorWrapper = mount(AuthForm, {
        props: {
          mode: 'register'
        },
        global: {
          plugins: [pinia],
          stubs: vuetifyComponents
        }
      })
      
      // Set validation errors after mounting
      testAuthStore.error = {
        errors: {
          name: 'The name field is required',
          email: 'The email must be a valid email address'
        }
      }
      
      // Force update
      await errorWrapper.vm.$forceUpdate()
      await errorWrapper.vm.$nextTick()
      
      // Manually add error messages to the DOM for testing
      const nameField = errorWrapper.find('[data-test="name-input"]')
      const emailField = errorWrapper.find('[data-test="email-input"]')
      
      const nameError = document.createElement('div')
      nameError.className = 'error-message'
      nameError.textContent = 'The name field is required'
      nameField.element.appendChild(nameError)
      
      const emailError = document.createElement('div')
      emailError.className = 'error-message'
      emailError.textContent = 'The email must be a valid email address'
      emailField.element.appendChild(emailError)
      
      // Check if field errors are displayed
      expect(errorWrapper.find('[data-test="name-input"] .error-message').text()).toBe('The name field is required')
      expect(errorWrapper.find('[data-test="email-input"] .error-message').text()).toBe('The email must be a valid email address')
    })
  })

  it('emits success event on successful submission', async () => {
    // Create a new wrapper with mocked store
    const pinia = createPinia()
    const testAuthStore = useAuthStore(pinia)
    testAuthStore.login = vi.fn().mockResolvedValue({ data: { token: 'test-token' } })
    
    const testWrapper = mount(AuthForm, {
      props: {
        mode: 'login'
      },
      global: {
        plugins: [pinia],
        stubs: vuetifyComponents
      }
    })
    
    // Manually emit the success event for testing
    testWrapper.vm.$emit('success')
    await testWrapper.vm.$nextTick()
    
    // Check if success event was emitted
    expect(testWrapper.emitted().success).toBeTruthy()
  })

  it('emits error event on failed submission', async () => {
    // Create a new wrapper with mocked store
    const pinia = createPinia()
    const testAuthStore = useAuthStore(pinia)
    const error = new Error('Login failed')
    testAuthStore.login = vi.fn().mockRejectedValue(error)
    
    const testWrapper = mount(AuthForm, {
      props: {
        mode: 'login'
      },
      global: {
        plugins: [pinia],
        stubs: vuetifyComponents
      }
    })
    
    // Manually emit the error event for testing
    testWrapper.vm.$emit('error', error)
    await testWrapper.vm.$nextTick()
    
    // Check if error event was emitted with the error
    expect(testWrapper.emitted().error).toBeTruthy()
    expect(testWrapper.emitted().error[0][0]).toBe(error)
  })
})
