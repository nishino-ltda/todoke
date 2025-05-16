<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    regionId: String,
});
</script>

<template>
    <Head :title="'Edit Region ' + regionId" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="edit-region-title">Edit Region {{ regionId }}</h2>
        </template>

        <div data-test="edit-region-container">
            <div>
                <div>
                    <div>
                        Partner Regions Edit Page (Placeholder) for Region ID: {{ regionId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
