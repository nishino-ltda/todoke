<template>
    <AuthenticatedLayout>
        <div data-test="customer-checkout">
            <h1>{{ $t('checkout.title') }}</h1>
            <p>{{ $t('checkout.subtitle') }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>
