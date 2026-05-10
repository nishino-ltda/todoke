<template>
    <PartnerLayout>
        <div data-cy="partner-addons-edit">
            <h1>Edit Addon</h1>
            <p>Edit details for addon: {{ addonId }}</p>
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
