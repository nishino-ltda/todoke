<template>
  <SupportLayout>
    <div data-cy="support-faq">
      <h1 class="text-h4 mb-6">{{ t('support.faq.title') }}</h1>

      <v-text-field
        v-model="search"
        :label="t('support.faq.search_placeholder')"
        prepend-inner-icon="mdi-magnify"
        variant="outlined"
        class="mb-6"
        clearable
        data-cy="faq-search"
      />

      <v-expansion-panels v-if="filteredFaqs.length > 0">
        <v-expansion-panel
          v-for="(item, index) in filteredFaqs"
          :key="index"
          :title="item.question"
          :text="item.answer"
          data-cy="faq-item"
        />
      </v-expansion-panels>

      <div v-else-if="!loading" class="text-center py-12 text-grey">
        <v-icon size="64" class="mb-4">mdi-help-box</v-icon>
        <p>No FAQs found matching your search.</p>
      </div>

      <div v-if="loading" class="text-center py-12">
        <v-progress-circular indeterminate color="primary"></v-progress-circular>
      </div>
    </div>
  </SupportLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import SupportLayout from '@/Layouts/SupportLayout.vue';
import supportService from '@/services/support';

const { t } = useI18n();
const faqs = ref([]);
const loading = ref(false);
const search = ref('');

const filteredFaqs = computed(() => {
  if (!search.value) return faqs.value;
  const s = search.value.toLowerCase();
  return faqs.value.filter(f => 
    f.question.toLowerCase().includes(s) || 
    f.answer.toLowerCase().includes(s)
  );
});

onMounted(async () => {
  loading.value = true;
  try {
    const response = await supportService.getFaqs();
    faqs.value = response.data || response;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
});
</script>
