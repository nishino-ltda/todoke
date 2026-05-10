<template>
    <CourierLayout>
        <div data-cy="courier-page">
            <h1>{{ t('courier.management.title') }}</h1>
            <p>{{ t('courier.management.description') }}</p>
        </div>
    </CourierLayout>
</template>

<script setup>
import CourierLayout from '@/Layouts/CourierLayout.vue';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
</script>
