<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Courier Hybrid Deliveries" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="hybrid-deliveries-title">Courier Hybrid Deliveries</h2>
        </template>

        <div data-test="hybrid-deliveries-container">
            <div>
                <div>
                    <div>
                        Courier Hybrid Deliveries Index Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
