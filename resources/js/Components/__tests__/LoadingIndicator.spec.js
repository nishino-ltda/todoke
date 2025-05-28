import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { setActivePinia, createPinia } from 'pinia'
import { useLoadingStore } from '@/stores/loading'
import LoadingIndicator from '../LoadingIndicator.vue'

describe('LoadingIndicator', () => {
  let loadingStore

  beforeEach(() => {
    setActivePinia(createPinia())
    loadingStore = useLoadingStore()
  })

  it('does not render when not loading', () => {
    loadingStore.isLoading = false
    const wrapper = mount(LoadingIndicator)
    expect(wrapper.find('.loading-indicator').exists()).toBe(false)
  })

  it('renders when loading', () => {
    loadingStore.isLoading = true
    const wrapper = mount(LoadingIndicator)
    expect(wrapper.find('.loading-indicator').exists()).toBe(true)
    expect(wrapper.find('.spinner').exists()).toBe(true)
  })

  it('shows message when provided', () => {
    loadingStore.isLoading = true
    loadingStore.loadingMessage = 'Loading data...'
    const wrapper = mount(LoadingIndicator)
    expect(wrapper.find('span').text()).toBe('Loading data...')
  })

  it('hides message when not provided', () => {
    loadingStore.isLoading = true
    loadingStore.loadingMessage = ''
    const wrapper = mount(LoadingIndicator)
    expect(wrapper.find('span').exists()).toBe(false)
  })
})
