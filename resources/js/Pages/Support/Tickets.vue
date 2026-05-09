<template>
  <SupportLayout>
    <div data-cy="support-tickets-index">
      <div class="d-flex justify-space-between align-center mb-6">
        <h1 class="text-h4">{{ t('support.tickets.title') }}</h1>
        <v-btn color="primary" prepend-icon="mdi-plus" @click="router.visit('/support/tickets/create')" data-cy="create-ticket-btn">
          {{ t('support.dashboard.actions.new_ticket') }}
        </v-btn>
      </div>

      <div class="mb-4 d-flex gap-2 flex-wrap">
        <v-btn
          v-for="filter in statusFilters"
          :key="filter.value"
          :variant="selectedStatus === filter.value ? 'flat' : 'outlined'"
          :color="selectedStatus === filter.value ? 'primary' : 'grey'"
          size="small"
          @click="selectedStatus = filter.value"
          class="mr-2 mb-2"
          data-cy="status-filter-btn"
        >
          {{ filter.title }}
        </v-btn>
      </div>

      <DataTable
        :headers="headers"
        :items="filteredTickets"
        :loading="loading"
        :show-search="true"
        :search-label="t('support.tickets.search_placeholder')"
        @click:row="goToDetail"
        data-cy="tickets-table"
      >
        <template #[`item.status`]="{ item }">
          <v-chip :color="getStatusColor(item.status)" size="small">
            {{ t(`support.status.${item.status}`) }}
          </v-chip>
        </template>
        <template #[`item.category`]="{ item }">
          {{ t(`support.categories.${item.category}`) }}
        </template>
        <template #[`item.created_at`]="{ item }">
          {{ formatDate(item.created_at) }}
        </template>
        <template #[`item.updated_at`]="{ item }">
          {{ formatDate(item.updated_at) }}
        </template>
      </DataTable>
    </div>
  </SupportLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import SupportLayout from '@/Layouts/SupportLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import supportService from '@/services/support';

const { t } = useI18n();
const tickets = ref([]);
const loading = ref(false);
const selectedStatus = ref('all');

const statusFilters = [
  { title: t('support.tickets.filters.all'), value: 'all' },
  { title: t('support.status.open'), value: 'open' },
  { title: t('support.status.in_progress'), value: 'in_progress' },
  { title: t('support.status.resolved'), value: 'resolved' },
  { title: t('support.status.closed'), value: 'closed' }
];

const headers = [
  { title: t('support.tickets.table.id'), key: 'id' },
  { title: t('support.tickets.table.subject'), key: 'subject' },
  { title: t('support.tickets.table.category'), key: 'category' },
  { title: t('support.tickets.table.status'), key: 'status' },
  { title: t('support.tickets.table.created'), key: 'created_at' },
  { title: t('support.tickets.table.updated'), key: 'updated_at' }
];

const filteredTickets = computed(() => {
  if (selectedStatus.value === 'all') return tickets.value;
  return tickets.value.filter(t => t.status === selectedStatus.value);
});

const getStatusColor = (status) => {
  switch (status) {
    case 'open': return 'primary';
    case 'in_progress': return 'warning';
    case 'resolved': return 'success';
    case 'closed': return 'grey';
    default: return 'primary';
  }
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleString();
};

const goToDetail = (event, { item }) => {
  router.visit(`/support/tickets/${item.id}`);
};

onMounted(async () => {
  loading.value = true;
  try {
    const response = await supportService.getTickets();
    tickets.value = response.data || [];
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
});
</script>
