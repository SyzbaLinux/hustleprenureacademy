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
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Category</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Update category information</p>
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
          <div class="grid grid-cols-2 gap-4">
            <button
              type="button"
              @click="form.type = 'event'"
              :class="[
                'p-4 rounded-lg border-2 transition-all',
                form.type === 'event'
                  ? 'border-[#b30d4f] bg-[#b30d4f]/5 dark:bg-[#b30d4f]/10'
                  : 'border-gray-200 dark:border-slate-600 hover:border-[#b30d4f]/50'
              ]"
            >
              <i class="fas fa-calendar text-2xl mb-2" :class="form.type === 'event' ? 'text-blue-600' : 'text-gray-400'"></i>
              <p class="font-semibold text-gray-900 dark:text-white">Event</p>
              <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">For one-time events</p>
            </button>
            <button
              type="button"
              @click="form.type = 'course'"
              :class="[
                'p-4 rounded-lg border-2 transition-all',
                form.type === 'course'
                  ? 'border-[#b30d4f] bg-[#b30d4f]/5 dark:bg-[#b30d4f]/10'
                  : 'border-gray-200 dark:border-slate-600 hover:border-[#b30d4f]/50'
              ]"
            >
              <i class="fas fa-book text-2xl mb-2" :class="form.type === 'course' ? 'text-purple-600' : 'text-gray-400'"></i>
              <p class="font-semibold text-gray-900 dark:text-white">Course</p>
              <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">For training courses</p>
            </button>
          </div>
          <p v-if="form.errors.type" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.type }}</p>
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Description
          </label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            placeholder="Brief description of this category..."
          ></textarea>
          <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.description }}</p>
        </div>

        <!-- Display Order -->
        <div>
          <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Display Order
          </label>
          <input
            id="display_order"
            v-model.number="form.display_order"
            type="number"
            min="0"
            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            placeholder="0"
          />
          <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Lower numbers appear first</p>
          <p v-if="form.errors.display_order" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.display_order }}</p>
        </div>

        <!-- Is Active -->
        <div class="flex items-center gap-3">
          <input
            id="is_active"
            v-model="form.is_active"
            type="checkbox"
            class="w-5 h-5 rounded border-gray-300 dark:border-slate-600 text-[#b30d4f] focus:ring-[#b30d4f] dark:focus:ring-[#e0156b]"
          />
          <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Active (visible to users)
          </label>
        </div>
        <p v-if="form.errors.is_active" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.is_active }}</p>

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
              Updating...
            </span>
            <span v-else>
              <i class="fas fa-save mr-2"></i>
              Update Category
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

interface Category {
  id: number;
  name: string;
  type: 'event' | 'course';
  description?: string;
  icon?: string;
  is_active: boolean;
  display_order: number;
}

const props = defineProps<{
  category: Category;
}>();

const form = useForm({
  name: props.category.name,
  type: props.category.type,
  description: props.category.description || '',
  is_active: props.category.is_active,
  display_order: props.category.display_order,
});

const submit = () => {
  form.put(route('admin.categories.update', props.category.id));
};
</script>
