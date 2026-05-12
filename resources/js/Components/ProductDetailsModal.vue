<template>
  <v-dialog v-if="product" v-model="showModal" max-width="550" transition="dialog-bottom-transition">
    <v-card class="product-details-card rounded-xl overflow-hidden">
      <div class="image-wrapper">
        <v-img
          :src="resolveImageUrl(product.image)"
          :alt="product.name"
          cover
          height="300"
        >
          <div class="d-flex justify-end pa-4">
             <v-btn 
                icon="mdi-close"
                @click="$emit('close')"
                variant="elevated"
                color="white"
                size="small"
                elevation="4"
                class="close-btn"
              ></v-btn>
          </div>
        </v-img>
      </div>
      
      <v-card-text class="pa-8">
        <div class="d-flex justify-space-between align-start mb-2">
            <div>
                <h2 class="text-h4 font-weight-bold mb-1">{{ product.name }}</h2>
                <v-chip size="small" color="primary" variant="tonal" class="font-weight-bold text-uppercase">
                    {{ product.category }}
                </v-chip>
            </div>
            <div class="text-h5 font-weight-black text-primary">
                {{ formatPrice(product.price) }}
            </div>
        </div>

        <p class="text-body-1 text-medium-emphasis mb-4">
          {{ product.description || $t('cart.no_description', 'Delicioso prato preparado com ingredientes frescos.') }}
        </p>

        <v-btn
          v-if="partnerSlug"
          variant="tonal"
          color="primary"
          size="small"
          class="mb-6"
          prepend-icon="mdi-store"
          @click="goToStore"
          data-cy="view-store-btn"
        >
          Ver Loja
        </v-btn>

        <div v-if="product.addons && product.addons.length" class="addons-section mb-6">
          <h3 class="text-h6 font-weight-bold mb-4 d-flex align-center">
            <v-icon icon="mdi-plus-circle-outline" class="mr-2" color="primary"></v-icon>
            {{ $t('cart.addons', 'Adicionais') }}
          </h3>
          <v-list class="pa-0 bg-transparent">
            <v-list-item
              v-for="addon in product.addons"
              :key="addon.id"
              class="addon-list-item px-0 mb-2"
            >
              <template v-slot:prepend>
                <v-checkbox
                  v-model="selectedAddonIds"
                  :value="addon.id"
                  color="primary"
                  hide-details
                  density="compact"
                  :data-cy="'addon-checkbox-' + addon.id"
                ></v-checkbox>
              </template>
              <v-list-item-title class="font-weight-medium">{{ addon.name }}</v-list-item-title>
              <template v-slot:append>
                <span class="text-body-2 font-weight-bold text-primary">+ {{ formatPrice(addon.price) }}</span>
              </template>
            </v-list-item>
          </v-list>
        </div>

        <div class="d-flex align-center ga-2">
          <div class="d-flex align-center bg-grey-lighten-4 rounded-pill flex-shrink-0">
            <v-btn @click="quantity > 1 ? quantity-- : null" icon="mdi-minus" variant="text" size="small" data-cy="decrease-quantity"></v-btn>
            <span class="mx-4 font-weight-black text-h6" data-cy="quantity-display">{{ quantity }}</span>
            <v-btn @click="quantity++" icon="mdi-plus" variant="text" size="small" data-cy="increase-quantity"></v-btn>
          </div>
          <v-btn
            color="primary"
            @click="addToCart"
            block
            size="x-large"
            rounded="pill"
            elevation="8"
            class="font-weight-bold add-to-cart-btn flex-grow-1"
            data-cy="add-to-cart"
            :loading="adding"
          >
            <v-icon icon="mdi-cart-plus" class="mr-2"></v-icon>
            {{ $t('cart.add_to_cart', 'Adicionar ao Carrinho') }}
            <v-spacer></v-spacer>
            {{ formatPrice(totalPrice) }}
          </v-btn>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'added'])

const cart = useCartStore()
const quantity = ref(1)
const selectedAddonIds = ref([])
const showModal = ref(true)
const adding = ref(false)

const partnerSlug = computed(() => props.product?.partner_slug || null)

const resolveImageUrl = (path) => {
  if (!path) return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
  if (path.startsWith('http')) return path
  return `/storage/${path}`
}

const formatPrice = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0)
}

const totalPrice = computed(() => {
  const addonsTotal = selectedAddonIds.value.reduce((sum, addonId) => {
    const addon = props.product.addons?.find(a => a.id === addonId)
    return sum + (addon?.price || 0)
  }, 0)
  return (props.product.price + addonsTotal) * quantity.value
})

const addToCart = () => {
  adding.value = true
  const addons = selectedAddonIds.value.map(addonId => 
    props.product.addons.find(a => a.id === addonId)
  ).filter(Boolean)

  const item = {
    ...props.product,
    selectedAddons: addons,
    quantity: quantity.value
  }
  
  cart.addItem(item)
  
  setTimeout(() => {
    adding.value = false
    emit('added')
    emit('close')
  }, 500)
}

const goToStore = () => {
  emit('close')
  router.visit(`/store/${partnerSlug.value}`)
}
</script>

<style scoped>
.product-details-card {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
}

.image-wrapper {
  position: relative;
}

.close-btn {
  border-radius: 12px !important;
  opacity: 0.9;
}

.close-btn:hover {
  opacity: 1;
}

.addon-list-item {
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.add-to-cart-btn {
    text-transform: none;
    letter-spacing: 0;
    transition: all 0.3s ease;
}

.add-to-cart-btn:hover {
    transform: scale(1.02);
}
</style>
