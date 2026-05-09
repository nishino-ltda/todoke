<template>
  <SupportLayout>
    <div data-cy="support-ticket-create">
      <h1 class="text-h4 mb-6">{{ t('support.create.title') }}</h1>

      <v-card border elevation="0" class="pa-6">
        <v-form @submit.prevent="submit" v-model="isValid">
          <v-text-field
            v-model="form.subject"
            :label="t('support.create.subject')"
            :rules="[rules.required]"
            required
            data-cy="subject-input"
          />

          <v-select
            v-model="form.category"
            :items="categories"
            :label="t('support.create.category')"
            :rules="[rules.required]"
            required
            data-cy="category-select"
          />

          <v-select
            v-model="form.priority"
            :items="priorities"
            :label="t('support.create.priority')"
            :rules="[rules.required]"
            required
            data-cy="priority-select"
          />

          <v-textarea
            v-model="form.message"
            :label="t('support.create.message')"
            :rules="[rules.required]"
            required
            rows="5"
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
              {{ t('support.create.submit') }}
            </v-btn>
            <v-btn variant="outlined" @click="router.visit('/support/tickets')" data-cy="cancel-btn">
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

const { t } = useI18n();
const notifications = useNotificationStore();

const loading = ref(false);
const isValid = ref(false);

const form = reactive({
  subject: '',
  category: '',
  priority: 'medium',
  message: '',
  attachment: null
});

const categories = [
  { title: t('support.categories.delivery_issue'), value: 'delivery_issue' },
  { title: t('support.categories.account'), value: 'account' },
  { title: t('support.categories.payment'), value: 'payment' },
  { title: t('support.categories.other'), value: 'other' }
];

const priorities = [
  { title: t('support.priorities.low'), value: 'low' },
  { title: t('support.priorities.medium'), value: 'medium' },
  { title: t('support.priorities.high'), value: 'high' }
];

const rules = {
  required: value => !!value || 'Required.'
};

const submit = async () => {
  if (!isValid.value) return;

  loading.value = true;
  try {
    const data = new FormData();
    data.append('subject', form.subject);
    data.append('category', form.category);
    data.append('priority', form.priority);
    data.append('message', form.message);
    if (form.attachment) {
      data.append('attachment', form.attachment);
    }

    await supportService.createTicket(data);
    notifications.success(t('support.create.success'));
    router.visit('/support/tickets');
  } catch (err) {
    console.error(err);
    notifications.error('Failed to create ticket');
  } finally {
    loading.value = false;
  }
};
</script>
