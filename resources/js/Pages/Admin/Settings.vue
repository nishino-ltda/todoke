<template>
  <AdminLayout>
    <div class="settings-page pa-8">
      <h1 class="text-h4 mb-6">{{ t('admin.settings.title', 'Platform Settings') }}</h1>
      
      <v-card border elevation="0" class="pa-8 rounded-xl">
        <v-form @submit.prevent="saveSettings">
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="settings.site_name"
                :label="t('admin.settings.site_name', 'Site Name')"
                data-cy="site-name-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="settings.delivery_fee"
                :label="t('admin.settings.delivery_fee', 'Default Delivery Fee')"
                type="number"
                prefix="$"
                data-cy="delivery-fee-input"
              ></v-text-field>
            </v-col>
            <v-col cols="12">
              <v-switch
                v-model="settings.maintenance_mode"
                :label="t('admin.settings.maintenance_mode', 'Maintenance Mode')"
                color="error"
                data-cy="maintenance-mode-switch"
              ></v-switch>
            </v-col>
          </v-row>
          
          <v-divider class="my-6"></v-divider>
          
          <div class="d-flex justify-end">
            <v-btn
              color="primary"
              type="submit"
              :loading="saving"
              data-cy="save-settings-btn"
            >
              {{ t('admin.settings.save', 'Save Settings') }}
            </v-btn>
          </div>
        </v-form>
      </v-card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useNotificationStore } from '@/stores/notification'

const { t } = useI18n()
const notifications = useNotificationStore()

const settings = ref({
  site_name: 'Todoke',
  delivery_fee: 5.00,
  maintenance_mode: false
})

const saving = ref(false)

const saveSettings = async () => {
  saving.value = true
  try {
    // Mock save
    await new Promise(resolve => setTimeout(resolve, 1000))
    notifications.success(t('admin.settings.save_success', 'Settings saved successfully'))
  } catch (err) {
    notifications.error(t('admin.settings.save_error', 'Failed to save settings'))
  } finally {
    saving.value = false
  }
}
</script>

