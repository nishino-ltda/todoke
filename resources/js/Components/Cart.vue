<template>
  <v-card data-cy="cart-component">
    <v-card-title class="text-h5">
      {{ $t('cart.title') }}
      <v-spacer />
      <v-btn icon @click="$emit('close')" data-cy="cart-close">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </v-card-title>

    <v-card-text v-if="cartStore.items.length === 0" class="text-center pa-8" data-cy="cart-empty">
      <v-icon color="grey" size="64">mdi-cart-off</v-icon>
      <p class="text-h6 mt-4">{{ $t('cart.empty_title') }}</p>
      <p class="text-body-2 text-grey mt-1">{{ $t('cart.empty_message') }}</p>
    </v-card-text>

    <template v-else>
      <v-list data-cy="cart-items-list">
        <v-list-item
          v-for="(item, index) in cartStore.items"
          :key="item.id + '-' + index"
          data-cy="cart-item"
        >
          <v-list-item-content>
            <v-list-item-title data-cy="cart-item-name">
              {{ item.name }}
              <span class="text-primary font-weight-bold ml-1">
                {{ formatPrice(item.price) }} <span class="text-grey text-caption">x {{ item.quantity }}</span>
              </span>
            </v-list-item-title>

            <v-list-item-subtitle v-if="item.selectedAddons && item.selectedAddons.length" data-cy="cart-item-addons">
              <span v-for="addon in item.selectedAddons" :key="addon.id" class="text-caption">
                + {{ addonLabel(addon) }}
              </span>
            </v-list-item-subtitle>


          </v-list-item-content>

          <v-list-item-action class="d-flex flex-row align-center">
            <v-btn
              icon
              size="small"
              :disabled="item.quantity <= 1"
              @click="cartStore.decrementQuantity(index)"
              data-cy="cart-decrease-qty"
            >
              <v-icon>mdi-minus</v-icon>
            </v-btn>
            <span class="mx-2 text-body-2" data-cy="cart-item-qty">{{ item.quantity }}</span>
            <v-btn
              icon
              size="small"
              @click="cartStore.incrementQuantity(index)"
              data-cy="cart-increase-qty"
            >
              <v-icon>mdi-plus</v-icon>
            </v-btn>
            <v-btn
              icon
              size="small"
              color="error"
              class="ml-2"
              @click="cartStore.removeItem(item.id)"
              data-cy="cart-remove-item"
            >
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </v-list-item-action>
        </v-list-item>
      </v-list>

      <v-divider />

      <v-card-text>
        <v-row class="text-body-1 mb-1">
          <v-col cols="8">{{ $t('cart.subtotal') }}</v-col>
          <v-col cols="4" class="text-right" data-cy="cart-subtotal">
            {{ formatPrice(cartStore.subtotal) }}
          </v-col>
        </v-row>
        <v-row class="text-body-1 mb-1">
          <v-col cols="8">{{ $t('cart.delivery_fee') }}</v-col>
          <v-col cols="4" class="text-right" data-cy="cart-delivery-fee">
            {{ formatPrice(cartStore.deliveryFee) }}
          </v-col>
        </v-row>
        <v-divider class="my-2" />
        <v-row class="text-h6 font-weight-bold">
          <v-col cols="8">{{ $t('cart.total') }}</v-col>
          <v-col cols="4" class="text-right" data-cy="cart-total">
            {{ formatPrice(cartStore.totalWithDelivery) }}
          </v-col>
        </v-row>
      </v-card-text>
    </template>

    <v-card-actions v-if="cartStore.items.length > 0">
      <v-spacer />
      <v-btn
        color="primary"
        size="large"
        @click="goToCheckout"
        data-cy="cart-checkout-btn"
      >
        {{ $t('cart.proceed_to_checkout') }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script setup>
import { useCartStore } from '@/stores/cart'
import { router } from '@inertiajs/vue3'

defineEmits(['close'])

const cartStore = useCartStore()

function formatPrice(value) {
  return `R$ ${Number(value || 0).toFixed(2)}`
}

function getItemSubtotal(item) {
  const addonsTotal = (item.selectedAddons || []).reduce((sum, addon) => sum + Number(addon.price || 0), 0)
  return (Number(item.price || 0) + addonsTotal) * Number(item.quantity || 0)
}

function addonLabel(addon) {
  if (typeof addon === 'object') {
    return `${addon.name} (${formatPrice(addon.price)})`
  }
  return addon
}

function goToCheckout() {
  router.visit('/customer/checkout')
}
</script>
