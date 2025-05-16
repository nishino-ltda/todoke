<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Partner Products" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="partner-products-title">Partner Products</h2>
        </template>

        <div data-test="partner-products-content">
            <div>
                <div>
                    <div>
                        Partner Products Index Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
