<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    orderId: String,
});
</script>

<template>
    <Head :title="'Partner Order ' + orderId" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="order-detail-header" :data-test-order-id="orderId">Partner Order {{ orderId }}</h2>
        </template>

        <div data-test="order-detail-container">
            <div>
                <div>
                    <div data-test="order-detail-content" :data-test-order-id="orderId">
                        Partner Order Show Page (Placeholder) for Order ID: {{ orderId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
