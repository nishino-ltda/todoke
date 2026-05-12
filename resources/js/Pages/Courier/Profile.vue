<template>
  <CourierLayout>
    <div class="profile-container" data-cy="courier-profile">
      <v-row>
        <!-- Left Column: Profile Info -->
        <v-col cols="12" md="4">
          <v-card class="glass-card mb-6 text-center pa-6">
            <v-avatar size="150" class="mx-auto mb-4 elevation-4 border-avatar">
              <v-img :src="profilePhotoUrl" cover>
                <template v-slot:placeholder>
                  <v-icon size="64" color="grey">mdi-account</v-icon>
                </template>
              </v-img>
            </v-avatar>
            <h2 class="text-h5 font-weight-bold mb-1">{{ user?.name }}</h2>
            <p class="text-subtitle-2 text-grey mb-4">{{ t('auth.roles.courier') }}</p>
            
            <v-file-input
              v-model="photoFile"
              accept="image/*"
              variant="outlined"
              density="compact"
              prepend-icon="mdi-camera"
              :label="t('courier.profile.choose_photo')"
              hide-details
              class="mb-4"
              @change="uploadPhoto"
            ></v-file-input>
          </v-card>

          <!-- Additional Roles Card -->
          <v-card class="glass-card pa-6">
            <h3 class="text-h6 font-weight-bold mb-4">{{ t('courier.profile.additional_roles') }}</h3>
            <v-btn
              v-if="!isPartner"
              block
              color="primary"
              variant="tonal"
              prepend-icon="mdi-store-plus"
              class="mb-2 text-none"
              @click="showBecomePartner = true"
              data-cy="become-partner-btn"
            >
              {{ t('courier.profile.become_partner') }}
            </v-btn>
            <v-chip
              v-if="isPartner"
              color="success"
              variant="flat"
              block
              class="mb-2 justify-center"
              prepend-icon="mdi-check-circle"
            >
              {{ t('courier.profile.already_partner') }}
            </v-chip>
            <v-btn
              block
              variant="outlined"
              prepend-icon="mdi-account-switch"
              class="text-none"
              @click="router.visit('/customer/dashboard')"
            >
              {{ t('courier.profile.access_as_customer') }}
            </v-btn>
          </v-card>
        </v-col>

        <!-- Right Column: Edit Form -->
        <v-col cols="12" md="8">
          <v-card class="glass-card pa-6">
            <h3 class="text-h6 font-weight-bold mb-6">{{ t('courier.profile.title') }}</h3>
            
            <form @submit.prevent="updateProfile">
              <v-row>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.name"
                    :label="t('courier.profile.name')"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="form.errors.name"
                    data-cy="profile-name"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.email"
                    :label="t('courier.profile.email')"
                    variant="outlined"
                    density="comfortable"
                    disabled
                    data-cy="profile-email"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.phone"
                    :label="t('courier.profile.phone')"
                    variant="outlined"
                    density="comfortable"
                    v-maska="'(##) #####-####'"
                    :error-messages="form.errors.phone"
                    data-cy="profile-phone"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.license_number"
                    :label="t('courier.profile.license_number')"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="form.errors.license_number"
                    data-cy="profile-license"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-select
                    v-model="form.vehicle_type"
                    :items="vehicleOptions"
                    item-title="title"
                    item-value="value"
                    :label="t('courier.profile.vehicle_type')"
                    variant="outlined"
                    density="comfortable"
                    :error-messages="form.errors.vehicle_type"
                    data-cy="profile-vehicle"
                  ></v-select>
                </v-col>
              </v-row>

              <div class="d-flex justify-end mt-4">
                <v-btn
                  type="submit"
                  color="primary"
                  size="large"
                  class="text-none px-8 font-weight-bold"
                  :loading="form.processing"
                  data-cy="save-profile-btn"
                >
                  {{ t('courier.profile.save') }}
                </v-btn>
              </div>
            </form>
          </v-card>
        </v-col>
      </v-row>

      <!-- Become Partner Dialog -->
      <v-dialog v-model="showBecomePartner" max-width="600px">
        <v-card class="glass-card pa-4">
          <v-card-title class="text-h5 font-weight-bold">
            {{ t('courier.profile.become_partner_title') }}
          </v-card-title>
          <v-card-text>
            <v-row class="mt-2">
              <v-col cols="12">
                <v-text-field
                  v-model="partnerForm.business_name"
                  :label="t('courier.profile.business_name')"
                  variant="outlined"
                  density="comfortable"
                  :error-messages="partnerForm.errors.business_name"
                  data-cy="partner-business-name"
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="partnerForm.business_type"
                  :items="businessTypeOptions"
                  item-title="title"
                  item-value="value"
                  :label="t('courier.profile.business_type')"
                  variant="outlined"
                  density="comfortable"
                  :error-messages="partnerForm.errors.business_type"
                  data-cy="partner-business-type"
                ></v-select>
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="partnerForm.tax_id"
                  :label="t('courier.profile.tax_id')"
                  variant="outlined"
                  density="comfortable"
                  v-maska="'##.###.###/####-##'"
                  :error-messages="partnerForm.errors.tax_id"
                  data-cy="partner-tax-id"
                ></v-text-field>
              </v-col>
              <v-col cols="12">
                <v-text-field
                  v-model="partnerForm.address"
                  :label="t('courier.profile.address')"
                  variant="outlined"
                  density="comfortable"
                  :error-messages="partnerForm.errors.address"
                  data-cy="partner-address"
                ></v-text-field>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="pa-4 pt-0">
            <v-spacer></v-spacer>
            <v-btn variant="text" @click="showBecomePartner = false">{{ t('partner.actions.cancel') }}</v-btn>
            <v-btn
              color="primary"
              variant="flat"
              class="px-6"
              :loading="partnerForm.processing"
              @click="submitBecomePartner"
              data-cy="submit-partner-btn"
            >
              {{ t('courier.profile.submit') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
  </CourierLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import CourierLayout from '@/Layouts/CourierLayout.vue';
import { useNotificationStore } from '@/stores/notification';

const { t } = useI18n();
const page = usePage();
const notifications = useNotificationStore();
const user = computed(() => page.props.auth.user);

const isPartner = computed(() => user.value?.type === 'partner' || user.value?.roles?.includes('partner'));
const showBecomePartner = ref(false);
const photoFile = ref(null);

const profilePhotoUrl = computed(() => {
  return user.value?.photo_url || `https://ui-avatars.com/api/?name=${user.value?.name || 'U'}&background=0D47A1&color=fff`;
});

const form = useForm({
  name: user.value?.name || '',
  email: user.value?.email || '',
  phone: user.value?.phone || '',
  license_number: user.value?.license_number || '',
  vehicle_type: user.value?.vehicle_type || 'motorcycle',
});

const partnerForm = useForm({
  business_name: '',
  business_type: 'restaurant',
  tax_id: '',
  address: '',
  role: 'partner'
});

const vehicleOptions = [
  { title: t('courier.profile.vehicle_type_options.motorcycle'), value: 'motorcycle' },
  { title: t('courier.profile.vehicle_type_options.car'), value: 'car' },
  { title: t('courier.profile.vehicle_type_options.bicycle'), value: 'bicycle' },
];

const businessTypeOptions = [
  { title: t('courier.profile.business_type_options.restaurant'), value: 'restaurant' },
  { title: t('courier.profile.business_type_options.market'), value: 'market' },
  { title: t('courier.profile.business_type_options.pharmacy'), value: 'pharmacy' },
];

const updateProfile = () => {
  form.patch(route('profile.update'), {
    preserveScroll: true,
    onSuccess: () => notifications.success(t('courier.profile.success.updated')),
    onError: () => notifications.error(t('courier.profile.error.save')),
  });
};

const uploadPhoto = async () => {
  if (!photoFile.value) return;
  
  const formData = new FormData();
  formData.append('photo', photoFile.value);
  
  try {
    await router.post(route('profile.photo.update'), formData);
    notifications.success(t('courier.profile.success.updated'));
  } catch (err) {
    notifications.error(t('courier.profile.error.save'));
  }
};

const submitBecomePartner = () => {
  partnerForm.post(route('profile.role.add'), {
    onSuccess: () => {
      showBecomePartner.value = false;
      notifications.success(t('courier.profile.success.role_added'));
      router.reload();
    },
    onError: () => notifications.error(t('courier.profile.error.role_added')),
  });
};
</script>

<style scoped>
.glass-card {
  background: rgba(255, 255, 255, 0.7) !important;
  backdrop-filter: blur(12px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  transition: transform 0.2s ease;
}

.border-avatar {
  border: 4px solid white;
}

.profile-container {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
