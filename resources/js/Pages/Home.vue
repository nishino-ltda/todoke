<template>
    <GuestLayout>
        <div data-test="home-page" class="home-page">
            <HomeHero />
            <HomeFeatures />
            <HomeCTA />
        </div>
    </GuestLayout>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useLogStore } from '@/stores/log';
import HomeHero from '@/Components/HomeHero.vue';
import HomeFeatures from '@/Components/HomeFeatures.vue';
import HomeCTA from '@/Components/HomeCTA.vue';

const { t } = useI18n();
const logStore = useLogStore();
logStore.log('Home page loaded');
</script>

<style scoped>
.home-page {
    overflow-x: hidden;
}
</style>
