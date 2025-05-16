<template>
    <AuthenticatedLayout>
        <div data-test="courier-hybrid-deliveries-show">
            <h1>Courier Hybrid Delivery Details</h1>
            <p>Details for hybrid delivery: {{ hybridDeliveryId }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    hybridDeliveryId: String,
});
</script>
