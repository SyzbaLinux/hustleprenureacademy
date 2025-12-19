<template>
  <Link
    :href="href"
    :class="[
      'flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200',
      active
        ? 'bg-[#b30d4f]/10 dark:bg-[#b30d4f]/20 text-[#b30d4f] dark:text-[#e0156b]'
        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-800'
    ]"
  >
    <i :class="`fas ${icon} w-5`"></i>
    <slot />
  </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
  href: string;
  active?: boolean;
  icon: string;
}>();
</script>

<style scoped>
/* Component styles */
</style>
