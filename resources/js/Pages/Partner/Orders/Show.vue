<template>
  <PartnerLayout>
    <div class="partner-order-show" data-cy="partner-order-show">
      <div v-if="loading" class="d-flex justify-center pa-12">
        <v-progress-circular indeterminate color="primary"></v-progress-circular>
      </div>
      <div v-else-if="order">
        <OrderDetails :order="order" />
      </div>
      <div v-else class="text-center pa-12">
        <v-alert type="error" variant="tonal">
          {{ t('partner.orders.not_found') }}
        </v-alert>
        <v-btn color="primary" class="mt-4" @click="router.visit('/partner/orders')">
          {{ t('partner.orders.back_to_orders') }}
        </v-btn>
      </div>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import OrderDetails from '../OrderDetails.vue';
import partnerService from '@/services/partner';
import { useNotificationStore } from '@/stores/notification';

const props = defineProps({
  orderId: {
    type: [String, Number],
    required: true
  }
});

const { t } = useI18n();
const notifications = useNotificationStore();

const loading = ref(true);
const order = ref(null);

const fetchOrder = async () => {
  loading.value = true;
  try {
    const response = await partnerService.getOrder(props.orderId);
    order.value = response.data;
  } catch (err) {
    notifications.error(t('partner.orders.failed_load'));
  } finally {
    loading.value = false;
  }
};

onMounted(fetchOrder);
</script>
