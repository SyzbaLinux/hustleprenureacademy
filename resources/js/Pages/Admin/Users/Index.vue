<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Manage user accounts</p>
        </div>
        <Link
          :href="route('admin.users.create')"
          class="px-4 py-2 bg-gradient-to-r from-[#b30d4f] to-[#8b0a3d] text-white font-semibold rounded-lg hover:shadow-lg hover:scale-105 transition-all duration-200 flex items-center gap-2"
        >
          <i class="fas fa-user-plus"></i>
          Add User
        </Link>
      </div>

      <!-- Filter Tabs -->
      <div class="flex gap-2">
        <button
          @click="filterByRole('all')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === 'all'
              ? 'bg-[#b30d4f] text-white'
              : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'
          ]"
        >
          <i class="fas fa-users mr-2"></i>
          All ({{ stats.total }})
        </button>
        <button
          @click="filterByRole('admin')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === 'admin'
              ? 'bg-[#b30d4f] text-white'
              : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'
          ]"
        >
          <i class="fas fa-shield-alt mr-2"></i>
          Admins ({{ stats.admins }})
        </button>
        <button
          @click="filterByRole('user')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === 'user'
              ? 'bg-[#b30d4f] text-white'
              : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'
          ]"
        >
          <i class="fas fa-user mr-2"></i>
          Users ({{ stats.users }})
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Total Users</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total }}</p>
            </div>
            <div class="w-12 h-12 bg-[#b30d4f]/10 dark:bg-[#b30d4f]/20 rounded-lg flex items-center justify-center">
              <i class="fas fa-users text-[#b30d4f] dark:text-[#e0156b] text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Admins</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.admins }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-shield-alt text-amber-600 dark:text-amber-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Verified</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.verified }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Unverified</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.unverified }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-list text-[#b30d4f] dark:text-[#e0156b]"></i>
            {{ currentFilter === 'all' ? 'All Users' : currentFilter === 'admin' ? 'Administrators' : 'Regular Users' }}
          </h2>
        </div>

        <div v-if="users.data.length > 0" class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Joined</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-slate-900/50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#b30d4f] to-[#8b0a3d] flex items-center justify-center text-white font-semibold flex-shrink-0">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                      <p v-if="user.phone_number" class="text-xs text-gray-600 dark:text-gray-400">{{ user.phone_number }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                  {{ user.email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      user.role === 'admin'
                        ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200'
                        : 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200'
                    ]"
                  >
                    <i :class="`fas ${user.role === 'admin' ? 'fa-shield-alt' : 'fa-user'} mr-1`"></i>
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      user.email_verified_at
                        ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
                        : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'
                    ]"
                  >
                    <i :class="`fas ${user.email_verified_at ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-1`"></i>
                    {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                  {{ formatDate(user.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('admin.users.show', user.id)"
                      class="p-2 text-[#b30d4f] dark:text-[#e0156b] hover:bg-[#b30d4f]/10 dark:hover:bg-[#b30d4f]/20 rounded-lg transition-colors"
                      title="View details"
                    >
                      <i class="fas fa-eye"></i>
                    </Link>
                    <button
                      @click="deleteUser(user)"
                      class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                      :disabled="user.id === currentUserId"
                      :class="{ 'opacity-50 cursor-not-allowed': user.id === currentUserId }"
                      :title="user.id === currentUserId ? 'Cannot delete your own account' : 'Delete user'"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="users.links.length > 3" class="p-6 border-t border-gray-200 dark:border-slate-700">
            <nav class="flex justify-center gap-2">
              <Link
                v-for="(link, index) in users.links"
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
            <i class="fas fa-users text-gray-400 dark:text-gray-500 text-2xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No users found</h3>
          <p class="text-gray-600 dark:text-gray-400">No users match the current filter</p>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface User {
  id: number;
  name: string;
  email: string;
  phone_number?: string;
  role: 'admin' | 'user';
  email_verified_at?: string;
  created_at: string;
}

interface PaginatedUsers {
  data: User[];
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

interface Stats {
  total: number;
  admins: number;
  users: number;
  verified: number;
  unverified: number;
}

const props = defineProps<{
  users: PaginatedUsers;
  stats: Stats;
  currentFilter?: string;
}>();

const page = usePage();
const currentUserId = ref(page.props.auth.user.id);
const currentFilter = ref(props.currentFilter || 'all');

const filterByRole = (role: string) => {
  currentFilter.value = role;
  router.get(route('admin.users.index'), { role }, { preserveState: true });
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const deleteUser = (user: User) => {
  if (user.id === currentUserId.value) {
    alert('You cannot delete your own account!');
    return;
  }

  if (confirm(`Are you sure you want to delete "${user.name}"? This action cannot be undone.`)) {
    router.delete(route('admin.users.destroy', user.id));
  }
};
</script>
