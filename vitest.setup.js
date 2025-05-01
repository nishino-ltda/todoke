import { vi } from 'vitest'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

// Create Vuetify instance for testing
const vuetify = createVuetify({
  components,
  directives,
})

// Mock all CSS imports during tests
vi.mock(/\.css$/, () => ({
  default: {},
  css: {},
  style: {}
}))

// Mock Vuetify CSS imports
vi.mock(/vuetify\/lib\/components\/.*\.css$/, () => ({}))
vi.mock(/vuetify\/lib\/styles\/.*\.css$/, () => ({}))
vi.mock(/vuetify\/lib\/.*\.css$/, () => ({}))

// Mock Vuetify icon imports
vi.mock(/vuetify\/lib\/iconsets\/.*/, () => ({}))
