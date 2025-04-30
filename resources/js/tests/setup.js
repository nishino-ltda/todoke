import { vi } from 'vitest'
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
