<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Categories</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage event and course categories</p>
        </div>
        <Link
          :href="route('admin.categories.create')"
          class="px-4 py-2 bg-gradient-to-r from-[#b30d4f] to-[#8b0a3d] text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-200 flex items-center gap-2"
        >
          <i class="fas fa-plus"></i>
          Create Category
        </Link>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Total Categories</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total }}</p>
            </div>
            <div class="w-12 h-12 bg-[#b30d4f]/10 dark:bg-[#b30d4f]/20 rounded-lg flex items-center justify-center">
              <i class="fas fa-folder text-[#b30d4f] dark:text-[#e0156b] text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Active</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.active }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Event Categories</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.events }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-calendar text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Course Categories</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.courses }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-book text-purple-600 dark:text-purple-400 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Categories Table -->
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-list text-[#b30d4f] dark:text-[#e0156b]"></i>
            All Categories
          </h2>
        </div>

        <div v-if="categories.length > 0" class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-700/50 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Items</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Display Order</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-[#b30d4f]/10 dark:bg-[#b30d4f]/20 flex items-center justify-center">
                      <i :class="`${category.icon || 'fas fa-folder'} text-[#b30d4f] dark:text-[#e0156b]`"></i>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900 dark:text-white">{{ category.name }}</p>
                      <p v-if="category.description" class="text-xs text-gray-600 dark:text-gray-400">{{ category.description?.substring(0, 50) }}...</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="category.type === 'event'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200">
                    <i class="fas fa-calendar mr-1"></i>
                    Event
                  </span>
                  <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200">
                    <i class="fas fa-book mr-1"></i>
                    Course
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-200">
                    {{ category.events_count || 0 }} items
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="category.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                    <i class="fas fa-check-circle mr-1"></i>
                    Active
                  </span>
                  <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-200">
                    <i class="fas fa-times-circle mr-1"></i>
                    Inactive
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                  {{ category.display_order }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('admin.categories.edit', category.id)"
                      class="text-[#b30d4f] dark:text-[#e0156b] hover:text-[#8b0a3d] dark:hover:text-[#b30d4f] transition-colors"
                    >
                      <i class="fas fa-edit"></i>
                    </Link>
                    <button
                      @click="deleteCategory(category)"
                      class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 transition-colors"
                      :disabled="category.events_count > 0"
                      :class="{ 'opacity-50 cursor-not-allowed': category.events_count > 0 }"
                      :title="category.events_count > 0 ? 'Cannot delete category with events' : 'Delete category'"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="p-12 text-center">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-slate-700 mb-4">
            <i class="fas fa-folder text-gray-400 dark:text-gray-500 text-2xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No categories yet</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">Get started by creating your first category</p>
          <Link
            :href="route('admin.categories.create')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#b30d4f] to-[#8b0a3d] text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-200"
          >
            <i class="fas fa-plus"></i>
            Create Category
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Category {
  id: number;
  name: string;
  slug: string;
  type: 'event' | 'course';
  description?: string;
  icon?: string;
  is_active: boolean;
  display_order: number;
  events_count?: number;
}

interface Stats {
  total: number;
  active: number;
  events: number;
  courses: number;
}

defineProps<{
  categories: Category[];
  stats: Stats;
}>();

const deleteCategory = (category: Category) => {
  if (category.events_count && category.events_count > 0) {
    alert('Cannot delete category with existing events/courses. Please remove or reassign them first.');
    return;
  }

  if (confirm(`Are you sure you want to delete "${category.name}"?`)) {
    router.delete(route('admin.categories.destroy', category.id));
  }
};
</script>
