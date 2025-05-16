<template>
    <AuthenticatedLayout>
        <div>
            <h1>Support Ticket Detail</h1>
            <p>Viewing details for ticket ID: {{ ticketId }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    ticketId: String,
});
</script>
