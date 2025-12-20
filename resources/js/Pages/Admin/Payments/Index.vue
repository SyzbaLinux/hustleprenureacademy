<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payments</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">View and manage payment transactions</p>
        </div>
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
          @click="filterByStatus('paid')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === 'paid'
              ? 'bg-[#b30d4f] text-white'
              : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'
          ]"
        >
          <i class="fas fa-check-circle mr-2"></i>
          Paid ({{ stats.paid }})
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
          @click="filterByStatus('failed')"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            currentFilter === 'failed'
              ? 'bg-[#b30d4f] text-white'
              : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700'
          ]"
        >
          <i class="fas fa-times-circle mr-2"></i>
          Failed ({{ stats.failed }})
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Total Payments</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total }}</p>
            </div>
            <div class="w-12 h-12 bg-[#b30d4f]/10 dark:bg-[#b30d4f]/20 rounded-lg flex items-center justify-center">
              <i class="fas fa-credit-card text-[#b30d4f] dark:text-[#e0156b] text-xl"></i>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 border border-gray-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Successful</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.paid }}</p>
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
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Total Revenue</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">USD {{ formatAmount(stats.total_amount) }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
              <i class="fas fa-dollar-sign text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Payments Table -->
      <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="fas fa-list text-[#b30d4f] dark:text-[#e0156b]"></i>
            Payment Transactions
          </h2>
        </div>

        <div v-if="payments.data.length > 0" class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Event & Reference</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Method</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
              <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-slate-900/50">
                <td class="px-6 py-4">
                  <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ payment.event?.title || 'N/A' }}</p>
                    <p class="text-xs font-mono text-gray-600 dark:text-gray-400 mt-1">{{ payment.reference_number }}</p>
                    <p v-if="payment.transaction_id" class="text-xs text-gray-500 dark:text-gray-500">ID: {{ payment.transaction_id }}</p>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ payment.user?.name || 'Guest' }}</p>
                  <p class="text-xs text-gray-600 dark:text-gray-400">{{ payment.phone_number }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ payment.currency }} {{ formatAmount(payment.amount) }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-200">
                    {{ payment.payment_method }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      payment.status === 'paid'
                        ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
                        : payment.status === 'pending'
                        ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200'
                        : payment.status === 'failed'
                        ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'
                        : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-200'
                    ]"
                  >
                    <i :class="`fas ${getStatusIcon(payment.status)} mr-1`"></i>
                    {{ payment.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                  {{ formatDate(payment.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="route('admin.payments.show', payment.id)"
                      class="px-3 py-2 bg-[#b30d4f] text-white rounded-lg hover:bg-[#a00a46] transition-colors inline-flex items-center gap-2 font-medium"
                      title="View details"
                    >
                      <i class="fas fa-eye"></i>
                      <span>View</span>
                    </Link>
                    <button
                      v-if="payment.status === 'failed' || payment.status === 'pending'"
                      @click="markAsPaid(payment)"
                      :disabled="processingPaymentId === payment.id"
                      class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition-colors inline-flex items-center gap-2 font-medium"
                      :title="`Mark as paid`"
                    >
                      <i class="fas fa-check"></i>
                      <span>Paid</span>
                    </button>
                    <button
                      v-if="payment.status === 'paid' && !payment.enrollment"
                      @click="createEnrollment(payment)"
                      :disabled="processingPaymentId === payment.id"
                      class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition-colors inline-flex items-center gap-2 font-medium"
                      :title="`Create enrollment`"
                    >
                      <i class="fas fa-plus"></i>
                      <span>Enroll</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="payments.links.length > 3" class="p-6 border-t border-gray-200 dark:border-slate-700">
            <nav class="flex justify-center gap-2">
              <Link
                v-for="(link, index) in payments.links"
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
            <i class="fas fa-credit-card text-gray-400 dark:text-gray-500 text-2xl"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No payments found</h3>
          <p class="text-gray-600 dark:text-gray-400">No payments match the current filter</p>
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
}

interface User {
  id: number;
  name: string;
}

interface Payment {
  id: number;
  reference_number: string;
  transaction_id?: string;
  phone_number: string;
  amount: number;
  currency: string;
  payment_method: string;
  status: 'pending' | 'processing' | 'paid' | 'failed' | 'refunded';
  created_at: string;
  paid_at?: string;
  event?: Event;
  user?: User;
}

interface PaginatedPayments {
  data: Payment[];
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

interface Stats {
  total: number;
  paid: number;
  pending: number;
  failed: number;
  total_amount: number;
}

const props = defineProps<{
  payments: PaginatedPayments;
  stats: Stats;
  currentFilter?: string;
}>();

const currentFilter = ref(props.currentFilter || 'all');
const processingPaymentId = ref<number | null>(null);

const filterByStatus = (status: string) => {
  currentFilter.value = status;
  router.get(route('admin.payments.index'), { status }, { preserveState: true });
};

const markAsPaid = (payment: Payment) => {
  processingPaymentId.value = payment.id;
  router.post(
    route('admin.payments.mark-paid', payment.id),
    {},
    {
      onSuccess: () => {
        router.reload();
      },
      onError: () => {
        processingPaymentId.value = null;
      },
      onFinish: () => {
        processingPaymentId.value = null;
      },
    }
  );
};

const createEnrollment = (payment: Payment) => {
  processingPaymentId.value = payment.id;
  router.post(
    route('admin.payments.create-enrollment', payment.id),
    {},
    {
      onSuccess: () => {
        router.reload();
      },
      onError: () => {
        processingPaymentId.value = null;
      },
      onFinish: () => {
        processingPaymentId.value = null;
      },
    }
  );
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatAmount = (amount: number | string) => {
  return Number(amount).toFixed(2);
};

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'paid':
      return 'fa-check-circle';
    case 'pending':
    case 'processing':
      return 'fa-clock';
    case 'failed':
      return 'fa-times-circle';
    case 'refunded':
      return 'fa-undo';
    default:
      return 'fa-circle';
  }
};
</script>
