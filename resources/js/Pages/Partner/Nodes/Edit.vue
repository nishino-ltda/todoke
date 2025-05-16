<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    nodeId: String,
});
</script>

<template>
    <Head :title="'Edit Node ' + nodeId" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="edit-node-title">Edit Node {{ nodeId }}</h2>
        </template>

        <div data-test="edit-node-container">
            <div>
                <div>
                    <div>
                        Partner Nodes Edit Page (Placeholder) for Node ID: {{ nodeId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
