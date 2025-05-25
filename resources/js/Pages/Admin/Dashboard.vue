<template>
    <AuthenticatedLayout>
        <div data-test="admin-dashboard">
            <h1 data-testid="admin-welcome">Admin Dashboard</h1>
            <p>Welcome to the admin dashboard.</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>
