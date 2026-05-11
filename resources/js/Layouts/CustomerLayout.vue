<template>
  <v-app>
    <v-app-bar app flat border color="white">
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-app-bar-title class="text-subtitle-1 font-weight-bold">
        {{ currentPageTitle }}
      </v-app-bar-title>
      <v-spacer></v-spacer>
      
      <CartIcon class="mr-4" />
      <LanguageSelector class="mr-2" />
    </v-app-bar>

    <v-navigation-drawer v-model="drawer" app :permanent="!$vuetify.display.mobile" :temporary="$vuetify.display.mobile" elevation="2">
      <v-list-item
        prepend-avatar="https://ui-avatars.com/api/?name=Customer&background=0D47A1&color=fff"
        title="Customer Portal"
        subtitle="My Account"
        class="pa-4"
      ></v-list-item>

      <v-divider></v-divider>

      <v-list density="comfortable" nav>
        <v-list-item
          v-for="item in navItems"
          :key="item.title"
          :active="isActive(item.route)"
          :prepend-icon="item.icon"
          :title="item.title"
          @click="navigateTo(item.route)"
          link
          data-cy="customer-nav-item"
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

    <v-main class="bg-grey-lighten-4">
      <v-container fluid class="pa-8">
        <slot />
      </v-container>
      <NotificationCenter />
    </v-main>

    <AppFooter />
  </v-app>
</template>

<script setup>
import { ref, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { usePage, router } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import CartIcon from '@/Components/CartIcon.vue';
import LanguageSelector from '../Components/LanguageSelector.vue';
import NotificationCenter from '@/Components/NotificationCenter.vue';
import AppFooter from '../Components/AppFooter.vue';

const page = usePage();
const authStore = useAuthStore();
const drawer = ref(true);

const { user } = storeToRefs(authStore);

const currentRoute = computed(() => page.url);

const navItems = [
  { title: 'Dashboard', icon: 'mdi-view-dashboard', route: '/customer/dashboard' },
  { title: 'Menu', icon: 'mdi-menu', route: '/customer/menu' },
  { title: 'Orders', icon: 'mdi-clipboard-list', route: '/customer/orders' },
  { title: 'Profile', icon: 'mdi-account', route: '/customer/profile' },
  { title: 'Settings', icon: 'mdi-cog', route: '/customer/settings' },
  { title: 'Support', icon: 'mdi-face-agent', route: '/support' },
];

const isActive = (route) => {
  if (route === '/support') {
    return currentRoute.value.startsWith(route);
  }
  if (route === '/customer/orders') {
    return currentRoute.value.startsWith(route);
  }
  return currentRoute.value === route;
};

const currentPageTitle = computed(() => {
  const item = navItems.find(n => isActive(n.route));
  return item ? item.title : 'Customer Portal';
});

const navigateTo = (route) => {
  router.visit(route);
};

const logout = () => {
  authStore.logout(router);
};

// Sync auth store with server-side props
import { watch } from 'vue';
watch(() => page.props.auth?.user, (newUser) => {
  if (newUser) {
    authStore.user = newUser;
  }
}, { immediate: true });
</script>

<style scoped>
.v-navigation-drawer {
  background: linear-gradient(180deg, #fafafa 0%, #f0f0f0 100%);
}
</style>
