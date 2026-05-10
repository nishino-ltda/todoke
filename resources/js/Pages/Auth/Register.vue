<template>
    <GuestLayout :minimal="true">
        <div data-cy="auth-register">
            <h1>Register</h1>
            <p>Create a new account.</p>

            <AuthForm 
                mode="register"
            />
            
            <v-btn 
                variant="text"
                color="primary"
                to="/login"
                class="mt-4"
                data-cy="login-link"
            >
                Already have an account? Login
            </v-btn>
        </div>
    </GuestLayout>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AuthForm from '@/Components/AuthForm.vue';

const { t } = useI18n();
</script>
