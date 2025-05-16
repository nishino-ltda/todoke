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
            <h2 data-test="node-details-title">Node Details {{ nodeId }}</h2>
        </template>

        <div data-test="node-details-container">
            <div>
                <div>
                    <div>
                        Partner Nodes Show Page (Placeholder) for Node ID: {{ nodeId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
