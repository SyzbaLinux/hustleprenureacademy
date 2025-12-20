<template>
  <AdminLayout>
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.events.index')"
          class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
        >
          <i class="fas fa-arrow-left"></i>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add Event/Course</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Create a new event or course</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6 space-y-6">

        <!-- Type Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Type <span class="text-red-500">*</span>
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
              <i class="fas fa-calendar-day text-2xl mb-2" :class="form.type === 'event' ? 'text-[#b30d4f]' : 'text-gray-400'"></i>
              <p class="font-semibold text-gray-900 dark:text-white">Event</p>
              <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">One-time or recurring event</p>
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
              <i class="fas fa-graduation-cap text-2xl mb-2" :class="form.type === 'course' ? 'text-[#b30d4f]' : 'text-gray-400'"></i>
              <p class="font-semibold text-gray-900 dark:text-white">Course</p>
              <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Multi-session learning program</p>
            </button>
          </div>
          <p v-if="form.errors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.type }}</p>
        </div>

        <!-- Flier Image -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Flier/Cover Image
          </label>
          <div class="flex items-start gap-6">
            <div v-if="flierPreview" class="w-48 h-48 rounded-lg overflow-hidden flex-shrink-0 border-2 border-gray-200 dark:border-slate-600">
              <img :src="flierPreview" alt="Preview" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-48 h-48 rounded-lg bg-gradient-to-br from-[#b30d4f] to-[#8b0a3d] flex items-center justify-center flex-shrink-0">
              <i class="fas fa-image text-white text-4xl opacity-30"></i>
            </div>
            <div class="flex-1">
              <input
                type="file"
                @input="handleFlierUpload"
                accept="image/*"
                class="block w-full text-sm text-gray-600 dark:text-gray-400
                  file:mr-4 file:py-2 file:px-4
                  file:rounded-lg file:border-0
                  file:text-sm file:font-semibold
                  file:bg-[#b30d4f] file:text-white
                  hover:file:bg-[#8b0a3d]
                  file:cursor-pointer cursor-pointer"
              />
              <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">PNG, JPG up to 5MB. Recommended: 1200x630px</p>
            </div>
          </div>
          <p v-if="form.errors.flier" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.flier }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Title -->
          <div class="md:col-span-2">
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Title <span class="text-red-500">*</span>
            </label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="Enter event/course title"
            />
            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.title }}</p>
          </div>

          <!-- Category -->
          <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Category <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-2">
              <select
                id="category_id"
                v-model="form.category_id"
                required
                class="flex-1 px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              >
                <option value="">Select category</option>
                <option v-for="category in categoryList" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
              <button
                type="button"
                @click="showCategoryModal = true"
                class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-600 transition-colors"
                title="Create new category"
              >
                <i class="fas fa-plus"></i>
              </button>
            </div>
            <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.category_id }}</p>
          </div>

          <!-- Instructors -->
          <div>
            <label for="instructors" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Instructors <span class="text-xs text-gray-500 dark:text-gray-400">(Optional)</span>
            </label>
            <select
              id="instructors"
              v-model="form.instructor_ids"
              multiple
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              size="4"
            >
              <option v-for="instructor in instructors" :key="instructor.id" :value="instructor.id">
                {{ instructor.name }}
              </option>
            </select>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Hold Ctrl/Cmd to select multiple</p>
            <p v-if="form.errors.instructor_ids" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.instructor_ids }}</p>
          </div>
        </div>

        <!-- Short Description -->
        <div>
          <label for="short_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Short Description
          </label>
          <input
            id="short_description"
            v-model="form.short_description"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            placeholder="Brief one-line description"
          />
          <p v-if="form.errors.short_description" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.short_description }}</p>
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Full Description <span class="text-red-500">*</span>
          </label>
          <textarea
            id="description"
            v-model="form.description"
            rows="6"
            required
            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            placeholder="Detailed description, objectives, target audience, etc."
          ></textarea>
          <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.description }}</p>
        </div>

        <!-- Location Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Location Type <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 gap-4">
            <button
              type="button"
              @click="form.location_type = 'physical'"
              :class="[
                'p-3 rounded-lg border-2 transition-all',
                form.location_type === 'physical'
                  ? 'border-[#b30d4f] bg-[#b30d4f]/5 dark:bg-[#b30d4f]/10'
                  : 'border-gray-200 dark:border-slate-600 hover:border-[#b30d4f]/50'
              ]"
            >
              <i class="fas fa-map-marker-alt mr-2" :class="form.location_type === 'physical' ? 'text-[#b30d4f]' : 'text-gray-400'"></i>
              <span class="font-semibold text-gray-900 dark:text-white">Physical Location</span>
            </button>
            <button
              type="button"
              @click="form.location_type = 'online'"
              :class="[
                'p-3 rounded-lg border-2 transition-all',
                form.location_type === 'online'
                  ? 'border-[#b30d4f] bg-[#b30d4f]/5 dark:bg-[#b30d4f]/10'
                  : 'border-gray-200 dark:border-slate-600 hover:border-[#b30d4f]/50'
              ]"
            >
              <i class="fas fa-video mr-2" :class="form.location_type === 'online' ? 'text-[#b30d4f]' : 'text-gray-400'"></i>
              <span class="font-semibold text-gray-900 dark:text-white">Online/Virtual</span>
            </button>
          </div>
          <p v-if="form.errors.location_type" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.location_type }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Location (if physical) -->
          <div v-if="form.location_type === 'physical'">
            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Physical Location
            </label>
            <input
              id="location"
              v-model="form.location"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="Enter venue address"
            />
            <p v-if="form.errors.location" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.location }}</p>
          </div>

          <!-- Meeting Link (if online) -->
          <div v-if="form.location_type === 'online'">
            <label for="meeting_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Meeting Link
            </label>
            <input
              id="meeting_link"
              v-model="form.meeting_link"
              type="url"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="https://zoom.us/j/..."
            />
            <p v-if="form.errors.meeting_link" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.meeting_link }}</p>
          </div>

          <!-- Capacity -->
          <div>
            <label for="capacity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Capacity
            </label>
            <input
              id="capacity"
              v-model.number="form.capacity"
              type="number"
              min="1"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="Maximum participants (leave empty for unlimited)"
            />
            <p v-if="form.errors.capacity" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.capacity }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Amount -->
          <div>
            <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Price <span class="text-red-500">*</span>
            </label>
            <input
              id="amount"
              v-model.number="form.amount"
              type="number"
              min="0"
              step="0.01"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="0.00"
            />
            <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.amount }}</p>
          </div>

          <!-- Currency -->
          <div>
            <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Currency <span class="text-red-500">*</span>
            </label>
            <select
              id="currency"
              v-model="form.currency"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            >
              <option value="USD">USD</option>
              <option value="ZWL">ZWL</option>
            </select>
            <p v-if="form.errors.currency" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.currency }}</p>
          </div>

          <!-- Duration -->
          <div>
            <label for="duration_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Duration (hours)
            </label>
            <input
              id="duration_hours"
              v-model.number="form.duration_hours"
              type="number"
              min="0.5"
              step="0.5"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="e.g., 2.5"
            />
            <p v-if="form.errors.duration_hours" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.duration_hours }}</p>
          </div>
        </div>

        <!-- Toggles -->
        <div class="flex flex-wrap gap-6">
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

          <div class="flex items-center gap-3">
            <input
              id="is_featured"
              v-model="form.is_featured"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300 dark:border-slate-600 text-[#b30d4f] focus:ring-[#b30d4f] dark:focus:ring-[#e0156b]"
            />
            <label for="is_featured" class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Featured (show prominently)
            </label>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
          <Link
            :href="route('admin.events.index')"
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
              Create {{ form.type === 'course' ? 'Course' : 'Event' }}
            </span>
          </button>
        </div>
      </form>
    </div>

    <!-- Create Category Modal -->
    <CreateCategoryModal
      :show="showCategoryModal"
      @close="showCategoryModal = false"
      @created="handleCategoryCreated"
    />
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CreateCategoryModal from '@/Components/CreateCategoryModal.vue';

interface Category {
  id: number;
  name: string;
}

interface Instructor {
  id: number;
  name: string;
}

const props = defineProps<{
  categories: Category[];
  instructors: Instructor[];
}>();

const flierPreview = ref<string | null>(null);
const showCategoryModal = ref(false);
const categoryList = ref<Category[]>([...props.categories]);

const form = useForm({
  type: 'event' as 'event' | 'course',
  title: '',
  category_id: '',
  instructor_ids: [] as number[],
  short_description: '',
  description: '',
  location_type: 'physical' as 'physical' | 'online',
  location: '',
  meeting_link: '',
  capacity: null as number | null,
  amount: 0,
  currency: 'USD',
  duration_hours: null as number | null,
  is_active: true,
  is_featured: false,
  flier: null as File | null,
});

const handleFlierUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];

  if (file) {
    form.flier = file;

    const reader = new FileReader();
    reader.onload = (e) => {
      flierPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
};

const handleCategoryCreated = (category: Category) => {
  categoryList.value.push(category);
  form.category_id = category.id;
};

const submit = () => {
  form.post(route('admin.events.store'));
};
</script>
