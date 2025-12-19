<template>
  <AdminLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('admin.instructors.index')"
          class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
        >
          <i class="fas fa-arrow-left"></i>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Instructor</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Update instructor information</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6 space-y-6">
        <!-- Profile Photo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Profile Photo
          </label>
          <div class="flex items-center gap-6">
            <div v-if="photoPreview" class="w-24 h-24 rounded-full overflow-hidden">
              <img :src="photoPreview" alt="Preview" class="w-full h-full object-cover" />
            </div>
            <div v-else-if="instructor.profile_photo" class="w-24 h-24 rounded-full overflow-hidden">
              <img :src="`/storage/${instructor.profile_photo}`" :alt="instructor.name" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-24 h-24 rounded-full bg-gradient-to-br from-[#b30d4f] to-[#8b0a3d] flex items-center justify-center text-white text-3xl font-bold">
              {{ instructor.name.charAt(0).toUpperCase() }}
            </div>
            <div class="flex-1">
              <input
                type="file"
                @input="handlePhotoUpload"
                accept="image/*"
                class="block w-full text-sm text-gray-600 dark:text-gray-400
                  file:mr-4 file:py-2 file:px-4
                  file:rounded-lg file:border-0
                  file:text-sm file:font-semibold
                  file:bg-[#b30d4f] file:text-white
                  hover:file:bg-[#8b0a3d]
                  file:cursor-pointer cursor-pointer"
              />
              <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">PNG, JPG up to 2MB</p>
            </div>
          </div>
          <p v-if="form.errors.profile_photo" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.profile_photo }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Full Name <span class="text-red-500">*</span>
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="John Doe"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
          </div>

          <!-- Specialization -->
          <div>
            <label for="specialization" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Specialization
            </label>
            <input
              id="specialization"
              v-model="form.specialization"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="Business Strategy, Marketing, etc."
            />
            <p v-if="form.errors.specialization" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.specialization }}</p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Email Address <span class="text-red-500">*</span>
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="john@example.com"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
          </div>

          <!-- Phone -->
          <div>
            <label for="phone_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Phone Number
            </label>
            <input
              id="phone_number"
              v-model="form.phone_number"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
              placeholder="+263 77 123 4567"
            />
            <p v-if="form.errors.phone_number" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.phone_number }}</p>
          </div>
        </div>

        <!-- Bio -->
        <div>
          <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Biography
          </label>
          <textarea
            id="bio"
            v-model="form.bio"
            rows="4"
            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-[#b30d4f] dark:focus:ring-[#e0156b] focus:border-transparent bg-white dark:bg-slate-900 text-gray-900 dark:text-white"
            placeholder="Brief background and expertise..."
          ></textarea>
          <p v-if="form.errors.bio" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.bio }}</p>
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
            Active (available for assignment)
          </label>
        </div>
        <p v-if="form.errors.is_active" class="text-sm text-red-600 dark:text-red-400">{{ form.errors.is_active }}</p>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-slate-700">
          <Link
            :href="route('admin.instructors.index')"
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
              Update Instructor
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

interface Instructor {
  id: number;
  name: string;
  email: string;
  phone_number?: string;
  bio?: string;
  profile_photo?: string;
  specialization?: string;
  is_active: boolean;
}

const props = defineProps<{
  instructor: Instructor;
}>();

const photoPreview = ref<string | null>(null);

const form = useForm({
  name: props.instructor.name,
  email: props.instructor.email,
  phone_number: props.instructor.phone_number || '',
  bio: props.instructor.bio || '',
  specialization: props.instructor.specialization || '',
  is_active: props.instructor.is_active,
  profile_photo: null as File | null,
});

const handlePhotoUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];

  if (file) {
    form.profile_photo = file;

    const reader = new FileReader();
    reader.onload = (e) => {
      photoPreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
};

const submit = () => {
  form.post(route('admin.instructors.update', props.instructor.id), {
    _method: 'put',
  });
};
</script>
