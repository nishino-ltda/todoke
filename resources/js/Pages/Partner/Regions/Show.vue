<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    regionId: String,
});
</script>

<template>
    <Head :title="'Region Details ' + regionId" />

    <AuthenticatedLayout>
        <template #header>
            <h2 data-test="region-details-title">Region Details {{ regionId }}</h2>
        </template>

        <div data-test="region-details-container">
            <div>
                <div>
                    <div>
                        Partner Regions Show Page (Placeholder) for Region ID: {{ regionId }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
