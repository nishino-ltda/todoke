<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Create Addon" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="create-addon-title">Create Addon</h2>
        </template>

        <div data-test="create-addon-container">
            <div>
                <div>
                    <div>
                        Partner Addons Create Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
