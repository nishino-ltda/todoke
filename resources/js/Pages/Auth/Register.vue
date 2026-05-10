<template>
    <GuestLayout :minimal="true">
        <v-container class="fill-height justify-center" fluid>
            <v-row justify="center">
                <v-col cols="12" sm="8" md="6" lg="5">
                    <v-card elevation="4" class="pa-6 rounded-xl" data-cy="auth-register">
                        <div class="text-center mb-8">
                            <h1 class="text-h4 font-weight-bold primary--text mb-2">
                                {{ t('auth.register') }}
                            </h1>
                            <p class="text-subtitle-1 text-medium-emphasis">
                                {{ t('home.cta.description') }}
                            </p>
                        </div>

                        <AuthForm mode="register" />

                        <div class="text-center mt-6">
                            <Link 
                                :href="route('login')"
                                class="text-primary text-decoration-none text-body-2"
                                data-cy="login-link"
                            >
                                {{ t('auth.already_have_account') }}
                            </Link>
                        </div>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </GuestLayout>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AuthForm from '@/Components/AuthForm.vue';

const { t } = useI18n();
</script>
