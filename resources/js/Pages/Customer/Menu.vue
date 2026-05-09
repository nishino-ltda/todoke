<template>
    <AuthenticatedLayout>
        <div data-test="customer-menu">
            <h1>{{ $t('menu.title') }}</h1>
            <p>{{ $t('menu.subtitle') }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>
