<template>
  <AdminLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.users.index')"
          class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
        >
          <i class="fas fa-arrow-left"></i>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add User</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Create a new user account</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6 space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Full Name <span class="text-red-500">*</span>
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="John Doe"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Email Address <span class="text-red-500">*</span>
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="john@example.com"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
          </div>

          <!-- Phone Number -->
          <div>
            <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Phone Number
            </label>
            <input
              id="phone_number"
              v-model="form.phone_number"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="+263 77 123 4567"
            />
            <p v-if="form.errors.phone_number" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.phone_number }}</p>
          </div>

          <!-- Role -->
          <div>
            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Role <span class="text-red-500">*</span>
            </label>
            <select
              id="role"
              v-model="form.role"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            >
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
            <p v-if="form.errors.role" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.role }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Password <span class="text-red-500">*</span>
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="Min 8 characters"
            />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.password }}</p>
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Confirm Password <span class="text-red-500">*</span>
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="Re-type password"
            />
          </div>
        </div>

        <!-- Info Note -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
          <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5"></i>
            <div class="text-sm text-blue-800 dark:text-blue-200">
              <p class="font-medium mb-1">User will be automatically verified</p>
              <p>Admin-created users can log in immediately without email verification.</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
          <Link
            :href="route('admin.users.index')"
            class="px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-6 py-2 bg-gradient-to-r from-[#b30d4f] to-[#8b0a3d] text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
          >
            <span v-if="form.processing">
              <i class="fas fa-spinner fa-spin mr-2"></i>
              Creating...
            </span>
            <span v-else>
              <i class="fas fa-user-plus mr-2"></i>
              Create User
            </span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const form = useForm({
  name: '',
  email: '',
  phone_number: '',
  password: '',
  password_confirmation: '',
  role: 'user',
});

const submit = () => {
  form.post(route('admin.users.store'));
};
</script>
