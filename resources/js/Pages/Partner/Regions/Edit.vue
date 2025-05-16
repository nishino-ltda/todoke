<template>
    <AuthenticatedLayout>
        <div data-test="partner-regions-edit">
            <h1>Edit Region</h1>
            <p>Edit details for region: {{ regionId }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    regionId: String,
});
</script>
