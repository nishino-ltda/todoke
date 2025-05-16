<template>
    <AuthenticatedLayout>
        <div data-test="support-ticket-detail">
            <h1>Support Ticket Details</h1>
            <p>Details for ticket: {{ ticketId }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    ticketId: String,
});
</script>
