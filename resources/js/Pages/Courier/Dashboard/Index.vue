<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
</script>

<template>
    <Head :title="t('courier.dashboard.index_title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-cy="courier-dashboard-title">{{ t('courier.dashboard.index_title') }}</h2>
        </template>

        <div data-cy="courier-dashboard-container">
            <div>
                <div>
                    <div>
                        {{ t('courier.dashboard.index_placeholder') }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
