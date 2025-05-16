<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Partner Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="partner-settings-title">Partner Settings</h2>
        </template>

        <div data-test="partner-settings-container">
            <div>
                <div>
                    <div>
                        Partner Settings Index Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
