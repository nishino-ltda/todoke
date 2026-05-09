<template>
  <v-card class="app-data-table-card" elevation="1">
    <v-card-title v-if="title || showSearch" class="d-flex align-center py-4 px-6">
      <span v-if="title" class="text-h6" data-cy="table-title">{{ title }}</span>
      <v-spacer></v-spacer>
      <v-text-field
        v-if="showSearch"
        v-model="search"
        prepend-inner-icon="mdi-magnify"
        label="Search"
        variant="outlined"
        density="compact"
        hide-details
        class="search-field"
        data-cy="table-search"
      ></v-text-field>
    </v-card-title>

    <v-data-table
      :headers="headers"
      :items="items"
      :search="search"
      :loading="loading"
      :items-per-page="itemsPerPage"
      class="app-data-table"
      data-cy="data-table"
    >
      <template v-for="(_, slotName) in $slots" #[slotName]="slotProps">
        <slot :name="slotName" v-bind="slotProps"></slot>
      </template>

      <template #loading>
        <v-skeleton-loader type="table-row-divider@5"></v-skeleton-loader>
      </template>

      <template #no-data>
        <div class="py-8 text-center text-grey">
          <v-icon size="48" icon="mdi-database-off" class="mb-2"></v-icon>
          <p>No data available</p>
        </div>
      </template>
    </v-data-table>
  </v-card>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  headers: {
    type: Array,
    required: true
  },
  items: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  itemsPerPage: {
    type: Number,
    default: 10
  },
  showSearch: {
    type: Boolean,
    default: true
  }
});

const search = ref('');
</script>

<style scoped>
.app-data-table-card {
  border-radius: 12px;
  overflow: hidden;
  border: 1px solid #f0f0f0;
}

.search-field {
  max-width: 300px;
}

.app-data-table :deep(.v-data-table-header) {
  background-color: #f8f9fa;
}

.app-data-table :deep(.v-data-table-header th) {
  font-weight: 600 !important;
  color: #495057 !important;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
}

.app-data-table :deep(.v-data-table__tr:hover) {
  background-color: #f1f3f5 !important;
}

.app-data-table :deep(.v-pagination__item--active) {
  background-color: var(--v-primary-base) !important;
}
</style>
