import { setActivePinia, createPinia } from 'pinia'
import { useUserStore } from '../user'
import { vi, describe, it, expect, beforeEach } from 'vitest'
import userService from '../../services/user'

vi.mock('../../services/user', () => ({
  default: {
    getProfile: vi.fn(),
    updateProfile: vi.fn()
  }
}))

describe('User Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('starts with empty profile', () => {
    const store = useUserStore()
    expect(store.profile).toBeNull()
    expect(store.isLoggedIn).toBe(false)
  })

  it('fetches profile successfully', async () => {
    const mockProfile = { id: 1, name: 'John Doe', email: 'john@example.com' }
    userService.getProfile.mockResolvedValue({ data: mockProfile })
    
    const store = useUserStore()
    await store.fetchProfile()
    
    expect(store.profile).toEqual(mockProfile)
    expect(store.isLoggedIn).toBe(true)
    expect(store.loading).toBe(false)
  })

  it('handles fetch profile error', async () => {
    userService.getProfile.mockRejectedValue({
      response: { data: { message: 'Unauthorized' } }
    })
    
    const store = useUserStore()
    await expect(store.fetchProfile()).rejects.toBeDefined()
    
    expect(store.profile).toBeNull()
    expect(store.error).toBe('Unauthorized')
    expect(store.loading).toBe(false)
  })

  it('updates profile successfully', async () => {
    const updatedProfile = { id: 1, name: 'John Updated' }
    userService.updateProfile.mockResolvedValue({ data: updatedProfile })
    
    const store = useUserStore()
    await store.updateProfile({ name: 'John Updated' })
    
    expect(store.profile).toEqual(updatedProfile)
    expect(userService.updateProfile).toHaveBeenCalledWith({ name: 'John Updated' })
  })

  it('clears profile', () => {
    const store = useUserStore()
    store.setProfile({ id: 1, name: 'John' })
    expect(store.isLoggedIn).toBe(true)
    
    store.clearProfile()
    expect(store.profile).toBeNull()
    expect(store.isLoggedIn).toBe(false)
  })
})
