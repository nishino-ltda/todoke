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
                <v-list-item-title>{{ t('partner.orders.payment_method') }}: {{ order.payment_method }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>

        <v-divider class="my-4"></v-divider>

        <h3 class="text-h6 mb-4">{{ t('partner.orders.items') }}</h3>
        <v-table data-test="order-items-table">
          <thead>
            <tr>
              <th>{{ t('partner.orders.items') }}</th>
              <th>{{ t('cart.quantity') }}</th>
              <th>{{ t('partner.products.price') }}</th>
              <th>{{ t('cart.addons') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in order.items" :key="item.id" data-test="order-item">
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

      <v-card-actions>
        <v-btn color="primary" @click="goBack">{{ t('partner.orders.back_to_orders') }}</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'

const { t } = useI18n()

const props = defineProps({
  order: {
    type: Object,
    required: true
  }
})

function goBack() {
  router.visit('/partner/orders')
}
</script>
