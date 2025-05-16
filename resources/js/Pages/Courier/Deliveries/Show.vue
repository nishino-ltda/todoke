<template>
    <AuthenticatedLayout>
        <div data-test="courier-deliveries-show">
            <h1>Courier Delivery Details</h1>
            <p>Details for delivery: {{ deliveryId }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    deliveryId: String,
});
</script>
