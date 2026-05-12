<template>
  <v-container>
    <v-card>
      <v-card-title>{{ t('partner.orders.id', { id: order.id }) }}</v-card-title>
      <v-card-subtitle>{{ t('partner.orders.status') }}: {{ t('partner.status.' + order.status) }}</v-card-subtitle>

      <v-card-text>
        <v-row>
          <v-col cols="12" md="6">
            <h3 class="text-h6 mb-4">{{ t('partner.orders.customer_info') }}</h3>
            <v-list>
              <v-list-item>
                <v-list-item-title>{{ t('auth.form.name') }}: {{ order.customer_name }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>{{ t('auth.form.phone') }}: {{ order.customer_phone }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>{{ t('auth.form.address') }}: {{ order.delivery_address }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-col>

          <v-col cols="12" md="6">
            <h3 class="text-h6 mb-4">{{ t('partner.orders.summary') }}</h3>
            <v-list>
              <v-list-item>
                <v-list-item-title>{{ t('partner.orders.subtotal') }}: {{ t('common.currency_symbol', { value: order.subtotal }, '$' + order.subtotal) }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>{{ t('partner.orders.delivery_fee') }}: {{ t('common.currency_symbol', { value: order.delivery_fee }, '$' + order.delivery_fee) }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>{{ t('partner.orders.total') }}: {{ t('common.currency_symbol', { value: order.total }, '$' + order.total) }}</v-list-item-title>
              </v-list-item>
              <v-list-item>
                <v-list-item-title>
                  {{ t('partner.orders.payment_method') }}: 
                  {{ t('components.payment.' + order.payment_method, order.payment_method) }}
                  <v-chip v-if="order.payment_method === 'cash' && order.change_for > 0" color="info" size="small" class="ml-2">
                    {{ t('components.payment.change_for') }}: {{ t('common.currency_symbol', { value: order.change_for }, 'R$ ' + order.change_for) }}
                  </v-chip>
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>

        <v-divider class="my-4"></v-divider>

        <h3 class="text-h6 mb-4">{{ t('partner.orders.items') }}</h3>
        <v-table data-cy="order-items-table">
          <thead>
            <tr>
              <th>{{ t('partner.orders.items') }}</th>
              <th>{{ t('cart.quantity') }}</th>
              <th>{{ t('partner.products.price') }}</th>
              <th>{{ t('cart.addons') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in order.items" :key="item.id" data-cy="order-item">
              <td>{{ item.product_name }}</td>
              <td>{{ item.quantity }}</td>
              <td>${{ item.price }}</td>
              <td>
                <v-chip
                  v-for="addon in item.addons"
                  :key="addon.id"
                  size="small"
                  class="mr-1 mb-1"
                >
                  {{ addon.name }} (+${{ addon.price }})
                </v-chip>
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-card-text>

      <v-card-actions class="pa-4 flex-wrap gap-2">
        <v-btn
          variant="outlined"
          prepend-icon="mdi-arrow-left"
          @click="goBack"
          data-cy="back-btn"
        >
          {{ t('partner.orders.back_to_orders') }}
        </v-btn>
        
        <v-spacer></v-spacer>

        <v-btn
          v-if="['confirmed', 'preparing', 'pending'].includes(order.status)"
          color="primary"
          prepend-icon="mdi-moped-electric"
          :loading="requestingCourier"
          @click="handleRequestCourier"
          data-cy="request-courier-btn"
        >
          {{ t('partner.orders.request_courier') }}
        </v-btn>

        <v-btn
          color="secondary"
          prepend-icon="mdi-printer"
          @click="printLabel"
          data-cy="print-label-btn"
        >
          {{ t('partner.orders.print_label') }}
        </v-btn>
      </v-card-actions>
    </v-card>

    <!-- Printable Label (Hidden) -->
    <div id="printable-label" class="d-none">
      <div class="label-content pa-4" style="font-family: monospace; border: 2px solid black; width: 80mm; margin: 0 auto; color: black; background: white;">
        <h2 style="text-align: center; border-bottom: 1px dashed black; padding-bottom: 5px; margin-top: 0;">TODOKE</h2>
        <p style="margin: 5px 0;"><strong>ORDER:</strong> #{{ order.id }}</p>
        <p style="margin: 5px 0;"><strong>CUSTOMER:</strong> {{ order.customer_name }}</p>
        <p style="margin: 5px 0;"><strong>ADDRESS:</strong> {{ order.delivery_address }}</p>
        <p style="margin: 5px 0;"><strong>PHONE:</strong> {{ order.customer_phone }}</p>
        <p style="margin: 5px 0;"><strong>PAYMENT:</strong> {{ t('components.payment.' + order.payment_method, order.payment_method) }} 
          <span v-if="order.payment_method === 'cash' && order.change_for > 0">
            ({{ t('components.payment.change_for') }}: {{ t('common.currency_symbol', { value: order.change_for }, 'R$ ' + order.change_for) }})
          </span>
        </p>
        <hr style="border-top: 1px dashed black; margin: 10px 0;">
        <div v-for="item in order.items" :key="item.id" style="margin: 2px 0;">
          {{ item.quantity }}x {{ item.product_name }}
        </div>
        <hr style="border-top: 1px dashed black; margin: 10px 0;">
        <p style="text-align: right; font-size: 1.2em; margin: 5px 0;"><strong>TOTAL: {{ t('common.currency_symbol', { value: order.total }, '$' + order.total) }}</strong></p>
        <div style="text-align: center; margin-top: 20px; font-size: 24px; letter-spacing: 4px; border: 1px solid #ccc; padding: 10px;">
          ||||| {{ order.id }} |||||
        </div>
      </div>
    </div>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import partnerService from '@/services/partner'
import { useNotificationStore } from '@/stores/notification'

const { t } = useI18n()
const notifications = useNotificationStore()

const props = defineProps({
  order: {
    type: Object,
    required: true
  }
})

const requestingCourier = ref(false)

function goBack() {
  router.visit('/partner/orders')
}

async function handleRequestCourier() {
  requestingCourier.value = true
  try {
    await partnerService.requestCourier(props.order.id)
    notifications.success(t('partner.orders.courier_requested'))
    // Refresh order data if needed, or just let realtime handle it
  } catch (err) {
    notifications.error(t('partner.orders.courier_request_failed'))
  } finally {
    requestingCourier.value = false
  }
}

function printLabel() {
  window.print()
}
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #printable-label, #printable-label * {
    visibility: visible;
  }
  #printable-label {
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    display: block !important;
    z-index: 9999;
  }
}

.gap-2 {
  gap: 8px;
}
</style>
