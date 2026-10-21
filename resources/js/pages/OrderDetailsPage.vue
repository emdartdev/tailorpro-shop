<template>
  <div class="space-y-4 max-w-7xl mx-auto pb-12">
    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-24 gap-3">
      <Loader2 class="h-8 w-8 animate-spin text-primary" />
      <p class="text-xs text-muted-foreground font-medium">Loading order details...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="!order" class="text-center py-20 bg-card rounded-2xl border border-border p-6 space-y-3">
      <p class="text-sm font-semibold text-foreground">Order not found</p>
      <button
        type="button"
        @click="goBack"
        class="inline-flex items-center gap-1.5 h-8 px-3 text-xs rounded-lg bg-primary text-primary-foreground font-medium cursor-pointer"
      >
        <ArrowLeft class="h-3.5 w-3.5" /> Back to Orders
      </button>
    </div>

    <!-- Order Details Content -->
    <template v-else>
      <!-- Top Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <!-- Back Button -->
          <button
            type="button"
            @click="goBack"
            class="h-9 w-9 inline-flex items-center justify-center rounded-xl border border-input bg-card hover:bg-accent text-foreground transition-colors shadow-2xs cursor-pointer shrink-0"
            title="Back"
          >
            <ArrowLeft class="h-4 w-4" />
          </button>

          <div>
            <div class="flex flex-wrap items-center gap-2">
              <h1 class="text-xl sm:text-2xl font-heading font-bold text-foreground">
                Order Details
              </h1>

              <!-- Order Number Pill -->
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-muted text-foreground border border-border/70">
                #{{ order.order_number }}
              </span>

              <!-- Category Pill -->
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border',
                  isAirman
                    ? 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800'
                    : 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                ]"
              >
                {{ isAirman ? 'Airman' : 'Officer' }}
              </span>
            </div>

            <!-- Customer Name Subtitle -->
            <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wide mt-1">
              {{ customerName }}
            </p>
          </div>
        </div>

        <!-- Status Badge / Selector Dropdown -->
        <div class="relative shrink-0">
          <button
            type="button"
            @click="statusDropdownOpen = !statusDropdownOpen"
            :class="[
              'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border shadow-2xs transition-all cursor-pointer',
              currentStatusConfig.badgeClass
            ]"
          >
            <span class="h-2 w-2 rounded-full" :class="currentStatusConfig.dotClass"></span>
            {{ currentStatusConfig.label }}
            <ChevronDown class="h-3.5 w-3.5 opacity-70 ml-0.5" />
          </button>

          <!-- Status Dropdown Menu -->
          <div
            v-if="statusDropdownOpen"
            class="absolute right-0 mt-1.5 w-48 rounded-xl border border-border bg-popover text-popover-foreground shadow-lg py-1 z-30 animate-in fade-in zoom-in-95"
          >
            <button
              v-for="st in statusOptions"
              :key="st.key"
              type="button"
              @click="changeStatus(st.key)"
              class="w-full text-left px-3 py-2 text-xs font-medium hover:bg-muted/70 flex items-center justify-between cursor-pointer"
            >
              <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full" :class="st.dotClass"></span>
                <span>{{ st.label }}</span>
              </div>
              <Check v-if="order.status === st.key" class="h-3.5 w-3.5 text-primary" />
            </button>
          </div>
        </div>
      </div>

      <!-- ── CARD 1: CUSTOMER INFORMATION ── -->
      <div class="rounded-2xl border border-border/70 bg-card p-5 shadow-2xs space-y-3">
        <h2 class="text-sm font-bold text-foreground">Customer Information</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <p class="text-xs text-muted-foreground">Customer Name</p>
            <p class="text-sm font-semibold text-foreground mt-0.5 uppercase">
              {{ customerName }}
            </p>
          </div>
          <div>
            <p class="text-xs text-muted-foreground">Phone</p>
            <p class="text-sm font-semibold text-foreground mt-0.5 font-mono">
              {{ order.customer?.phone || '-' }}
            </p>
          </div>
          <div>
            <p class="text-xs text-muted-foreground">BD Number</p>
            <p class="text-sm font-semibold font-mono text-foreground mt-0.5">
              {{ order.customer?.bd_number || order.bd_number || '-' }}
            </p>
          </div>
        </div>
      </div>

      <!-- ── CARD 2: ORDER INFORMATION ── -->
      <div class="rounded-2xl border border-border/70 bg-card p-5 shadow-2xs space-y-4">
        <h2 class="text-sm font-bold text-foreground">Order Information</h2>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div>
            <p class="text-xs text-muted-foreground">Order Date</p>
            <p class="text-sm font-semibold text-foreground mt-0.5 font-mono">
              {{ order.order_date || '-' }}
            </p>
          </div>
          <div>
            <p class="text-xs text-muted-foreground">Delivery Date</p>
            <p class="text-sm font-semibold text-foreground mt-0.5 font-mono">
              {{ order.delivery_date || '-' }}
            </p>
          </div>
          <div>
            <p class="text-xs text-muted-foreground">Delivered Date</p>
            <p class="text-sm font-semibold text-foreground mt-0.5 font-mono">
              {{ order.delivered_date || '-' }}
            </p>
          </div>
          <div>
            <p class="text-xs text-muted-foreground">{{ isAirman ? 'Trade / Office' : 'Branch' }}</p>
            <p class="text-sm font-semibold text-foreground mt-0.5">
              {{ isAirman ? (order.trade || order.office || '-') : (order.branch || '-') }}
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 pt-1">
          <div>
            <p class="text-xs text-muted-foreground">Ribon</p>
            <p class="text-sm font-semibold text-foreground mt-0.5 font-mono">
              {{ order.ribon != null && order.ribon !== '' ? order.ribon : '-' }}
            </p>
          </div>
        </div>

        <div class="space-y-3 pt-1">
          <div>
            <p class="text-xs text-muted-foreground">Receiver Name</p>
            <p class="text-sm font-semibold text-foreground mt-0.5">
              {{ order.receiver_name || '-' }}
            </p>
          </div>

          <div>
            <p class="text-xs text-muted-foreground">Extra Notes</p>
            <p class="text-sm font-semibold text-foreground mt-0.5">
              {{ order.extra_notes ? order.extra_notes.trim() : '-' }}
            </p>
          </div>
        </div>
      </div>

      <!-- ── CARD 3: PAYMENT INFORMATION ── -->
      <div class="rounded-2xl border border-border/70 bg-card p-5 shadow-2xs space-y-4">
        <h2 class="text-sm font-bold text-foreground">Payment Information</h2>

        <!-- Horizontal 3 Stats -->
        <div class="grid grid-cols-3 gap-2 sm:gap-4">
          <div class="min-w-0">
            <p class="text-xs text-muted-foreground truncate">Total</p>
            <p class="text-sm sm:text-lg font-bold text-foreground mt-0.5 font-mono truncate" :title="'৳' + formatMoney(order.total_amount)">
              ৳{{ formatMoney(order.total_amount) }}
            </p>
          </div>
          <div class="min-w-0">
            <p class="text-xs text-muted-foreground truncate">Advance</p>
            <p class="text-sm sm:text-lg font-bold text-emerald-600 mt-0.5 font-mono truncate" :title="'৳' + formatMoney(order.advance)">
              ৳{{ formatMoney(order.advance) }}
            </p>
          </div>
          <div class="min-w-0">
            <p class="text-xs text-muted-foreground truncate">Due</p>
            <p
              :class="[
                'text-sm sm:text-lg font-bold mt-0.5 font-mono truncate',
                Number(order.due || 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600'
              ]"
              :title="'৳' + formatMoney(order.due)"
            >
              ৳{{ formatMoney(order.due) }}
            </p>
          </div>
        </div>

        <!-- Sub-card: Payment History -->
        <div class="rounded-xl border border-border/60 bg-muted/15 p-4 space-y-3">
          <div class="flex items-center gap-1.5 text-xs font-bold text-foreground">
            <Clock class="h-3.5 w-3.5 text-muted-foreground" />
            <span>Payment History</span>
          </div>

          <!-- History Rows -->
          <div v-if="paymentList.length > 0" class="space-y-2">
            <div
              v-for="pay in paymentList"
              :key="pay.id"
              class="flex items-center justify-between py-1 text-xs"
            >
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-muted border border-border/60 text-muted-foreground capitalize">
                  {{ pay.payment_type || 'Payment' }}
                </span>
                <span class="font-medium text-foreground">
                  {{ pay.note || (pay.payment_type === 'advance' ? 'Advance Payment' : 'Payment') }}
                </span>
              </div>

              <div class="flex items-center gap-2 font-mono">
                <span class="font-bold text-emerald-600">+৳{{ formatMoney(pay.amount) }}</span>
                <span class="text-muted-foreground text-[11px]">{{ formatDisplayDate(pay.created_at) }}</span>
              </div>
            </div>
          </div>
          <div v-else class="text-xs text-muted-foreground italic py-1">
            No payment history recorded yet
          </div>

          <!-- Total Paid Footer -->
          <div class="border-t border-border/50 pt-2.5 flex items-center justify-between text-xs">
            <span class="font-bold text-foreground">Total Paid</span>
            <span class="font-bold text-sm sm:text-base text-emerald-600 font-mono">
              ৳{{ formatMoney(totalPaid) }}
            </span>
          </div>
        </div>
      </div>

      <!-- ── CARD 4: ITEMS (COUNT) ── -->
      <div class="rounded-2xl border border-border/70 bg-card p-5 shadow-2xs space-y-3">
        <h2 class="text-sm font-bold text-foreground">
          Items ({{ groupedItems.length }})
        </h2>

        <div class="space-y-2">
          <div
            v-for="(group, idx) in groupedItems"
            :key="idx"
            class="rounded-xl border border-border/70 bg-card p-3 px-4 flex items-center justify-between gap-3 shadow-2xs hover:border-primary/40 transition-colors"
          >
            <!-- Left: Item Name, Quantity and Download Indication -->
            <div class="flex items-center gap-2 flex-wrap">
              <p class="text-xs sm:text-sm font-semibold text-foreground">
                {{ group.name }} - Quantity - {{ group.quantity }}
              </p>
              <span
                v-if="isAirman && isGroupDownloaded(group)"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200 dark:bg-green-950/40 dark:text-green-300 dark:border-green-800"
              >
                <CheckCircle class="h-3 w-3 text-green-600" /> Downloaded
              </span>
              <span
                v-else-if="isAirman"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800"
              >
                Not Downloaded
              </span>
            </div>

            <!-- Right: Cutting Slip Button -->
            <button
              type="button"
              @click="printSingleGroupSlip(group)"
              class="h-8 px-3 inline-flex items-center gap-1.5 rounded-lg border border-border bg-card hover:bg-accent text-foreground text-xs font-medium shadow-2xs transition-colors cursor-pointer"
            >
              <Printer class="h-3.5 w-3.5 text-muted-foreground" />
              Cutting Slip
            </button>
          </div>
        </div>
      </div>

      <!-- ── CARD 5: MEASUREMENTS ── -->
      <div class="rounded-2xl border border-border/70 bg-card p-5 shadow-2xs space-y-4">
        <h2 class="text-sm font-bold text-foreground">Measurements</h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <!-- Left: SHIRT MEASUREMENTS -->
          <div class="rounded-xl border border-border/70 p-4 space-y-4 bg-muted/10">
            <h3 class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
              SHIRT MEASUREMENTS
            </h3>

            <!-- Grid of Metric Chips -->
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
              <div
                v-for="field in shirtDisplayFields"
                :key="field.key"
                class="rounded-lg border border-border/60 bg-card p-2 text-center shadow-2xs"
              >
                <p class="text-[10px] font-semibold text-muted-foreground uppercase">
                  {{ field.label }}
                </p>
                <p class="text-xs sm:text-sm font-bold text-foreground mt-0.5 font-mono">
                  {{ shirtMeasurements[field.key] || '-' }}
                </p>
              </div>
            </div>

            <!-- Notes -->
            <div class="space-y-1">
              <p class="text-xs font-semibold text-muted-foreground">Notes</p>
              <div class="w-full min-h-[38px] p-2.5 rounded-lg border border-border/70 bg-card text-xs text-foreground font-medium shadow-2xs flex items-center">
                {{ notesText || '-' }}
              </div>
            </div>

            <!-- Extra Notes -->
            <div class="space-y-1">
              <p class="text-xs font-semibold text-muted-foreground">Extra Notes</p>
              <div class="w-full min-h-[38px] p-2.5 rounded-lg border border-border/70 bg-card text-xs text-foreground font-medium shadow-2xs flex items-center">
                {{ extraNotesText || '-' }}
              </div>
            </div>
          </div>

          <!-- Right: PANT MEASUREMENTS -->
          <div class="rounded-xl border border-border/70 p-4 space-y-4 bg-muted/10">
            <h3 class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
              PANT MEASUREMENTS
            </h3>

            <!-- Grid of Metric Chips -->
            <div class="grid grid-cols-3 gap-2.5">
              <div
                v-for="field in pantDisplayFields"
                :key="field.key"
                class="rounded-lg border border-border/60 bg-card p-2 text-center shadow-2xs"
              >
                <p class="text-[10px] font-semibold text-muted-foreground uppercase">
                  {{ field.label }}
                </p>
                <p class="text-xs sm:text-sm font-bold text-foreground mt-0.5 font-mono">
                  {{ pantMeasurements[field.key] || '-' }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── BOTTOM ACTION BAR ── -->
      <div class="flex flex-wrap items-center gap-2.5 pt-2">
        <button
          type="button"
          @click="printFullSlip"
          class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 h-10 sm:h-9 px-4 rounded-xl border border-input bg-card hover:bg-accent text-foreground text-xs font-medium shadow-2xs transition-colors cursor-pointer"
        >
          <Printer class="h-4 w-4 text-muted-foreground" />
          Print Slip
        </button>

        <button
          type="button"
          @click="printFullInvoice"
          class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 h-10 sm:h-9 px-4 rounded-xl border border-input bg-card hover:bg-accent text-foreground text-xs font-medium shadow-2xs transition-colors cursor-pointer"
        >
          <FileText class="h-4 w-4 text-muted-foreground" />
          Invoice
        </button>

        <button
          type="button"
          @click="openEditModal"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 h-10 sm:h-9 px-4 rounded-xl border border-input bg-card hover:bg-accent text-foreground text-xs font-medium shadow-2xs transition-colors cursor-pointer"
        >
          <Edit class="h-4 w-4 text-muted-foreground" />
          Edit Order
        </button>
      </div>
    </template>

    <!-- ── EDIT ORDER MODAL ── -->
    <Teleport to="body">
      <div v-if="editModalOpen" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
        <div class="bg-card border border-border rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90dvh] flex flex-col p-4 sm:p-6 animate-in zoom-in-95 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-border">
            <h3 class="text-base font-bold text-foreground">
              Edit Order <span class="font-mono text-primary">#{{ order?.order_number }}</span>
            </h3>
            <button type="button" @click="editModalOpen = false" class="text-muted-foreground hover:text-foreground text-lg cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="saveOrderEdits" class="space-y-4 overflow-y-auto flex-1 pr-1">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-foreground mb-1">Order Date</label>
                <input
                  v-model="editForm.order_date"
                  type="date"
                  class="w-full h-8 px-2.5 text-xs rounded-md border border-input bg-background"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-foreground mb-1">Delivery Date</label>
                <input
                  v-model="editForm.delivery_date"
                  type="date"
                  class="w-full h-8 px-2.5 text-xs rounded-md border border-input bg-background"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-medium text-foreground mb-1">Total Amount (৳)</label>
                <input
                  v-model.number="editForm.total_amount"
                  type="number"
                  step="any"
                  class="w-full h-8 px-2.5 text-xs rounded-md border border-input bg-background font-mono"
                  @input="recalcDue"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-foreground mb-1">Advance (৳)</label>
                <input
                  v-model.number="editForm.advance"
                  type="number"
                  step="any"
                  class="w-full h-8 px-2.5 text-xs rounded-md border border-input bg-background font-mono"
                  @input="recalcDue"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-foreground mb-1">Due (৳)</label>
                <input
                  v-model.number="editForm.due"
                  type="number"
                  step="any"
                  class="w-full h-8 px-2.5 text-xs rounded-md border border-input bg-background font-mono"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-foreground mb-1">Receiver Name</label>
                <input
                  v-model="editForm.receiver_name"
                  type="text"
                  class="w-full h-8 px-2.5 text-xs rounded-md border border-input bg-background"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-foreground mb-1">Receiver Phone</label>
                <input
                  v-model="editForm.receiver_phone"
                  type="text"
                  class="w-full h-8 px-2.5 text-xs rounded-md border border-input bg-background"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium text-foreground mb-1">Branch / Trade</label>
                <input
                  v-model="editForm.branch"
                  type="text"
                  class="w-full h-8 px-2.5 text-xs rounded-md border border-input bg-background"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-foreground mb-1">Ribon</label>
                <input
                  v-model="editForm.ribon"
                  type="number"
                  class="w-full h-8 px-2.5 text-xs rounded-md border border-input bg-background"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-medium text-foreground mb-1">Extra Notes</label>
              <textarea
                v-model="editForm.extra_notes"
                rows="2"
                class="w-full p-2 text-xs rounded-md border border-input bg-background"
              ></textarea>
            </div>

            <div>
              <label class="block text-xs font-medium text-foreground mb-1">Measurement Notes</label>
              <textarea
                v-model="editForm.measurement_notes"
                rows="2"
                class="w-full p-2 text-xs rounded-md border border-input bg-background"
              ></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-border">
              <button
                type="button"
                @click="editModalOpen = false"
                class="h-8 px-3 text-xs border border-input rounded-md hover:bg-accent cursor-pointer"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="h-8 px-4 text-xs bg-primary text-primary-foreground font-semibold rounded-md hover:bg-primary/90 cursor-pointer flex items-center gap-1.5"
              >
                <Loader2 v-if="saving" class="h-3 w-3 animate-spin" />
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  ArrowLeft, Printer, FileText, Edit, ChevronDown, Check, Loader2, Clock, CheckCircle
} from 'lucide-vue-next';
import api from '@/lib/api';
import { generateInvoicePDF, generateSlipPDF } from '@/lib/pdf';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const saving = ref(false);
const order = ref(null);
const statusDropdownOpen = ref(false);
const editModalOpen = ref(false);

const editForm = ref({
  order_date: '',
  delivery_date: '',
  total_amount: 0,
  advance: 0,
  due: 0,
  receiver_name: '',
  receiver_phone: '',
  branch: '',
  ribon: null,
  extra_notes: '',
  measurement_notes: '',
});

const isAirman = computed(() => {
  return order.value?.order_category === 'airman';
});

const customerName = computed(() => {
  return (order.value?.customer?.name || order.value?.customer_name || 'UNKNOWN').trim();
});

const statusOptions = [
  {
    key: 'pending',
    label: 'Pending',
    dotClass: 'bg-amber-500',
    badgeClass: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
  },
  {
    key: 'due_payment',
    label: 'Cutting Slip ~ Done',
    dotClass: 'bg-sky-500',
    badgeClass: 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800',
  },
  {
    key: 'delivered',
    label: 'Delivered',
    dotClass: 'bg-emerald-500',
    badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
  },
];

const currentStatusConfig = computed(() => {
  const st = order.value?.status || 'pending';
  return statusOptions.find(o => o.key === st) || {
    key: st,
    label: st,
    dotClass: 'bg-slate-400',
    badgeClass: 'bg-slate-100 text-slate-700 border-slate-200',
  };
});

// Financials & Payment list
const paymentList = computed(() => {
  const ph = order.value?.payment_history || order.value?.paymentHistory || [];
  return [...ph].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

const totalPaid = computed(() => {
  if (paymentList.value.length > 0) {
    return paymentList.value.reduce((sum, p) => sum + Number(p.amount || 0), 0);
  }
  const adv = Number(order.value?.advance || 0);
  const total = Number(order.value?.total_amount || 0);
  const due = Number(order.value?.due || 0);
  if (due === 0 && total > 0) return total;
  return adv;
});

// Grouped items
const groupedItems = computed(() => {
  const items = order.value?.items || [];
  const map = new Map();

  items.forEach((item) => {
    const name = (item.item_name || item.item_type || 'Item').trim();
    if (map.has(name)) {
      const g = map.get(name);
      g.quantity += Number(item.quantity || 1);
      g.rawItems.push(item);
    } else {
      map.set(name, {
        name,
        quantity: Number(item.quantity || 1),
        rawItems: [item],
      });
    }
  });

  return Array.from(map.values());
});

// Measurement Display Fields
const shirtDisplayFields = [
  { key: 'length', label: 'LENGTH' },
  { key: 'chest', label: 'CHEST' },
  { key: 'belly', label: 'BELLY' },
  { key: 'hip', label: 'HIP' },
  { key: 'shoulder', label: 'SHOULDER' },
  { key: 'sleeve', label: 'SLEEVE' },
  { key: 'collar', label: 'COLLAR' },
  { key: 'front', label: 'FRONT' },
  { key: 'back', label: 'BACK' },
];

const pantDisplayFields = [
  { key: 'length', label: 'LENGTH' },
  { key: 'waist', label: 'WAIST' },
  { key: 'hip', label: 'HIP' },
  { key: 'high', label: 'HIGH' },
  { key: 'thai', label: 'THAI' },
  { key: 'leg_open', label: 'LEG OPEN' },
];

// Extracted shirt & pant measurements
const shirtMeasurements = computed(() => {
  const result = {};
  const items = order.value?.items || [];
  for (const it of items) {
    const m = it.measurements || {};
    for (const f of shirtDisplayFields) {
      if (!result[f.key] && m[f.key]) {
        result[f.key] = m[f.key];
      }
    }
  }
  return result;
});

const pantMeasurements = computed(() => {
  const result = {};
  const items = order.value?.items || [];
  for (const it of items) {
    const m = it.measurements || {};
    for (const f of pantDisplayFields) {
      const val = m[`pant_${f.key}`] || m[f.key];
      if (!result[f.key] && val) {
        result[f.key] = val;
      }
    }
  }
  return result;
});

const notesText = computed(() => {
  const items = order.value?.items || [];
  for (const it of items) {
    if (it.notes && it.notes.trim()) {
      return it.notes.trim();
    }
  }
  return order.value?.measurement_notes ? order.value.measurement_notes.trim() : '';
});

const extraNotesText = computed(() => {
  return order.value?.extra_notes ? order.value.extra_notes.trim() : '';
});

function formatMoney(amount) {
  const num = Number(amount || 0);
  return num.toLocaleString();
}

function formatDisplayDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  return `${day}/${month}/${year}`;
}

async function fetchOrder() {
  loading.value = true;
  try {
    const res = await api.get(`/orders/${route.params.id}`);
    order.value = res.data?.data || res.data;
  } catch (err) {
    console.error('Failed to load order', err);
    order.value = null;
  } finally {
    loading.value = false;
  }
}

async function changeStatus(newStatus) {
  statusDropdownOpen.value = false;
  if (!order.value || order.value.status === newStatus) return;

  try {
    const payload = { status: newStatus };
    if (newStatus === 'delivered' && !order.value.delivered_date) {
      payload.delivered_date = new Date().toISOString().split('T')[0];
    }
    const res = await api.put(`/orders/${order.value.id}`, payload);
    order.value = res.data?.data || res.data || { ...order.value, ...payload };
  } catch (err) {
    console.error('Failed to change status', err);
  }
}

function isGroupDownloaded(group) {
  if (!order.value) return false;
  const cats = Array.isArray(order.value.downloaded_categories)
    ? order.value.downloaded_categories.map(c => String(c).toLowerCase().trim())
    : [];
  const target = String(group.name || '').toLowerCase().trim();
  return cats.includes(target);
}

async function markItemsDownloaded(items) {
  if (!order.value) return;
  const existing = Array.isArray(order.value.downloaded_categories)
    ? [...order.value.downloaded_categories.map(c => String(c).toLowerCase().trim())]
    : [];
  const toAdd = items
    .map(i => String(i.item_name || i.item_type || '').toLowerCase().trim())
    .filter(Boolean);

  let changed = false;
  const updated = [...existing];
  for (const cat of toAdd) {
    if (!updated.includes(cat)) {
      updated.push(cat);
      changed = true;
    }
  }

  if (changed) {
    order.value.downloaded_categories = updated;
    try {
      await api.put(`/orders/${order.value.id}`, { downloaded_categories: updated });
    } catch (e) {
      console.error('Failed to update downloaded_categories', e);
    }
  }
}

function printFullSlip() {
  if (!order.value) return;
  generateSlipPDF(order.value, order.value.customer || {}, order.value.items || []);
  if (isAirman.value) {
    markItemsDownloaded(order.value.items || []);
  }
}

function printSingleGroupSlip(group) {
  if (!order.value) return;
  generateSlipPDF(order.value, order.value.customer || {}, group.rawItems || []);
  if (isAirman.value) {
    markItemsDownloaded(group.rawItems || [{ item_name: group.name }]);
  }
}

function printFullInvoice() {
  if (!order.value) return;
  generateInvoicePDF(order.value, order.value.customer || {}, order.value.items || []);
}

function openEditModal() {
  if (!order.value) return;
  if (!isAirman.value) {
    router.push(`/orders/${order.value.id}/edit`);
    return;
  }
  router.push(`/airman/${order.value.id}/edit`);
}

function recalcDue() {
  editForm.value.due = Math.max(0, Number(editForm.value.total_amount || 0) - Number(editForm.value.advance || 0));
}

async function saveOrderEdits() {
  if (!order.value) return;
  saving.value = true;
  try {
    const res = await api.put(`/orders/${order.value.id}`, {
      ...editForm.value,
    });
    order.value = res.data?.data || res.data || { ...order.value, ...editForm.value };
    editModalOpen.value = false;
  } catch (err) {
    console.error('Failed to update order', err);
  } finally {
    saving.value = false;
  }
}

function goBack() {
  if (window.history.length > 1) {
    router.back();
  } else {
    router.push(isAirman.value ? '/airman' : '/orders');
  }
}

onMounted(() => {
  fetchOrder();
});
</script>
