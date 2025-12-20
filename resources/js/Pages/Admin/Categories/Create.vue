<template>
  <AdminLayout>
    <div class="max-w-3xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.categories.index')"
          class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
        >
          <i class="fas fa-arrow-left"></i>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Category</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Add a new category for events and courses</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6 space-y-6">
        <!-- Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Category Name <span class="text-red-500">*</span>
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            placeholder="e.g., Business, Technology, Marketing"
          />
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
        </div>

        <!-- Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Category Type <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 gap-3">
            <button
              type="button"
              @click="form.type = 'event'"
              :class="[
                'p-2.5 rounded-lg border-2 transition-all',
                form.type === 'event'
                  ? 'border-[#b30d4f] bg-[#b30d4f]/5 dark:bg-[#b30d4f]/10'
                  : 'border-gray-200 dark:border-slate-600 hover:border-[#b30d4f]/50'
              ]"
            >
              <i class="fas fa-calendar text-lg mb-1" :class="form.type === 'event' ? 'text-blue-600' : 'text-gray-400'"></i>
              <p class="text-sm font-semibold text-gray-900 dark:text-white">Event</p>
              <p class="text-xs text-gray-600 dark:text-gray-400">One-time events</p>
            </button>
            <button
              type="button"
              @click="form.type = 'course'"
              :class="[
                'p-2.5 rounded-lg border-2 transition-all',
                form.type === 'course'
                  ? 'border-[#b30d4f] bg-[#b30d4f]/5 dark:bg-[#b30d4f]/10'
                  : 'border-gray-200 dark:border-slate-600 hover:border-[#b30d4f]/50'
              ]"
            >
              <i class="fas fa-book text-lg mb-1" :class="form.type === 'course' ? 'text-purple-600' : 'text-gray-400'"></i>
              <p class="text-sm font-semibold text-gray-900 dark:text-white">Course</p>
              <p class="text-xs text-gray-600 dark:text-gray-400">Training courses</p>
            </button>
          </div>
          <p v-if="form.errors.type" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.type }}</p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
          <Link
            :href="route('admin.categories.index')"
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
              <i class="fas fa-save mr-2"></i>
              Create Category
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
  type: 'event' as 'event' | 'course',
  is_active: true,
});

const submit = () => {
  form.post(route('admin.categories.store'));
};
</script>
