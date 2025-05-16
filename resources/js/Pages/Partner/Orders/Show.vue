<template>
    <AuthenticatedLayout>
        <div data-test="partner-orders-show">
            <h1>Partner Order Details</h1>
            <p>Details for order: {{ orderId }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    orderId: String,
});
</script>
