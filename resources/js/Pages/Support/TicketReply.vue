<template>
    <AuthenticatedLayout>
        <div data-test="support-ticket-reply">
            <h1>Reply to Support Ticket</h1>
            <p>Reply form for ticket: {{ ticketId }}</p>
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
