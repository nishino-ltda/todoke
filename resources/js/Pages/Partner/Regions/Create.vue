<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Create Region" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="create-region-title">Create Region</h2>
        </template>

        <div data-test="create-region-container">
            <div>
                <div>
                    <div>
                        Partner Regions Create Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
