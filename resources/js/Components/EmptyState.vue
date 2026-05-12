<template>
  <div
    class="empty-state text-center pa-8"
    :class="containerClass"
    data-cy="empty-state"
  >
    <v-icon
      :color="iconColor"
      :size="iconSize"
      class="mb-4"
    >
      {{ icon }}
    </v-icon>
    <h2
      v-if="title"
      class="text-h5 font-weight-bold mt-2"
    >
      {{ title }}
    </h2>
    <p
      class="text-body-1 text-medium-emphasis mt-2"
      style="max-width: 400px; margin-left: auto; margin-right: auto;"
    >
      {{ message }}
    </p>
    <v-btn
      v-if="actionLabel"
      :color="actionColor || 'primary'"
      class="mt-4 font-weight-bold text-none"
      size="large"
      rounded="xl"
      @click="$emit('action')"
      data-cy="empty-state-action"
    >
      {{ actionLabel }}
    </v-btn>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  icon: { type: String, default: 'mdi-information-outline' },
  iconColor: { type: String, default: 'grey' },
  iconSize: { type: [Number, String], default: 64 },
  title: { type: String, default: '' },
  message: { type: String, default: '' },
  actionLabel: { type: String, default: '' },
  actionColor: { type: String, default: 'primary' },
  containerClass: { type: String, default: '' },
})

defineEmits(['action'])
</script>
