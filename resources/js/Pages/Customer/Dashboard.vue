<template>
    <CustomerLayout>
        <div data-cy="customer-dashboard" class="dashboard-container">
            <!-- Hero / Welcome -->
            <header class="mb-8">
                <h1 class="text-h3 font-weight-black mb-2" data-testid="user-welcome">
                    Olá, {{ user?.name }}! 😋
                </h1>
                <p class="text-subtitle-1 text-medium-emphasis" data-testid="user-email">
                    {{ $t('dashboard.subtitle', 'O que vamos pedir hoje?') }}
                </p>
            </header>

            <!-- Discovery Map -->
            <CustomerDiscoveryMap />

            <!-- Category Quick Nav -->
            <section class="mb-8">
                <h2 class="text-h5 font-weight-bold mb-4">{{ $t('dashboard.categories', 'Categorias') }}</h2>
                <CategoryQuickNav v-model:selectedCategory="selectedCategory" />
            </section>

            <!-- Product Recommendations -->
            <section class="mb-12">
                <div class="d-flex align-center justify-space-between mb-6">
                    <h2 class="text-h5 font-weight-bold">
                        {{ selectedCategory ? `Melhores de ${selectedCategory}` : 'Recomendados para Você' }}
                    </h2>
                    <v-btn variant="text" color="primary" @click="router.visit('/customer/menu')">
                        Ver tudo <v-icon icon="mdi-chevron-right"></v-icon>
                    </v-btn>
                </div>

                <v-row v-if="loading" class="pa-8">
                    <v-col v-for="i in 4" :key="i" cols="12" sm="6" md="4" lg="3">
                        <v-skeleton-loader type="card" class="rounded-xl"></v-skeleton-loader>
                    </v-col>
                </v-row>

                <v-row v-else-if="filteredProducts.length > 0">
                    <v-col 
                        v-for="product in filteredProducts.slice(0, 8)" 
                        :key="product.id" 
                        cols="12" sm="6" md="4" lg="3"
                    >
                        <ProductCard 
                            :product="product"
                            @product-clicked="handleProductClicked"
                            @add-to-cart="addToCart"
                            class="premium-card"
                        />
                    </v-col>
                </v-row>

                <v-empty-state
                    v-else
                    icon="mdi-magnify-close"
                    title="Nenhum produto encontrado"
                    text="Tente trocar a categoria ou buscar outro termo."
                ></v-empty-state>
            </section>

            <!-- Recent Orders (Mock for now) -->
            <section v-if="recentOrders.length > 0" class="mb-8">
                <h2 class="text-h5 font-weight-bold mb-4">Pedir de novo?</h2>
                <v-row>
                    <v-col v-for="order in recentOrders" :key="order.id" cols="12" md="4">
                        <v-card variant="outlined" class="rounded-xl pa-4">
                            <div class="d-flex align-center">
                                <v-avatar color="primary" class="mr-4">
                                    <v-icon icon="mdi-history" color="white"></v-icon>
                                </v-avatar>
                                <div>
                                    <div class="font-weight-bold">{{ order.partner_name }}</div>
                                    <div class="text-caption text-medium-emphasis">{{ order.date }}</div>
                                </div>
                                <v-spacer></v-spacer>
                                <v-btn 
                                    icon="mdi-refresh" 
                                    variant="text" 
                                    color="primary" 
                                    size="small"
                                    @click="handleReorder(order)"
                                ></v-btn>
                            </div>
                        </v-card>
                    </v-col>
                </v-row>
            </section>
        </div>

        <ProductDetailsModal
          v-if="selectedProduct"
          :product="selectedProduct"
          v-model="showDetails"
          @added="onItemAdded"
          @close="showDetails = false"
        />

        <v-snackbar v-model="showSnackbar" color="success" rounded="pill" elevation="24">
            {{ snackbarText }}
            <template v-slot:actions>
                <v-btn variant="text" @click="showSnackbar = false">Fechar</v-btn>
            </template>
        </v-snackbar>
    </CustomerLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '@/stores/auth';
import { useCartStore } from '@/stores/cart';
import { useRealtime } from '@/composables/useRealtime';
import api from '@/services/api';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import CustomerDiscoveryMap from '@/Components/CustomerDiscoveryMap.vue';
import CategoryQuickNav from '@/Components/CategoryQuickNav.vue';
import ProductCard from '@/Components/ProductCard.vue';
import ProductDetailsModal from '@/Components/ProductDetailsModal.vue';

const authStore = useAuthStore();
const cartStore = useCartStore();
const realtime = useRealtime();
const { user } = storeToRefs(authStore);

const products = ref([]);
const loading = ref(true);
const selectedCategory = ref(null);
const selectedProduct = ref(null);
const showDetails = ref(false);
const recentOrders = ref([]);
const showSnackbar = ref(false);
const snackbarText = ref('');

const fetchProducts = async () => {
    loading.value = true;
    try {
        const response = await api.get('/products');
        products.value = response.data.products || [];
    } catch (err) {
        console.error('Failed to fetch products:', err);
    } finally {
        loading.value = false;
    }
};

const filteredProducts = computed(() => {
    if (!selectedCategory.value) return products.value;
    return products.value.filter(p => p.category === selectedCategory.value);
});

const handleProductClicked = (product) => {
    selectedProduct.value = product;
    showDetails.value = true;
};

const addToCart = (product) => {
    cartStore.addItem(product);
    onItemAdded();
};

const onItemAdded = () => {
    snackbarText.value = 'Item adicionado ao carrinho! 🛒';
    showSnackbar.value = true;
    showDetails.value = false;
};

const handleReorder = (order) => {
    cartStore.reorder(order.items);
    snackbarText.value = `Itens de ${order.partner_name} adicionados ao carrinho!`;
    showSnackbar.value = true;
};

onMounted(() => {
    fetchProducts();
    realtime.setupListeners();
    // Mock recent orders with items for reorder logic
    recentOrders.value = [
        { 
            id: 1, 
            partner_name: 'Pizzaria Bella Itália', 
            date: 'Ontem',
            items: [
                { id: 101, name: 'Pizza Calabresa', price: 75.00, quantity: 1, partner_id: 2 }
            ]
        },
        { 
            id: 2, 
            partner_name: 'Burguer do Porto', 
            date: 'Há 3 dias',
            items: [
                { id: 201, name: 'Combo Smash Bacon', price: 45.00, quantity: 1, partner_id: 3 }
            ]
        },
    ];
});

onUnmounted(() => {
    realtime.leaveChannels();
});
</script>

<style scoped>
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
}

.premium-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 24px !important;
}

.premium-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1) !important;
}
</style>
