<template>
  <v-app data-cy="guest-layout">
    <v-navigation-drawer v-model="drawer" temporary data-cy="guest-drawer">
      <v-list-item
        prepend-avatar="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
        :title="isAuthenticated ? user?.name : 'Convidado'"
        :subtitle="isAuthenticated ? user?.email : 'Bem-vindo ao TODOKE'"
      ></v-list-item>

      <v-divider></v-divider>

      <v-list density="compact" nav>
        <v-list-item
          prepend-icon="mdi-home"
          title="Home"
          value="home"
          @click="router.visit(route('home'))"
        ></v-list-item>

        <template v-if="!isAuthenticated">
          <v-list-item
            prepend-icon="mdi-login"
            title="Entrar"
            value="login"
            @click="router.visit(route('login'))"
          ></v-list-item>
          <v-list-item
            prepend-icon="mdi-account-plus"
            title="Registrar"
            value="register"
            @click="router.visit(route('register'))"
          ></v-list-item>
        </template>

        <template v-else>
          <v-list-item
            v-if="user?.role === 'customer'"
            prepend-icon="mdi-silverware-fork-knife"
            title="Menu"
            value="menu"
            @click="router.visit(route('menu'))"
          ></v-list-item>
          <v-list-item
            prepend-icon="mdi-logout"
            title="Sair"
            value="logout"
            @click="handleLogout"
          ></v-list-item>
        </template>

        <v-divider class="my-2"></v-divider>

        <v-list-item
          prepend-icon="mdi-information-outline"
          title="Sobre Nós"
          value="about"
        ></v-list-item>
        <v-list-item
          prepend-icon="mdi-email-outline"
          title="Contato"
          value="contact"
        ></v-list-item>
      </v-list>
    </v-navigation-drawer>

    <AppHeader 
      :minimal="props.minimal" 
      :with-drawer-toggle="true" 
      @toggle-drawer="drawer = !drawer"
    />

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
import { router } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import AppHeader from '@/Components/AppHeader.vue'
import AppFooter from '@/Components/AppFooter.vue'

const props = defineProps({
  minimal: {
    type: Boolean,
    default: false
  }
})

const drawer = ref(false)
const authStore = useAuthStore()
const { isAuthenticated, user } = storeToRefs(authStore)

const handleLogout = () => {
  authStore.logout()
  drawer.value = false
}
</script>

<style scoped>
.v-main {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  min-height: 100vh;
}
</style>
