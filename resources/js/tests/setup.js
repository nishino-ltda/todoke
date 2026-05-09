import { vi, beforeAll, afterEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'

// Mock localStorage
const localStorageMock = (function() {
  let store = {}
  return {
    getItem(key) {
      return store[key] || null
    },
    setItem(key, value) {
      store[key] = value.toString()
    },
    removeItem(key) {
      delete store[key]
    },
    clear() {
      store = {}
    }
  }
})()

// Global mocks
vi.mock('vue-i18n', () => ({
  useI18n: () => ({
    t: (key) => key,
    d: (val) => val,
    n: (val) => val
  })
}))

vi.mock('@inertiajs/vue3', () => ({
  router: {
    visit: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
    reload: vi.fn()
  },
  Link: {
    template: '<a><slot /></a>',
    props: ['href']
  },
  useForm: () => ({
    post: vi.fn(),
    put: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
    processing: false,
    errors: {},
    reset: vi.fn()
  }),
  usePage: () => ({
    props: {}
  })
}))

// Mock route helper globally
global.route = vi.fn().mockImplementation((name) => `/${name}`)

// Mock log store
vi.mock('@/stores/log', () => ({
  useLogStore: () => ({
    log: vi.fn()
  })
}))

// Setup Pinia
beforeAll(() => {
  global.localStorage = localStorageMock
  setActivePinia(createPinia())
})

// Reset mocks after each test
afterEach(() => {
  vi.restoreAllMocks()
  localStorage.clear()
})
