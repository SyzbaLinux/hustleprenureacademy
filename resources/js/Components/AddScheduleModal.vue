<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="close"
      >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

        <!-- Modal -->
        <div class="flex min-h-full items-center justify-center p-4">
          <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div
              v-if="show"
              class="relative w-full max-w-2xl transform rounded-lg bg-white dark:bg-slate-800 shadow-xl border border-gray-200 dark:border-slate-700"
            >
              <!-- Header -->
              <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ isEditing ? 'Edit Schedule' : 'Add Schedule' }}
                </h3>
                <button
                  type="button"
                  @click="close"
                  class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <!-- Form -->
              <form @submit.prevent="submit" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Session Number -->
                  <div>
                    <label for="session-number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Session Number <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="session-number"
                      ref="sessionNumberInput"
                      v-model.number="form.session_number"
                      type="number"
                      min="1"
                      required
                      class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
                      placeholder="1"
                    />
                    <p v-if="form.errors.session_number" class="mt-1 text-sm text-red-600 dark:text-red-400">
                      {{ form.errors.session_number }}
                    </p>
                  </div>

                  <!-- Start Date -->
                  <div>
                    <label for="start-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Date <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="start-date"
                      v-model="form.start_date"
                      type="date"
                      required
                      class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
                    />
                    <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                      {{ form.errors.start_date }}
                    </p>
                  </div>
                </div>

                <!-- Title -->
                <div>
                  <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Session Title <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
                    placeholder="Enter session title"
                  />
                  <p v-if="form.errors.title" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.title }}
                  </p>
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
                    placeholder="Session description (optional)"
                  ></textarea>
                  <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.description }}
                  </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Start Time -->
                  <div>
                    <label for="start-time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Start Time <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="start-time"
                      v-model="form.start_time"
                      type="time"
                      required
                      class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
                    />
                    <p v-if="form.errors.start_time" class="mt-1 text-sm text-red-600 dark:text-red-400">
                      {{ form.errors.start_time }}
                    </p>
                  </div>

                  <!-- End Time -->
                  <div>
                    <label for="end-time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      End Time <span class="text-red-500">*</span>
                    </label>
                    <input
                      id="end-time"
                      v-model="form.end_time"
                      type="time"
                      required
                      class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
                    />
                    <p v-if="form.errors.end_time" class="mt-1 text-sm text-red-600 dark:text-red-400">
                      {{ form.errors.end_time }}
                    </p>
                  </div>
                </div>

                <!-- Completed Checkbox (only for editing) -->
                <div v-if="isEditing" class="flex items-center gap-3">
                  <input
                    id="is-completed"
                    v-model="form.is_completed"
                    type="checkbox"
                    class="w-5 h-5 rounded border-gray-300 dark:border-slate-600 text-[#b30d4f] focus:ring-[#b30d4f] dark:focus:ring-[#e0156b]"
                  />
                  <label for="is-completed" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Mark as completed
                  </label>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
                  <button
                    type="button"
                    @click="close"
                    class="px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-6 py-2 bg-gradient-to-r from-[#b30d4f] to-[#8b0a3d] text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                  >
                    <span v-if="form.processing">
                      <i class="fas fa-spinner fa-spin mr-2"></i>
                      {{ isEditing ? 'Updating...' : 'Creating...' }}
                    </span>
                    <span v-else>
                      <i :class="isEditing ? 'fas fa-save' : 'fas fa-plus'" class="mr-2"></i>
                      {{ isEditing ? 'Update Schedule' : 'Add Schedule' }}
                    </span>
                  </button>
                </div>
              </form>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, computed } from 'vue';
import axios from 'axios';

interface Schedule {
  id?: number;
  session_number: number;
  title: string;
  description?: string;
  start_date: string;
  start_time: string;
  end_time: string;
  is_completed?: boolean;
}

const props = defineProps<{
  show: boolean;
  eventId: number;
  schedule?: Schedule | null;
}>();

const emit = defineEmits<{
  close: [];
  saved: [schedule: Schedule];
}>();

const sessionNumberInput = ref<HTMLInputElement | null>(null);

const isEditing = computed(() => !!props.schedule?.id);

const form = ref({
  session_number: 1,
  title: '',
  description: '',
  start_date: '',
  start_time: '',
  end_time: '',
  is_completed: false,
  processing: false,
  errors: {} as Record<string, string>,
});

watch(() => props.show, (newValue) => {
  if (newValue) {
    // Reset form first to ensure clean state
    resetForm();

    if (props.schedule) {
      // Editing mode - populate form with schedule data
      form.value.session_number = props.schedule.session_number;
      form.value.title = props.schedule.title;
      form.value.description = props.schedule.description || '';
      form.value.start_date = props.schedule.start_date;
      form.value.start_time = props.schedule.start_time;
      form.value.end_time = props.schedule.end_time;
      form.value.is_completed = props.schedule.is_completed || false;
    }

    nextTick(() => {
      sessionNumberInput.value?.focus();
    });
  } else {
    resetForm();
  }
});

const resetForm = () => {
  form.value = {
    session_number: 1,
    title: '',
    description: '',
    start_date: '',
    start_time: '',
    end_time: '',
    is_completed: false,
    processing: false,
    errors: {},
  };
};

const close = () => {
  if (!form.value.processing) {
    emit('close');
  }
};

const submit = async () => {
  if (form.value.processing) return;

  form.value.processing = true;
  form.value.errors = {};

  try {
    const url = props.schedule?.id
      ? `/admin/events/${props.eventId}/schedules/${props.schedule.id}`
      : `/admin/events/${props.eventId}/schedules`;

    const method = props.schedule?.id ? 'put' : 'post';

    const response = await axios[method](url, {
      session_number: form.value.session_number,
      title: form.value.title,
      description: form.value.description,
      start_date: form.value.start_date,
      start_time: form.value.start_time,
      end_time: form.value.end_time,
      is_completed: form.value.is_completed,
    });

    if (response.data.success) {
      emit('saved', response.data.schedule);
      emit('close');
    }
  } catch (error: any) {
    if (error.response?.data?.errors) {
      form.value.errors = Object.entries(error.response.data.errors).reduce((acc, [key, value]) => {
        acc[key] = Array.isArray(value) ? value[0] : value as string;
        return acc;
      }, {} as Record<string, string>);
    } else {
      form.value.errors.title = error.response?.data?.message || 'An error occurred. Please try again.';
    }
  } finally {
    form.value.processing = false;
  }
};
</script>
