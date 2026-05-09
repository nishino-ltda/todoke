<template>
    <AuthenticatedLayout>
        <div data-cy="courier-hybrid-deliveries-show">
            <h1>{{ t('courier.hybrid_deliveries.show_title') }}</h1>
            <p>{{ t('courier.hybrid_deliveries.show_description', { id: hybridDeliveryId }) }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineProps } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({
    hybridDeliveryId: String,
});
</script>
