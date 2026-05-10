<template>
    <CustomerLayout>
        <div>
            <h1>Order Detail</h1>
            <p>Viewing details for order ID: {{ orderId }}</p>
        </div>
    </CustomerLayout>
</template>

<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    orderId: String,
});
</script>
