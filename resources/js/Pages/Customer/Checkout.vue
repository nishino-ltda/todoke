<template>
  <CustomerLayout>
    <div data-cy="customer-checkout" class="pa-8 fade-in">
      <EmptyState
        v-if="!isAuthenticated"
        icon="mdi-account-lock-outline"
        :title="$t('checkout.login_required_title')"
        :message="$t('checkout.login_required_message')"
      >
        <v-btn
          color="primary"
          class="mt-4 mr-2 font-weight-bold text-none"
          size="large"
          rounded="xl"
          @click="goToLogin"
          data-cy="checkout-login-btn"
        >
          {{ $t('auth.login') }}
        </v-btn>
        <v-btn
          color="secondary"
          class="mt-4 font-weight-bold text-none"
          size="large"
          rounded="xl"
          @click="goToRegister"
          data-cy="checkout-register-btn"
        >
          {{ $t('auth.register') }}
        </v-btn>
      </EmptyState>

      <EmptyState
        v-else-if="cartStore.items.length === 0"
        icon="mdi-cart-off"
        :title="$t('checkout.empty_cart_title')"
        :message="$t('checkout.empty_cart_message')"
        :action-label="$t('checkout.browse_menu')"
        @action="goToMenu"
        data-cy="checkout-browse-menu-btn"
      />

      <v-row v-else>
        <v-col cols="12" md="7">
          <h1 class="text-h4 mb-2">{{ $t('checkout.title') }}</h1>
          <p class="text-subtitle-1 text-medium-emphasis mb-6">{{ $t('checkout.subtitle') }}</p>

          <CheckoutForm
            :is-submitting="isSubmitting"
            :error-message="errorMessage"
            :total="cartStore.totalWithDelivery"
            @submit="handleSubmit"
          />
        </v-col>

        <v-col cols="12" md="5">
          <v-card class="glass-card" data-cy="checkout-order-summary">
            <v-card-title class="text-h5 font-weight-black pt-6 px-6">
              {{ $t('checkout.order_summary') }}
            </v-card-title>

            <v-list v-if="cartStore.items.length > 0" class="bg-transparent px-4 py-2">
              <v-list-item
                v-for="(item, index) in cartStore.items"
                :key="item.id + '-' + index"
                class="mb-2 rounded-lg"
                data-cy="checkout-summary-item"
              >
                <template v-slot:prepend>
                  <v-avatar rounded="lg" size="48" class="mr-3" color="grey-lighten-3">
                    <v-img :src="resolveImageUrl(item.image)" cover></v-img>
                  </v-avatar>
                </template>

                <v-list-item-title class="font-weight-medium">{{ item.name }}</v-list-item-title>
                <v-list-item-subtitle class="text-primary font-weight-bold">
                  {{ formatCurrency(getItemSubtotal(item)) }} <span class="text-medium-emphasis text-caption ml-1">x {{ item.quantity }}</span>
                </v-list-item-subtitle>

                <template v-if="item.selectedAddons?.length" v-slot:append>
                  <div class="text-caption text-medium-emphasis text-right" style="max-width: 100px;">
                    <div v-for="addon in item.selectedAddons" :key="addon.id">
                      + {{ addonLabel(addon) }}
                    </div>
                  </div>
                </template>
              </v-list-item>
            </v-list>

            <v-divider v-if="cartStore.items.length > 0" class="mx-6"></v-divider>

            <v-card-text v-if="cartStore.items.length > 0" class="px-6 py-4">
              <v-row class="text-body-1 mb-1">
                <v-col cols="8">{{ $t('cart.subtotal') }}</v-col>
                <v-col cols="4" class="text-right">{{ formatCurrency(cartStore.subtotal) }}</v-col>
              </v-row>
              <v-row class="text-body-1 mb-1">
                <v-col cols="8">{{ $t('cart.delivery_fee') }}</v-col>
                <v-col cols="4" class="text-right">{{ formatCurrency(cartStore.deliveryFee) }}</v-col>
              </v-row>
              <v-divider class="my-2" />
              <v-row class="text-h6 font-weight-black">
                <v-col cols="8">{{ $t('cart.total') }}</v-col>
                <v-col cols="4" class="text-right text-primary">{{ formatCurrency(cartStore.totalWithDelivery) }}</v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import CustomerLayout from '@/Layouts/CustomerLayout.vue'
import CheckoutForm from '@/Components/CheckoutForm.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useOrderApi } from '@/services/order'
import { useCurrency } from '@/Composables/useCurrency'

const cartStore = useCartStore()
const authStore = useAuthStore()
const orderApi = useOrderApi()
const { formatCurrency } = useCurrency()

const isSubmitting = ref(false)
const errorMessage = ref('')
const isAuthenticated = computed(() => authStore.isAuthenticated)

const resolveImageUrl = (path) => {
  if (!path) return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
  if (path.startsWith('http')) return path
  return `/storage/${path}`
}

function getItemSubtotal(item) {
  const addonsTotal = (item.selectedAddons || []).reduce((sum, addon) => sum + Number(addon.price || 0), 0)
  return Number(item.price || 0) + addonsTotal
}

function addonLabel(addon) {
  if (typeof addon === 'object') {
    return `${addon.name} (${formatCurrency(addon.price)})`
  }
  return addon
}

function goToLogin() {
  const currentPath = window.location.pathname + window.location.search
  router.visit(`/login?redirect=${encodeURIComponent(currentPath)}`)
}

function goToRegister() {
  const currentPath = window.location.pathname + window.location.search
  router.visit(`/register?redirect=${encodeURIComponent(currentPath)}`)
}

function goToMenu() {
  router.visit('/customer/menu')
}

async function handleSubmit(formData) {
  isSubmitting.value = true
  errorMessage.value = ''

  const partnerId = cartStore.items[0]?.partner_id

  const payment =
    typeof formData.paymentMethod === 'string'
      ? { method: formData.paymentMethod }
      : formData.paymentMethod

  const orderPayload = {
    partner_id: partnerId,
    items: cartStore.items.map(item => ({
      product_id: item.id,
      quantity: item.quantity,
      addons: item.selectedAddons?.map(addon => ({
        addon_id: addon.id,
        quantity: 1,
      })) || [],
    })),
    payment,
    delivery: {
      destination: {
        lat: formData.lat || 0,
        lng: formData.lng || 0,
        address: formData.address || '',
      },
    },
  }

  try {
    const result = await orderApi.createOrder(orderPayload)
    cartStore.clearCart()
    router.visit(`/customer/orders/${result.id}`)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || error.message || 'Failed to submit order'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  if (!authStore.isAuthenticated) {
    try {
      authStore.init(router)
    } catch (e) {
    }
  }
})
</script>

<style scoped>
:deep(.v-list-item) {
  border-radius: 12px;
}
</style>
