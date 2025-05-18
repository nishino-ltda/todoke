<template>
    <GuestLayout>
        <div data-test="auth-login">
            <h1>Login</h1>
            <p>Login to your account.</p>
            <AuthForm mode="login" />
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AuthForm from '@/components/AuthForm.vue';
</script>
