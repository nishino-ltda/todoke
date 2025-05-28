<template>
  <!-- Full page loader -->
  <v-overlay
    v-if="fullPage"
    :value="isLoading"
    @update:modelValue="$emit('update:isLoading', $event)"
    data-test="full-loader"
    class="align-center justify-center text-center"
    persistent
  >
    <v-progress-circular
      indeterminate
      size="64"
      color="primary"
    ></v-progress-circular>
    <div class="mt-4" v-if="message">{{ message }}</div>
  </v-overlay>

  <!-- Inline loader -->
  <div v-else-if="isLoading" data-test="inline-loader">
    <v-progress-circular
      indeterminate
      size="24"
      color="primary"
    ></v-progress-circular>
    <span class="ml-2" v-if="message">{{ message }}</span>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  isLoading: {
    type: Boolean,
    required: true
  },
  fullPage: {
    type: Boolean,
    default: false
  },
  message: {
    type: String,
    default: 'Loading...'
  }
})
</script>
