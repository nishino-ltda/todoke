<template>
    <AuthenticatedLayout>
        <div data-test="partner-addons-show">
            <h1>Partner Addon Details</h1>
            <p>Details for addon: {{ addonId }}</p>
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
