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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Product {{ productId }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Partner Products Edit Page (Placeholder) for Product ID: {{ productId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
