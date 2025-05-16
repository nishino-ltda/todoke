<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Partner Nodes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="partner-nodes-title">Partner Nodes</h2>
        </template>

        <div data-test="partner-nodes-container">
            <div>
                <div>
                    <div>
                        Partner Nodes Index Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
