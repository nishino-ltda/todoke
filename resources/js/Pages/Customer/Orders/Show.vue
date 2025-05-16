<template>
    <AuthenticatedLayout>
        <div data-test="customer-orders-show">
            <h1>Customer Order Details</h1>
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
