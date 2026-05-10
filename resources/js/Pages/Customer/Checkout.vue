<template>
  <CustomerLayout>
    <div data-cy="customer-checkout" class="pa-6">
      <v-row v-if="!isAuthenticated" class="justify-center pa-6">
        <v-col cols="12" md="6" class="text-center">
          <v-icon color="grey" size="64">mdi-account-lock-outline</v-icon>
          <h2 class="text-h5 mt-4">{{ $t('checkout.login_required_title') }}</h2>
          <p class="text-body-1 text-grey mt-2">{{ $t('checkout.login_required_message') }}</p>
          <v-btn
            color="primary"
            class="mt-4 mr-2"
            @click="goToLogin"
            data-cy="checkout-login-btn"
          >
            {{ $t('auth.login') }}
          </v-btn>
          <v-btn
            color="secondary"
            class="mt-4"
            @click="goToRegister"
            data-cy="checkout-register-btn"
          >
            {{ $t('auth.register') }}
          </v-btn>
        </v-col>
      </v-row>

      <v-row v-else-if="cartStore.items.length === 0" class="justify-center pa-6">
        <v-col cols="12" md="6" class="text-center">
          <v-icon color="grey" size="64">mdi-cart-off</v-icon>
          <h2 class="text-h5 mt-4">{{ $t('checkout.empty_cart_title') }}</h2>
          <p class="text-body-1 text-grey mt-2">{{ $t('checkout.empty_cart_message') }}</p>
          <v-btn
            color="primary"
            class="mt-4"
            @click="goToMenu"
            data-cy="checkout-browse-menu-btn"
          >
            {{ $t('checkout.browse_menu') }}
          </v-btn>
        </v-col>
      </v-row>

      <v-row v-else>
        <v-col cols="12" md="7">
          <h1 class="text-h4 mb-2">{{ $t('checkout.title') }}</h1>
          <p class="text-subtitle-1 text-grey mb-6">{{ $t('checkout.subtitle') }}</p>

          <CheckoutForm
            :is-submitting="isSubmitting"
            :error-message="errorMessage"
            @submit="handleSubmit"
          />
        </v-col>

        <v-col cols="12" md="5">
          <v-card variant="outlined" data-cy="checkout-order-summary">
            <v-card-title>{{ $t('checkout.order_summary') }}</v-card-title>
            <v-list>
              <v-list-item
                v-for="(item, index) in cartStore.items"
                :key="item.id + '-' + index"
                data-cy="checkout-summary-item"
              >
                <v-list-item-content>
                  <v-list-item-title>{{ item.name }} × {{ item.quantity }}</v-list-item-title>
                  <v-list-item-subtitle v-if="item.selectedAddons?.length">
                    <span v-for="addon in item.selectedAddons" :key="addon.id" class="text-caption mr-2">
                      + {{ addonLabel(addon) }}
                    </span>
                  </v-list-item-subtitle>
                </v-list-item-content>
                <v-list-item-action class="text-right">
                  {{ formatPrice(item.price * item.quantity) }}
                </v-list-item-action>
              </v-list-item>
            </v-list>
            <v-divider />
            <v-card-text>
              <v-row class="text-body-1 mb-1">
                <v-col cols="8">{{ $t('cart.subtotal') }}</v-col>
                <v-col cols="4" class="text-right">{{ formatPrice(cartStore.subtotal) }}</v-col>
              </v-row>
              <v-row class="text-body-1 mb-1">
                <v-col cols="8">{{ $t('cart.delivery_fee') }}</v-col>
                <v-col cols="4" class="text-right">{{ formatPrice(cartStore.deliveryFee) }}</v-col>
              </v-row>
              <v-divider class="my-2" />
              <v-row class="text-h6 font-weight-bold">
                <v-col cols="8">{{ $t('cart.total') }}</v-col>
                <v-col cols="4" class="text-right">{{ formatPrice(cartStore.totalWithDelivery) }}</v-col>
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
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useOrderApi } from '@/services/order'

const cartStore = useCartStore()
const authStore = useAuthStore()
const orderApi = useOrderApi()

const isSubmitting = ref(false)
const errorMessage = ref('')
const isAuthenticated = computed(() => authStore.isAuthenticated)

function formatPrice(value) {
  return `R$ ${(value || 0).toFixed(2)}`
}

function addonLabel(addon) {
  if (typeof addon === 'object') {
    return `${addon.name}`
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
