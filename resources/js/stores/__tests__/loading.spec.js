import { useLoadingStore } from '../loading'

describe('Loading Store', () => {
  it('should initialize with default state', () => {
    const store = useLoadingStore()
    expect(store.isLoading).toBe(false)
    expect(store.loadingMessage).toBe('')
  })

  it('should start loading with message', () => {
    const store = useLoadingStore()
    const message = 'Processing...'
    
    store.startLoading(message)
    expect(store.isLoading).toBe(true)
    expect(store.loadingMessage).toBe(message)
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
