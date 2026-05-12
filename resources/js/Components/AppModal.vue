<template>
  <v-dialog
    v-model="modelValue"
    :max-width="maxWidth"
    :persistent="persistent"
    :scrollable="scrollable"
    @click:outside="onOutsideClick"
    data-cy="app-modal"
  >
    <v-card class="app-modal-card d-flex flex-column" :style="scrollable ? 'max-height: 90vh' : ''">

      <v-card-title v-if="title || $slots.title || showClose" class="d-flex align-center justify-space-between modal-header">
        <slot name="title">
          <span class="text-h5" data-cy="modal-title">{{ title }}</span>
        </slot>
        <v-btn
          v-if="showClose"
          icon="mdi-close"
          variant="text"
          @click="close"
          data-cy="modal-close-btn"
        ></v-btn>
      </v-card-title>

      <v-divider v-if="title || $slots.title"></v-divider>

      <v-card-text class="modal-content" data-cy="modal-content">
        <slot></slot>
      </v-card-text>

      <v-divider v-if="$slots.actions"></v-divider>

      <v-card-actions v-if="$slots.actions" class="modal-actions" data-cy="modal-actions">
        <v-spacer></v-spacer>
        <slot name="actions"></slot>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  maxWidth: {
    type: [String, Number],
    default: 500
  },
  persistent: {
    type: Boolean,
    default: false
  },
  showClose: {
    type: Boolean,
    default: true
  },
  scrollable: {
    type: Boolean,
    default: false
  }
});


const emit = defineEmits(['update:modelValue', 'close']);

const modelValue = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});

const close = () => {
  modelValue.value = false;
  emit('close');
};

const onOutsideClick = () => {
  if (!props.persistent) {
    close();
  }
};
</script>

<style scoped>
.app-modal-card {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-header {
  padding: 16px 24px;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.modal-content {
  padding: 24px;
}

.modal-actions {
  padding: 16px 24px;
  background-color: #f8f9fa;
}

/* Glassmorphism effect if needed, but keeping it clean for now */
</style>
