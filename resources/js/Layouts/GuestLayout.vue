<template>
  <Head :title="$t('app.title')" />
  <v-app data-cy="guest-layout">
    <v-app-bar app elevation="2" color="primary" theme="dark" data-cy="layout-header">
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-app-bar-title class="text-subtitle-1 font-weight-bold">
        {{ $page.props.app.name }}
      </v-app-bar-title>
      <v-spacer></v-spacer>
      <CartIcon class="mr-4" />
      <LanguageSelector />
    </v-app-bar>

    <v-navigation-drawer v-model="drawer" temporary data-cy="guest-drawer">
      <v-list density="compact" nav>
        <v-list-item
          prepend-icon="mdi-home"
          :title="$t('nav.home')"
          value="home"
          @click="router.visit(route('home'))"
        ></v-list-item>

        <template v-if="!isAuthenticated">
          <v-list-item
            prepend-icon="mdi-login"
            :title="$t('auth.login')"
            value="login"
            @click="router.visit(route('login'))"
          ></v-list-item>
          <v-list-item
            prepend-icon="mdi-account-plus"
            :title="$t('auth.register')"
            value="register"
            @click="router.visit(route('register'))"
          ></v-list-item>
        </template>

        <template v-else>
          <v-list-item
            v-if="user?.role === 'customer'"
            prepend-icon="mdi-silverware-fork-knife"
            :title="$t('auth.menu')"
            value="menu"
            @click="router.visit(route('menu'))"
          ></v-list-item>
          <v-list-item
            prepend-icon="mdi-logout"
            :title="$t('auth.logout')"
            value="logout"
            @click="handleLogout"
          ></v-list-item>
        </template>

        <v-divider class="my-2"></v-divider>

        <v-list-item
          prepend-icon="mdi-information-outline"
          :title="$t('nav.about')"
          value="about"
        ></v-list-item>
        <v-list-item
          prepend-icon="mdi-email-outline"
          :title="$t('nav.contact')"
          value="contact"
        ></v-list-item>
      </v-list>

      <template v-slot:append>
        <UserMenuAppend />
      </template>
    </v-navigation-drawer>

    <v-main>
      <v-container fluid class="pa-0">
        <slot data-cy="guest-content" />
      </v-container>
    </v-main>

    <AppFooter :minimal="props.minimal" />
  </v-app>
</template>

<script setup>
import { ref } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import { useLayout } from '@/Composables/useLayout'
import LanguageSelector from '@/Components/LanguageSelector.vue'
import AppFooter from '@/Components/AppFooter.vue'
import CartIcon from '@/Components/CartIcon.vue'
import UserMenuAppend from '@/Components/UserMenuAppend.vue'

const props = defineProps({
  minimal: {
    type: Boolean,
    default: false
  }
})

const { drawer, logout, isAuthenticated, user } = useLayout()

const handleLogout = () => {
  logout()
}
</script>

<style scoped>
.v-navigation-drawer {
  background: linear-gradient(180deg, #fafafa 0%, #f0f0f0 100%);
}

.v-main {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
}
</style>
