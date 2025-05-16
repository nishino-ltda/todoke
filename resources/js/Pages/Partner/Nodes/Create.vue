<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Create Node" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="create-node-title">Create Node</h2>
        </template>

        <div data-test="create-node-container">
            <div>
                <div>
                    <div>
                        Partner Nodes Create Page (Placeholder)
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
