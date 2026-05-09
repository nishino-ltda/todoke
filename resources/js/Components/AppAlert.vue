<template>
  <v-alert
    v-if="modelValue"
    :type="type"
    :title="title"
    :text="text"
    :closable="closable"
    class="app-alert"
    :variant="variant"
    @click:close="close"
    data-cy="app-alert"
  >
    <template v-if="$slots.default">
      <slot></slot>
    </template>
    
    <template v-if="autoDismiss && modelValue">
      <div class="progress-bar" :style="{ width: progress + '%' }"></div>
    </template>
  </v-alert>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'info',
    validator: (val) => ['success', 'info', 'warning', 'error'].includes(val)
  },
  title: {
    type: String,
    default: ''
  },
  text: {
    type: String,
    default: ''
  },
  closable: {
    type: Boolean,
    default: true
  },
  variant: {
    type: String,
    default: 'flat'
  },
  autoDismiss: {
    type: [Boolean, Number],
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'close']);

const progress = ref(100);
let timer = null;
let progressInterval = null;

const close = () => {
  emit('update:modelValue', false);
  emit('close');
  clearTimers();
};

const clearTimers = () => {
  if (timer) clearTimeout(timer);
  if (progressInterval) clearInterval(progressInterval);
};

const startTimer = () => {
  if (!props.autoDismiss) return;
  
  const duration = typeof props.autoDismiss === 'number' ? props.autoDismiss : 5000;
  progress.value = 100;
  
  clearTimers();
  
  timer = setTimeout(() => {
    close();
  }, duration);
  
  const step = 100 / (duration / 10);
  progressInterval = setInterval(() => {
    progress.value -= step;
    if (progress.value <= 0) {
      clearInterval(progressInterval);
    }
  }, 10);
};

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    startTimer();
  } else {
    clearTimers();
  }
});

onMounted(() => {
  if (props.modelValue) {
    startTimer();
  }
});

onUnmounted(() => {
  clearTimers();
});
</script>

<style scoped>
.app-alert {
  border-radius: 8px;
  margin-bottom: 16px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.progress-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  background-color: rgba(255, 255, 255, 0.5);
  transition: width 0.01s linear;
}

/* Animations for premium feel */
.v-alert--variant-flat {
  background-image: linear-gradient(rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0));
}
</style>
