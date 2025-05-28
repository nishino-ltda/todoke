<template>
    <GuestLayout>
        <div data-test="auth-register">
            <h1>Register</h1>
            <p>Create a new account.</p>
            <AuthForm mode="register" />
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AuthForm from '@/Components/AuthForm.vue';
</script>
