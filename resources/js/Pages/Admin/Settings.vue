<template>
    <AuthenticatedLayout>
        <div data-test="admin-settings-page">
            <h1 data-test="admin-settings-title">Admin Settings</h1>
            <p data-test="admin-settings-description">This is the admin settings page.</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>
