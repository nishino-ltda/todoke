<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    productId: String,
});
</script>

<template>
    <Head :title="'Product Details ' + productId" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="product-details-title">Product Details {{ productId }}</h2>
        </template>

        <div data-test="product-details-container">
            <div>
                <div>
                    <div>
                        Partner Products Show Page (Placeholder) for Product ID: {{ productId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
