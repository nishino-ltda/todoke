import { describe, it, expect, vi } from 'vitest'
import { TEST_USERS, setupAuthTests, loginTestUser } from './authTestHelpers'
import api from '@/services/api'

// Mock API module
vi.mock('@/services/api', () => ({
    __esModule: true,
    default: {
        post: vi.fn()
    }
}))

describe('Auth Store', () => {
    const { authStore } = setupAuthTests()

    beforeEach(() => {
        vi.clearAllMocks()
    })

    describe('Login', () => {
        it.each(Object.keys(TEST_USERS))('should login %s user', async (userType) => {
            const user = TEST_USERS[userType]
            
            // Mock successful API response
            api.post.mockResolvedValueOnce({
                data: {
                    token: `${userType}-token`,
                    user: {
                        id: 1,
                        name: 'Test User',
                        email: user.email,
                        type: user.type
                    }
                }
            })

            await loginTestUser(authStore, userType)

            expect(api.post).toHaveBeenCalledWith('/auth/login', {
                email: user.email,
                password: user.password
            })
            expect(authStore.user).toEqual({
                id: 1,
                name: 'Test User',
                email: user.email,
                type: user.type
            })
            expect(authStore.token).toBe(`${userType}-token`)
            expect(authStore.isAuthenticated).toBe(true)
        })
    })

    describe('Logout', () => {
        it('should clear auth state', async () => {
            // First login to set state
            api.post.mockResolvedValueOnce({
                data: {
                    token: 'test-token',
                    user: {
                        id: 1,
                        name: 'Test User',
                        email: 'test@example.com',
                        type: 'customer'
                    }
                }
            })
            await loginTestUser(authStore, 'customer')

            // Then logout
            authStore.logout()

            expect(authStore.user).toBeNull()
            expect(authStore.token).toBeNull()
            expect(authStore.isAuthenticated).toBe(false)
        })
    })
})
