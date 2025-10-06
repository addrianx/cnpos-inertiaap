<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <!-- Fullscreen Background dengan Centering -->
        <div class="login-fullscreen">
            <div class="login-center-container">
                <div class="login-content">
                    <!-- Header Section -->
                    <div class="text-center mb-5">
                        <div class="auth-logo mb-4">
                            <i class="fas fa-store"></i>
                        </div>
                        <h1 class="auth-title">Welcome Back</h1>
                        <p class="auth-subtitle">Sign in to your POS account</p>
                    </div>

                    <!-- Status Message -->
                    <div v-if="status" class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ status }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>

                    <!-- Login Form -->
                    <div class="auth-card">
                        <form @submit.prevent="submit" class="auth-form">
                            <!-- Email Input -->
                            <div class="form-group">
                                <InputLabel for="email" value="Email Address" class="form-label" />
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="form-control"
                                        v-model="form.email"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        placeholder="Enter your email"
                                    />
                                </div>
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <!-- Password Input -->
                            <div class="form-group">
                                <InputLabel for="password" value="Password" class="form-label" />
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <TextInput
                                        id="password"
                                        type="password"
                                        class="form-control"
                                        v-model="form.password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Enter your password"
                                    />
                                </div>
                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <label class="form-check-label remember-me">
                                    <Checkbox name="remember" v-model:checked="form.remember" />
                                    <span class="ms-2">Remember me</span>
                                </label>

                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="forgot-password"
                                >
                                    Forgot password?
                                </Link>
                            </div>

                            <!-- Submit Button -->
                            <PrimaryButton
                                class="auth-submit-btn w-100"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                                {{ form.processing ? 'Signing in...' : 'Sign In' }}
                            </PrimaryButton>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
/* Fullscreen Background */
.login-fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    overflow: hidden;
}

/* Perfect Centering Container */
.login-center-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    max-width: 480px;
    padding: 20px;
}

/* Lebarkan untuk layar besar */
@media (min-width: 1200px) {
    .login-center-container {
        max-width: 520px;
    }
}

@media (min-width: 1400px) {
    .login-center-container {
        max-width: 550px;
    }
}

/* Login Content */
.login-content {
    width: 100%;
}

.auth-logo {
    font-size: 3.5rem;
    color: #667eea;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    width: 90px;
    height: 90px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.auth-logo i {
    color: white;
}

.auth-title {
    color: #343a40;
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.auth-subtitle {
    color: #6c757d;
    font-size: 1rem;
    margin-bottom: 0;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.auth-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15), 0 10px 20px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}

.auth-form {
    width: 100%;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.input-group {
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.input-group input{
    height: 34px;
}

.input-group:focus-within {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.input-group-text {
    background: #f8f9fa;
    border: none;
    color: #667eea;
    padding: 0.75rem 1rem;
    min-width: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-control {
    border: none;
    padding: 0.75rem;
    font-size: 1rem;
    background: transparent;
}

.form-control:focus {
    box-shadow: none;
    background: transparent;
}

.form-control::placeholder {
    color: #6c757d;
    opacity: 0.7;
}

.remember-me {
    display: flex;
    align-items: center;
    color: #495057;
    font-size: 0.9rem;
    cursor: pointer;
}

.forgot-password {
    color: #667eea;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: color 0.3s ease;
}

.forgot-password:hover {
    color: #764ba2;
    text-decoration: underline;
}

.auth-submit-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    height: 50px;
    color: white;
}

.auth-submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.auth-submit-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.auth-footer-text {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0;
}

.auth-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.auth-link:hover {
    color: #764ba2;
    text-decoration: underline;
}

.alert {
    border-radius: 12px;
    border: none;
    padding: 1rem 1.25rem;
}

.alert-success {
    background: rgba(40, 167, 69, 0.1);
    color: #155724;
    border-left: 4px solid #28a745;
}

/* Responsive Design */
@media (max-width: 576px) {
    .login-center-container {
        padding: 15px;
        max-width: 100%;
    }
    
    .auth-card {
        padding: 2rem;
        border-radius: 15px;
    }
    
    .auth-title {
        font-size: 1.75rem;
    }
    
    .auth-logo {
        width: 80px;
        height: 80px;
        font-size: 3rem;
    }
    
    .form-control {
        font-size: 0.9rem;
    }
    
    .auth-submit-btn {
        height: 45px;
        font-size: 0.9rem;
    }
}

@media (max-width: 400px) {
    .auth-card {
        padding: 1.5rem;
    }
    
    .auth-title {
        font-size: 1.5rem;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .forgot-password {
        align-self: flex-end;
    }
}

/* Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate(-50%, -45%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

.login-center-container {
    animation: fadeInUp 0.6s ease-out;
}

/* Loading spinner */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Custom checkbox styling */
:deep(.border-gray-300) {
    border-radius: 6px;
}

:deep(.border-gray-300:focus) {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Background pattern overlay untuk efek depth */
.login-fullscreen::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
    opacity: 0.4;
}
</style>