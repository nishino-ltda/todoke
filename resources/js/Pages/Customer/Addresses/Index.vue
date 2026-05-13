<template>
  <CustomerLayout>
    <div class="addresses-page">
      <div class="d-flex align-center mb-6">
        <h1 class="text-h4 font-weight-black">{{ t('components.address.manage_addresses') }}</h1>
        <v-spacer />
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          rounded="pill"
          class="text-none"
          @click="openAddDialog"
          data-cy="add-address-btn"
        >
          {{ t('components.address.add_new') }}
        </v-btn>
      </div>

      <v-row v-if="loading" justify="center" class="mt-12">
        <v-progress-circular indeterminate color="primary" size="64" />
      </v-row>

      <v-row v-else-if="addresses.length === 0" justify="center" class="mt-12">
        <v-col cols="12" md="6" class="text-center">
          <v-icon size="120" color="grey-lighten-2" class="mb-4">mdi-map-marker-off</v-icon>
          <h2 class="text-h5 text-medium-emphasis mb-2">{{ t('components.address.no_saved_addresses') }}</h2>
          <p class="text-body-1 text-medium-emphasis mb-6">Cadastre seus endereços frequentes para facilitar seus pedidos.</p>
          <v-btn color="primary" variant="tonal" rounded="pill" @click="openAddDialog">
            Cadastrar Primeiro Endereço
          </v-btn>
        </v-col>
      </v-row>

      <v-row v-else>
        <v-col v-for="addr in addresses" :key="addr.id" cols="12" md="6" lg="4">
          <v-card class="address-card rounded-xl" elevation="2">
            <v-card-item>
              <template v-slot:prepend>
                <v-avatar color="primary-lighten-5" size="48">
                  <v-icon color="primary">{{ getAddressIcon(addr.label) }}</v-icon>
                </v-avatar>
              </template>
              <v-card-title class="font-weight-bold">{{ addr.label }}</v-card-title>
              <v-card-subtitle>{{ addr.is_default ? 'Endereço Padrão' : '' }}</v-card-subtitle>
              
              <template v-slot:append>
                <v-menu location="bottom end">
                  <template v-slot:activator="{ props }">
                    <v-btn icon="mdi-dots-vertical" variant="text" v-bind="props" data-cy="address-menu-btn" />
                  </template>
                  <v-list>
                    <v-list-item @click="editAddress(addr)" prepend-icon="mdi-pencil">
                      <v-list-item-title>Editar</v-list-item-title>
                    </v-list-item>
                    <v-list-item v-if="!addr.is_default" @click="setDefault(addr)" prepend-icon="mdi-home-check">
                      <v-list-item-title>Tornar Padrão</v-list-item-title>
                    </v-list-item>
                    <v-divider />
                    <v-list-item @click="confirmDelete(addr)" prepend-icon="mdi-delete" color="error">
                      <v-list-item-title>Excluir</v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </template>
            </v-card-item>

            <v-card-text class="pt-2">
              <div class="text-body-2 mb-1">{{ addr.address }}</div>
              <div class="text-caption text-medium-emphasis">
                {{ addr.neighborhood }}, {{ addr.city }} - {{ addr.state }}
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Add/Edit Dialog -->
      <v-dialog v-model="dialog" max-width="600px" persistent>
        <v-card class="rounded-xl">
          <v-card-title class="pa-6 pb-0 d-flex align-center">
            <span class="text-h5 font-weight-black">{{ editingId ? 'Editar Endereço' : 'Novo Endereço' }}</span>
            <v-spacer />
            <v-btn icon="mdi-close" variant="text" @click="closeDialog" />
          </v-card-title>

          <v-card-text class="pa-6">
            <v-form ref="formRef" @submit.prevent="saveAddress">
              <v-text-field
                v-model="form.label"
                label="Nome do Endereço (ex: Casa, Trabalho)"
                required
                variant="outlined"
                rounded="lg"
                class="mb-4"
                placeholder="Ex: Casa do Papaco"
                data-cy="address-label-input"
              />

              <AddressSelector
                v-model="selectedAddress"
                class="mb-4"
                data-cy="address-selector"
              />

              <v-row v-if="selectedAddress">
                <v-col cols="12">
                  <v-text-field
                    v-model="form.complement"
                    label="Complemento / Ponto de Referência"
                    variant="outlined"
                    rounded="lg"
                    hide-details
                    data-cy="address-complement-input"
                  />
                </v-col>
              </v-row>

              <v-alert v-if="error" type="error" variant="tonal" class="mt-4 rounded-lg">
                {{ error }}
              </v-alert>
            </v-form>
          </v-card-text>

          <v-card-actions class="pa-6 pt-0">
            <v-spacer />
            <v-btn variant="text" @click="closeDialog" class="text-none" rounded="pill">
              {{ t('components.address.cancel') }}
            </v-btn>
            <v-btn
              color="primary"
              variant="flat"
              rounded="pill"
              class="text-none px-8"
              :loading="saving"
              :disabled="!isFormValid"
              @click="saveAddress"
              data-cy="save-address-submit"
            >
              {{ editingId ? 'Atualizar' : 'Salvar' }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- Delete Confirmation -->
      <v-dialog v-model="deleteDialog" max-width="400px">
        <v-card class="rounded-xl pa-4">
          <v-card-title class="text-h6 font-weight-bold text-center">Confirmar Exclusão</v-card-title>
          <v-card-text class="text-center">
            Tem certeza que deseja excluir o endereço <strong>{{ addressToDelete?.label }}</strong>?
          </v-card-text>
          <v-card-actions class="justify-center mt-2">
            <v-btn variant="text" @click="deleteDialog = false" class="text-none">Cancelar</v-btn>
            <v-btn color="error" variant="flat" rounded="pill" @click="doDelete" class="text-none px-6">Excluir</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import CustomerLayout from '@/Layouts/CustomerLayout.vue'
import AddressSelector from '@/Components/AddressSelector.vue'

const { t } = useI18n()

const loading = ref(true)
const addresses = ref([])
const dialog = ref(false)
const saving = ref(false)
const error = ref(null)
const editingId = ref(null)

const deleteDialog = ref(false)
const addressToDelete = ref(null)

const form = ref({
  label: '',
  complement: '',
  is_default: false
})

const selectedAddress = ref(null)

const isFormValid = computed(() => {
  return form.value.label && selectedAddress.value
})

onMounted(fetchAddresses)

async function fetchAddresses() {
  loading.value = true
  try {
    const response = await api.get('/customer/addresses')
    addresses.value = response.data.data || []
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

function openAddDialog() {
  editingId.value = null
  form.value = { label: '', complement: '', is_default: false }
  selectedAddress.value = null
  dialog.value = true
}

function editAddress(addr) {
  editingId.value = addr.id
  form.value = { 
    label: addr.label, 
    complement: addr.complement || '', 
    is_default: addr.is_default 
  }
  selectedAddress.value = {
    address: addr.address,
    lat: addr.lat,
    lng: addr.lng
  }
  dialog.value = true
}

async function saveAddress() {
  if (!isFormValid.value) return

  saving.value = true
  error.value = null

  const payload = {
    label: form.value.label,
    address: selectedAddress.value.address,
    lat: selectedAddress.value.lat,
    lng: selectedAddress.value.lng,
    city: selectedAddress.value.city,
    state: selectedAddress.value.state,
    neighborhood: selectedAddress.value.neighborhood,
    complement: form.value.complement,
    is_default: form.value.is_default
  }

  try {
    if (editingId.value) {
      await api.put(`/customer/addresses/${editingId.value}`, payload)
    } else {
      await api.post('/customer/addresses', payload)
    }
    await fetchAddresses()
    closeDialog()
  } catch (err) {
    error.value = 'Falha ao salvar endereço. Tente novamente.'
    console.error(err)
  } finally {
    saving.value = false
  }
}

async function setDefault(addr) {
  try {
    await api.patch(`/customer/addresses/${addr.id}/default`)
    await fetchAddresses()
  } catch (err) {
    console.error(err)
  }
}

function confirmDelete(addr) {
  addressToDelete.value = addr
  deleteDialog.value = true
}

async function doDelete() {
  if (!addressToDelete.value) return
  
  try {
    await api.delete(`/customer/addresses/${addressToDelete.value.id}`)
    await fetchAddresses()
    deleteDialog.value = false
  } catch (err) {
    console.error(err)
  }
}

function closeDialog() {
  dialog.value = false
  editingId.value = null
}

function getAddressIcon(label) {
  const l = label.toLowerCase()
  if (l.includes('casa')) return 'mdi-home'
  if (l.includes('trabalho') || l.includes('escritorio')) return 'mdi-briefcase'
  return 'mdi-map-marker'
}
</script>

<style scoped>
.address-card {
  transition: transform 0.2s, box-shadow 0.2s;
  border: 1px solid rgba(var(--v-border-color), 0.05);
}

.address-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
}
</style>
