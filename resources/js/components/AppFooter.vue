<script setup>
const currentYear = new Date().getFullYear()
</script>

<template>
  <footer class="app-footer">
    <div class="footer-content">
      <div class="legal">
        <p>&copy; {{ currentYear }} TODOKE. All rights reserved.</p>
        <div class="legal-links">
          <router-link to="/terms">Terms of Service</router-link>
          <router-link to="/privacy">Privacy Policy</router-link>
        </div>
      </div>
      
      <div class="social">
        <a href="#" class="social-link">
          <span class="icon">f</span> Facebook
        </a>
        <a href="#" class="social-link">
          <span class="icon">t</span> Twitter
        </a>
        <a href="#" class="social-link">
          <span class="icon">i</span> Instagram
        </a>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.app-footer {
  background-color: #2c3e50;
  color: white;
  padding: 2rem 1rem;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.legal {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.legal-links {
  display: flex;
  gap: 1rem;
}

.legal-links a {
  color: white;
  text-decoration: none;
}

.social {
  display: flex;
  gap: 1.5rem;
}

.social-link {
  color: white;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.icon {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  width: 24px;
  height: 24px;
  background-color: white;
  color: #2c3e50;
  border-radius: 50%;
  font-weight: bold;
}
</style>
