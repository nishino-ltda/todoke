<template>
    <AuthenticatedLayout>
        <div data-cy="courier-page">
            <h1>{{ t('courier.management.title') }}</h1>
            <p>{{ t('courier.management.description') }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
</script>
