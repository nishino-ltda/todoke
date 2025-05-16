<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    deliveryId: String,
});
</script>

<template>
    <Head :title="'Courier Hybrid Delivery ' + deliveryId" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="hybrid-delivery-title">Courier Hybrid Delivery {{ deliveryId }}</h2>
        </template>

        <div data-test="hybrid-delivery-container">
            <div>
                <div>
                    <div>
                        Courier Hybrid Delivery Show Page (Placeholder) for Delivery ID: {{ deliveryId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
