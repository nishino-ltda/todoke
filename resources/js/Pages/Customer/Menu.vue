<template>
    <AuthenticatedLayout>
        <div data-cy="customer-menu" class="pa-6">
            <h1 class="text-h4 mb-2">{{ $t('menu.title') }}</h1>
            <p class="text-subtitle-1 text-grey mb-6">{{ $t('menu.subtitle') }}</p>
            
            <ProductList 
                :products="products" 
                @product-clicked="handleProductClicked"
            />

            <ProductDetailsModal 
                v-if="selectedProduct"
                :product="selectedProduct"
                v-model="showDetails"
                @add-to-cart="addToCart"
            />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ProductList from '@/Components/ProductList.vue'
import ProductDetailsModal from '@/Components/ProductDetailsModal.vue'
import { useCartStore } from '@/stores/cart'

const products = ref([])
const selectedProduct = ref(null)
const showDetails = ref(false)
const cartStore = useCartStore()

const handleProductClicked = (product) => {
    selectedProduct.value = product
    showDetails.value = true
}

const addToCart = (product) => {
    cartStore.addItem(product)
    showDetails.value = false
}

onMounted(async () => {
    // In a real app, fetch from API
    // For now, mock some data or use a service
    products.value = [
        { id: 1, name: 'Margherita Pizza', price: 12.99, description: 'Classic pizza', category: 'Pizza' },
        { id: 2, name: 'Caesar Salad', price: 8.99, description: 'Fresh salad', category: 'Salad' },
        { id: 3, name: 'Pepperoni Pizza', price: 14.99, description: 'Spicy pizza', category: 'Pizza' }
    ]
})
</script>

