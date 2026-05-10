<template>
  <SupportLayout>
    <div data-cy="support-dashboard">
      <h1 class="text-h4 mb-6">{{ t('support.dashboard.title') }}</h1>

      <v-row class="mb-6">
        <v-col cols="12" md="4" v-for="(count, status) in stats" :key="status">
          <v-card border elevation="0" class="text-center py-4" data-cy="stat-card">
            <div class="text-h3 font-weight-bold" :class="getStatusColor(status)" data-cy="stat-count">{{ count }}</div>
            <div class="text-subtitle-1 text-grey">{{ t(`support.dashboard.stats.${status}`) }}</div>
          </v-card>
        </v-col>
      </v-row>

      <v-row class="mb-6">
        <v-col cols="12" md="4">
          <v-btn block color="primary" size="large" prepend-icon="mdi-plus" @click="router.visit('/support/tickets/create')" data-cy="new-ticket-btn">
            {{ t('support.dashboard.actions.new_ticket') }}
          </v-btn>
        </v-col>
        <v-col cols="12" md="4">
          <v-btn block variant="outlined" size="large" prepend-icon="mdi-ticket-outline" @click="router.visit('/support/tickets')" data-cy="my-tickets-btn">
            {{ t('support.dashboard.actions.my_tickets') }}
          </v-btn>
        </v-col>
        <v-col cols="12" md="4">
          <v-btn block variant="outlined" size="large" prepend-icon="mdi-help-circle-outline" @click="router.visit('/support/faq')" data-cy="faq-btn">
            {{ t('support.dashboard.actions.faq') }}
          </v-btn>
        </v-col>
      </v-row>

      <v-card border elevation="0">
        <v-card-title class="px-4 pt-4">
          {{ t('support.dashboard.recent_tickets') }}
        </v-card-title>
        <v-list>
          <v-list-item
            v-for="ticket in recentTickets"
            :key="ticket.id"
            :title="ticket.subject"
            :subtitle="`${t('support.tickets.table.status')}: ${t(`support.status.${ticket.status}`)}`"
            link
            @click="router.visit(`/support/tickets/${ticket.id}`)"
            data-cy="ticket-item"
          >
            <template v-slot:append>
              <v-chip :color="getStatusColor(ticket.status)" size="small">
                {{ t(`support.status.${ticket.status}`) }}
              </v-chip>
            </template>
          </v-list-item>
          <v-list-item v-if="recentTickets.length === 0">
            <v-list-item-title class="text-grey text-center py-4">No recent activity</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-card>
    </div>
  </SupportLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import SupportLayout from '@/Layouts/SupportLayout.vue';
import supportService from '@/services/support';

const { t } = useI18n();
const tickets = ref([]);
const loading = ref(false);

const stats = computed(() => {
  return {
    open: tickets.value.filter(t => t.status === 'open').length,
    in_progress: tickets.value.filter(t => t.status === 'in_progress').length,
    resolved: tickets.value.filter(t => t.status === 'resolved').length
  };
});

const recentTickets = computed(() => {
  return [...tickets.value]
    .sort((a, b) => new Date(b.updated_at || b.created_at) - new Date(a.updated_at || a.created_at))
    .slice(0, 5);
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
