<template>
  <AdminLayout>
    <div class="deliveries-monitoring pa-6" data-cy="deliveries-monitoring">
      <h1 class="text-h4 mb-6">{{ t('admin.deliveries.title', 'Delivery Monitoring') }}</h1>
      
      <v-row class="mb-6">
        <v-col cols="12" md="3" v-for="stat in deliveryStats" :key="stat.label">
          <v-card border elevation="0" class="text-center py-4 rounded-xl" data-cy="delivery-metric-card">
            <div class="text-h4 font-weight-bold" :class="stat.color + '--text'">{{ stat.value }}</div>
            <div class="text-subtitle-2 text-grey">{{ t(stat.labelKey, stat.label) }}</div>
          </v-card>
        </v-col>
      </v-row>

      <v-card border elevation="0" class="rounded-xl">
        <DataTable
          :headers="headers"
          :items="deliveries"
          :loading="loading"
          data-cy="deliveries-table"
        >
          <template #[`item.status`]="{ item }">
            <v-chip
              :color="getStatusColor(item.status)"
              size="small"
              class="text-uppercase font-weight-bold"
              data-cy="delivery-status-chip"
            >
              {{ t('common.status.' + item.status, item.status) }}
            </v-chip>
          </template>
        </DataTable>
      </v-card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DataTable from '@/Components/DataTable.vue'
import adminService from '@/services/admin'

const { t } = useI18n()
const loading = ref(false)
const deliveries = ref([])

const deliveryStats = ref([
  { label: 'Active', labelKey: 'admin.deliveries.stats.active', value: 0, color: 'primary' },
  { label: 'Pending', labelKey: 'admin.deliveries.stats.pending', value: 0, color: 'warning' },
  { label: 'Completed', labelKey: 'admin.deliveries.stats.completed', value: 0, color: 'success' },
  { label: 'Cancelled', labelKey: 'admin.deliveries.stats.cancelled', value: 0, color: 'error' }
])

const headers = computed(() => [
  { title: 'ID', key: 'id' },
  { title: t('admin.deliveries.table.customer', 'Customer'), key: 'customer_name' },
  { title: t('admin.deliveries.table.courier', 'Courier'), key: 'courier_name' },
  { title: t('admin.deliveries.table.status', 'Status'), key: 'status' },
  { title: t('admin.deliveries.table.created', 'Created'), key: 'created_at' }
])

const getStatusColor = (status) => {
  const colors = {
    'pending': 'warning',
    'accepted': 'info',
    'picked_up': 'primary',
    'delivered': 'success',
    'cancelled': 'error'
  }
  return colors[status] || 'grey'
}

onMounted(async () => {
  loading.value = true
  try {
    const response = await adminService.getDeliveries()
    deliveries.value = response.data || []
    
    // Update stats based on fetched data
    deliveryStats.value[0].value = deliveries.value.filter(d => ['accepted', 'picked_up'].includes(d.status)).length
    deliveryStats.value[1].value = deliveries.value.filter(d => d.status === 'pending').length
    deliveryStats.value[2].value = deliveries.value.filter(d => d.status === 'delivered').length
    deliveryStats.value[3].value = deliveries.value.filter(d => d.status === 'cancelled').length
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
})
</script>

