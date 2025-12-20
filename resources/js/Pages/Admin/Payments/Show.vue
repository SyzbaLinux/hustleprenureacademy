<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header with Back Button -->
      <div class="flex items-center justify-between">
        <div>
          <Link href="/admin/payments" class="text-[#b30d4f] dark:text-[#e0156b] hover:underline flex items-center gap-2 mb-2">
            <i class="fas fa-arrow-left"></i>
            Back to Payments
          </Link>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payment Details</h1>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Payment Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Payment Status Card -->
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Payment Information</h2>
              <span
                :class="[
                  'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                  payment.status === 'paid'
                    ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
                    : payment.status === 'pending'
                    ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200'
                    : payment.status === 'failed'
                    ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'
                    : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-200'
                ]"
              >
                <i :class="`fas ${getStatusIcon(payment.status)} mr-2`"></i>
                {{ payment.status }}
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Reference Number -->
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Reference Number</p>
                <p class="text-lg font-mono font-semibold text-gray-900 dark:text-white mt-1">{{ payment.reference_number }}</p>
              </div>

              <!-- Transaction ID -->
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Transaction ID</p>
                <p class="text-lg font-mono font-semibold text-gray-900 dark:text-white mt-1">{{ payment.transaction_id || 'N/A' }}</p>
              </div>

              <!-- Amount -->
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Amount</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ payment.currency }} {{ formatAmount(payment.amount) }}</p>
              </div>

              <!-- Payment Method -->
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Payment Method</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1 capitalize">{{ payment.payment_method }}</p>
              </div>

              <!-- Phone Number -->
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Phone Number</p>
                <p class="text-lg font-mono font-semibold text-gray-900 dark:text-white mt-1">{{ payment.phone_number }}</p>
              </div>

              <!-- Created Date -->
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Created</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ formatDate(payment.created_at) }}</p>
              </div>

              <!-- Paid Date -->
              <div v-if="payment.paid_at">
                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Paid</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ formatDate(payment.paid_at) }}</p>
              </div>

              <!-- Failed Reason -->
              <div v-if="payment.failed_reason">
                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Failed Reason</p>
                <p class="text-lg font-semibold text-red-600 dark:text-red-400 mt-1">{{ payment.failed_reason }}</p>
              </div>
            </div>
          </div>

          <!-- Event Details -->
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Event Information</h2>
            <div v-if="payment.event" class="space-y-4">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Event/Course</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ payment.event.title }}</p>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Category</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ payment.event.category?.name || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Type</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1 capitalize">{{ payment.event.type }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-gray-600 dark:text-gray-400">No event associated with this payment</div>
          </div>

          <!-- User Details -->
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Information</h2>
            <div v-if="payment.user" class="space-y-4">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Name</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ payment.user.name }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">Email</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ payment.user.email }}</p>
              </div>
            </div>
            <div v-else class="text-gray-600 dark:text-gray-400">Guest user (no account)</div>
          </div>
        </div>

        <!-- Right Column - Actions -->
        <div>
          <!-- Enrollment Status -->
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Enrollment Status</h3>
            <div v-if="payment.enrollment">
              <div class="mb-3">
                <span
                  :class="[
                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                    payment.enrollment.status === 'confirmed'
                      ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
                      : 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200'
                  ]"
                >
                  {{ payment.enrollment.status }}
                </span>
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Enrolled on {{ formatDate(payment.enrollment.enrollment_date) }}
              </p>
            </div>
            <div v-else class="text-gray-600 dark:text-gray-400 text-sm mb-4">
              No enrollment record for this payment yet.
            </div>
          </div>

          <!-- Admin Actions -->
          <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 p-6 space-y-3">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Admin Actions</h3>

            <!-- Mark as Paid Button -->
            <button
              v-if="payment.status !== 'paid'"
              @click="markAsPaid"
              :disabled="loading"
              class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400 transition-colors font-medium flex items-center justify-center gap-2"
            >
              <i class="fas fa-check-circle"></i>
              {{ loading ? 'Processing...' : 'Mark as Paid ✓' }}
            </button>

            <!-- Create Enrollment Button -->
            <button
              v-if="payment.status === 'paid' && !payment.enrollment"
              @click="createEnrollment"
              :disabled="loading"
              class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition-colors font-medium flex items-center justify-center gap-2"
            >
              <i class="fas fa-user-plus"></i>
              {{ loading ? 'Processing...' : 'Create Enrollment' }}
            </button>

            <!-- Success Message -->
            <div v-if="successMessage" class="p-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 rounded-lg text-sm flex items-center gap-2">
              <i class="fas fa-check-circle"></i>
              {{ successMessage }}
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="p-3 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 rounded-lg text-sm flex items-center gap-2">
              <i class="fas fa-exclamation-circle"></i>
              {{ errorMessage }}
            </div>
          </div>
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
  type: string;
  category?: {
    name: string;
  };
}

interface User {
  id: number;
  name: string;
  email: string;
}

interface Enrollment {
  id: number;
  status: string;
  enrollment_date: string;
}

interface Payment {
  id: number;
  reference_number: string;
  transaction_id?: string;
  phone_number: string;
  amount: number | string;
  currency: string;
  payment_method: string;
  status: 'pending' | 'processing' | 'paid' | 'failed' | 'refunded';
  created_at: string;
  paid_at?: string;
  failed_reason?: string;
  event?: Event;
  user?: User;
  enrollment?: Enrollment;
}

const props = defineProps<{
  payment: Payment;
}>();

const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
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

const markAsPaid = async () => {
  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    await router.post(
      route('admin.payments.mark-paid', props.payment.id),
      {},
      {
        onSuccess: () => {
          successMessage.value = 'Payment marked as paid successfully!';
          setTimeout(() => {
            router.reload();
          }, 1500);
        },
        onError: (errors) => {
          errorMessage.value = Object.values(errors).join(', ') || 'Failed to mark payment as paid';
        },
      }
    );
  } catch (error) {
    errorMessage.value = 'An error occurred. Please try again.';
  } finally {
    loading.value = false;
  }
};

const createEnrollment = async () => {
  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    await router.post(
      route('admin.payments.create-enrollment', props.payment.id),
      {},
      {
        onSuccess: () => {
          successMessage.value = 'Enrollment created successfully!';
          setTimeout(() => {
            router.reload();
          }, 1500);
        },
        onError: (errors) => {
          errorMessage.value = Object.values(errors).join(', ') || 'Failed to create enrollment';
        },
      }
    );
  } catch (error) {
    errorMessage.value = 'An error occurred. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>
