<template>
    <AuthenticatedLayout>
        <div data-cy="partner-products-show">
            <h1>Partner Product Details</h1>
            <p>Details for product: {{ productId }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    productId: String,
});
</script>
