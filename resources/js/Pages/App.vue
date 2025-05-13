<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="TODOKE" />
  
  <v-app>
    <v-main>
      <slot />
    </v-main>
  </v-app>
</template>
