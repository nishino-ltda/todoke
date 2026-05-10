<template>
  <v-app data-cy="authenticated-layout">
    <AppHeader @toggle-drawer="drawer = !drawer" />

    <v-navigation-drawer
      v-model="drawer"
      app
      :temporary="$vuetify.display.mobile"
      :permanent="!$vuetify.display.mobile"
      data-cy="nav-drawer"
    >
      <v-list density="comfortable" nav>
        <v-list-item
          v-for="(item, i) in navItems"
          :key="i"
          :active="page.url === item.to"
          :prepend-icon="item.icon"
          :title="item.text"
          @click="router.visit(item.to)"
          :data-cy="`nav-item-${item.text.toLowerCase()}`"
          link
        ></v-list-item>
      </v-list>

      <template v-slot:append>
        <v-divider></v-divider>
        <v-list-item
          :prepend-avatar="`https://ui-avatars.com/api/?name=${user?.name || 'U'}&background=0D47A1&color=fff`"
          :title="user?.name || user?.email || 'User'"
          :subtitle="user?.email"
        >
          <template v-slot:append>
            <v-btn icon="mdi-logout" variant="text" @click="logout" size="small"></v-btn>
          </template>
        </v-list-item>
      </template>
    </v-navigation-drawer>

    <v-main>
      <v-container>
        <slot data-cy="authenticated-content" />
      </v-container>
      <NotificationCenter />
    </v-main>

    <AppFooter />
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { router, usePage } from '@inertiajs/vue3'
import { useAuthStore } from '@/stores/auth'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'
import NotificationCenter from '@/Components/NotificationCenter.vue'

const page = usePage()
const authStore = useAuthStore()
const drawer = ref(true)

const { user } = storeToRefs(authStore)

const navItems = computed(() => {
  const items = [
    { text: 'Dashboard', icon: 'mdi-view-dashboard', to: '/customer/dashboard' },
    { text: 'Profile', icon: 'mdi-account', to: '/customer/profile' },
    { text: 'Settings', icon: 'mdi-cog', to: '/customer/settings' },
  ]

  const allRoles = user.value?.all_roles || []

  if (allRoles.includes('partner')) {
    items.push({ text: 'Partner Panel', icon: 'mdi-store', to: '/partner/dashboard' })
  }

  if (allRoles.includes('courier')) {
    items.push({ text: 'Courier Panel', icon: 'mdi-truck-delivery', to: '/courier/dashboard' })
  }

  return items
})

const logout = () => {
  authStore.logout(router)
}
</script>
