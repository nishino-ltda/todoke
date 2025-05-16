<template>
    <AuthenticatedLayout>
        <div data-test-id="admin-users-container">
            <h1 data-test-id="admin-users-title">Admin Users</h1>
            <p data-test-id="admin-users-description">This is the admin users page.</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>
