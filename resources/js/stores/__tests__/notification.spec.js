import { setActivePinia, createPinia } from 'pinia'
import { useNotificationStore } from '../notification'
import { vi, describe, it, expect, beforeEach } from 'vitest'

describe('Notification Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.useFakeTimers()
  })

  it('adds a notification', () => {
    const store = useNotificationStore()
    store.add({ message: 'Test message' })
    expect(store.notifications).toHaveLength(1)
    expect(store.notifications[0].message).toBe('Test message')
    expect(store.notifications[0].type).toBe('info')
  })

  it('removes a notification after duration', () => {
    const store = useNotificationStore()
    store.add({ message: 'Test message', duration: 1000 })
    expect(store.notifications).toHaveLength(1)
    
    vi.advanceTimersByTime(1000)
    expect(store.notifications).toHaveLength(0)
  })

  it('removes a notification manually', () => {
    const store = useNotificationStore()
    const id = store.add({ message: 'Test message' })
    expect(store.notifications).toHaveLength(1)
    
    store.remove(id)
    expect(store.notifications).toHaveLength(0)
  })

  it('clears all notifications', () => {
    const store = useNotificationStore()
    store.add({ message: 'Msg 1' })
    store.add({ message: 'Msg 2' })
    expect(store.notifications).toHaveLength(2)
    
    store.clear()
    expect(store.notifications).toHaveLength(0)
  })

  it('provides helper methods for different types', () => {
    const store = useNotificationStore()
    store.success('Success')
    store.error('Error')
    store.warning('Warning')
    store.info('Info')
    
    expect(store.notifications).toHaveLength(4)
    expect(store.notifications[0].type).toBe('success')
    expect(store.notifications[1].type).toBe('error')
    expect(store.notifications[2].type).toBe('warning')
    expect(store.notifications[3].type).toBe('info')
  })
})
