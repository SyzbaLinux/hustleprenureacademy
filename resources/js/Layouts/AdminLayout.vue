<template>
  <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
    <!-- Sidebar -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-black/50 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <aside
      :class="[
        'fixed left-0 top-0 h-full z-50 w-64 bg-white dark:bg-slate-900 border-r border-gray-200 dark:border-slate-700',
        'transform transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <!-- Logo -->
      <div class="h-16 flex items-center justify-between px-6 border-b border-gray-200 dark:border-slate-700 flex-shrink-0">
        <Link :href="route('welcome')" class="flex items-center gap-3 group">
          <img src="/logo.png" alt="Hustleprenuer Network" class="h-10 w-auto" />
          <div class="flex flex-col">
            <span class="text-base font-bold text-gray-900 dark:text-white leading-tight">Hustleprenuer</span>
            <span class="text-xs text-gray-600 dark:text-gray-400 leading-tight">Network</span>
          </div>
        </Link>
        <button
          @click="sidebarOpen = false"
          class="lg:hidden p-1 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800"
        >
          <i class="fas fa-times text-lg"></i>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="overflow-y-auto px-3 py-4 space-y-1 flex-1">
        <!-- Dashboard Link -->
        <NavLink
          :href="route('admin.dashboard')"
          :active="route().current('admin.dashboard')"
          icon="fa-chart-line"
          class="text-sm"
        >
          Dashboard
        </NavLink>

        <!-- Content Management Section -->
        <div class="pt-4">
          <p class="px-2 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Content Management</p>
          <NavLink
            :href="route('admin.events.index')"
            :active="route().current('admin.events.*')"
            icon="fa-calendar-alt"
            class="text-sm"
          >
            Events & Courses
          </NavLink>
          <NavLink
            :href="route('admin.categories.index')"
            :active="route().current('admin.categories.*')"
            icon="fa-folder"
            class="text-sm"
          >
            Categories
          </NavLink>
          <NavLink
            :href="route('admin.instructors.index')"
            :active="route().current('admin.instructors.*')"
            icon="fa-chalkboard-teacher"
            class="text-sm"
          >
            Instructors
          </NavLink>
        </div>

        <!-- Enrollments & Payments Section -->
        <div class="pt-4">
          <p class="px-2 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Enrollments</p>
          <NavLink
            :href="route('admin.enrollments.index')"
            :active="route().current('admin.enrollments.*')"
            icon="fa-user-graduate"
            class="text-sm"
          >
            All Enrollments
          </NavLink>
          <NavLink
            :href="route('admin.payments.index')"
            :active="route().current('admin.payments.*')"
            icon="fa-credit-card"
            class="text-sm"
          >
            Payments
          </NavLink>
          <NavLink
            href="#"
            icon="fa-bell"
            class="text-sm opacity-60 cursor-not-allowed"
          >
            Reminders
          </NavLink>
        </div>

        <!-- Administration Section -->
        <div class="pt-4">
          <p class="px-2 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Administration</p>
          <NavLink
            :href="route('admin.users.index')"
            :active="route().current('admin.users.*')"
            icon="fa-users"
            class="text-sm"
          >
            Users
          </NavLink>
          <NavLink
            href="#"
            icon="fa-chart-bar"
            class="text-sm opacity-60 cursor-not-allowed"
          >
            Reports
          </NavLink>
          <NavLink
            href="#"
            icon="fa-cogs"
            class="text-sm opacity-60 cursor-not-allowed"
          >
            Settings
          </NavLink>
        </div>
      </nav>

      <!-- Admin Footer -->
      <div class="border-t border-gray-200 dark:border-slate-700 p-3 bg-white dark:bg-slate-900 relative flex-shrink-0">
        <button
          @click="userMenuOpen = !userMenuOpen"
          class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
        >
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#b30d4f] to-[#8b0a3d] flex items-center justify-center text-white text-xs font-semibold">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <div class="text-left min-w-0">
              <p class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ user.name }}</p>
              <p class="text-xs text-gray-600 dark:text-gray-400 capitalize truncate">{{ user.role }}</p>
            </div>
          </div>
          <i :class="`fas fa-chevron-down transition-transform text-xs ${userMenuOpen ? 'rotate-180' : ''}`"></i>
        </button>

        <!-- User Dropdown Menu (Drop Up) -->
        <div v-if="userMenuOpen" class="absolute bottom-full mb-2 left-0 right-0 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-gray-200 dark:border-slate-700 z-10 mx-1">
          <div class="space-y-1 p-1">
            <Link
              :href="route('user.profile.edit')"
              @click="userMenuOpen = false"
              class="block w-full text-left px-2 py-1.5 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 rounded transition-colors"
            >
              <i class="fas fa-user-circle mr-1.5"></i>
              Profile
            </Link>
            <form @submit.prevent="logout" class="w-full">
              <button
                type="submit"
                class="w-full text-left px-2 py-1.5 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors flex items-center gap-1.5"
              >
                <i class="fas fa-sign-out-alt"></i>
                Sign Out
              </button>
            </form>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-64">
      <!-- Top Navigation -->
      <header class="h-16 bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between px-6 sticky top-0 z-40">
        <button
          @click="sidebarOpen = !sidebarOpen"
          class="lg:hidden p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800"
        >
          <i class="fas fa-bars text-xl"></i>
        </button>

        <div class="flex-1"></div>

        <!-- Top Right Actions -->
        <div class="flex items-center gap-4">
          <!-- Admin Badge -->
          <span class="px-3 py-1 bg-[#b30d4f]/10 dark:bg-[#b30d4f]/20 text-[#b30d4f] dark:text-[#e0156b] text-xs font-semibold rounded-full flex items-center gap-1">
            <i class="fas fa-shield-alt"></i>
            Admin
          </span>

          <!-- Notifications -->
          <button class="p-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-lg transition-colors relative">
            <i class="fas fa-bell text-lg"></i>
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>

          <!-- Theme Toggle -->
          <button
            @click="toggleTheme"
            class="p-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
          >
            <i :class="`fas ${isDark ? 'fa-sun' : 'fa-moon'}`"></i>
          </button>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';

const sidebarOpen = ref(false);
const userMenuOpen = ref(false);
const isDark = ref(false);
const page = usePage();

const user = computed(() => page.props.auth.user);

const toggleTheme = () => {
  isDark.value = !isDark.value;
  // In a real app, you'd save this preference and apply it to the document
};

const logout = () => {
  router.post(route('logout'));
};
</script>

<style scoped>
/* Layout styles */
</style>
