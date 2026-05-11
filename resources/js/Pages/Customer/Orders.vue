<template>
  <CustomerLayout>
    <div data-cy="customer-orders" class="orders-container pa-8">
      <header class="mb-8">
        <h1 class="text-h3 font-weight-bold mb-2">{{ $t('orders.title', 'Meus Pedidos') }}</h1>
        <p class="text-subtitle-1 text-medium-emphasis">Acompanhe suas entregas e veja seu histórico.</p>
      </header>

      <v-tabs v-model="tab" color="primary" class="mb-6">
        <v-tab value="active">Em Aberto</v-tab>
        <v-tab value="history">Histórico</v-tab>
      </v-tabs>

      <v-window v-model="tab">
        <v-window-item value="active">
          <div v-if="activeOrders.length > 0">
            <v-card 
              v-for="order in activeOrders" 
              :key="order.id" 
              class="order-card mb-4 rounded-xl pa-4"
              elevation="2"
            >
              <v-row align="center">
                <v-col cols="12" sm="2" class="text-center">
                  <v-avatar color="primary" size="64">
                    <v-icon icon="mdi-truck-delivery" color="white"></v-icon>
                  </v-avatar>
                </v-col>
                <v-col cols="12" sm="6">
                  <div class="d-flex align-center mb-1">
                    <h3 class="text-h6 font-weight-bold mr-2">{{ order.partner_name }}</h3>
                    <v-chip :color="getStatusColor(order.status)" size="x-small" class="font-weight-bold text-uppercase">
                      {{ $t(`orders.status.${order.status}`, order.status) }}
                    </v-chip>
                  </div>
                  <p class="text-body-2 text-medium-emphasis mb-0">
                    {{ order.items_summary }} • {{ formatPrice(order.total) }}
                  </p>
                </v-col>
                <v-col cols="12" sm="4" class="text-right">
                  <v-btn
                    color="primary"
                    variant="tonal"
                    rounded="pill"
                    @click="viewOrder(order.id)"
                    class="mr-2"
                  >
                    Acompanhar
                  </v-btn>
                </v-col>
              </v-row>
              <v-divider class="my-4"></v-divider>
              <div class="d-flex align-center px-2">
                <v-progress-linear
                  :model-value="order.progress"
                  color="primary"
                  height="8"
                  rounded
                ></v-progress-linear>
                <span class="ml-4 text-caption font-weight-bold">{{ order.eta }}</span>
              </div>
            </v-card>
          </div>
          <v-empty-state
            v-else
            icon="mdi-package-variant"
            title="Nenhum pedido ativo"
            text="Seus pedidos em andamento aparecerão aqui."
          ></v-empty-state>
        </v-window-item>

        <v-window-item value="history">
          <div v-if="orderHistory.length > 0">
            <v-card 
              v-for="order in orderHistory" 
              :key="order.id" 
              class="order-card mb-4 rounded-xl pa-4"
              variant="outlined"
            >
              <v-row align="center">
                <v-col cols="12" sm="8">
                  <div class="d-flex align-center mb-1">
                    <h3 class="text-h6 font-weight-bold mr-2">{{ order.partner_name }}</h3>
                    <span class="text-caption text-medium-emphasis">{{ order.date }}</span>
                  </div>
                  <p class="text-body-2 text-medium-emphasis mb-0">
                    {{ order.items_summary }} • {{ formatPrice(order.total) }}
                  </p>
                </v-col>
                <v-col cols="12" sm="4" class="text-right">
                  <v-btn
                    variant="text"
                    color="primary"
                    @click="viewOrder(order.id)"
                  >
                    Detalhes
                  </v-btn>
                  <v-btn
                    color="primary"
                    variant="flat"
                    rounded="pill"
                    prepend-icon="mdi-refresh"
                    @click="handleReorder(order)"
                  >
                    Repetir
                  </v-btn>
                </v-col>
              </v-row>
            </v-card>
          </div>
        </v-window-item>
      </v-window>

      <v-snackbar v-model="showSnackbar" color="success" rounded="pill" elevation="24">
        {{ snackbarText }}
        <template v-slot:actions>
          <v-btn variant="text" @click="showSnackbar = false">Fechar</v-btn>
        </template>
      </v-snackbar>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import CustomerLayout from '@/Layouts/CustomerLayout.vue'
import { useCartStore } from '@/stores/cart'

const cartStore = useCartStore()
const tab = ref('active')
const showSnackbar = ref(false)
const snackbarText = ref('')

// Mock Data
const activeOrders = ref([
  {
    id: 'TK-8821',
    partner_name: 'Pizzaria Bella Itália',
    status: 'preparing',
    total: 89.90,
    items_summary: '1x Pizza Calabresa, 1x Coca-Cola 2L',
    progress: 45,
    eta: '25-35 min',
    items: [
        { id: 101, name: 'Pizza Calabresa', price: 75.00, quantity: 1, partner_id: 2 },
        { id: 102, name: 'Coca-Cola 2L', price: 14.90, quantity: 1, partner_id: 2 }
    ]
  }
])

const orderHistory = ref([
  {
    id: 'TK-7712',
    partner_name: 'Burguer do Porto',
    status: 'completed',
    total: 45.00,
    items_summary: '1x Combo Smash Bacon',
    date: 'Ontem, 20:15',
    items: [
        { id: 201, name: 'Combo Smash Bacon', price: 45.00, quantity: 1, partner_id: 3 }
    ]
  },
  {
    id: 'TK-6605',
    partner_name: 'Sushi Garden',
    status: 'completed',
    total: 120.00,
    items_summary: 'Combo 40 peças Mix',
    date: '3 dias atrás',
    items: [
        { id: 301, name: 'Combo 40 peças Mix', price: 120.00, quantity: 1, partner_id: 4 }
    ]
  }
])

const formatPrice = (value) => {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
}

const getStatusColor = (status) => {
  const colors = {
    pending: 'warning',
    preparing: 'info',
    ready: 'success',
    completed: 'success',
    cancelled: 'error'
  }
  return colors[status] || 'grey'
}

const viewOrder = (id) => {
  router.visit(`/customer/orders/${id}`)
}

const handleReorder = (order) => {
    cartStore.reorder(order.items)
    snackbarText.value = `Itens de ${order.partner_name} adicionados ao carrinho!`
    showSnackbar.value = true
}
</script>

<style scoped>
.orders-container {
  max-width: 900px;
  margin: 0 auto;
}

.order-card {
  transition: transform 0.2s ease;
}

.order-card:hover {
  transform: scale(1.01);
}
</style>
