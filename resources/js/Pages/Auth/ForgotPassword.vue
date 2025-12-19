<template>
  <GuestLayout>
    <AuthCard
      title="Reset Password"
      subtitle="We'll send you a reset link"
    >
      <form @submit.prevent="submitForgotPassword" class="space-y-4">
        <!-- Info message -->
        <div v-if="status" class="p-4 bg-pink-50 dark:bg-[#b30d4f]/30 border border-pink-200 dark:border-[#b30d4f]/50 rounded-lg">
          <p class="text-sm text-[#8b0a3d] dark:text-[#b30d4f] flex items-start gap-2">
            <i class="fas fa-check-circle mt-0.5 flex-shrink-0"></i>
            <span>{{ status }}</span>
          </p>
        </div>

        <!-- Email field -->
        <FormInput
          v-model="form.email"
          type="email"
          label="Email Address"
          id="email"
          icon="fa-envelope"
          placeholder="your@email.com"
          required
          autocomplete="email"
          :error="form.errors.email"
        />

        <!-- Submit button -->
        <AuthButton
          type="submit"
          label="Send Reset Link"
          :loading="form.processing"
          loadingText="Sending..."
          icon="fa-paper-plane"
        />
      </form>

      <!-- Back to login link -->
      <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
        Remember your password?
        <Link
          :href="route('login')"
          class="font-medium text-[#b30d4f] dark:text-[#e0156b] hover:text-[#8b0a3d] dark:hover:text-[#b30d4f] transition-colors"
        >
          Sign in
        </Link>
      </div>
    </AuthCard>
  </GuestLayout>
</template>

<script setup lang="ts">
import { defineProps } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AuthCard from '@/Components/Auth/AuthCard.vue';
import FormInput from '@/Components/Auth/FormInput.vue';
import AuthButton from '@/Components/Auth/AuthButton.vue';

interface Props {
  status?: string;
}

defineProps<Props>();

const form = useForm({
  email: '',
});

const submitForgotPassword = () => {
  form.post(route('password.email'), {
    preserveScroll: true,
  });
};
</script>

<style scoped>
/* Component-specific styles */
</style>
