<template>
    <CourierLayout>
        <div data-cy="courier-hybrid-deliveries-index">
            <h1>{{ t('courier.hybrid_deliveries.index_title') }}</h1>
            <p>{{ t('courier.hybrid_deliveries.index_description') }}</p>
        </div>
    </CourierLayout>
</template>

<script setup>
import CourierLayout from '@/Layouts/CourierLayout.vue';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
</script>
