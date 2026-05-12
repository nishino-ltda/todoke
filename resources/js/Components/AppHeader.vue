<template>
  <v-app-bar app elevation="2" color="primary" theme="dark" data-cy="app-header">
    <v-app-bar-nav-icon v-if="withDrawerToggle" @click="$emit('toggle-drawer')" />
    <v-toolbar-title>
      <Link :href="route('home')" class="text-white text-decoration-none font-weight-bold" data-cy="app-title-link">
        {{ $t('app.title') }}
      </Link>
    </v-toolbar-title>
    <v-spacer />

    <LanguageSelector class="mr-2" />

    <template v-if="page.props.auth?.user || user">
      <span class="welcome-message" data-cy="welcome-message">
        {{ $t('app.welcome', { name: (page.props.auth?.user?.name || user?.name || 'User') }) }}
      </span>
      <Link 
        v-if="(page.props.auth?.user?.type || user?.type) === 'customer'"
        :href="route('menu')" 
        class="text-white mr-2" 
        data-cy="menu-link"
      >
        {{ $t('auth.menu') }}
      </Link>
      <v-btn text disabled v-if="loading" data-cy="loading-indicator">{{ $t('app.loading') }}</v-btn>
      <CartIcon v-if="(page.props.auth?.user?.type || user?.type) === 'customer' && !minimal" />
      <v-btn text @click="handleLogout" :disabled="loading" data-cy="logout-button">
        {{ $t('auth.logout') }}
      </v-btn>
    </template>
    
    <template v-else>
      <CartIcon v-if="!minimal" />
      <Link :href="route('login')" class="text-white mr-2" data-cy="login-link">{{ $t('auth.login') }}</Link>
    </template>
  </v-app-bar>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import { useLogStore } from '@/stores/log'
import { Link, usePage } from '@inertiajs/vue3'
import CartIcon from './CartIcon.vue'
import LanguageSelector from './LanguageSelector.vue'

const page = usePage()

const emit = defineEmits(['toggle-drawer'])

const props = defineProps({
  minimal: {
    type: Boolean,
    default: false
  },
  withDrawerToggle: {
    type: Boolean,
    default: true
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
</script>
