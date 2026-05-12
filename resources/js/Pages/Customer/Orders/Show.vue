<template>
  <CustomerLayout>
    <div data-cy="customer-order-show" class="order-detail-container">
      <!-- Top Navigation -->
      <nav class="mb-6">
        <v-btn
          variant="text"
          prepend-icon="mdi-arrow-left"
          class="text-none font-weight-bold px-0"
          @click="router.visit('/customer/orders')"
        >
          Voltar para meus pedidos
        </v-btn>
      </nav>

      <v-row>
        <!-- Main Content -->
        <v-col cols="12" md="8">
          <!-- Order Header -->
          <header class="mb-8 d-flex align-center justify-space-between flex-wrap gap-4">
            <div>
                <div class="d-flex align-center mb-2">
                    <h1 class="text-h3 font-weight-black mr-4">Pedido #{{ orderId }}</h1>
                    <v-chip 
                        :color="getStatusColor(order.status)" 
                        variant="flat" 
                        class="font-weight-black text-uppercase"
                    >
                        {{ $t(`orders.status.${order.status}`, order.status) }}
                    </v-chip>
                </div>
                <p class="text-subtitle-1 text-medium-emphasis">
                    Realizado em {{ order.created_at }} • {{ order.items_count }} itens
                </p>
            </div>
            <v-btn
              color="primary"
              variant="flat"
              rounded="pill"
              prepend-icon="mdi-refresh"
              size="large"
              class="text-none font-weight-bold px-6"
              @click="handleReorder"
            >
              Pedir Novamente
            </v-btn>
          </header>

          <!-- Status Tracker Card -->
          <v-card class="premium-card mb-8 pa-8" elevation="0">
            <h2 class="text-h5 font-weight-bold mb-8">Status do Pedido</h2>
            
            <div class="tracking-timeline">
                <div 
                    v-for="(step, index) in timelineSteps" 
                    :key="index"
                    :class="['timeline-item', { 'active': step.completed, 'current': step.current }]"
                >
                    <div class="timeline-dot">
                        <v-icon :icon="step.icon" size="20" color="white"></v-icon>
                    </div>
                    <div class="timeline-content">
                        <div class="text-h6 font-weight-bold">{{ step.title }}</div>
                        <div class="text-body-2 text-medium-emphasis">{{ step.description }}</div>
                        <div v-if="step.time" class="text-caption font-weight-black mt-1">{{ step.time }}</div>
                    </div>
                </div>
            </div>
          </v-card>

          <!-- Items Card -->
          <v-card class="premium-card mb-8 pa-8" elevation="0">
            <h2 class="text-h5 font-weight-bold mb-6">Itens do Pedido</h2>
            <v-list class="pa-0 bg-transparent">
                <v-list-item v-for="item in order.items" :key="item.id" class="px-0 py-4 border-bottom">
                    <template v-slot:prepend>
                        <v-avatar color="grey-lighten-4" rounded="lg" size="64" class="mr-4">
                            <v-img :src="item.image || '/images/placeholder-food.jpg'" cover></v-img>
                        </v-avatar>
                    </template>
                    <v-list-item-title class="text-h6 font-weight-bold">{{ item.name }}</v-list-item-title>
                    <v-list-item-subtitle class="text-body-2 text-medium-emphasis">{{ item.description }}</v-list-item-subtitle>
                    <template v-slot:append>
                        <div class="text-right">
                            <div class="font-weight-black text-body-1">{{ formatPrice(item.price * item.quantity) }}</div>
                            <div class="text-caption opacity-60">{{ item.quantity }}x {{ formatPrice(item.price) }}</div>
                        </div>
                    </template>
                </v-list-item>
            </v-list>
          </v-card>
        </v-col>

        <!-- Sidebar -->
        <v-col cols="12" md="4">
          <!-- Summary Card -->
          <v-card class="premium-card mb-6 pa-8 summary-card" elevation="0">
            <h2 class="text-h6 font-weight-bold mb-4">Resumo do Pagamento</h2>
            <div class="d-flex justify-space-between mb-2">
                <span class="text-body-1">Subtotal</span>
                <span class="text-body-1 font-weight-medium">{{ formatPrice(order.subtotal) }}</span>
            </div>
            <div class="d-flex justify-space-between mb-2">
                <span class="text-body-1">Taxa de Entrega</span>
                <span class="text-body-1 font-weight-medium">{{ formatPrice(order.delivery_fee) }}</span>
            </div>
            <v-divider class="my-4"></v-divider>
            <div class="d-flex justify-space-between align-center mb-4">
                <span class="text-h6 font-weight-bold">Total</span>
                <span class="text-h5 font-weight-black color-primary">{{ formatPrice(order.total) }}</span>
            </div>
            <v-chip variant="tonal" :color="order.payment_method === 'cash' ? 'info' : 'success'" block class="justify-center font-weight-bold">
                <v-icon start :icon="order.payment_method === 'cash' ? 'mdi-cash' : 'mdi-credit-card-outline'"></v-icon>
                {{ $t('components.payment.' + order.payment_method, order.payment_method) }}
                <span v-if="order.payment_method === 'cash' && order.change_for > 0" class="ml-1">
                  ({{ $t('components.payment.change_for') }}: {{ formatPrice(order.change_for) }})
                </span>
            </v-chip>
          </v-card>

          <!-- Delivery Address Card -->
          <v-card class="premium-card mb-6 pa-8" elevation="0">
            <h2 class="text-h6 font-weight-bold mb-4">Endereço de Entrega</h2>
            <div class="d-flex">
                <v-icon icon="mdi-map-marker" color="primary" class="mr-3 mt-1"></v-icon>
                <div>
                    <div class="font-weight-bold">{{ order.address.street }}, {{ order.address.number }}</div>
                    <div class="text-body-2 text-medium-emphasis">{{ order.address.neighborhood }} - {{ order.address.city }}</div>
                    <div v-if="order.address.complement" class="text-caption mt-1">Ref: {{ order.address.complement }}</div>
                </div>
            </div>
          </v-card>

          <!-- Help / Support -->
          <v-btn
            variant="outlined"
            block
            rounded="pill"
            prepend-icon="mdi-help-circle-outline"
            class="text-none font-weight-bold mb-4"
            color="primary"
            @click="router.visit('/support')"
          >
            Preciso de ajuda com este pedido
          </v-btn>
        </v-col>
      </v-row>

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
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import CustomerLayout from '@/Layouts/CustomerLayout.vue'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  orderId: String,
  orderData: Object,
})

const cartStore = useCartStore()
const showSnackbar = ref(false)
const snackbarText = ref('')

// Map Backend Order Data
const order = computed(() => {
  const data = props.orderData || {}
  const delivery = data.delivery || {}
  
  return {
    id: data.id,
    status: data.status,
    created_at: data.created_at_formatted || new Date(data.created_at).toLocaleString('pt-BR'),
    items_count: data.items?.length || 0,
    subtotal: parseFloat(data.total_value) - (parseFloat(delivery.value) || 0),
    delivery_fee: parseFloat(delivery.value) || 0,
    total: parseFloat(data.total_value),
    address: {
        street: delivery.destination?.address?.split(',')[0] || delivery.destination?.address || 'N/A',
        number: delivery.destination?.address?.split(',')[1] || '',
        neighborhood: delivery.destination?.neighborhood || '',
        city: delivery.destination?.city || '',
        complement: delivery.destination?.complement || ''
    },
    full_address: delivery.destination?.address || 'N/A',
    payment_method: delivery.payment_method || 'credit_card',
    change_for: parseFloat(delivery.change_for) || 0,
    items: (data.items || []).map(item => ({
        id: item.id,
        name: item.product?.name || 'Produto',
        price: parseFloat(item.unit_price),
        quantity: item.quantity,
        description: item.product?.description || '',
        image: item.product?.image || null
    }))
  }
})

const timelineSteps = computed(() => {
    const s = order.value.status
    return [
        { title: 'Pedido Recebido', description: 'O restaurante já está com seu pedido.', icon: 'mdi-check', completed: true },
        { title: 'Em Preparação', description: 'Seu pedido está sendo preparado.', icon: 'mdi-pot-steam', completed: ['preparing', 'ready', 'completed'].includes(s), current: s === 'preparing' },
        { title: 'Saiu para Entrega', description: 'O entregador está a caminho.', icon: 'mdi-moped', completed: ['ready', 'completed'].includes(s), current: s === 'ready' },
        { title: 'Entregue', description: 'Bom apetite!', icon: 'mdi-home-heart', completed: s === 'completed', current: s === 'completed' }
    ]
})

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

const handleReorder = () => {
    cartStore.reorder(order.value.items)
    snackbarText.value = 'Itens adicionados ao seu carrinho! 🛒'
    showSnackbar.value = true
}
</script>

<style scoped>
.order-detail-container {
  max-width: 1200px;
  margin: 0 auto;
}

.premium-card {
  background: white !important;
  border: 1px solid rgba(var(--v-border-color), 0.08);
  border-radius: 28px !important;
  box-shadow: 0 4px 20px rgba(0,0,0,0.02) !important;
}

.summary-card {
    background: rgba(var(--v-theme-primary), 0.02) !important;
    border: 1px solid rgba(var(--v-theme-primary), 0.05);
}

.tracking-timeline {
    position: relative;
    padding-left: 48px;
}

.tracking-timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: rgba(var(--v-border-color), 0.1);
}

.timeline-item {
    position: relative;
    padding-bottom: 40px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-dot {
    position: absolute;
    left: -48px;
    top: 4px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e0e0e0;
    display: flex;
    align-center: center;
    justify-content: center;
    z-index: 1;
    transition: all 0.3s ease;
}

.timeline-item.active .timeline-dot {
    background: var(--v-theme-primary);
    box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.3);
}

.timeline-item.current .timeline-dot {
    background: var(--v-theme-primary);
    animation: pulse 2s infinite;
}

.timeline-content {
    opacity: 0.5;
    transition: all 0.3s ease;
}

.timeline-item.active .timeline-content,
.timeline-item.current .timeline-content {
    opacity: 1;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(var(--v-theme-primary), 0.4); }
  70% { box-shadow: 0 0 0 15px rgba(var(--v-theme-primary), 0); }
  100% { box-shadow: 0 0 0 0 rgba(var(--v-theme-primary), 0); }
}

.border-bottom {
    border-bottom: 1px solid rgba(var(--v-border-color), 0.05);
}

.border-bottom:last-child {
    border-bottom: none;
}

.color-primary {
    color: rgb(var(--v-theme-primary));
}

.gap-4 {
    gap: 16px;
}
</style>
