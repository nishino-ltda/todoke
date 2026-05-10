<template>
    <CustomerLayout>
        <div data-cy="customer-orders-show">
            <h1>Customer Order Details</h1>
            <p>Details for order: {{ orderId }}</p>
        </div>
    </CustomerLayout>
</template>

<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    orderId: String,
});
</script>
