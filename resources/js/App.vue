<template>
  <div>
    <header data-cy="app-header">
      <h1>Food Delivery App</h1>
      <CartIcon />
    </header>
    <main>
      <router-view />
    </main>
    <footer data-cy="app-footer">
      <p>&copy; 2025 Food Delivery</p>
    </footer>
  </div>
</template>

<script>
import CartIcon from '@/Components/CartIcon.vue'

export default {
  name: 'App',
  components: {
    CartIcon
  }
}
</script>

<style scoped>
header, footer {
  padding: 1rem;
  text-align: center;
  background: #f5f5f5;
}
</style>
