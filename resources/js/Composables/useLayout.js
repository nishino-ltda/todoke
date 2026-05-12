import { ref, computed, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { usePage, router } from '@inertiajs/vue3'
import { useAuthStore } from '@/stores/auth'

export function useLayout() {
  const page = usePage()
  const authStore = useAuthStore()
  const drawer = ref(true)

  const { user, isAuthenticated } = storeToRefs(authStore)

  const currentRoute = computed(() => page.url)

  const navigateTo = (route) => {
    router.visit(route)
  }

  const logout = () => {
    authStore.logout(router)
  }

  const avatarUrl = computed(() => {
    const u = page.props.auth?.user
    if (!u) return 'https://ui-avatars.com/api/?name=U&background=0D47A1&color=fff'
    if (u.photoUrl) return u.photoUrl.startsWith('http') ? u.photoUrl : `/storage/${u.photoUrl}`
    return `https://ui-avatars.com/api/?name=${u.name || 'U'}&background=0D47A1&color=fff`
  })

  const userName = computed(() => page.props.auth?.user?.name || page.props.auth?.user?.email || 'User')

  const userEmail = computed(() => page.props.auth?.user?.email || '')

  watch(() => page.props.auth?.user, (newUser) => {
    if (newUser) authStore.user = newUser
  }, { immediate: true })

  return { drawer, currentRoute, navigateTo, logout, avatarUrl, userName, userEmail, page, user, isAuthenticated }
}
