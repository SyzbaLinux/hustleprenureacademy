<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Instructors</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage instructors and facilitators</p>
        </div>
        <Link
          :href="route('admin.instructors.create')"
          class="px-4 py-2 bg-gradient-to-r from-[#b30d4f] to-[#8b0a3d] text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-200 flex items-center gap-2"
        >
          <i class="fas fa-plus"></i>
          Add Instructor
        </Link>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Total Instructors</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total }}</p>
            </div>
            <div class="w-12 h-12 bg-[#b30d4f]/10 dark:bg-[#b30d4f]/20 rounded-lg flex items-center justify-center">
              <i class="fas fa-chalkboard-teacher text-[#b30d4f] dark:text-[#e0156b] text-xl"></i>
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
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Inactive</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.inactive }}</p>
            </div>
            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-times-circle text-gray-600 dark:text-gray-400 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Instructors Grid/Table -->
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-users text-[#b30d4f] dark:text-[#e0156b]"></i>
            All Instructors
          </h2>
        </div>

        <div v-if="instructors.length > 0" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="instructor in instructors"
              :key="instructor.id"
              class="bg-white dark:bg-slate-900 rounded-lg border border-gray-200 dark:border-slate-700 p-6 hover:shadow-lg transition-all duration-200 hover:border-[#b30d4f] dark:hover:border-[#e0156b]"
            >
              <!-- Profile -->
              <div class="flex items-start gap-4 mb-4">
                <div v-if="instructor.profile_photo" class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0">
                  <img :src="`/storage/${instructor.profile_photo}`" :alt="instructor.name" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-16 h-16 rounded-full bg-gradient-to-br from-[#b30d4f] to-[#8b0a3d] flex items-center justify-center text-white text-xl font-bold flex-shrink-0">
                  {{ instructor.name.charAt(0).toUpperCase() }}
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">{{ instructor.name }}</h3>
                  <p v-if="instructor.specialization" class="text-sm text-[#b30d4f] dark:text-[#e0156b] truncate">{{ instructor.specialization }}</p>
                </div>
              </div>

              <!-- Contact Info -->
              <div class="space-y-2 mb-4">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                  <i class="fas fa-envelope w-4"></i>
                  <span class="truncate">{{ instructor.email }}</span>
                </div>
                <div v-if="instructor.phone_number" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                  <i class="fas fa-phone w-4"></i>
                  <span>{{ instructor.phone_number }}</span>
                </div>
              </div>

              <!-- Bio -->
              <p v-if="instructor.bio" class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                {{ instructor.bio }}
              </p>

              <!-- Stats & Actions -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-slate-700">
                <div class="flex items-center gap-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ instructor.events_count || 0 }} events
                  </span>
                  <span v-if="instructor.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                    <i class="fas fa-check-circle mr-1"></i>
                    Active
                  </span>
                  <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-200">
                    <i class="fas fa-times-circle mr-1"></i>
                    Inactive
                  </span>
                </div>

                <div class="flex items-center gap-2">
                  <Link
                    :href="route('admin.instructors.edit', instructor.id)"
                    class="p-2 text-[#b30d4f] dark:text-[#e0156b] hover:bg-[#b30d4f]/10 dark:hover:bg-[#b30d4f]/20 rounded-lg transition-colors"
                  >
                    <i class="fas fa-edit"></i>
                  </Link>
                  <button
                    @click="deleteInstructor(instructor)"
                    class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                    :disabled="instructor.events_count > 0"
                    :class="{ 'opacity-50 cursor-not-allowed': instructor.events_count > 0 }"
                    :title="instructor.events_count > 0 ? 'Cannot delete instructor with assigned events' : 'Delete instructor'"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="p-12 text-center">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-slate-700 mb-4">
            <i class="fas fa-chalkboard-teacher text-gray-400 dark:text-gray-500 text-2xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No instructors yet</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">Get started by adding your first instructor</p>
          <Link
            :href="route('admin.instructors.create')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#b30d4f] to-[#8b0a3d] text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-200"
          >
            <i class="fas fa-plus"></i>
            Add Instructor
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Instructor {
  id: number;
  name: string;
  email: string;
  phone_number?: string;
  bio?: string;
  profile_photo?: string;
  specialization?: string;
  is_active: boolean;
  events_count?: number;
}

interface Stats {
  total: number;
  active: number;
  inactive: number;
}

defineProps<{
  instructors: Instructor[];
  stats: Stats;
}>();

const deleteInstructor = (instructor: Instructor) => {
  if (instructor.events_count && instructor.events_count > 0) {
    alert('Cannot delete instructor with assigned events/courses. Please remove or reassign them first.');
    return;
  }

  if (confirm(`Are you sure you want to delete "${instructor.name}"?`)) {
    router.delete(route('admin.instructors.destroy', instructor.id));
  }
};
</script>
