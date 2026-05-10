import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import { createRouter, createMemoryHistory } from 'vue-router'
import AuthForm from '../AuthForm.vue'
import { useAuthStore } from '@/stores/auth'

// Mock vue-i18n
vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key, params = {}) => {
      const translations = {
        'auth.form.email': 'Email',
        'auth.form.password': 'Password',
        'auth.form.name': 'Name',
        'auth.form.phone': 'Phone',
        'auth.form.cpf': 'CPF',
        'auth.form.confirm_password': 'Password confirmation',
        'auth.form.account_type': 'Account Type',
        'auth.validation.required': '{field} is required',
        'auth.validation.email': 'Invalid email format',
        'auth.validation.password_match': 'Password confirmation does not match',
        'auth.login': 'Login',
        'auth.register': 'Register',
        'auth.validation.general_error': 'Validation failed'
      }
      let result = translations[key] || key
      if (params) {
        Object.entries(params).forEach(([k, v]) => {
          result = result.replace(new RegExp(`{${k}}`, 'g'), v)
        })
      }
      return result
    }
  })
}))

// Mock auth service
vi.mock('@/services/auth', () => ({
  default: {
    login: vi.fn(),
    register: vi.fn(),
    logout: vi.fn(),
    refreshToken: vi.fn()
  }
}))

// Mock Inertia router
vi.mock('@inertiajs/vue3', () => ({
  router: {
    visit: vi.fn().mockResolvedValue(undefined),
    post: vi.fn().mockResolvedValue(undefined),
    patch: vi.fn().mockResolvedValue(undefined),
    put: vi.fn().mockResolvedValue(undefined),
    delete: vi.fn().mockResolvedValue(undefined),
    reload: vi.fn().mockResolvedValue(undefined),
  }
}))

// Create mock router with basic routes
const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/', component: { template: '<div>Home</div>' } },
    { path: '/login', component: { template: '<div>Login</div>' } },
    { path: '/register', component: { template: '<div>Register</div>' } },
    { path: '/dashboard', component: { template: '<div>Dashboard</div>' } }
  ]
})

// Enhanced Vuetify component stubs matching actual component structure
const vuetifyComponents = {
  'v-progress-circular': {
    template: '<div class="v-progress-circular"></div>',
    props: ['indeterminate', 'size', 'width']
  },
  'v-form': {
    template: `
      <form 
        class="v-form"
        @submit.prevent="$emit('submit', $event)"
        ref="form"
        novalidate
      >
        <slot />
      </form>
    `,
    methods: {
      validate: vi.fn().mockResolvedValue({ valid: true }),
      reset: vi.fn(),
      resetValidation: vi.fn()
    }
  },
  'v-text-field': {
    template: `
      <div class="v-text-field">
        <input 
          ref="input"
          :type="type || 'text'"
          :value="modelValue"
          @input="$emit('update:modelValue', $event.target.value)"
          @blur="$emit('blur', $event)"
          :data-cy="dataTest"
          class="v-text-field__input"
          :required="required"
        />
        <div v-if="errorMessages" class="v-messages">
          <div class="v-messages__message" data-cy="error-message">{{ errorMessages }}</div>
        </div>
      </div>
    `,
    props: ['modelValue', 'label', 'type', 'errorMessages', 'dataTest', 'required'],
    methods: {
      focus: vi.fn(),
      validate: vi.fn().mockReturnValue(true),
      setValue(value) {
        this.$refs.input.value = value
        this.$emit('update:modelValue', value)
        this.$refs.input.dispatchEvent(new Event('input'))
      }
    }
  },
  'v-btn': {
    template: `
      <button 
        class="v-btn"
        :data-cy="dataTest"
        :disabled="loading || disabled"
        :type="type || 'button'"
        :loading="loading"
      >
        <span v-if="!loading" data-cy="button-text"><slot></slot></span>
        <v-progress-circular
          v-else
          indeterminate
          size="20"
          width="2"
          data-cy="button-loader"
        ></v-progress-circular>
      </button>
    `,
    props: {
      loading: {
        type: Boolean,
        default: false
      },
      disabled: Boolean,
      dataTest: String,
      type: String
    }
  },
  'v-alert': {
    template: `
      <div class="v-alert" :data-cy="dataTest">
        <div data-cy="alert-content"><slot></slot></div>
      </div>
    `,
    props: ['type', 'dataTest']
  },
  'v-select': {
    template: `
      <div class="v-select" :data-cy="dataTest">
        <select 
          ref="select"
          :value="modelValue"
          @change="$emit('update:modelValue', $event.target.value)"
        >
          <option 
            v-for="item in items" 
            :value="item.value"
            :key="item.value"
          >
            {{ item.title }}
          </option>
        </select>
        <div v-if="errorMessages" class="v-messages">
          <div class="v-messages__message">{{ errorMessages }}</div>
        </div>
      </div>
    `,
    props: ['modelValue', 'items', 'errorMessages', 'dataTest'],
    methods: {
      focus: vi.fn(),
      setValue(value) {
        this.$refs.select.value = value
        this.$emit('update:modelValue', value)
        this.$refs.select.dispatchEvent(new Event('change'))
      }
    }
  },
  'v-file-input': {
    template: `
      <div class="v-file-input" :data-cy="dataTest">
        <input 
          ref="input"
          type="file"
          @change="$emit('update:modelValue', $event.target.files[0])"
        />
        <div v-if="errorMessages" class="v-messages">
          <div class="v-messages__message">{{ errorMessages }}</div>
        </div>
      </div>
    `,
    props: ['modelValue', 'errorMessages', 'dataTest'],
    methods: {
      setValue(file) {
        const mockFile = file instanceof File ? file : new File(['test'], file)
        Object.defineProperty(this.$refs.input, 'files', {
          value: [mockFile]
        })
        this.$emit('update:modelValue', mockFile)
        this.$refs.input.dispatchEvent(new Event('change'))
      }
    }
  }
}

describe('AuthForm', () => {
  let wrapper
  let authStore

  beforeEach(async () => {
    const pinia = createPinia()
    setActivePinia(pinia)
    authStore = useAuthStore(pinia)
    
    // Mock auth store methods
    authStore.login = vi.fn().mockResolvedValue({ token: 'test-token' })
    authStore.register = vi.fn().mockResolvedValue({ data: { token: 'test-token' } })
    authStore.loading = false
    authStore.error = null

    // Install router
    await router.push('/')
  })

  describe('Login Mode', () => {
    beforeEach(() => {
      const pinia = createPinia()
      setActivePinia(pinia)
      authStore = useAuthStore(pinia)
      authStore.login = vi.fn().mockResolvedValue({ token: 'test-token' })
      
      wrapper = mount(AuthForm, {
        props: { mode: 'login' },
        global: {
          plugins: [pinia, router],
          stubs: {
            ...vuetifyComponents,
            transition: false,
            'transition-group': false
          }
        }
      })
    })

    it('renders login form correctly', () => {
      expect(wrapper.find('.v-form').exists()).toBe(true)
      expect(wrapper.find('[data-cy="email-input"]').exists()).toBe(true)
      expect(wrapper.find('[data-cy="password-input"]').exists()).toBe(true)
      expect(wrapper.find('[data-cy="login-button"]').exists()).toBe(true)
    })

    it('submits login form with correct data', async () => {
      const emailInput = wrapper.find('[data-cy="email-input"] input');
      const passwordInput = wrapper.find('[data-cy="password-input"] input');
      
      // Mock the formRef validate method directly on the component instance
      wrapper.vm.formRef = {
        validate: vi.fn().mockResolvedValue({ valid: true }),
        reset: vi.fn(),
        resetValidation: vi.fn()
      };

      await emailInput.setValue('test@example.com');
      await passwordInput.setValue('password123');
      await wrapper.vm.$nextTick();

      await wrapper.find('.v-form').trigger('submit');
      await wrapper.vm.$nextTick();

      expect(authStore.login).toHaveBeenCalledWith({
        email: 'test@example.com',
        password: 'password123'
      }, expect.anything());
    })

    it('shows loading state during login', async () => {
      authStore.loading = true
      await wrapper.vm.$nextTick()
      
      const button = wrapper.find('[data-cy="login-button"]');
      expect(button.attributes('disabled')).toBeDefined();
    })

    it('displays error messages', async () => {
      wrapper.vm.errors = {
        general: 'Invalid credentials',
        email: ['Invalid email format']
      }
      await wrapper.vm.$nextTick()
      
      const alert = wrapper.find('[data-cy="auth-alert"]')
      expect(alert.exists()).toBe(true)
      expect(alert.text()).toContain('Invalid credentials')
    })
  })

  describe('Register Mode', () => {
    beforeEach(() => {
      const pinia = createPinia()
      setActivePinia(pinia)
      authStore = useAuthStore(pinia)
      authStore.register = vi.fn().mockResolvedValue({ data: { token: 'test-token' } })

      wrapper = mount(AuthForm, {
        props: { mode: 'register' },
        global: {
          plugins: [pinia, router],
          stubs: {
            ...vuetifyComponents,
            transition: false,
            'transition-group': false
          }
        }
      })
    })

    it('renders registration form correctly', () => {
      expect(wrapper.find('.v-form').exists()).toBe(true)
      expect(wrapper.find('[data-cy="name-input"]').exists()).toBe(true)
      expect(wrapper.find('[data-cy="email-input"]').exists()).toBe(true)
      expect(wrapper.find('[data-cy="password-input"]').exists()).toBe(true)
      expect(wrapper.find('[data-cy="password-confirmation-input"]').exists()).toBe(true)
      expect(wrapper.find('[data-cy="role-select"]').exists()).toBe(true)
      expect(wrapper.find('[data-cy="register-button"]').exists()).toBe(true)
    })

    it('shows courier-specific fields when role is courier', async () => {
      const roleSelect = wrapper.find('[data-cy="role-select"] select')
      await roleSelect.setValue('courier')
      await wrapper.vm.$nextTick()

      expect(wrapper.find('[data-cy="license-input"]').exists()).toBe(true)
      expect(wrapper.find('[data-cy="vehicle-select"]').exists()).toBe(true)
      expect(wrapper.find('[data-cy="document-upload"]').exists()).toBe(true)
    })

    it('submits registration form with customer data', async () => {
      const formData = {
        name: 'Test User',
        email: 'test@example.com',
        password: 'password123',
        password_confirmation: 'password123',
        role: 'customer'
      }
      
      // Mock validation
      wrapper.vm.formRef = { validate: vi.fn().mockResolvedValue({ valid: true }) }
      
      // Set form data
      Object.assign(wrapper.vm.form, formData)
      
      await wrapper.vm.submit()
      expect(authStore.register).toHaveBeenCalled()
    })

    it('validates email format', () => {
      const invalidEmail = 'plainstring'
      const result = wrapper.vm.rules.email[1](invalidEmail)
      expect(typeof result).toBe('string')

      const validEmail = 'valid@example.com'
      expect(wrapper.vm.rules.email[1](validEmail)).toBe(true)
    })

    it('validates required fields', async () => {
      expect(wrapper.vm.rules.name[0]('')).toBe('Name is required')
      expect(wrapper.vm.rules.email[0]('')).toBe('Email is required')
      expect(wrapper.vm.rules.password[0]('')).toBe('Password is required')
      expect(wrapper.vm.rules.password_confirmation[0]('')).toBe('Password confirmation is required')
    })

    it('displays server validation errors', async () => {
      const pinia = createPinia()
      const store = useAuthStore(pinia)
      store.error = {
        message: 'Validation failed',
        errors: {
          name: ['Name is required'],
          email: ['Invalid email format']
        }
      }

      const errorWrapper = mount(AuthForm, {
        props: { mode: 'register' },
        global: {
          plugins: [pinia, router],
          stubs: vuetifyComponents
        }
      })
      
      await errorWrapper.vm.$nextTick()
      await new Promise(resolve => setTimeout(resolve, 0))
      
      expect(errorWrapper.find('[data-cy="auth-alert"]').exists()).toBe(true)
      expect(errorWrapper.vm.errors.name).toBe('Name is required')
    })
  })

  it('emits success event on successful login', async () => {
    const pinia = createPinia()
    const store = useAuthStore(pinia)
    const mockResponse = { token: 'test-token' }
    store.login = vi.fn().mockResolvedValue(mockResponse)

    const loginWrapper = mount(AuthForm, {
      props: { mode: 'login' },
      global: {
        plugins: [pinia, router],
        stubs: vuetifyComponents
      }
    })

    loginWrapper.vm.formRef = { validate: vi.fn().mockResolvedValue({ valid: true }) }
    loginWrapper.vm.form.email = 'test@example.com'
    loginWrapper.vm.form.password = 'password123'
    
    await loginWrapper.vm.submit()
    expect(loginWrapper.emitted('success')).toBeTruthy()
  })

  it('emits error event on failed login', async () => {
    const pinia = createPinia()
    const store = useAuthStore(pinia)
    const mockError = new Error('Login failed')
    store.login = vi.fn().mockRejectedValue(mockError)

    const loginWrapper = mount(AuthForm, {
      props: { mode: 'login' },
      global: {
        plugins: [pinia, router],
        stubs: vuetifyComponents
      }
    })

    loginWrapper.vm.formRef = { validate: vi.fn().mockResolvedValue({ valid: true }) }
    
    try {
      await loginWrapper.vm.submit()
    } catch (e) {
      // Expected
    }
    
    expect(loginWrapper.emitted('error')).toBeTruthy()
  })
})
