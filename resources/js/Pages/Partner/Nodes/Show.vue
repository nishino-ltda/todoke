<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    nodeId: String,
});
</script>

<template>
    <Head :title="'Node Details ' + nodeId" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Node Details {{ nodeId }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        Partner Nodes Show Page (Placeholder) for Node ID: {{ nodeId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
