<template>
    <AuthenticatedLayout>
        <div>
            <h1>Order Detail</h1>
            <p>Viewing details for order ID: {{ orderId }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    orderId: String,
});
</script>
