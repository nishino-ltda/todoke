<template>
  <PartnerLayout>
    <div class="order-create" data-cy="partner-order-create-page">
      <v-row>
        <v-col cols="12" md="8">
          <!-- Product Selection -->
          <v-card class="glass-card mb-6" rounded="xl">
            <v-card-text class="pa-6">
              <h2 class="text-h6 font-weight-bold mb-4">{{ t('partner.orders.items') }}</h2>
              <v-row>
                <v-col cols="12">
                  <v-autocomplete
                    v-model="selectedProduct"
                    :items="products"
                    item-title="name"
                    item-value="id"
                    :label="t('partner.products.add')"
                    prepend-inner-icon="mdi-plus"
                    variant="outlined"
                    return-object
                    @update:model-value="addItem"
                    data-cy="order-product-search"
                  >
                    <template v-slot:item="{ props, item }">
                      <v-list-item
                        v-bind="props"
                        :prepend-avatar="resolveImageUrl(item.raw.image)"
                        :title="item.raw.name"
                        :subtitle="t('common.currency_symbol', { value: item.raw.price.toFixed(2) }, '$' + item.raw.price.toFixed(2))"
                      ></v-list-item>
                    </template>
                  </v-autocomplete>
                </v-col>
              </v-row>

              <v-table class="bg-transparent mt-4">
                <thead>
                  <tr>
                    <th>{{ t('partner.products.name') }}</th>
                    <th class="text-center" width="150">{{ t('common.quantity') || 'Qty' }}</th>
                    <th class="text-right">{{ t('partner.products.price') }}</th>
                    <th class="text-right"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in orderItems" :key="index">
                    <td>{{ item.name }}</td>
                    <td>
                      <div class="d-flex align-center justify-center gap-2">
                        <v-btn icon="mdi-minus" size="x-small" variant="tonal" @click="updateQty(index, -1)"></v-btn>
                        <span class="font-weight-bold">{{ item.quantity }}</span>
                        <v-btn icon="mdi-plus" size="x-small" variant="tonal" @click="updateQty(index, 1)"></v-btn>
                      </div>
                    </td>
                    <td class="text-right">{{ t('common.currency_symbol', { value: (item.price * item.quantity).toFixed(2) }, '$' + (item.price * item.quantity).toFixed(2)) }}</td>
                    <td class="text-right">
                      <v-btn icon="mdi-delete" variant="text" color="error" size="small" @click="removeItem(index)"></v-btn>
                    </td>
                  </tr>
                  <tr v-if="!orderItems.length">
                    <td colspan="4" class="text-center text-medium-emphasis py-8">
                      {{ t('cart.empty') }}
                    </td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
          </v-card>

          <!-- Customer Info -->
          <v-card class="glass-card" rounded="xl">
            <v-card-text class="pa-6">
              <h2 class="text-h6 font-weight-bold mb-4">{{ t('partner.orders.customer_info') }}</h2>
              <v-row>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model="customer.name"
                    :label="t('partner.profile.name')"
                    variant="outlined"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model="customer.phone"
                    :label="t('partner.profile.phone')"
                    variant="outlined"
                    required
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="customer.address"
                    :label="t('partner.profile.address')"
                    variant="outlined"
                    required
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card class="glass-card mb-6" rounded="xl">
            <v-card-text class="pa-6">
              <h2 class="text-h6 font-weight-bold mb-4">{{ t('partner.orders.summary') }}</h2>
              
              <div class="d-flex justify-space-between mb-2">
                <span>{{ t('partner.orders.subtotal') }}</span>
                <span>{{ t('common.currency_symbol', { value: subtotal.toFixed(2) }, '$' + subtotal.toFixed(2)) }}</span>
              </div>
              <div class="d-flex justify-space-between mb-2">
                <span>{{ t('partner.orders.delivery_fee') }}</span>
                <span>{{ t('common.currency_symbol', { value: deliveryFee.toFixed(2) }, '$' + deliveryFee.toFixed(2)) }}</span>
              </div>
              <v-divider class="my-4"></v-divider>
              <div class="d-flex justify-space-between text-h6 font-weight-bold">
                <span>{{ t('common.total') || 'Total' }}</span>
                <span class="text-primary">{{ t('common.currency_symbol', { value: total.toFixed(2) }, '$' + total.toFixed(2)) }}</span>
              </div>

              <v-divider class="my-4"></v-divider>
              
              <h3 class="text-subtitle-1 font-weight-bold mb-3">{{ t('partner.orders.payment_method') }}</h3>
              <v-select
                v-model="paymentMethod"
                :items="paymentOptions"
                variant="outlined"
                density="comfortable"
                hide-details
              ></v-select>

              <v-btn
                color="primary"
                block
                size="large"
                rounded="lg"
                class="mt-6 elevation-4"
                :loading="saving"
                :disabled="!canSave"
                @click="createOrder"
                data-cy="submit-order-btn"
              >
                {{ t('partner.actions.create') }}
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </PartnerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import partnerService from '@/services/partner';
import api from '@/services/api';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const notifications = useNotificationStore();
const loading = ref(false);
const saving = ref(false);

const products = ref([]);
const selectedProduct = ref(null);
const orderItems = ref([]);
const deliveryFee = ref(5.00); // Default for manual
const paymentMethod = ref('cash');

const customer = ref({
  name: '',
  phone: '',
  address: ''
});

const paymentOptions = [
  { title: 'Cash on Delivery', value: 'cash' },
  { title: 'Card on Delivery', value: 'card_delivery' },
  { title: 'Pix', value: 'pix' }
];

const subtotal = computed(() => {
  return orderItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const total = computed(() => subtotal.value + deliveryFee.value);

const canSave = computed(() => {
  return orderItems.value.length > 0 && customer.value.name && customer.value.address;
});

const resolveImageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
};

const addItem = (product) => {
  if (!product) return;
  
  const existing = orderItems.value.find(i => i.id === product.id);
  if (existing) {
    existing.quantity++;
  } else {
    orderItems.value.push({
      id: product.id,
      name: product.name,
      price: product.price,
      quantity: 1
    });
  }
  
  // Reset search
  nextTick(() => {
    selectedProduct.value = null;
  });
};

const updateQty = (index, delta) => {
  const item = orderItems.value[index];
  item.quantity += delta;
  if (item.quantity <= 0) {
    removeItem(index);
  }
};

const removeItem = (index) => {
  orderItems.value.splice(index, 1);
};

const fetchProducts = async () => {
  try {
    const response = await partnerService.getMenu();
    products.value = response.data;
  } catch (err) {
    console.error('Failed to load products', err);
  }
};

const createOrder = async () => {
  saving.value = true;
  try {
    const data = {
      customer: customer.value,
      items: orderItems.value,
      payment_method: paymentMethod.value,
      delivery_fee: deliveryFee.value,
      total: total.value
    };
    
    await api.post('/partner/orders', data);
    notifications.success(t('partner.orders.status_updated'));
    router.visit('/partner/orders');
  } catch (err) {
    notifications.error(t('partner.orders.failed_update'));
  } finally {
    saving.value = false;
  }
};

import { nextTick } from 'vue';

onMounted(fetchProducts);
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07) !important;
}

.gap-2 {
  gap: 0.5rem;
}
.gap-3 {
  gap: 0.75rem;
}
</style>
