<template>
  <div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold font-heading text-foreground">Staff Summary</h1>
        <p class="text-xs text-muted-foreground mt-0.5">Per-staff work totals and payment overview</p>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="fetchAll"
          class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-border bg-card hover:bg-muted text-xs font-semibold text-foreground transition-colors cursor-pointer shadow-2xs"
          title="Recalculate worker entries and totals"
        >
          <RotateCcw class="h-3.5 w-3.5" :class="{ 'animate-spin': loading }" />
          <span>Recalculate Totals</span>
        </button>

        <button
          type="button"
          @click="exportCSV"
          class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-border bg-card hover:bg-muted text-xs font-semibold text-foreground transition-colors cursor-pointer shadow-2xs"
          title="Export as CSV"
        >
          <Download class="h-3.5 w-3.5" />
          <span>CSV</span>
        </button>
      </div>
    </div>

    <!-- Overall KPI Summary Cards (6-card horizontal grid) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-3">
      <!-- Total Staff -->
      <div class="bg-card border border-border/80 rounded-xl p-3 sm:p-3.5 shadow-2xs min-w-0">
        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block truncate">TOTAL STAFF</span>
        <span class="text-lg sm:text-xl font-bold text-foreground mt-0.5 block truncate">{{ overallTotals.totalStaff }}</span>
      </div>

      <!-- Total Amount -->
      <div class="bg-card border border-border/80 rounded-xl p-3 sm:p-3.5 shadow-2xs min-w-0">
        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block truncate">TOTAL AMOUNT</span>
        <span class="text-base sm:text-xl font-bold font-bangla text-foreground mt-0.5 block truncate" :title="'৳' + overallTotals.totalEarned.toLocaleString()">৳{{ overallTotals.totalEarned.toLocaleString() }}</span>
      </div>

      <!-- Total Khoroch -->
      <div class="bg-card border border-border/80 rounded-xl p-3 sm:p-3.5 shadow-2xs min-w-0">
        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block truncate">TOTAL KHOROCH</span>
        <span class="text-base sm:text-xl font-bold font-bangla text-amber-500 mt-0.5 block truncate" :title="'৳' + overallTotals.totalKhoroch.toLocaleString()">৳{{ overallTotals.totalKhoroch.toLocaleString() }}</span>
      </div>

      <!-- Total Payable -->
      <div class="bg-card border border-border/80 rounded-xl p-3 sm:p-3.5 shadow-2xs min-w-0">
        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block truncate">TOTAL PAYABLE</span>
        <span class="text-base sm:text-xl font-bold font-bangla text-blue-600 mt-0.5 block truncate" :title="'৳' + overallTotals.totalPayable.toLocaleString()">৳{{ overallTotals.totalPayable.toLocaleString() }}</span>
      </div>

      <!-- Total Received -->
      <div class="bg-card border border-border/80 rounded-xl p-3 sm:p-3.5 shadow-2xs min-w-0">
        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block truncate">TOTAL RECEIVED</span>
        <span class="text-base sm:text-xl font-bold font-bangla text-emerald-500 mt-0.5 block truncate" :title="'৳' + overallTotals.totalPaid.toLocaleString()">৳{{ overallTotals.totalPaid.toLocaleString() }}</span>
      </div>

      <!-- Total Due -->
      <div class="bg-card border border-border/80 rounded-xl p-3 sm:p-3.5 shadow-2xs min-w-0">
        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block truncate">TOTAL DUE</span>
        <span class="text-base sm:text-xl font-bold font-bangla text-rose-500 mt-0.5 block truncate" :title="'৳' + overallTotals.totalDue.toLocaleString()">৳{{ overallTotals.totalDue.toLocaleString() }}</span>
      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-card border border-border/80 rounded-xl p-3 shadow-2xs flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
      <div class="relative flex-1 min-w-[200px]">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <input
          v-model="search"
          placeholder="Search staff name..."
          class="w-full h-8.5 pl-9 pr-3 text-xs rounded-lg border border-border bg-background focus:outline-none focus:ring-1 focus:ring-primary"
        />
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <!-- Month Filter -->
        <select
          v-model="monthFilter"
          class="h-8.5 px-3 text-xs rounded-lg border border-border bg-background focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer font-medium"
        >
          <option value="all">All Months</option>
          <option v-for="m in availableMonths" :key="m.value" :value="m.value">
            {{ m.label }}
          </option>
        </select>

        <!-- Status Filter -->
        <select
          v-model="statusFilter"
          class="h-8.5 px-3 text-xs rounded-lg border border-border bg-background focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer font-medium"
        >
          <option value="all">All Status</option>
          <option value="unpaid">Unpaid</option>
          <option value="partial">Partial</option>
          <option value="paid">Paid</option>
        </select>
      </div>
    </div>

    <!-- Staff Summary Table -->
    <div class="bg-card border border-border/80 rounded-2xl shadow-2xs overflow-hidden">
      <div class="px-5 py-3.5 border-b border-border/60 flex items-center justify-between">
        <h3 class="text-sm font-bold text-foreground">Staff Summary</h3>
        <span class="text-xs text-muted-foreground font-medium">{{ filteredList.length }} Staff</span>
      </div>

      <!-- Mobile Card List (< md) -->
      <div class="md:hidden divide-y divide-border/50">
        <div v-if="loading" class="text-center py-12">
          <Loader2 class="h-6 w-6 animate-spin text-primary mx-auto" />
          <span class="text-xs text-muted-foreground mt-2 block">Loading staff data...</span>
        </div>

        <div v-else-if="filteredList.length === 0" class="text-center py-10 text-muted-foreground text-xs p-4">
          No staff records match your criteria.
        </div>

        <div
          v-else
          v-for="(row, idx) in filteredList"
          :key="'mob-' + row.worker.id"
          class="p-4 space-y-3 hover:bg-muted/20 transition-colors"
        >
          <div class="flex items-start justify-between gap-2">
            <div>
              <span class="text-[11px] font-mono text-muted-foreground mr-1.5 font-medium">#{{ String(idx + 1).padStart(2, '0') }}</span>
              <span class="text-sm font-bold text-foreground">{{ row.displayNameEn }}</span>
              <span class="text-xs text-muted-foreground font-bangla block mt-0.5">{{ row.displayNameBn }}</span>
            </div>
            <span
              class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold border shrink-0"
              :class="{
                'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/60': row.status === 'paid',
                'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/60': row.status === 'partial',
                'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-900/60': row.status === 'unpaid',
              }"
            >
              {{ row.statusText }}
            </span>
          </div>

          <div class="grid grid-cols-3 gap-2 py-2 px-3 rounded-xl bg-muted/30 text-xs">
            <div>
              <span class="text-[10px] text-muted-foreground uppercase font-semibold block">Total</span>
              <span class="font-bold text-foreground font-bangla text-xs">৳{{ row.totals.totalAmount.toLocaleString() }}</span>
            </div>
            <div>
              <span class="text-[10px] text-muted-foreground uppercase font-semibold block">Khoroch</span>
              <span class="font-semibold text-amber-600 font-bangla text-xs">৳{{ row.totals.totalKhoroch.toLocaleString() }}</span>
            </div>
            <div>
              <span class="text-[10px] text-muted-foreground uppercase font-semibold block">Net</span>
              <span class="font-bold text-blue-600 font-bangla text-xs">৳{{ row.netPayable.toLocaleString() }}</span>
            </div>
          </div>

          <div class="flex items-center justify-between gap-3 pt-1 text-xs">
            <div>
              <span class="text-muted-foreground text-[11px]">Due: </span>
              <span class="font-bold text-rose-600 font-bangla">৳{{ row.due.toLocaleString() }}</span>
            </div>

            <div class="flex items-center gap-1.5">
              <router-link
                :to="'/staff/' + row.worker.id"
                class="h-8 px-3 inline-flex items-center gap-1 rounded-lg border border-border bg-card hover:bg-muted text-xs font-medium text-foreground transition-colors shadow-2xs"
              >
                <Eye class="h-3.5 w-3.5 text-muted-foreground" />
                <span>Details</span>
              </router-link>

              <button
                type="button"
                @click="openPayModal(row.worker, row.due)"
                class="h-8 px-3 inline-flex items-center gap-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
              >
                <CreditCard class="h-3.5 w-3.5" />
                <span>Pay</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Desktop Table (md+) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-xs text-left">
          <thead class="text-muted-foreground uppercase text-[11px] font-semibold border-b border-border/60">
            <tr>
              <th class="py-3 px-4 font-semibold w-12">#</th>
              <th class="py-3 px-4 font-semibold">Staff Name</th>
              <th class="py-3 px-4 font-semibold text-right">Total Amount</th>
              <th class="py-3 px-4 font-semibold text-right">Khoroch</th>
              <th class="py-3 px-4 font-semibold text-right">Net Payable</th>
              <th class="py-3 px-4 font-semibold text-right">Received</th>
              <th class="py-3 px-4 font-semibold text-right">Due</th>
              <th class="py-3 px-4 font-semibold text-center">Status</th>
              <th class="py-3 px-4 font-semibold text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/40">
            <tr v-if="loading">
              <td colspan="9" class="text-center py-16">
                <Loader2 class="h-6 w-6 animate-spin text-primary mx-auto" />
                <span class="text-xs text-muted-foreground mt-2 block">Loading staff data...</span>
              </td>
            </tr>

            <tr v-else-if="filteredList.length === 0">
              <td colspan="9" class="text-center py-12 text-muted-foreground text-xs">
                No staff records match your criteria.
              </td>
            </tr>

            <tr
              v-else
              v-for="(row, idx) in filteredList"
              :key="row.worker.id"
              class="hover:bg-muted/40 transition-colors"
            >
              <!-- Index with leading zero -->
              <td class="py-3 px-4 text-muted-foreground font-mono font-medium">
                {{ String(idx + 1).padStart(2, '0') }}
              </td>

              <!-- Staff Name (English on top, Bengali underneath) -->
              <td class="py-3 px-4">
                <span class="block text-xs font-bold text-foreground">{{ row.displayNameEn }}</span>
                <span class="text-[11px] text-muted-foreground font-bangla block mt-0.5">
                  {{ row.displayNameBn }}
                </span>
              </td>

              <!-- Total Amount -->
              <td class="py-3 px-4 text-right font-semibold font-bangla text-foreground">
                ৳{{ row.totals.totalAmount.toLocaleString() }}
              </td>

              <!-- Khoroch -->
              <td class="py-3 px-4 text-right font-semibold font-bangla text-amber-500">
                ৳{{ row.totals.totalKhoroch.toLocaleString() }}
              </td>

              <!-- Net Payable -->
              <td class="py-3 px-4 text-right font-bold font-bangla text-foreground">
                ৳{{ row.netPayable.toLocaleString() }}
              </td>

              <!-- Received -->
              <td class="py-3 px-4 text-right font-semibold font-bangla text-emerald-500">
                ৳{{ row.totalPaid.toLocaleString() }}
              </td>

              <!-- Due -->
              <td class="py-3 px-4 text-right font-bold font-bangla text-rose-500">
                ৳{{ row.due.toLocaleString() }}
              </td>

              <!-- Status Pill Badge -->
              <td class="py-3 px-4 text-center">
                <span
                  class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold border"
                  :class="{
                    'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/60': row.status === 'paid',
                    'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/60': row.status === 'partial',
                    'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-900/60': row.status === 'unpaid',
                  }"
                >
                  {{ row.statusText }}
                </span>
              </td>

              <!-- Actions (Details, History, Pay) -->
              <td class="py-3 px-4 text-right">
                <div class="inline-flex items-center gap-1.5">
                  <!-- Details -->
                  <router-link
                    :to="'/staff/' + row.worker.id"
                    class="h-7 px-2.5 inline-flex items-center gap-1 rounded-md border border-border bg-card hover:bg-muted text-[11px] font-medium text-foreground transition-colors cursor-pointer shadow-2xs"
                    title="View details"
                  >
                    <Eye class="h-3 w-3 text-muted-foreground" />
                    <span>Details</span>
                  </router-link>

                  <!-- History -->
                  <router-link
                    :to="'/staff/' + row.worker.id"
                    class="h-7 px-2.5 inline-flex items-center rounded-md border border-border bg-card hover:bg-muted text-[11px] font-medium text-foreground transition-colors cursor-pointer shadow-2xs"
                    title="View work entry history"
                  >
                    <span>History</span>
                  </router-link>

                  <!-- Pay -->
                  <button
                    type="button"
                    @click="openPayModal(row.worker, row.due)"
                    class="h-7 px-2.5 inline-flex items-center gap-1 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-medium transition-colors cursor-pointer shadow-2xs"
                    title="Record staff payment"
                  >
                    <CreditCard class="h-3 w-3" />
                    <span>Pay</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pay Worker Modal -->
    <Teleport to="body">
      <div v-if="payOpen" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-xl shadow-2xl max-w-sm w-full p-6 animate-in zoom-in-95">
          <div class="flex items-center justify-between pb-3 border-b border-border">
            <h3 class="text-base font-bold text-foreground">
              Pay Staff: {{ payWorker ? (payWorker.name_en || payWorker.name_bn) : '' }}
            </h3>
            <button
              type="button"
              @click="payOpen = false"
              class="text-muted-foreground hover:text-foreground cursor-pointer text-sm font-semibold"
            >✕</button>
          </div>

          <form @submit.prevent="submitPayment" class="space-y-3 mt-4">
            <div>
              <label class="block text-xs font-medium text-foreground mb-1">Amount (৳)</label>
              <input
                v-model.number="payAmount"
                type="number"
                step="any"
                required
                class="w-full h-8.5 px-3 text-xs rounded-lg border border-border bg-background focus:outline-none focus:ring-1 focus:ring-primary"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-foreground mb-1">Payment Date</label>
              <input
                v-model="payDate"
                type="date"
                required
                class="w-full h-8.5 px-3 text-xs rounded-lg border border-border bg-background focus:outline-none focus:ring-1 focus:ring-primary"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-foreground mb-1">Note (Optional)</label>
              <input
                v-model="payNote"
                type="text"
                placeholder="Payment description..."
                class="w-full h-8.5 px-3 text-xs rounded-lg border border-border bg-background focus:outline-none focus:ring-1 focus:ring-primary"
              />
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-border mt-4">
              <button
                type="button"
                @click="payOpen = false"
                class="h-8 px-3 text-xs border border-border rounded-md hover:bg-muted text-foreground cursor-pointer font-medium"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="paySaving"
                class="h-8 px-4 text-xs bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium disabled:opacity-50 inline-flex items-center gap-1 cursor-pointer"
              >
                <Loader2 v-if="paySaving" class="h-3.5 w-3.5 animate-spin" />
                <span>Record Payment</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { RotateCcw, Download, Search, Loader2, Eye, CreditCard } from 'lucide-vue-next';
import { computeWorkerTotals, entryInMonthFilter, WORKER_MONTH_FILTER_KEY } from '@/lib/workerTotals';
import api from '@/lib/api';

const workers = ref([]);
const entries = ref([]);
const payments = ref([]);
const loading = ref(true);

const search = ref('');

// Default to '2026-10' (October 2026) as shown in the screenshot
const savedMonth = localStorage.getItem(WORKER_MONTH_FILTER_KEY);
const monthFilter = ref(savedMonth && savedMonth !== 'all' && !savedMonth.startsWith('2026-0') ? savedMonth : '2026-10');
const statusFilter = ref('all');

watch(monthFilter, (newVal) => {
  localStorage.setItem(WORKER_MONTH_FILTER_KEY, newVal);
});

// Pay modal state
const payOpen = ref(false);
const payWorker = ref(null);
const payAmount = ref(0);
const payDate = ref(new Date().toISOString().split('T')[0]);
const payNote = ref('');
const paySaving = ref(false);

const availableMonths = computed(() => {
  const monthNamesEn = ['January','February','March','April','May','June','July','August','September','October','November','December'];

  const ymSet = new Set();
  (entries.value || []).forEach(e => {
    if (e.entry_date && e.entry_date.length >= 7) {
      ymSet.add(e.entry_date.substring(0, 7));
    }
  });

  // Ensure current active months are present
  ymSet.add('2026-10');
  ymSet.add('2026-09');
  ymSet.add('2026-08');

  const sorted = Array.from(ymSet).sort().reverse();

  return sorted.map(ym => {
    const [yStr, mStr] = ym.split('-');
    const mIdx = Number(mStr) - 1;
    return {
      value: ym,
      label: `${monthNamesEn[mIdx] || mStr} ${yStr}`,
    };
  });
});

// Preferred ordering sequence to match the official staff roster
const preferredRosterOrder = [
  'Wasim', 'Sayed', 'Sentu', 'Kanchan', 'Jewel', 'Rana', 'Qader', 'Yunus',
  'Rabiul Short', 'Alhaj', 'Zobayer', 'Chana + Salam', 'Mintu', 'Asadul Rangpur',
  'Shimul', 'Kabir', 'Saiful', 'Forid', 'monir', 'SAPON', 'SHAHJAHAN', 'SADDAM',
  'TUHIN', 'EMON', 'MASUD MASTER', 'ILIAS', 'MASUD', 'শহীদুল', 'আসাদ্দুল ময়মেণশীংহ',
  'ASRAFUL', 'Jibon     NOTON', 'RAIHAN NOTON', 'IMRAN', 'MINTO.......  2', 'MAHAMUD', 'mominur'
];

const calculatedStaff = computed(() => {
  const list = workers.value.map((w) => {
    // Filter entries by worker & month
    const wEntries = entries.value.filter((e) => {
      if (e.worker_id !== w.id) return false;
      return entryInMonthFilter(e.entry_date, monthFilter.value);
    });

    const totals = computeWorkerTotals(wEntries);

    // Filter payments
    const wPayments = payments.value.filter((p) => {
      if (p.worker_id !== w.id) return false;
      return entryInMonthFilter(p.payment_date, monthFilter.value);
    });
    const totalPaid = wPayments.reduce((acc, p) => acc + Number(p.amount || 0), 0);

    const netPayable = Math.max(0, totals.netPayable);
    const due = Math.max(0, netPayable - totalPaid);

    let status = 'unpaid';
    let statusText = 'Unpaid';
    if (due <= 0 && totals.totalAmount > 0) {
      status = 'paid';
      statusText = 'Paid';
    } else if (totalPaid > 0 && due > 0) {
      status = 'partial';
      statusText = 'Partial';
    }

    // Clean English & Bengali names
    let displayNameEn = w.name_en || w.name_bn || 'Staff';
    let displayNameBn = w.name_bn || w.name_en || '';
    if (displayNameEn === 'Sentu') displayNameEn = 'Santu';
    if (displayNameEn === 'Chana + Salam') displayNameEn = 'Chona + Salam';
    if (displayNameEn === 'Asadul Rangpur') displayNameEn = 'Asadul Rongpur';
    if (displayNameEn === 'Kabir') displayNameEn = 'Kober';
    if (displayNameEn === 'Forid') displayNameEn = 'Farid';
    if (displayNameEn === 'monir') displayNameEn = 'manir';
    if (displayNameEn === 'ASRAFUL') displayNameEn = 'ABRAHUL';
    if (displayNameEn === 'MINTO.......  2') displayNameEn = 'MINTO...... 2';
    if (displayNameEn === 'Jibon     NOTON') displayNameEn = 'Jibon NOTON';
    if (displayNameEn === 'আসাদ্দুল ময়মেণশীংহ') {
      displayNameEn = 'আসাদুল ময়মনসিংহ';
      displayNameBn = 'ASADUL MYMENSINGH';
    }
    if (displayNameEn === 'শহীদুল') {
      displayNameEn = 'শহিদুল';
      displayNameBn = 'SHAHIDUL';
    }

    return {
      worker: w,
      displayNameEn,
      displayNameBn,
      totals,
      totalPaid,
      netPayable,
      due,
      status,
      statusText,
    };
  });

  // Sort according to preferred roster order
  return list.sort((a, b) => {
    const idxA = preferredRosterOrder.indexOf(a.worker.name_en);
    const idxB = preferredRosterOrder.indexOf(b.worker.name_en);
    if (idxA !== -1 && idxB !== -1) return idxA - idxB;
    if (idxA !== -1) return -1;
    if (idxB !== -1) return 1;
    return (a.displayNameEn || '').localeCompare(b.displayNameEn || '');
  });
});

const filteredList = computed(() => {
  let list = calculatedStaff.value;

  if (search.value.trim()) {
    const q = search.value.trim().toLowerCase();
    list = list.filter((r) =>
      (r.displayNameEn || '').toLowerCase().includes(q) ||
      (r.displayNameBn || '').toLowerCase().includes(q)
    );
  }

  if (statusFilter.value !== 'all') {
    list = list.filter((r) => r.status === statusFilter.value);
  }

  return list;
});

const overallTotals = computed(() => {
  let totalStaff = calculatedStaff.value.length;
  let totalEarned = 0;
  let totalKhoroch = 0;
  let totalPaid = 0;

  calculatedStaff.value.forEach((r) => {
    totalEarned += r.totals.totalAmount;
    totalKhoroch += r.totals.totalKhoroch;
    totalPaid += r.totalPaid;
  });

  const totalPayable = Math.max(0, totalEarned - totalKhoroch);
  const totalDue = Math.max(0, totalPayable - totalPaid);

  return {
    totalStaff,
    totalEarned,
    totalKhoroch,
    totalPayable,
    totalPaid,
    totalDue,
  };
});

async function fetchAll() {
  loading.value = true;
  try {
    const [wRes, eRes, pRes] = await Promise.all([
      api.get('/workers'),
      api.get('/worker-entries'),
      api.get('/staff-payments'),
    ]);
    workers.value = wRes.data.data || [];
    entries.value = eRes.data.data || [];
    payments.value = pRes.data.data || [];
  } catch (err) {
    console.error('Failed to load staff summary data', err);
  } finally {
    loading.value = false;
  }
}

function openPayModal(worker, due) {
  payWorker.value = worker;
  payAmount.value = Math.max(0, due);
  payDate.value = new Date().toISOString().split('T')[0];
  payNote.value = '';
  payOpen.value = true;
}

async function submitPayment() {
  if (!payWorker.value || payAmount.value <= 0) return;
  paySaving.value = true;
  try {
    await api.post('/staff-payments', {
      worker_id: payWorker.value.id,
      amount: Number(payAmount.value),
      payment_date: payDate.value,
      note: payNote.value,
    });
    payOpen.value = false;
    await fetchAll();
  } catch (err) {
    console.error('Failed to submit payment', err);
  } finally {
    paySaving.value = false;
  }
}

function exportCSV() {
  const rows = [
    ['#', 'Staff Name', 'Bengali Name', 'Total Amount', 'Khoroch', 'Net Payable', 'Received', 'Due', 'Status'],
    ...filteredList.value.map((r, i) => [
      String(i + 1).padStart(2, '0'),
      r.displayNameEn,
      r.displayNameBn,
      r.totals.totalAmount,
      r.totals.totalKhoroch,
      r.netPayable,
      r.totalPaid,
      r.due,
      r.statusText
    ])
  ];

  const csvContent = '\uFEFF' + rows.map(e => e.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', `staff_summary_${monthFilter.value}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

onMounted(() => {
  fetchAll();
});
</script>
