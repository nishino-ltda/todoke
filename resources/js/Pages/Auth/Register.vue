<template>
    <GuestLayout>
        <div data-test="auth-register">
            <h1>Register</h1>
            <p>Create a new account.</p>
            
            <v-alert
                v-if="pendingApproval"
                type="info"
                data-test="pending-alert"
                class="mb-4"
            >
                Your account is pending approval. You'll receive an email when your account is activated.
            </v-alert>

            <AuthForm 
                mode="register" 
                @pending="pendingApproval = true"
            />
            
            <v-btn 
                variant="text"
                color="primary"
                to="/login"
                class="mt-4"
                data-test="login-link"
            >
                Already have an account? Login
            </v-btn>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AuthForm from '@/Components/AuthForm.vue';
import { useLogStore } from '@/stores/log';

const pendingApproval = ref(false);
const { t } = useI18n();

const logStore = useLogStore();
logStore.log('✨ Registration page loaded');
</script>
