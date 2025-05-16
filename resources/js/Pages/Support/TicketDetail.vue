<template>
    <AuthenticatedLayout>
        <div data-test="ticket-detail-page">
            <h1 data-test="ticket-detail-title">Support Ticket Detail</h1>
            <p data-test="ticket-detail-id">Viewing details for ticket ID: {{ ticketId }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    ticketId: String,
});
</script>
