<template>
  <CustomerLayout>
    <div data-cy="customer-orders-index" class="orders-container">
      <!-- Header -->
      <header class="mb-10">
        <h1 class="text-h3 font-weight-black mb-2">
          {{ $t('orders.title', 'Meus Pedidos') }}
        </h1>
        <p class="text-subtitle-1 text-medium-emphasis">
          Acompanhe suas entregas e veja seu histórico completo.
        </p>
      </header>

      <!-- Tabs -->
      <v-tabs v-model="tab" color="primary" class="mb-8 premium-tabs" align-tabs="start">
        <v-tab value="active" class="text-none font-weight-bold">
          <v-icon start icon="mdi-clock-outline"></v-icon>
          Em Aberto
        </v-tab>
        <v-tab value="history" class="text-none font-weight-bold">
          <v-icon start icon="mdi-history"></v-icon>
          Histórico
        </v-tab>
      </v-tabs>

      <!-- Content -->
      <v-window v-model="tab" class="mt-4">
        <!-- Active Orders -->
        <v-window-item value="active">
          <div v-if="activeOrders.length > 0">
            <v-card 
              v-for="order in activeOrders" 
              :key="order.id" 
              class="order-card-premium mb-6 pa-8"
              elevation="0"
            >
              <v-row align="center">
                <v-col cols="12" sm="2" class="d-flex justify-center">
                  <v-avatar size="80" class="elevation-4 avatar-gradient">
                    <v-icon icon="mdi-moped" color="white" size="40"></v-icon>
                  </v-avatar>
                </v-col>
                
                <v-col cols="12" sm="6">
                  <div class="d-flex align-center flex-wrap gap-2 mb-2">
                    <h3 class="text-h5 font-weight-bold">{{ order.partner_name }}</h3>
                    <v-chip 
                      :color="getStatusColor(order.status)" 
                      size="small" 
                      variant="flat"
                      class="font-weight-black text-uppercase px-3"
                    >
                      {{ $t(`orders.status.${order.status}`, order.status) }}
                    </v-chip>
                  </div>
                  <p class="text-body-1 text-medium-emphasis mb-1">
                    {{ order.items_summary }}
                  </p>
                  <p class="text-h6 font-weight-black color-primary">
                    {{ formatPrice(order.total) }}
                  </p>
                </v-col>

                <v-col cols="12" sm="4" class="text-right">
                  <v-btn
                    color="primary"
                    size="large"
                    rounded="pill"
                    elevation="4"
                    class="px-8 text-none font-weight-bold"
                    @click="viewOrder(order.id)"
                  >
                    Acompanhar
                  </v-btn>
                </v-col>
              </v-row>

              <v-divider class="my-6 opacity-20"></v-divider>
              
              <div class="px-2">
                <div class="d-flex justify-space-between align-center mb-2">
                    <span class="text-caption font-weight-bold text-uppercase opacity-60">Status da Entrega</span>
                    <span class="text-caption font-weight-black color-primary">{{ order.eta }} restantes</span>
                </div>
                <v-progress-linear
                  :model-value="order.progress"
                  color="primary"
                  height="12"
                  rounded
                  striped
                  indeterminate-color="white"
                  class="progress-premium"
                ></v-progress-linear>
              </div>
            </v-card>
          </div>
          <v-empty-state
            v-else
            icon="mdi-package-variant-closed"
            title="Nenhum pedido ativo agora"
            text="Quando você fizer um pedido, ele aparecerá aqui para você acompanhar em tempo real."
            class="mt-12"
          >
            <template v-slot:actions>
              <v-btn color="primary" variant="flat" rounded="pill" @click="router.visit('/customer/dashboard')">
                Fazer meu primeiro pedido
              </v-btn>
            </template>
          </v-empty-state>
        </v-window-item>

        <!-- History -->
        <v-window-item value="history">
          <div v-if="orderHistory.length > 0">
            <v-card 
              v-for="order in orderHistory" 
              :key="order.id" 
              class="history-card-premium mb-4 pa-5"
              variant="outlined"
            >
              <v-row align="center" no-gutters>
                <v-col cols="12" sm="8">
                  <div class="d-flex align-center mb-1">
                    <h4 class="text-h6 font-weight-bold mr-3">{{ order.partner_name }}</h4>
                    <span class="text-caption text-medium-emphasis">{{ order.date }}</span>
                  </div>
                  <p class="text-body-2 text-medium-emphasis mb-0">
                    {{ order.items_summary }} • <span class="font-weight-bold">{{ formatPrice(order.total) }}</span>
                  </p>
                </v-col>
                <v-col cols="12" sm="4" class="text-right mt-4 mt-sm-0">
                  <v-btn
                    variant="text"
                    color="medium-emphasis"
                    class="mr-2 text-none"
                    @click="viewOrder(order.id)"
                  >
                    Detalhes
                  </v-btn>
                  <v-btn
                    color="primary"
                    variant="tonal"
                    rounded="pill"
                    prepend-icon="mdi-refresh"
                    class="text-none font-weight-bold"
                    @click="handleReorder(order)"
                  >
                    Repetir
                  </v-btn>
                </v-col>
              </v-row>
            </v-card>
          </div>
          <v-empty-state
            v-else
            icon="mdi-history"
            title="Histórico vazio"
            text="Você ainda não completou nenhum pedido no Todoke."
          ></v-empty-state>
        </v-window-item>
      </v-window>

      <v-snackbar v-model="showSnackbar" color="success" rounded="pill" elevation="24">
        <div class="d-flex align-center">
            <v-icon icon="mdi-check-circle" start></v-icon>
            {{ snackbarText }}
        </div>
        <template v-slot:actions>
          <v-btn variant="text" @click="showSnackbar = false">Fechar</v-btn>
        </template>
      </v-snackbar>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import CustomerLayout from '@/Layouts/CustomerLayout.vue'
import { useCartStore } from '@/stores/cart'

const cartStore = useCartStore()
const tab = ref('active')
const showSnackbar = ref(false)
const snackbarText = ref('')

// Mock Data (In a real app, this would come from props)
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
  max-width: 1200px;
  margin: 0 auto;
}

.glass-text {
  background: linear-gradient(135deg, var(--v-theme-primary) 0%, #6366f1 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.order-card-premium {
  background: rgba(255, 255, 255, 0.8) !important;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(var(--v-border-color), 0.1);
  border-radius: 28px !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.order-card-premium:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(0,0,0,0.08) !important;
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.history-card-premium {
  border-radius: 20px !important;
  transition: all 0.2s ease;
  border: 1px solid rgba(var(--v-border-color), 0.05);
  background: white !important;
}

.history-card-premium:hover {
  background: rgba(var(--v-theme-primary), 0.02) !important;
  border-color: rgba(var(--v-theme-primary), 0.1);
}

.avatar-gradient {
  background: linear-gradient(135deg, var(--v-theme-primary) 0%, #6366f1 100%) !important;
}

.premium-tabs :deep(.v-tab--selected) {
    background: rgba(var(--v-theme-primary), 0.05);
    border-radius: 12px;
}

.progress-premium :deep(.v-progress-linear__determinate) {
    background: linear-gradient(90deg, var(--v-theme-primary) 0%, #6366f1 100%) !important;
}

.gap-2 {
    gap: 8px;
}
</style>
