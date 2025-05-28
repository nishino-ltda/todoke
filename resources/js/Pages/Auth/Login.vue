<template>
    <GuestLayout>
        <div data-test="auth-login">
            <h1>Login</h1>
            <p>Login to your account.</p>
            <AuthForm mode="login" />
            <v-btn 
                variant="text"
                color="primary"
                to="/forgot-password"
                class="mt-4"
                data-test="forgot-password-link"
            >
                {{ t('auth.forgot_password') }}
            </v-btn>
        </div>
    </GuestLayout>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AuthForm from '@/Components/AuthForm.vue';
import { useLogStore } from '@/stores/log';

const { t } = useI18n();

const logStore = useLogStore();
logStore.log('💪 🌞 Login page loaded 🌈 ❤️');
</script>
