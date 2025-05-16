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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Courier Delivery {{ deliveryId }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Courier Delivery Show Page (Placeholder) for Delivery ID: {{ deliveryId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
