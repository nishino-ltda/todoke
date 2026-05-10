<template>
    <PartnerLayout>
        <div data-cy="partner-products-show">
            <h1>Partner Product Details</h1>
            <p>Details for product: {{ productId }}</p>
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
