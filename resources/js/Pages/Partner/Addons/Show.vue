<template>
    <PartnerLayout>
        <div data-cy="partner-addons-show">
            <h1>Partner Addon Details</h1>
            <p>Details for addon: {{ addonId }}</p>
        </div>
    </PartnerLayout>
</template>

<script setup>
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    addonId: String,
});
</script>
