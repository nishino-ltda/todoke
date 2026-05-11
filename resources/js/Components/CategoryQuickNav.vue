<template>
  <div class="category-quick-nav py-4" data-cy="category-quick-nav">
    <div class="d-flex overflow-x-auto pb-2 hide-scrollbar">
      <v-chip
        v-for="category in categories"
        :key="category.id"
        :value="category.name"
        :color="selectedCategory === category.name ? 'primary' : undefined"
        :variant="selectedCategory === category.name ? 'elevated' : 'tonal'"
        class="mr-3 px-6 py-4 font-weight-bold"
        @click="selectCategory(category.name)"
        hover
        data-cy="category-chip"
      >
        <v-icon :icon="category.icon" start size="small"></v-icon>
        {{ category.name }}
      </v-chip>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  selectedCategory: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['update:selectedCategory'])

const categories = [
  { id: 1, name: 'Tudo', icon: 'mdi-apps' },
  { id: 2, name: 'Pizza', icon: 'mdi-pizza' },
  { id: 3, name: 'Burger', icon: 'mdi-hamburger' },
  { id: 4, name: 'Açaí', icon: 'mdi-ice-cream' },
  { id: 5, name: 'Japonesa', icon: 'mdi-fish' },
  { id: 6, name: 'Brasileira', icon: 'mdi-food-variant' },
  { id: 7, name: 'Saudável', icon: 'mdi-leaf' },
  { id: 8, name: 'Bebidas', icon: 'mdi-glass-wine' },
]

const selectCategory = (name) => {
  emit('update:selectedCategory', name === 'Tudo' ? null : name)
}
</script>

<style scoped>
.category-quick-nav {
  position: sticky;
  top: 0;
  z-index: 5;
  background: transparent;
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}

.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
