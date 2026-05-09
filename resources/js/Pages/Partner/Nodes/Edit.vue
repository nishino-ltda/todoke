<template>
    <AuthenticatedLayout>
        <div data-cy="partner-nodes-edit">
            <h1>Edit Node</h1>
            <p>Edit details for node: {{ nodeId }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    nodeId: String,
});
</script>
