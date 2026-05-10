<template>
  <v-dialog v-if="product" v-model="showModal" max-width="600" persistent>
    <v-card>
      <v-btn 
        icon
        @click="$emit('close')"
        class="close-button"
        absolute
        right
        top
      >
        <v-icon>mdi-close</v-icon>
      </v-btn>
      
      <v-img
        :src="product.image || '/images/placeholder-food.jpg'"
        :alt="product.name"
        cover
        max-height="300"
        class="ma-4"
      ></v-img>
      
      <v-card-text>
        <v-card-title class="text-h5">{{ product.name }}</v-card-title>
        <v-card-subtitle class="text-h6 font-weight-bold primary--text">
          ${{ product.price.toFixed(2) }}
        </v-card-subtitle>
        <v-card-text class="text-body-1">
          {{ product.description || $t('cart.no_description') }}
        </v-card-text>

        <v-card-text v-if="product.addons && product.addons.length" class="addons-section">
          <h3>{{ $t('cart.addons') }}</h3>
          <div v-for="addon in product.addons" :key="addon.id" class="addon-item">
            <input 
              type="checkbox" 
              :id="'addon-' + addon.id" 
              v-model="selectedAddons" 
              :value="addon.id"
              :data-cy="'addon-checkbox-' + addon.id"
            >
            <label :for="'addon-' + addon.id" :data-cy="'addon-label-' + addon.id">
              {{ addon.name }} (+ ${{ addon.price.toFixed(2) }})
            </label>
          </div>
        </v-card-text>
        
        <v-card-actions class="quantity-controls">
          <v-btn @click="quantity > 1 ? quantity-- : null" icon data-cy="decrease-quantity">
            <v-icon>mdi-minus</v-icon>
          </v-btn>
          <span class="mx-2" data-cy="quantity-display">{{ quantity }}</span>
          <v-btn @click="quantity++" icon data-cy="increase-quantity">
            <v-icon>mdi-plus</v-icon>
          </v-btn>
        </v-card-actions>
        
        <v-card-actions>
          <v-btn
            color="primary"
            @click="addToCart"
            block
            data-cy="add-to-cart"
          >
            {{ $t('cart.add_to_cart') }} (${{ totalPrice.toFixed(2) }})
          </v-btn>
        </v-card-actions>
      </v-card-text>
    </v-card>
  </v-dialog>
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
const showModal = ref(true)

const totalPrice = computed(() => {
  const addonsTotal = selectedAddons.value.reduce((sum, addonId) => {
    const addon = props.product.addons?.find(a => a.id === addonId)
    return sum + (addon?.price || 0)
  }, 0)
  return (props.product.price + addonsTotal) * quantity.value
})

const addToCart = () => {
  const addons = selectedAddons.value.map(addonId => 
    props.product.addons.find(a => a.id === addonId)
  ).filter(Boolean)

  const item = {
    ...props.product,
    selectedAddons: addons
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

.quantity-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 1rem 0;
}
</style>
