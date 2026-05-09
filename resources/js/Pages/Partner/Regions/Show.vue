<template>
    <AuthenticatedLayout>
        <div data-cy="partner-regions-show">
            <h1>Partner Region Details</h1>
            <p>Details for region: {{ regionId }}</p>
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
