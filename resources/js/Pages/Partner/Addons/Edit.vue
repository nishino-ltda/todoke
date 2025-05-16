<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    addonId: String,
});
</script>

<template>
    <Head :title="'Edit Addon ' + addonId" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="edit-addon-title">Edit Addon {{ addonId }}</h2>
        </template>

        <div data-test="edit-addon-container">
            <div>
                <div>
                    <div>
                        Partner Addons Edit Page (Placeholder) for Addon ID: {{ addonId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
