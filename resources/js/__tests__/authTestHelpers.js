import { createPinia } from 'pinia'
import { useAuthStore } from '../stores/auth'
import { beforeEach, afterEach } from 'vitest'

// Test user credentials
export const TEST_USERS = {
    admin: {
        email: 'admin@todoke.com',
        password: 'Admin123',
        type: 'admin'
    },
    courier: {
        email: 'courier@example.com',
        password: 'Password123',
        type: 'courier'
    },
    partner: {
        email: 'partner@example.com',
        password: 'Password123',
        type: 'partner'
    },
    customer: {
        email: 'customer@example.com',
        password: 'Password123',
        type: 'customer'
    }
}

// Setup auth store for testing
export function setupAuthStore() {
    const pinia = createPinia()
    const authStore = useAuthStore(pinia)
    return { pinia, authStore }
}

// Login helper function
export async function loginTestUser(authStore, userType) {
    const user = TEST_USERS[userType]
    if (!user) throw new Error(`Invalid user type: ${userType}`)
    
    await authStore.login({
        email: user.email,
        password: user.password
    })
    
    return authStore.user
}

// Reset auth store between tests
export function resetAuthStore(authStore) {
    authStore.$reset()
}

// Test setup wrapper
export function setupAuthTests() {
    const { pinia, authStore } = setupAuthStore()
    
    beforeEach(() => {
        resetAuthStore(authStore)
    })
    
    afterEach(() => {
        resetAuthStore(authStore)
    })
    
    return { pinia, authStore }
}
