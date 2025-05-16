<template>
    <AuthenticatedLayout>
        <div data-test="support-dashboard-page">
            <h1 data-test="support-dashboard-title">Support Dashboard</h1>
            <p data-test="support-dashboard-welcome">Welcome to the support dashboard.</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>
