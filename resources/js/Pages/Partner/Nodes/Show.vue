<template>
    <AuthenticatedLayout>
        <div data-cy="partner-nodes-show">
            <h1>Partner Node Details</h1>
            <p>Details for node: {{ nodeId }}</p>
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
