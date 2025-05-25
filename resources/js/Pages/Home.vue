<template>
    <GuestLayout>
        <div data-test="home-page">
            <HomeHero />
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useLogStore } from '@/stores/log';
import HomeHero from '@/components/HomeHero.vue'

const logStore = useLogStore();
logStore.log('Home page loaded');
</script>
