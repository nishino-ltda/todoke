<template>
    <GuestLayout :minimal="true">
        <v-container class="fill-height justify-center" fluid>
            <v-row justify="center">
                <v-col cols="12" sm="8" md="6" lg="5">
                    <v-card elevation="4" class="pa-6 rounded-xl" data-cy="forgot-password-card">
                        <div class="text-center mb-8">
                            <h1 class="text-h4 font-weight-bold primary--text mb-2">
                                {{ t('auth.forgot_password') }}
                            </h1>
                            <p class="text-subtitle-1 text-medium-emphasis">
                                {{ t('auth.forgot_password_instructions') }}
                            </p>
                        </div>

                        <v-form @submit.prevent="submit">
                            <v-alert
                                v-if="error"
                                type="error"
                                variant="tonal"
                                data-cy="error-alert"
                                class="mb-4"
                            >
                                {{ error }}
                            </v-alert>

                            <v-alert
                                v-if="success"
                                type="success"
                                variant="tonal"
                                data-cy="success-alert"
                                class="mb-4"
                            >
                                {{ t('auth.reset_link_sent') }}
                            </v-alert>

                            <v-text-field
                                v-model="email"
                                :label="t('auth.form.email')"
                                type="email"
                                required
                                :error-messages="errors.email"
                                prepend-inner-icon="mdi-email-outline"
                                variant="outlined"
                                density="comfortable"
                                data-cy="email-input"
                            ></v-text-field>

                            <v-btn
                                type="submit"
                                color="primary"
                                size="large"
                                block
                                elevation="2"
                                :loading="loading"
                                data-cy="submit-button"
                                class="mt-4"
                            >
                                {{ t('auth.send_reset_link') }}
                            </v-btn>

                            <div class="text-center mt-6">
                                <Link 
                                    :href="route('login')"
                                    class="text-primary text-decoration-none text-body-2"
                                    data-cy="login-link"
                                >
                                    <v-icon start icon="mdi-arrow-left" size="small"></v-icon>
                                    {{ t('auth.back_to_login') }}
                                </Link>
                            </div>
                        </v-form>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </GuestLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { useAuthStore } from '@/stores/auth'
import { useLogStore } from '@/stores/log'

const { t } = useI18n()
const authStore = useAuthStore()
const logStore = useLogStore()

const email = ref('')
const errors = ref({})
const error = ref('')
const success = ref(false)
const loading = ref(false)

async function submit() {
  loading.value = true
  error.value = ''
  errors.value = {}
  success.value = false

  try {
    logStore.log('📧 Attempting password reset request')
    await authStore.requestPasswordReset(email.value)
    success.value = true
    logStore.log('✅ Password reset request successful')
  } catch (err) {
    error.value = err.message || 'Failed to send reset link'
    errors.value = err.errors || {}
    logStore.log(`❌ Password reset failed: ${error.value}`)
  } finally {
    loading.value = false
  }
}
</script>
