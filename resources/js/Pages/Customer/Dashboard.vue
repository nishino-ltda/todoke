<template>
    <AuthenticatedLayout>
        <div data-cy="customer-dashboard">
            <h1 data-testid="user-welcome">Customer Dashboard</h1>
            <p data-testid="user-email">Welcome to the customer dashboard.</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>
