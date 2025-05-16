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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Partner Order {{ orderId }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Partner Order Show Page (Placeholder) for Order ID: {{ orderId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
