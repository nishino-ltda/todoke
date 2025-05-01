import { vi } from 'vitest'

// Mock CSS imports during tests
vi.mock(/\.css$/, () => ({
    default: {},
    css: {}
}))

// Mock Vuetify CSS specifically
vi.mock(/vuetify\/lib\/components\/.*\.css$/, () => ({}))
