<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="TODOKE" />
  
  <div class="min-h-screen bg-gray-100">
    <main>
      <slot />
    </main>
  </div>
</template>
