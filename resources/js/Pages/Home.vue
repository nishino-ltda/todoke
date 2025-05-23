<template>
    <GuestLayout>
        <div data-test="home-page">
            <h1>Welcome to TODOKE</h1>
            <p>Your innovative delivery management platform.</p>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useLogStore } from '@/stores/log';

const logStore = useLogStore();
logStore.log('Home page loaded');
</script>
