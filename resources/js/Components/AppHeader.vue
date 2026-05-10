<template>
  <v-app-bar app color="primary" dark data-cy="app-header">
    <v-app-bar-nav-icon @click="toggleDrawer" />
    <v-toolbar-title>{{ $t('app.title') }}</v-toolbar-title>
    <v-spacer />

    <LanguageSelector class="mr-2" />

    <template v-if="isAuthenticated">
      <span class="welcome-message" data-cy="welcome-message">{{ $t('app.welcome', { name: user?.name }) }}</span>
      <Link 
        v-if="user?.role === 'customer'"
        :href="route('menu')" 
        class="text-white mr-2" 
        data-cy="menu-link"
      >
        {{ $t('auth.menu') }}
      </Link>
      <v-btn text disabled v-if="loading" data-cy="loading-indicator">{{ $t('app.loading') }}</v-btn>
      <CartIcon v-if="user?.role === 'customer' && !minimal" />
      <v-btn text @click="handleLogout" :disabled="loading" data-cy="logout-button">
        {{ $t('auth.logout') }}
      </v-btn>
    </template>
    
    <template v-else>
      <CartIcon v-if="!minimal" />
      <Link :href="route('login')" class="text-white mr-2" data-cy="login-link">{{ $t('auth.login') }}</Link>
      <Link :href="route('register')" class="text-white" data-cy="register-link">{{ $t('auth.register') }}</Link>
    </template>
  </v-app-bar>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useLogStore } from '@/stores/log'
import { Link } from '@inertiajs/vue3'
import CartIcon from './CartIcon.vue'
import LanguageSelector from './LanguageSelector.vue'

const props = defineProps({
  minimal: {
    type: Boolean,
    default: false
  }
})

const authStore = useAuthStore()
const logStore = useLogStore()

const { isAuthenticated, user, loading } = storeToRefs(authStore)
const handleLogout = () => {
  logStore.log('🔄 AppHeader: Logout initiated')
  authStore.logout()
    .then(() => logStore.log('✅ AppHeader: Logout successful'))
    .catch(err => logStore.log(`❌ AppHeader: Logout failed - ${err.message}`))
}

function toggleDrawer() {
  logStore.log('📱 AppHeader: Drawer toggled')
  // Will implement drawer toggle later
}
</script>
