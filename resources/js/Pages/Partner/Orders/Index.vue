<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Partner Orders" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="orders-header">Partner Orders</h2>
        </template>

        <div data-test="orders-container">
            <div>
                <div>
                    <div data-test="orders-content">
                        Partner Orders Index Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
