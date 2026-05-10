<script setup>
import CourierLayout from '@/Layouts/CourierLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
</script>

<template>
    <Head :title="t('courier.dashboard.index_title')" />

    <CourierLayout>
        <h2 data-cy="courier-dashboard-title">{{ t('courier.dashboard.index_title') }}</h2>

        <div data-cy="courier-dashboard-container">
            <div>
                <div>
                    <div>
                        {{ t('courier.dashboard.index_placeholder') }}
                    </div>
                </div>
            </div>
        </div>
    </CourierLayout>
</template>
