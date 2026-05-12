<template>
  <SupportLayout>
    <div v-if="ticket" data-cy="support-ticket-detail">
      <div class="d-flex justify-space-between align-center mb-6">
        <div>
          <h1 class="text-h4 mb-1" data-cy="ticket-subject">{{ ticket.subject }}</h1>
          <div class="text-caption text-grey">#{{ ticket.id }} • {{ formatDate(ticket.created_at) }}</div>
        </div>
        <v-chip :color="getStatusColor(ticket.status)" class="text-uppercase font-weight-bold" data-cy="ticket-status-chip">
          {{ t(`support.status.${ticket.status}`) }}
        </v-chip>
      </div>

      <v-row>
        <v-col cols="12" md="8">
          <v-card border elevation="0" class="mb-6">
            <v-card-title class="bg-grey-lighten-4 py-3">
              {{ t('support.detail.messages') }}
            </v-card-title>
            <v-card-text class="pa-0">
              <v-list class="message-thread">
                <v-list-item v-for="(msg, index) in ticket.messages" :key="index" class="py-4 border-b" data-cy="reply-item">
                  <v-list-item-title class="d-flex justify-space-between mb-2">
                    <span class="font-weight-bold">{{ msg.user_name || 'System' }}</span>
                    <span class="text-caption text-grey">{{ formatDate(msg.created_at) }}</span>
                  </v-list-item-title>
                  <div class="text-body-1 whitespace-pre-wrap" data-cy="ticket-message">{{ msg.body }}</div>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>

          <div class="d-flex gap-4">
            <v-btn color="primary" prepend-icon="mdi-reply" @click="router.visit(`/support/tickets/${ticket.id}/reply`)" :disabled="ticket.status === 'closed'" data-cy="reply-btn">
              {{ t('support.detail.reply') }}
            </v-btn>
            <v-btn variant="outlined" color="error" prepend-icon="mdi-close-circle-outline" @click="confirmClose" :disabled="ticket.status === 'closed'" data-cy="close-btn">
              {{ t('support.detail.close') }}
            </v-btn>
          </div>
        </v-col>

        <v-col cols="12" md="4">
          <v-card border elevation="0">
            <v-card-title class="bg-grey-lighten-4 py-3">
              {{ t('support.detail.info') }}
            </v-card-title>
            <v-card-text class="py-4">
              <div class="mb-4">
                <div class="text-caption text-grey">{{ t('support.tickets.table.category') }}</div>
                <div class="text-body-1">{{ t(`support.categories.${ticket.category}`) }}</div>
              </div>
              <div>
                <div class="text-caption text-grey">{{ t('support.create.priority') }}</div>
                <v-chip :color="getPriorityColor(ticket.priority)" size="small">
                  {{ t(`support.priorities.${ticket.priority}`) }}
                </v-chip>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-dialog v-model="showCloseDialog" max-width="400">
        <v-card>
          <v-card-title>{{ t('support.detail.close') }}</v-card-title>
          <v-card-text>{{ t('support.detail.close_confirm') }}</v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn variant="text" @click="showCloseDialog = false">Cancel</v-btn>
            <v-btn color="error" @click="closeTicket" :loading="closing" data-cy="confirm-close-btn">Confirm</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
    <div v-else-if="loading" class="text-center py-12">
      <v-progress-circular indeterminate color="primary"></v-progress-circular>
    </div>
  </SupportLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import SupportLayout from '@/Layouts/SupportLayout.vue';
import supportService from '@/services/support';
import { useNotificationStore } from '@/stores/notification';

const props = defineProps({
  id: String
});

const { t } = useI18n();
const notifications = useNotificationStore();

const ticket = ref(null);
const loading = ref(true);
const showCloseDialog = ref(false);
const closing = ref(false);

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleString();
};

const getStatusColor = (status) => {
  switch (status) {
    case 'open': return 'primary';
    case 'in_progress': return 'warning';
    case 'resolved': return 'success';
    case 'closed': return 'grey';
    default: return 'primary';
  }
};

const getPriorityColor = (priority) => {
  switch (priority) {
    case 'low': return 'grey';
    case 'medium': return 'info';
    case 'high': return 'error';
    default: return 'grey';
  }
};

const confirmClose = () => {
  showCloseDialog.value = true;
};

const closeTicket = async () => {
  closing.value = true;
  try {
    await supportService.closeTicket(ticket.value.id);
    notifications.success('Ticket closed');
    showCloseDialog.value = false;
    fetchTicket();
  } catch (err) {
    console.error(err);
  } finally {
    closing.value = false;
  }
};

const fetchTicket = async () => {
  loading.value = true;
  try {
    const id = props.id || window.location.pathname.split('/').pop();
    const response = await supportService.getTicket(id);
    ticket.value = response.data.data || response;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchTicket);
</script>

<style scoped>
.whitespace-pre-wrap {
  white-space: pre-wrap;
}
.border-b {
  border-bottom: 1px solid #f0f0f0;
}
.border-b:last-child {
  border-bottom: none;
}
</style>
