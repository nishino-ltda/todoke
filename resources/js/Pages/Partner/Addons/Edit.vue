<template>
    <AuthenticatedLayout>
        <div data-cy="partner-addons-edit">
            <h1>Edit Addon</h1>
            <p>Edit details for addon: {{ addonId }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    addonId: String,
});
</script>
