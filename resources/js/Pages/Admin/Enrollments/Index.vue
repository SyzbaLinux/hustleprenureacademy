<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Enrollments</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">View and manage event enrollments</p>
        </div>
        <Link
          :href="route('admin.enrollments.create')"
          class="px-4 py-2 bg-gradient-to-r from-[#b30d4f] to-[#8b0a3d] text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-200 flex items-center gap-2"
        >
          <i class="fas fa-user-check"></i>
          Add Enrollment
        </Link>
      </div>

      <!-- Filter Tabs -->
      <div class="flex gap-2">
        <button
          @click="filterByStatus('all')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === 'all'
              ? 'bg-[#b30d4f] text-white'
              : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'
          ]"
        >
          <i class="fas fa-list mr-2"></i>
          All ({{ stats.total }})
        </button>
        <button
          @click="filterByStatus('confirmed')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === 'confirmed'
              ? 'bg-[#b30d4f] text-white'
              : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'
          ]"
        >
          <i class="fas fa-check-circle mr-2"></i>
          Confirmed ({{ stats.confirmed }})
        </button>
        <button
          @click="filterByStatus('pending')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === 'pending'
              ? 'bg-[#b30d4f] text-white'
              : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'
          ]"
        >
          <i class="fas fa-clock mr-2"></i>
          Pending ({{ stats.pending }})
        </button>
        <button
          @click="filterByStatus('cancelled')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === 'cancelled'
              ? 'bg-[#b30d4f] text-white'
              : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'
          ]"
        >
          <i class="fas fa-times-circle mr-2"></i>
          Cancelled ({{ stats.cancelled }})
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Total Enrollments</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total }}</p>
            </div>
            <div class="w-12 h-12 bg-[#b30d4f]/10 dark:bg-[#b30d4f]/20 rounded-lg flex items-center justify-center">
              <i class="fas fa-user-graduate text-[#b30d4f] dark:text-[#e0156b] text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Confirmed</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.confirmed }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Pending</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.pending }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-clock text-amber-600 dark:text-amber-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Completed</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.completed }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-certificate text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Enrollments Table -->
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-list text-[#b30d4f] dark:text-[#e0156b]"></i>
            All Enrollments
          </h2>
        </div>

        <div v-if="enrollments.data.length > 0" class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Participant</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Event/Course</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Payment</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Enrolled</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="enrollment in enrollments.data" :key="enrollment.id" class="hover:bg-gray-50 dark:hover:bg-slate-900/50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#b30d4f] to-[#8b0a3d] flex items-center justify-center text-white font-semibold flex-shrink-0">
                      {{ (enrollment.user?.name || enrollment.full_name || 'G').charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">{{ enrollment.user?.name || enrollment.full_name || 'Guest' }}</p>
                      <p v-if="enrollment.email" class="text-xs text-gray-600 dark:text-gray-400">{{ enrollment.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ enrollment.event?.title || 'N/A' }}</p>
                  <p v-if="enrollment.event?.type" class="text-xs text-gray-600 dark:text-gray-400 capitalize">{{ enrollment.event.type }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                  {{ enrollment.phone_number }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      enrollment.status === 'confirmed'
                        ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
                        : enrollment.status === 'pending'
                        ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200'
                        : enrollment.status === 'cancelled'
                        ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'
                        : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-200'
                    ]"
                  >
                    <i :class="`fas ${getStatusIcon(enrollment.status)} mr-1`"></i>
                    {{ enrollment.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    v-if="enrollment.payment"
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      enrollment.payment.status === 'paid'
                        ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
                        : 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200'
                    ]"
                  >
                    <i :class="`fas ${enrollment.payment.status === 'paid' ? 'fa-check-circle' : 'fa-clock'} mr-1`"></i>
                    {{ enrollment.payment.status }}
                  </span>
                  <span v-else class="text-xs text-gray-600 dark:text-gray-400">No payment</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                  {{ formatDate(enrollment.enrollment_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('admin.enrollments.show', enrollment.id)"
                      class="p-2 text-[#b30d4f] dark:text-[#e0156b] hover:bg-[#b30d4f]/10 dark:hover:bg-[#b30d4f]/20 rounded-lg transition-colors"
                      title="View details"
                    >
                      <i class="fas fa-eye"></i>
                    </Link>
                    <button
                      @click="deleteEnrollment(enrollment)"
                      class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                      :disabled="enrollment.status === 'confirmed'"
                      :class="{ 'opacity-50 cursor-not-allowed': enrollment.status === 'confirmed' }"
                      :title="enrollment.status === 'confirmed' ? 'Cannot delete confirmed enrollment' : 'Delete enrollment'"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="enrollments.links.length > 3" class="p-6 border-t border-gray-200 dark:border-slate-700">
            <nav class="flex justify-center gap-2">
              <Link
                v-for="(link, index) in enrollments.links"
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
            <i class="fas fa-user-graduate text-gray-400 dark:text-gray-500 text-2xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No enrollments found</h3>
          <p class="text-gray-600 dark:text-gray-400">No enrollments match the current filter</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Event {
  id: number;
  title: string;
  type: 'event' | 'course';
}

interface User {
  id: number;
  name: string;
}

interface Payment {
  id: number;
  status: string;
}

interface Enrollment {
  id: number;
  full_name?: string;
  email?: string;
  phone_number: string;
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
  enrollment_date: string;
  completion_date?: string;
  event?: Event;
  user?: User;
  payment?: Payment;
}

interface PaginatedEnrollments {
  data: Enrollment[];
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

interface Stats {
  total: number;
  confirmed: number;
  pending: number;
  cancelled: number;
  completed: number;
}

const props = defineProps<{
  enrollments: PaginatedEnrollments;
  stats: Stats;
  currentFilter?: string;
}>();

const currentFilter = ref(props.currentFilter || 'all');

const filterByStatus = (status: string) => {
  currentFilter.value = status;
  router.get(route('admin.enrollments.index'), { status }, { preserveState: true });
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'confirmed':
      return 'fa-check-circle';
    case 'pending':
      return 'fa-clock';
    case 'cancelled':
      return 'fa-times-circle';
    case 'completed':
      return 'fa-certificate';
    default:
      return 'fa-circle';
  }
};

const deleteEnrollment = (enrollment: Enrollment) => {
  if (enrollment.status === 'confirmed') {
    alert('Cannot delete confirmed enrollment!');
    return;
  }

  if (confirm(`Are you sure you want to delete this enrollment?`)) {
    router.delete(route('admin.enrollments.destroy', enrollment.id));
  }
};
</script>
