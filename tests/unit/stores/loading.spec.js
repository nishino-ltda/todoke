import { setActivePinia, createPinia } from 'pinia'
import { useLoadingStore } from '@/stores/loading'

describe('Loading Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('should initialize with default state', () => {
    const store = useLoadingStore()
    expect(store.isLoading).toBe(false)
    expect(store.loadingMessage).toBe('')
  })

  it('should start loading with message', () => {
    const store = useLoadingStore()
    store.startLoading('Processing...')
    
    expect(store.isLoading).toBe(true)
    expect(store.loadingMessage).toBe('Processing...')
  })

  it('should start loading without message', () => {
    const store = useLoadingStore()
    store.startLoading()
    
    expect(store.isLoading).toBe(true)
    expect(store.loadingMessage).toBe('')
  })

  it('should stop loading', () => {
    const store = useLoadingStore()
    store.startLoading('Processing...')
    store.stopLoading()
    
    expect(store.isLoading).toBe(false)
    expect(store.loadingMessage).toBe('')
  })
})
