<template>
  <v-card 
    class="product-card rounded-xl overflow-hidden elevation-2"
    @click="$emit('product-clicked', product)"
    data-cy="product-card"
    hover
  >
    <div class="image-container">
      <v-img
        :src="resolveImageUrl(product.image)"
        height="180px"
        cover
        data-cy="product-image"
        class="product-image"
      >
        <template v-slot:placeholder>
          <v-row class="fill-height ma-0" align="center" justify="center">
            <v-progress-circular indeterminate color="grey-lighten-5"></v-progress-circular>
          </v-row>
        </template>
        
        <v-chip
          v-if="product.category"
          class="category-badge ma-2 px-3 font-weight-bold"
          size="x-small"
          color="white"
          variant="elevated"
          elevation="4"
        >
          {{ product.category }}
        </v-chip>
      </v-img>
    </div>

    <v-card-text class="pa-4">
      <div class="d-flex justify-space-between align-center mb-1">
        <h3 class="text-h6 font-weight-bold product-name text-truncate" data-cy="product-name">
          {{ product.name }}
        </h3>
      </div>
      
      <p class="text-caption text-medium-emphasis mb-4 description-text" data-cy="product-description">
        {{ truncateDescription }}
      </p>

      <div class="d-flex justify-space-between align-center mt-auto">
        <div class="price-container">
          <span class="text-h6 font-weight-black text-primary" data-cy="product-price">
            {{ formatPrice(product.price) }}
          </span>
        </div>
        
        <v-btn 
          @click.stop="$emit('add-to-cart', product)"
          color="primary"
          variant="elevated"
          icon="mdi-plus"
          size="small"
          elevation="4"
          data-cy="add-to-cart-button"
          class="add-btn"
        ></v-btn>
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  product: {
    type: Object,
    required: true,
    default: () => ({})
  }
})

const emit = defineEmits(['product-clicked', 'add-to-cart'])

const resolveImageUrl = (path) => {
  if (!path) return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
  if (path.startsWith('http')) return path
  return `/storage/${path}`
}

const formatPrice = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0)
}

const truncateDescription = computed(() => {
  if (!props.product.description) return ''
  const desc = props.product.description
  return desc.length >= 70
    ? `${desc.substring(0, 67)}...`
    : desc
})
</script>

<style scoped>
.product-card {
  transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
  display: flex;
  flex-direction: column;
  height: 100%;
  border: none !important;
}

.product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.12) !important;
}

.image-container {
  overflow: hidden;
  border-radius: 20px 20px 0 0;
}

.product-image {
  transition: transform 0.6s ease;
}

.product-card:hover .product-image {
  transform: scale(1.1);
}

.category-badge {
  position: absolute;
  top: 0;
  right: 0;
  color: #1976D2 !important;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.product-name {
  color: #2c3e50;
  line-height: 1.2;
}

.description-text {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  height: 32px;
}

.add-btn {
    border-radius: 12px !important;
}

.price-container {
    background: rgba(25, 118, 210, 0.05);
    padding: 2px 8px;
    border-radius: 8px;
}
</style>
