<template>
    <PartnerLayout>
        <div data-cy="partner-products-edit">
            <h1>Edit Product</h1>
            <p>Edit details for product: {{ productId }}</p>
        </div>
    </PartnerLayout>
</template>

<script setup>
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    productId: String,
});
</script>
