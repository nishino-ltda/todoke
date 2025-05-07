import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'happy-dom',
    setupFiles: ['./resources/js/tests/setup.js'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      include: ['resources/js/**/*.{js,vue}']
    },
    transformMode: {
      web: [/\.[jt]sx?$/, /\.vue$/]
    },
    css: {
      modules: {
        classNameStrategy: 'non-scoped'
      }
    },
    // Mock CSS imports
    server: {
      deps: {
        inline: [/vuetify/]
      }
    }
  },
  resolve: {
    alias: [
      {
        find: '@/',
        replacement: path.resolve(__dirname, './resources/js/')
      },
      {
        find: '@',
        replacement: path.resolve(__dirname, './resources/js')
      },
      {
        find: '@components',
        replacement: path.resolve(__dirname, './resources/js/components')
      },
      {
        find: '@stores', 
        replacement: path.resolve(__dirname, './resources/js/stores')
      },
      {
        find: '@services',
        replacement: path.resolve(__dirname, './resources/js/services')
      }
    ]
  }
})
