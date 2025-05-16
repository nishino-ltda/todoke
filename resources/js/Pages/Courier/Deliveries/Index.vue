<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Courier Deliveries" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="courier-deliveries-title">Courier Deliveries</h2>
        </template>

        <div data-test="courier-deliveries-container">
            <div>
                <div>
                    <div>
                        Courier Deliveries Index Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
