<template>
  <div v-if="product" class="product-details-modal">
    <div class="modal-content">
      <button class="close-button" @click="$emit('close')">×</button>
      
      <div class="product-image">
        <img :src="product.image || '/images/placeholder-food.jpg'" :alt="product.name">
      </div>
      
      <div class="product-info">
        <h2>{{ product.name }}</h2>
        <p class="price">${{ product.price.toFixed(2) }}</p>
        <p class="description">{{ product.description || 'No description available' }}</p>

        <div v-if="product.addons && product.addons.length" class="addons-section">
          <h3>Addons</h3>
          <div v-for="addon in product.addons" :key="addon.id" class="addon-item">
            <input 
              type="checkbox" 
              :id="'addon-' + addon.id" 
              v-model="selectedAddons" 
              :value="addon.id"
            >
            <label :for="'addon-' + addon.id">
              {{ addon.name }} (+ \${{ addon.price.toFixed(2) }})
            </label>
          </div>
        </div>
        
        <div class="quantity-controls">
          <button @click="quantity > 1 ? quantity-- : null">-</button>
          <span>{{ quantity }}</span>
          <button @click="quantity++">+</button>
        </div>
        
        <button class="add-to-cart" @click="addToCart">
          Add to Cart (\${{ totalPrice.toFixed(2) }})
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useCartStore } from '../stores/cart'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close'])

const cart = useCartStore()
const quantity = ref(1)
const selectedAddons = ref([])

const totalPrice = computed(() => {
  const addonsTotal = selectedAddons.value.reduce((sum, addonId) => {
    const addon = props.product.addons?.find(a => a.id === addonId)
    return sum + (addon?.price || 0)
  }, 0)
  return (props.product.price + addonsTotal) * quantity.value
})

const addToCart = () => {
  const item = {
    ...props.product,
    selectedAddons: [...selectedAddons.value]
  }
  for (let i = 0; i < quantity.value; i++) {
    cart.addItem(item)
  }
  emit('close')
}
</script>

<style scoped>
.addons-section {
  margin: 1rem 0;
  border-top: 1px solid #eee;
  padding-top: 1rem;
}

.addons-section h3 {
  margin-bottom: 0.5rem;
  font-size: 1rem;
}

.addon-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0.5rem 0;
}

.addon-item input[type="checkbox"] {
  margin-right: 0.5rem;
}
.product-details-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  max-width: 600px;
  width: 90%;
  position: relative;
}

.close-button {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}

.product-image img {
  width: 100%;
  max-height: 300px;
  object-fit: cover;
  border-radius: 4px;
}

.product-info {
  margin-top: 1rem;
}

.price {
  font-weight: bold;
  font-size: 1.2rem;
  color: #2c3e50;
}

.description {
  margin: 1rem 0;
  color: #666;
}

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 1rem 0;
}

.quantity-controls button {
  width: 2rem;
  height: 2rem;
  border: 1px solid #ddd;
  background: none;
  font-size: 1rem;
  cursor: pointer;
}

.add-to-cart {
  background-color: #42b983;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  font-weight: bold;
  cursor: pointer;
  width: 100%;
}

.add-to-cart:hover {
  background-color: #3aa876;
}
</style>
