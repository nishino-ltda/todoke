<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Partner Addons" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="partner-addons-title">Partner Addons</h2>
        </template>

        <div data-test="partner-addons-container">
            <div>
                <div>
                    <div>
                        Partner Addons Index Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
