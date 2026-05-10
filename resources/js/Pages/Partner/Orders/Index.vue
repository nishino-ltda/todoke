<template>
  <PartnerLayout>
    <div class="partner-orders-index" data-cy="partner-orders-index">
      <DataTable
        :title="t('partner.orders.title')"
        :headers="translatedHeaders"
        :items="orders"
        :loading="loading"
        data-cy="partner-orders-table"
      >
        <template #item.status="{ item }">
          <v-chip
            :color="getStatusColor(item.status)"
            size="small"
            class="text-uppercase font-weight-bold"
          >
            {{ t('partner.status.' + item.status) }}
          </v-chip>
        </template>
        
        <template #item.total="{ item }">
          <span class="font-weight-bold">{{ t('common.currency_symbol', { value: item.total?.toFixed(2) || '0.00' }, '$' + (item.total?.toFixed(2) || '0.00')) }}</span>
        </template>

        <template #item.actions="{ item }">
          <v-btn
            variant="text"
            color="primary"
            icon="mdi-eye"
            @click="router.visit(`/partner/orders/${item.id}`)"
            data-cy="view-order-btn"
          ></v-btn>
          <v-btn
            v-if="item.status === 'pending'"
            variant="text"
            color="success"
            icon="mdi-check"
            @click="updateStatus(item.id, 'preparing')"
            data-cy="accept-order-btn"
          ></v-btn>
        </template>
      </DataTable>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import partnerService from '@/services/partner';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();

const loading = ref(false);
const orders = ref([]);

const orderHeaders = [
  { titleKey: 'partner.orders.id', key: 'id' },
  { titleKey: 'partner.orders.customer', key: 'customer_name' },
  { titleKey: 'partner.orders.total', key: 'total' },
  { titleKey: 'partner.orders.status', key: 'status' },
  { titleKey: 'partner.orders.actions', key: 'actions', sortable: false },
];

const translatedHeaders = computed(() => 
  orderHeaders.map(h => ({ ...h, title: t(h.titleKey) }))
);

const getStatusColor = (status) => {
  const colors = {
    'pending': 'orange',
    'preparing': 'blue',
    'ready': 'teal',
    'completed': 'grey',
    'cancelled': 'red'
  };
  return colors[status] || 'grey';
};

const fetchOrders = async () => {
  loading.value = true;
  try {
    const response = await partnerService.getOrders();
    orders.value = response.data || [];
  } catch (err) {
    notifications.error(t('partner.orders.failed_load'));
  } finally {
    loading.value = false;
  }
};

const updateStatus = async (orderId, status) => {
  try {
    await partnerService.updateOrderStatus(orderId, status);
    notifications.success(t('partner.orders.status_updated'));
    fetchOrders();
  } catch (err) {
    notifications.error(t('partner.orders.failed_update'));
  }
};

onMounted(fetchOrders);
</script>
