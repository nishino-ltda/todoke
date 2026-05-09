<template>
  <SupportLayout>
    <div data-cy="support-ticket-reply">
      <h1 class="text-h4 mb-6">{{ t('support.reply.title') }}</h1>

      <v-card border elevation="0" class="pa-6">
        <v-form @submit.prevent="submit" v-model="isValid">
          <v-textarea
            v-model="form.message"
            :label="t('support.reply.message_placeholder')"
            :rules="[rules.required]"
            required
            rows="8"
            data-cy="message-input"
          />

          <v-file-input
            v-model="form.attachment"
            :label="t('support.create.attachment')"
            prepend-icon="mdi-paperclip"
            data-cy="attachment-input"
          />

          <div class="mt-6 d-flex gap-4">
            <v-btn color="primary" type="submit" :loading="loading" :disabled="!isValid" data-cy="submit-btn">
              {{ t('support.reply.submit') }}
            </v-btn>
            <v-btn variant="outlined" @click="goBack" data-cy="cancel-btn">
              Cancel
            </v-btn>
          </div>
        </v-form>
      </v-card>
    </div>
  </SupportLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import SupportLayout from '@/Layouts/SupportLayout.vue';
import supportService from '@/services/support';
import { useNotificationStore } from '@/stores/notification';

const props = defineProps({
  id: String
});

const { t } = useI18n();
const notifications = useNotificationStore();

const loading = ref(false);
const isValid = ref(false);

const form = reactive({
  message: '',
  attachment: null
});

const rules = {
  required: value => !!value || 'Required.'
};

const getTicketId = () => {
  if (props.id) return props.id;
  const parts = window.location.pathname.split('/');
  // /support/tickets/:id/reply
  return parts[parts.length - 2];
};

const goBack = () => {
  router.visit(`/support/tickets/${getTicketId()}`);
};

const submit = async () => {
  if (!isValid.value) return;

  loading.value = true;
  const ticketId = getTicketId();
  try {
    const data = new FormData();
    data.append('message', form.message);
    if (form.attachment) {
      data.append('attachment', form.attachment);
    }

    await supportService.addReply(ticketId, data);
    notifications.success(t('support.reply.success'));
    goBack();
  } catch (err) {
    console.error(err);
    notifications.error('Failed to send reply');
  } finally {
    loading.value = false;
  }
};
</script>
