<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    addonId: String,
});
</script>

<template>
    <Head :title="'Addon Details ' + addonId" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="addon-details-title">Addon Details {{ addonId }}</h2>
        </template>

        <div data-test="addon-details-container">
            <div>
                <div>
                    <div>
                        Partner Addons Show Page (Placeholder) for Addon ID: {{ addonId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
