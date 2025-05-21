import { defineConfig } from 'vitest/config'
import path from 'path'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  test: {
    environment: 'happy-dom',
    globals: true,
    css: false,
    deps: {
      inline: ['vuetify']
    },
    coverage: {
      provider: 'c8',
      reporter: ['text', 'html', 'lcov'],
      reportsDirectory: './tests/coverage',
      all: true,
      include: ['resources/js/**/*.{js,vue}'],
      exclude: [
        '**/*.spec.js',
        '**/__mocks__/**',
        '**/main.js'
      ],
      thresholds: {
        lines: 80,
        functions: 80,
        branches: 80,
        statements: 80
      }
    }
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js')
    }
  }
})
