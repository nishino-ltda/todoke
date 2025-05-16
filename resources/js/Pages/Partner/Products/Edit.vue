<template>
    <AuthenticatedLayout>
        <div data-test="partner-products-edit">
            <h1>Edit Product</h1>
            <p>Edit details for product: {{ productId }}</p>
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
