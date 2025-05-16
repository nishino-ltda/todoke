<template>
    <GuestLayout>
        <div>
            <h1>Partner Menu</h1>
            <p>Viewing menu for partner ID: {{ partnerId }}</p>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    partnerId: String,
});
</script>
