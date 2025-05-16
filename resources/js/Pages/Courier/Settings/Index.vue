<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Courier Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test-id="courier-settings-title">Courier Settings</h2>
        </template>

        <div data-test-id="courier-settings-content">
            <div>
                <div>
                    <div>
                        Courier Settings Index Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
