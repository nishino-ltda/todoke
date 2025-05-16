<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    productId: String,
});
</script>

<template>
    <Head :title="'Edit Product ' + productId" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="edit-product-title">Edit Product {{ productId }}</h2>
        </template>

        <div data-test="edit-product-container">
            <div>
                <div>
                    <div>
                        Partner Products Edit Page (Placeholder) for Product ID: {{ productId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
