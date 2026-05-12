<template>
    <GuestLayout :minimal="true">
        <v-container class="fill-height justify-center" fluid>
            <v-row justify="center">
                <v-col cols="12" sm="8" md="6" lg="5">
                    <v-card elevation="4" class="pa-8 rounded-xl" data-cy="auth-login">
                        <div class="text-center mb-8">
                            <h1 class="text-h4 font-weight-bold primary--text mb-2">
                                {{ t('auth.login') }}
                            </h1>
                            <p class="text-subtitle-1 text-medium-emphasis">
                                {{ t('home.hero.subtitle') }}
                            </p>
                        </div>

                        <AuthForm mode="login" :redirect="redirectUrl" />

                        <div class="text-center mt-6 d-flex flex-column gap-2">
                            <Link 
                                :href="route('password.request')"
                                class="text-primary text-decoration-none text-body-2 mb-2"
                                data-cy="forgot-password-link"
                            >
                                {{ t('auth.forgot_password') }}
                            </Link>
                            
                            <Link 
                                :href="route('register')"
                                class="text-secondary text-decoration-none text-body-2"
                                data-cy="register-link"
                            >
                                {{ t('auth.no_account') }}
                            </Link>
                        </div>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </GuestLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AuthForm from '@/Components/AuthForm.vue';
import { useLogStore } from '@/stores/log';

const { t } = useI18n();

const params = new URLSearchParams(window.location.search);
const redirectUrl = ref(params.get('redirect') || null);

const logStore = useLogStore();
logStore.log('💪 🌞 Login page loaded 🌈 ❤️');
</script>
