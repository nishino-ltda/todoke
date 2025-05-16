<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Courier Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="courier-dashboard-title">Courier Dashboard</h2>
        </template>

        <div data-test="courier-dashboard-container">
            <div>
                <div>
                    <div>
                        Courier Dashboard Index Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
