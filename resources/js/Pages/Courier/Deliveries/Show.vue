<template>
    <CourierLayout>
        <div data-cy="courier-deliveries-show">
            <h1>{{ t('courier.deliveries.show_title') }}</h1>
            <p>{{ t('courier.deliveries.show_description', { id: deliveryId }) }}</p>
        </div>
    </CourierLayout>
</template>

<script setup>
import CourierLayout from '@/Layouts/CourierLayout.vue';
import { defineProps } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const props = defineProps({
    deliveryId: String,
});
</script>
