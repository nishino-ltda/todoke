<template>
    <AuthenticatedLayout>
        <div data-test="courier-hybrid-deliveries-index">
            <h1>{{ t('courier.hybrid_deliveries.index_title') }}</h1>
            <p>{{ t('courier.hybrid_deliveries.index_description') }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
</script>
