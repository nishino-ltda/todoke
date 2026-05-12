<template>
  <CourierLayout>
    <div class="handoff-container" data-cy="handoff-page">
      <v-row justify="center">
        <v-col cols="12" md="6">
          <v-card class="glass-card pa-8 text-center">
            <v-icon size="64" color="secondary" class="mb-4">mdi-handshake-outline</v-icon>
            <h2 class="text-h4 font-weight-black mb-2">Stage Handoff</h2>
            <p class="text-grey mb-8">You are at the handoff point for delivery #{{ deliveryId }}. Please confirm the handoff to the next logistics partner or drone hub.</p>

            <v-card variant="outlined" class="pa-4 mb-8 text-left bg-grey-lighten-5">
              <div class="d-flex align-center mb-2">
                <v-icon size="20" class="mr-2">mdi-qrcode-scan</v-icon>
                <span class="font-weight-bold">Security Code</span>
              </div>
              <div class="text-h3 font-weight-black text-center py-4 letter-spacing-wide">
                {{ handoffCode }}
              </div>
              <p class="text-caption text-center text-grey">Show this code to the recipient or enter it in the terminal.</p>
            </v-card>

            <v-btn
              block
              color="secondary"
              size="large"
              class="font-weight-bold text-none"
              :loading="processing"
              @click="confirmHandoff"
              data-cy="confirm-handoff-btn"
            >
              Confirm Handoff Complete
            </v-btn>
            
            <v-btn
              variant="text"
              block
              class="mt-4 text-none"
              @click="router.back()"
            >
              Back to Details
            </v-btn>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </CourierLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import CourierLayout from '@/Layouts/CourierLayout.vue';
import { useNotificationStore } from '@/stores/notification';
import deliveryService from '@/services/delivery';

const props = defineProps({
  deliveryId: { type: [String, Number], required: true }
});

const notifications = useNotificationStore();
const processing = ref(false);
const handoffCode = ref(Math.floor(100000 + Math.random() * 900000).toString());

const confirmHandoff = async () => {
  processing.value = true;
  try {
    await deliveryService.updateDeliveryStatus(props.deliveryId, 'collected', {
      stage_type: 'distribution_center'
    });
    notifications.success('Handoff confirmed successfully');
    router.visit(`/courier/hybrid-deliveries/${props.deliveryId}`);
  } catch (err) {
    notifications.error('Failed to confirm handoff');
  } finally {
    processing.value = false;
  }
};
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.letter-spacing-wide {
  letter-spacing: 0.2em;
}

.handoff-container {
  animation: scaleIn 0.3s ease-out;
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
