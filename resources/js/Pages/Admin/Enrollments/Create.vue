<template>
  <AdminLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.enrollments.index')"
          class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
        >
          <i class="fas fa-arrow-left"></i>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add Enrollment</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Enroll a user to an event or course</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6 space-y-6">

        <!-- Event Selection -->
        <div>
          <label for="event_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Event/Course <span class="text-red-500">*</span>
          </label>
          <select
            id="event_id"
            v-model="form.event_id"
            required
            @change="onEventChange"
            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
          >
            <option value="">Select an event or course</option>
            <option v-for="event in events" :key="event.id" :value="event.id">
              {{ event.title }} ({{ event.type }}) - {{ event.currency }} {{ event.amount }}
            </option>
          </select>
          <p v-if="form.errors.event_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.event_id }}</p>
        </div>

        <!-- User Type Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Enrollment Type <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 gap-4">
            <button
              type="button"
              @click="enrollmentType = 'existing_user'"
              :class="[
                'p-4 rounded-lg border-2 transition-all',
                enrollmentType === 'existing_user'
                  ? 'border-[#b30d4f] bg-[#b30d4f]/5 dark:bg-[#b30d4f]/10'
                  : 'border-gray-200 dark:border-slate-600 hover:border-[#b30d4f]/50'
              ]"
            >
              <i class="fas fa-user text-2xl mb-2" :class="enrollmentType === 'existing_user' ? 'text-[#b30d4f]' : 'text-gray-400'"></i>
              <p class="font-semibold text-gray-900 dark:text-white">Existing User</p>
              <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Select from registered users</p>
            </button>
            <button
              type="button"
              @click="enrollmentType = 'guest'"
              :class="[
                'p-4 rounded-lg border-2 transition-all',
                enrollmentType === 'guest'
                  ? 'border-[#b30d4f] bg-[#b30d4f]/5 dark:bg-[#b30d4f]/10'
                  : 'border-gray-200 dark:border-slate-600 hover:border-[#b30d4f]/50'
              ]"
            >
              <i class="fas fa-user-plus text-2xl mb-2" :class="enrollmentType === 'guest' ? 'text-[#b30d4f]' : 'text-gray-400'"></i>
              <p class="font-semibold text-gray-900 dark:text-white">Guest User</p>
              <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Manual entry without account</p>
            </button>
          </div>
        </div>

        <!-- Existing User Selection -->
        <div v-if="enrollmentType === 'existing_user'">
          <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Select User <span class="text-red-500">*</span>
          </label>
          <select
            id="user_id"
            v-model="form.user_id"
            @change="onUserChange"
            :required="enrollmentType === 'existing_user'"
            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
          >
            <option value="">Select a user</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} ({{ user.email }})
            </option>
          </select>
          <p v-if="form.errors.user_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.user_id }}</p>
        </div>

        <!-- Guest User Details -->
        <div v-if="enrollmentType === 'guest'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Full Name <span class="text-red-500">*</span>
            </label>
            <input
              id="full_name"
              v-model="form.full_name"
              type="text"
              :required="enrollmentType === 'guest'"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="John Doe"
            />
            <p v-if="form.errors.full_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.full_name }}</p>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Email Address <span class="text-red-500">*</span>
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              :required="enrollmentType === 'guest'"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="john@example.com"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
          </div>
        </div>

        <!-- Phone Number (Required for all) -->
        <div>
          <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Phone Number <span class="text-red-500">*</span>
          </label>
          <input
            id="phone_number"
            v-model="form.phone_number"
            type="text"
            required
            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            placeholder="+263 77 123 4567"
          />
          <p v-if="form.errors.phone_number" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.phone_number }}</p>
        </div>

        <!-- Status -->
        <div>
          <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Status <span class="text-red-500">*</span>
          </label>
          <select
            id="status"
            v-model="form.status"
            required
            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
          >
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
          </select>
          <p v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.status }}</p>
        </div>

        <!-- Notes -->
        <div>
          <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Notes (Optional)
          </label>
          <textarea
            id="notes"
            v-model="form.notes"
            rows="3"
            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            placeholder="Additional information about this enrollment..."
          ></textarea>
          <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.notes }}</p>
        </div>

        <!-- Info Note -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
          <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-amber-600 dark:text-amber-400 mt-0.5"></i>
            <div class="text-sm text-amber-800 dark:text-amber-200">
              <p class="font-medium mb-1">Payment Note</p>
              <p>This enrollment does not include payment processing. Payment must be handled separately or marked manually.</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
          <Link
            :href="route('admin.enrollments.index')"
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
              <i class="fas fa-user-check mr-2"></i>
              Create Enrollment
            </span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Event {
  id: number;
  title: string;
  type: 'event' | 'course';
  amount: number;
  currency: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  phone_number?: string;
}

const props = defineProps<{
  events: Event[];
  users: User[];
}>();

const enrollmentType = ref<'existing_user' | 'guest'>('existing_user');

const form = useForm({
  event_id: '',
  user_id: '',
  phone_number: '',
  full_name: '',
  email: '',
  status: 'confirmed',
  notes: '',
});

const onEventChange = () => {
  // Optional: You could fetch additional event details here if needed
};

const onUserChange = () => {
  // Auto-fill phone number if user has one
  if (form.user_id) {
    const selectedUser = props.users.find(u => u.id === parseInt(form.user_id as string));
    if (selectedUser?.phone_number) {
      form.phone_number = selectedUser.phone_number;
    }
  }
};

const submit = () => {
  if (enrollmentType.value === 'guest') {
    // For guest enrollment, remove user_id from the request
    form.transform((data) => ({
      ...data,
      user_id: null,
    })).post(route('admin.enrollments.store'));
  } else {
    // For existing user, remove guest fields entirely from the request
    form.transform((data) => {
      const { full_name, email, ...rest } = data;
      return rest;
    }).post(route('admin.enrollments.store'));
  }
};
</script>
