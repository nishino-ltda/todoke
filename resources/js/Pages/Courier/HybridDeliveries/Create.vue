<template>
  <CourierLayout>
    <div class="create-hybrid-container" data-cy="hybrid-create">
      <v-row justify="center">
        <v-col cols="12" md="8">
          <v-card class="glass-card pa-8">
            <h2 class="text-h4 font-weight-black mb-2">Create Hybrid Delivery</h2>
            <p class="text-grey mb-8">Set up a delivery that involves multiple logistics stages (e.g., Courier to Drone Hub).</p>

            <v-form @submit.prevent="submit">
              <v-row>
                <v-col cols="12">
                  <h3 class="text-subtitle-1 font-weight-bold mb-3">Origin (Pickup)</h3>
                  <v-text-field
                    v-model="form.origin.address"
                    label="Pickup Address"
                    variant="outlined"
                    density="comfortable"
                    prepend-inner-icon="mdi-circle-slice-8"
                    :error-messages="form.errors['origin.address']"
                  ></v-text-field>
                </v-col>

                <v-col cols="12">
                  <h3 class="text-subtitle-1 font-weight-bold mb-3">Destination (Final Drop-off)</h3>
                  <v-text-field
                    v-model="form.destination.address"
                    label="Drop-off Address"
                    variant="outlined"
                    density="comfortable"
                    prepend-inner-icon="mdi-map-marker"
                    :error-messages="form.errors['destination.address']"
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.estimated_weight"
                    label="Weight (kg)"
                    type="number"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="form.errors.estimated_weight"
                  ></v-text-field>
                </v-col>

                <v-col cols="12" md="6">
                  <v-select
                    v-model="form.type"
                    :items="['standard', 'express', 'priority']"
                    label="Service Type"
                    variant="outlined"
                    density="comfortable"
                  ></v-select>
                </v-col>

                <v-col cols="12">
                  <v-textarea
                    v-model="form.item_description"
                    label="Item Description"
                    variant="outlined"
                    rows="3"
                    :error-messages="form.errors.item_description"
                  ></v-textarea>
                </v-col>
              </v-row>

              <div class="d-flex justify-end mt-6 ga-3">
                <v-btn variant="text" @click="router.visit('/courier/hybrid-deliveries')">Cancel</v-btn>
                <v-btn
                  color="primary"
                  size="large"
                  class="px-8 font-weight-bold text-none"
                  :loading="form.processing"
                  type="submit"
                  data-cy="submit-hybrid-btn"
                >
                  Create Hybrid Request
                </v-btn>
              </div>
            </v-form>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </CourierLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import CourierLayout from '@/Layouts/CourierLayout.vue';
import { useNotificationStore } from '@/stores/notification';

const notifications = useNotificationStore();

const form = useForm({
  origin: {
    lat: -23.550520,
    lng: -46.633308,
    address: ''
  },
  destination: {
    lat: -23.559520,
    lng: -46.643308,
    address: ''
  },
  estimated_weight: 1.5,
  dimensions: { width: 20, height: 20, depth: 20 },
  type: 'standard',
  item_description: '',
  payment_method: 'credit_card',
  is_hybrid: true,
  stages: [
    { type: 'delivery_point', status: 'pending' },
    { type: 'distribution_center', status: 'pending' }
  ]
});

const submit = () => {
  form.post('/api/v1/deliveries', {
    onSuccess: () => {
      notifications.success('Hybrid delivery created successfully');
      router.visit('/courier/hybrid-deliveries');
    },
    onError: () => notifications.error('Failed to create hybrid delivery')
  });
};
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.3);
}
</style>
