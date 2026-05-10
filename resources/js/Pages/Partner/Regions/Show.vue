<template>
    <PartnerLayout>
        <div data-cy="partner-regions-show">
            <h1>Partner Region Details</h1>
            <p>Details for region: {{ regionId }}</p>
        </div>
    </PartnerLayout>
</template>

<script setup>
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    regionId: String,
});
</script>
