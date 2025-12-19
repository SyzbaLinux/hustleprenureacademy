<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Events & Courses</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage events and courses</p>
        </div>
        <Link
          :href="route('admin.events.create')"
          class="px-4 py-2 bg-gradient-to-r from-[#b30d4f] to-[#8b0a3d] text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-200 flex items-center gap-2"
        >
          <i class="fas fa-plus"></i>
          Add Event/Course
        </Link>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Total</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total }}</p>
            </div>
            <div class="w-12 h-12 bg-[#b30d4f]/10 dark:bg-[#b30d4f]/20 rounded-lg flex items-center justify-center">
              <i class="fas fa-calendar-alt text-[#b30d4f] dark:text-[#e0156b] text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Events</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.events }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-calendar-day text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Courses</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.courses }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-graduation-cap text-purple-600 dark:text-purple-400 text-xl"></i>
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
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Enrollments</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.enrollments }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-user-graduate text-amber-600 dark:text-amber-400 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
          <!-- Search -->
          <div class="lg:col-span-2">
            <div class="relative">
              <input
                v-model="searchQuery"
                @input="handleSearch"
                type="text"
                placeholder="Search events/courses..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              />
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
          </div>

          <!-- Type Filter -->
          <div>
            <select
              v-model="typeFilter"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            >
              <option value="all">All Types</option>
              <option value="event">Events</option>
              <option value="course">Courses</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <select
              v-model="statusFilter"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            >
              <option value="all">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <!-- Category Filter -->
          <div>
            <select
              v-model="categoryFilter"
              @change="applyFilters"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            >
              <option value="all">All Categories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <!-- Clear Filters -->
          <div>
            <button
              @click="clearFilters"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors"
            >
              <i class="fas fa-times mr-2"></i>
              Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Events Table -->
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-table text-[#b30d4f] dark:text-[#e0156b]"></i>
            Events & Courses List
          </h2>
        </div>

        <div v-if="events.data.length > 0" class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Event/Course</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Instructors</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Enrollments</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="event in events.data" :key="event.id" class="hover:bg-gray-50 dark:hover:bg-slate-900/50">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div v-if="event.flier" class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0">
                      <img :src="`/storage/${event.flier}`" :alt="event.title" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="w-12 h-12 rounded-lg bg-gradient-to-br from-[#b30d4f] to-[#8b0a3d] flex items-center justify-center flex-shrink-0">
                      <i class="fas fa-calendar-alt text-white"></i>
                    </div>
                    <div class="min-w-0">
                      <p class="font-medium text-gray-900 dark:text-white truncate">{{ event.title }}</p>
                      <p v-if="event.short_description" class="text-sm text-gray-600 dark:text-gray-400 truncate">
                        {{ event.short_description }}
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      event.type === 'course'
                        ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200'
                        : 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200'
                    ]"
                  >
                    <i :class="`fas ${event.type === 'course' ? 'fa-graduation-cap' : 'fa-calendar-day'} mr-1`"></i>
                    {{ event.type }}
                  </span>
                  <span v-if="event.is_featured" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200">
                    <i class="fas fa-star mr-1"></i>
                    Featured
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span v-if="event.category" class="text-gray-900 dark:text-white">{{ event.category.name }}</span>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4">
                  <div v-if="event.instructors && event.instructors.length > 0" class="flex flex-wrap gap-1">
                    <span
                      v-for="instructor in event.instructors.slice(0, 2)"
                      :key="instructor.id"
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300"
                    >
                      {{ instructor.name }}
                    </span>
                    <span v-if="event.instructors.length > 2" class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300">
                      +{{ event.instructors.length - 2 }}
                    </span>
                  </div>
                  <span v-else class="text-sm text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                  {{ event.currency }} {{ event.amount }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      event.is_active
                        ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
                        : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'
                    ]"
                  >
                    <i :class="`fas ${event.is_active ? 'fa-check-circle' : 'fa-times-circle'} mr-1`"></i>
                    {{ event.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                  {{ event.enrollments_count || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('admin.events.edit', event.id)"
                      class="p-2 text-[#b30d4f] dark:text-[#e0156b] hover:bg-[#b30d4f]/10 dark:hover:bg-[#b30d4f]/20 rounded-lg transition-colors"
                      title="Edit"
                    >
                      <i class="fas fa-edit"></i>
                    </Link>
                    <button
                      @click="deleteEvent(event)"
                      class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                      :disabled="event.enrollments_count > 0"
                      :class="{ 'opacity-50 cursor-not-allowed': event.enrollments_count > 0 }"
                      :title="event.enrollments_count > 0 ? 'Cannot delete with enrollments' : 'Delete'"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="events.links.length > 3" class="p-6 border-t border-gray-200 dark:border-slate-700">
            <nav class="flex justify-center gap-2">
              <Link
                v-for="(link, index) in events.links"
                :key="index"
                :href="link.url || '#'"
                :class="[
                  'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                  link.active
                    ? 'bg-[#b30d4f] text-white'
                    : link.url
                    ? 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'
                    : 'bg-gray-100 dark:bg-slate-900 text-gray-400 dark:text-gray-600 cursor-not-allowed'
                ]"
                v-html="link.label"
              />
            </nav>
          </div>
        </div>

        <div v-else class="p-12 text-center">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-slate-700 mb-4">
            <i class="fas fa-calendar-alt text-gray-400 dark:text-gray-500 text-2xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No events or courses found</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">Try adjusting your filters or add a new event</p>
          <Link
            :href="route('admin.events.create')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#b30d4f] to-[#8b0a3d] text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-200"
          >
            <i class="fas fa-plus"></i>
            Add Event/Course
          </Link>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Category {
  id: number;
  name: string;
  icon?: string;
}

interface Instructor {
  id: number;
  name: string;
}

interface Event {
  id: number;
  title: string;
  slug: string;
  type: 'event' | 'course';
  short_description?: string;
  description?: string;
  flier?: string;
  location?: string;
  location_type: 'physical' | 'online';
  meeting_link?: string;
  capacity?: number;
  amount: number;
  currency: string;
  duration_hours?: number;
  is_active: boolean;
  is_featured: boolean;
  category?: Category;
  instructors?: Instructor[];
  enrollments_count?: number;
}

interface PaginatedEvents {
  data: Event[];
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

interface Stats {
  total: number;
  events: number;
  courses: number;
  active: number;
  featured: number;
  enrollments: number;
}

interface Filters {
  search?: string;
  type: string;
  status: string;
  category_id: string;
  featured: string;
}

const props = defineProps<{
  events: PaginatedEvents;
  categories: Category[];
  stats: Stats;
  filters: Filters;
}>();

const searchQuery = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || 'all');
const statusFilter = ref(props.filters.status || 'all');
const categoryFilter = ref(props.filters.category_id || 'all');

// Custom debounce function
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const applyFilters = () => {
  router.get(route('admin.events.index'), {
    search: searchQuery.value,
    type: typeFilter.value,
    status: statusFilter.value,
    category_id: categoryFilter.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearFilters = () => {
  searchQuery.value = '';
  typeFilter.value = 'all';
  statusFilter.value = 'all';
  categoryFilter.value = 'all';
  router.get(route('admin.events.index'), {}, { preserveState: true });
};

const deleteEvent = (event: Event) => {
  if (event.enrollments_count && event.enrollments_count > 0) {
    alert('Cannot delete event/course with enrollments. Please remove or refund them first.');
    return;
  }

  if (confirm(`Are you sure you want to delete "${event.title}"?`)) {
    router.delete(route('admin.events.destroy', event.id));
  }
};
</script>
