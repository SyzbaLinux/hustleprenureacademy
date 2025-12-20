<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-4 flex-1">
          <Link
            :href="route('admin.events.index')"
            class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors mt-1"
          >
            <i class="fas fa-arrow-left"></i>
          </Link>
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ event.title }}</h1>
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-semibold',
                  event.type === 'course'
                    ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300'
                    : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'
                ]"
              >
                <i :class="event.type === 'course' ? 'fas fa-graduation-cap' : 'fas fa-calendar-day'" class="mr-1"></i>
                {{ event.type === 'course' ? 'Course' : 'Event' }}
              </span>
              <span
                v-if="event.is_featured"
                class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300"
              >
                <i class="fas fa-star mr-1"></i>
                Featured
              </span>
            </div>
            <p class="text-gray-600 dark:text-gray-400">{{ event.short_description || event.category?.name }}</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2">
          <Link
            :href="route('admin.events.edit', event.id)"
            class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-600 transition-colors"
          >
            <i class="fas fa-edit mr-2"></i>
            Edit
          </Link>
          <button
            @click="confirmDelete"
            class="px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors"
          >
            <i class="fas fa-trash mr-2"></i>
            Delete
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 dark:border-slate-700">
        <nav class="flex gap-2 overflow-x-auto">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'px-4 py-3 font-medium text-sm whitespace-nowrap border-b-2 transition-colors',
              activeTab === tab.id
                ? 'border-[#b30d4f] text-[#b30d4f] dark:border-[#e0156b] dark:text-[#e0156b]'
                : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-slate-600'
            ]"
          >
            <i :class="tab.icon" class="mr-2"></i>
            {{ tab.label }}
            <span v-if="tab.count !== undefined" class="ml-2 px-2 py-0.5 rounded-full text-xs bg-gray-100 dark:bg-slate-700">
              {{ tab.count }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div>
        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview'" class="space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Details Card -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6 space-y-6">
              <!-- Cover Image -->
              <div v-if="event.flier" class="w-full h-64 rounded-lg overflow-hidden border border-gray-200 dark:border-slate-600">
                <img :src="`/storage/${event.flier}`" :alt="event.title" class="w-full h-full object-cover" />
              </div>

              <!-- Description -->
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Description</h3>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ event.description }}</p>
              </div>

              <!-- Location -->
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Location</h3>
                <div class="flex items-center gap-3">
                  <div class="p-3 rounded-lg bg-gray-100 dark:bg-slate-700">
                    <i :class="event.location_type === 'physical' ? 'fas fa-map-marker-alt' : 'fas fa-video'" class="text-[#b30d4f] dark:text-[#e0156b]"></i>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">
                      {{ event.location_type === 'physical' ? 'Physical Location' : 'Online/Virtual' }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                      {{ event.location_type === 'physical' ? event.location : event.meeting_link }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Instructors -->
              <div v-if="event.instructors && event.instructors.length > 0">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Instructors</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div
                    v-for="instructor in event.instructors"
                    :key="instructor.id"
                    class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50"
                  >
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#b30d4f] to-[#8b0a3d] flex items-center justify-center text-white font-semibold">
                      {{ instructor.name.charAt(0) }}
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">{{ instructor.name }}</p>
                      <p v-if="instructor.specialization" class="text-xs text-gray-600 dark:text-gray-400">{{ instructor.specialization }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
              <!-- Quick Stats -->
              <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Details</h3>
                <div class="space-y-4">
                  <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Category</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ event.category?.name || 'N/A' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Price</p>
                    <p class="text-2xl font-bold text-[#b30d4f] dark:text-[#e0156b]">
                      {{ event.currency }} {{ typeof event.amount === 'number' ? event.amount.toFixed(2) : event.amount }}
                    </p>
                  </div>
                  <div v-if="event.capacity">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Capacity</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ event.capacity }} participants</p>
                  </div>
                  <div v-if="event.duration_hours">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Duration</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ event.duration_hours }} hours</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Status</p>
                    <span
                      :class="[
                        'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold',
                        event.is_active
                          ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                          : 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-300'
                      ]"
                    >
                      {{ event.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Schedules Tab -->
        <div v-if="activeTab === 'schedules'" class="space-y-4">
          <div class="flex items-center justify-between">
            <p class="text-gray-600 dark:text-gray-400">
              Manage {{ event.type === 'course' ? 'course sessions' : 'event dates' }}
            </p>
            <button
              @click="showScheduleModal = true"
              class="px-4 py-2 bg-gradient-to-r from-[#b30d4f] to-[#8b0a3d] text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-200"
            >
              <i class="fas fa-plus mr-2"></i>
              Add Schedule
            </button>
          </div>

          <!-- Schedules List -->
          <div v-if="schedulesList.length > 0" class="space-y-3">
            <div
              v-for="schedule in schedulesList"
              :key="schedule.id"
              class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-4"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4 flex-1">
                  <div class="p-3 rounded-lg bg-gray-100 dark:bg-slate-700">
                    <i class="fas fa-calendar-alt text-[#b30d4f] dark:text-[#e0156b]"></i>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                      <h4 class="font-semibold text-gray-900 dark:text-white">
                        Session {{ schedule.session_number }}: {{ schedule.title }}
                      </h4>
                      <span
                        v-if="schedule.is_completed"
                        class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300"
                      >
                        Completed
                      </span>
                    </div>
                    <p v-if="schedule.description" class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ schedule.description }}</p>
                    <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                      <span>
                        <i class="fas fa-calendar mr-1"></i>
                        {{ formatDate(schedule.start_date) }}
                      </span>
                      <span>
                        <i class="fas fa-clock mr-1"></i>
                        {{ schedule.start_time }} - {{ schedule.end_time }}
                      </span>
                    </div>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <button
                    @click="editSchedule(schedule)"
                    class="p-2 text-gray-600 dark:text-gray-400 hover:text-[#b30d4f] dark:hover:text-[#e0156b] hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                    title="Edit"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    @click="confirmDeleteSchedule(schedule)"
                    class="p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                    title="Delete"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-12 text-center">
            <i class="fas fa-calendar-times text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <p class="text-gray-600 dark:text-gray-400 mb-4">No schedules added yet</p>
            <button
              @click="showScheduleModal = true"
              class="px-6 py-2 bg-gradient-to-r from-[#b30d4f] to-[#8b0a3d] text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-200"
            >
              <i class="fas fa-plus mr-2"></i>
              Add Your First Schedule
            </button>
          </div>
        </div>

        <!-- Enrollments Tab -->
        <div v-if="activeTab === 'enrollments'" class="space-y-4">
          <div v-if="event.enrollments && event.enrollments.length > 0" class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 overflow-hidden">
            <table class="w-full">
              <thead class="bg-gray-50 dark:bg-slate-700">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Enrolled Date</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                <tr v-for="enrollment in event.enrollments" :key="enrollment.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ enrollment.user?.name || 'N/A' }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ enrollment.user?.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'px-3 py-1 rounded-full text-xs font-semibold',
                        enrollment.status === 'confirmed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' :
                        enrollment.status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300' :
                        'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-300'
                      ]"
                    >
                      {{ enrollment.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                    {{ formatDate(enrollment.created_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-12 text-center">
            <i class="fas fa-users-slash text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <p class="text-gray-600 dark:text-gray-400">No enrollments yet</p>
          </div>
        </div>

        <!-- Statistics Tab -->
        <div v-if="activeTab === 'stats'" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Enrollments</p>
                <i class="fas fa-users text-blue-500"></i>
              </div>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ event.enrollments?.length || 0 }}</p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-600 dark:text-gray-400">Sessions</p>
                <i class="fas fa-calendar-check text-purple-500"></i>
              </div>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ schedulesList.length || 0 }}</p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-600 dark:text-gray-400">Instructors</p>
                <i class="fas fa-chalkboard-teacher text-green-500"></i>
              </div>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ event.instructors?.length || 0 }}</p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6">
              <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-600 dark:text-gray-400">Capacity Used</p>
                <i class="fas fa-percentage text-orange-500"></i>
              </div>
              <p class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ event.capacity ? Math.round(((event.enrollments?.length || 0) / event.capacity) * 100) : 0 }}%
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Schedule Modal -->
    <AddScheduleModal
      :show="showScheduleModal"
      :event-id="event.id"
      :schedule="editingSchedule"
      @close="closeScheduleModal"
      @saved="handleScheduleSaved"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AddScheduleModal from '@/Components/AddScheduleModal.vue';
import axios from 'axios';

interface Category {
  id: number;
  name: string;
}

interface Instructor {
  id: number;
  name: string;
  email?: string;
  specialization?: string;
}

interface Schedule {
  id: number;
  event_id: number;
  session_number: number;
  title: string;
  description?: string;
  start_date: string;
  start_time: string;
  end_time: string;
  is_completed: boolean;
}

interface Enrollment {
  id: number;
  user?: {
    name: string;
    email: string;
  };
  status: string;
  created_at: string;
}

interface Event {
  id: number;
  title: string;
  type: 'event' | 'course';
  category?: Category;
  short_description?: string;
  description: string;
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
  instructors?: Instructor[];
  schedules?: Schedule[];
  enrollments?: Enrollment[];
}

const props = defineProps<{
  event: Event;
}>();

const activeTab = ref('overview');
const showScheduleModal = ref(false);
const editingSchedule = ref<Schedule | null>(null);
const schedulesList = ref<Schedule[]>([...(props.event.schedules || [])]);

const tabs = computed(() => [
  { id: 'overview', label: 'Overview', icon: 'fas fa-info-circle' },
  { id: 'schedules', label: props.event.type === 'course' ? 'Sessions' : 'Dates', icon: 'fas fa-calendar-alt', count: schedulesList.value.length },
  { id: 'enrollments', label: 'Enrollments', icon: 'fas fa-users', count: props.event.enrollments?.length || 0 },
  { id: 'stats', label: 'Statistics', icon: 'fas fa-chart-bar' },
]);

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const editSchedule = (schedule: Schedule) => {
  editingSchedule.value = schedule;
  showScheduleModal.value = true;
};

const closeScheduleModal = () => {
  showScheduleModal.value = false;
  editingSchedule.value = null;
};

const handleScheduleSaved = (schedule: Schedule) => {
  if (editingSchedule.value) {
    const index = schedulesList.value.findIndex(s => s.id === schedule.id);
    if (index !== -1) {
      schedulesList.value[index] = schedule;
    }
  } else {
    schedulesList.value.push(schedule);
  }
  schedulesList.value.sort((a, b) => a.session_number - b.session_number);
};

const confirmDeleteSchedule = async (schedule: Schedule) => {
  if (!confirm(`Are you sure you want to delete "${schedule.title}"?`)) {
    return;
  }

  try {
    await axios.delete(`/admin/events/${props.event.id}/schedules/${schedule.id}`);
    schedulesList.value = schedulesList.value.filter(s => s.id !== schedule.id);
  } catch (error: any) {
    alert(error.response?.data?.message || 'An error occurred while deleting the schedule');
  }
};

const confirmDelete = () => {
  if (confirm(`Are you sure you want to delete "${props.event.title}"?`)) {
    router.delete(route('admin.events.destroy', props.event.id));
  }
};
</script>
