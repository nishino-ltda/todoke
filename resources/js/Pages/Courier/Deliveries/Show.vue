<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    deliveryId: String,
});
</script>

<template>
    <Head :title="'Courier Delivery ' + deliveryId" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="courier-delivery-title">Courier Delivery {{ deliveryId }}</h2>
        </template>

        <div data-test="courier-delivery-container">
            <div>
                <div>
                    <div>
                        Courier Delivery Show Page (Placeholder) for Delivery ID: {{ deliveryId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
