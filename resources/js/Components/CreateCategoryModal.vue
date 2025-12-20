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
              class="relative w-full max-w-md transform rounded-lg bg-white dark:bg-slate-800 shadow-xl border border-gray-200 dark:border-slate-700"
            >
              <!-- Header -->
              <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Create New Category
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
              <form @submit.prevent="submit" class="p-4 space-y-4">
                <div>
                  <label for="category-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Category Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="category-name"
                    ref="nameInput"
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
                    placeholder="Enter category name"
                  />
                  <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.name }}
                  </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-2">
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
                      Creating...
                    </span>
                    <span v-else>
                      <i class="fas fa-plus mr-2"></i>
                      Create Category
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
import { ref, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';

interface Category {
  id: number;
  name: string;
}

const props = defineProps<{
  show: boolean;
}>();

const emit = defineEmits<{
  close: [];
  created: [category: Category];
}>();

const nameInput = ref<HTMLInputElement | null>(null);

const form = ref({
  name: '',
  processing: false,
  errors: {
    name: '',
  },
});

watch(() => props.show, (newValue) => {
  if (newValue) {
    nextTick(() => {
      nameInput.value?.focus();
    });
  } else {
    resetForm();
  }
});

const resetForm = () => {
  form.value.name = '';
  form.value.errors.name = '';
  form.value.processing = false;
};

const close = () => {
  if (!form.value.processing) {
    emit('close');
  }
};

const submit = async () => {
  if (form.value.processing) return;

  form.value.processing = true;
  form.value.errors.name = '';

  try {
    const response = await fetch('/admin/categories/quick', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        name: form.value.name,
      }),
    });

    const data = await response.json();

    if (response.ok) {
      emit('created', data.category);
      emit('close');
    } else {
      if (data.errors && data.errors.name) {
        form.value.errors.name = Array.isArray(data.errors.name)
          ? data.errors.name[0]
          : data.errors.name;
      } else {
        form.value.errors.name = data.message || 'Failed to create category';
      }
    }
  } catch (error) {
    form.value.errors.name = 'An error occurred. Please try again.';
  } finally {
    form.value.processing = false;
  }
};
</script>
