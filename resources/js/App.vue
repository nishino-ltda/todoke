<template>
  <div>
    <header data-test="app-header">
      <h1>Food Delivery App</h1>
    </header>
    <main>
      <router-view />
    </main>
    <footer data-test="app-footer">
      <p>&copy; 2025 Food Delivery</p>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style scoped>
header, footer {
  padding: 1rem;
  text-align: center;
  background: #f5f5f5;
}
</style>
