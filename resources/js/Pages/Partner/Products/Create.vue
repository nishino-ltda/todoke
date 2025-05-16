<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Create Product" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="partner-products-create-title">Create Product</h2>
        </template>

        <div data-test="partner-products-create-content">
            <div>
                <div>
                    <div>
                        Partner Products Create Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
