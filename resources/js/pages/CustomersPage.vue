<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-heading font-bold text-foreground">{{ t('customers') }}</h1>
        <p class="text-xs text-muted-foreground mt-0.5">{{ t('manageCustomers') || 'Manage all customers' }}</p>
      </div>

      <div class="flex items-center gap-2">
        <!-- Export Dropdown -->
        <div class="relative" @click.stop>
          <button
            type="button"
            @click="exportOpen = !exportOpen"
            class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-xl border border-input bg-card text-foreground text-xs font-medium hover:bg-accent transition-colors shadow-xs cursor-pointer"
          >
            <Download class="h-3.5 w-3.5 text-muted-foreground" />
            <span>Export</span>
            <ChevronDown class="h-3.5 w-3.5 text-muted-foreground" />
          </button>

          <!-- Export Menu -->
          <div
            v-if="exportOpen"
            class="absolute right-0 mt-1 w-36 rounded-xl bg-card border border-border shadow-lg py-1 z-50 text-xs animate-in fade-in zoom-in-95 duration-100"
          >
            <button
              type="button"
              @click="exportCSV"
              class="w-full text-left px-3 py-2 hover:bg-muted text-foreground transition-colors cursor-pointer"
            >
              Export CSV
            </button>
            <button
              type="button"
              @click="exportPDF"
              class="w-full text-left px-3 py-2 hover:bg-muted text-foreground transition-colors cursor-pointer"
            >
              Export PDF
            </button>
          </div>
        </div>

        <!-- Add Customer Button (Solid Blue) -->
        <button
          type="button"
          @click="openAddModal"
          class="inline-flex items-center gap-1.5 h-9 px-4 rounded-xl bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 transition-colors shadow-xs cursor-pointer"
        >
          <Plus class="h-4 w-4" /> {{ t('addCustomer') }}
        </button>
      </div>
    </div>

    <!-- Category Switcher Pills: Officer vs Airman -->
    <div class="flex items-center gap-2 pt-1">
      <button
        type="button"
        @click="customerTab = 'officer'"
        :class="[
          'px-4 py-1.5 text-xs rounded-full font-medium transition-colors cursor-pointer',
          customerTab === 'officer'
            ? 'bg-primary text-primary-foreground font-semibold shadow-xs'
            : 'border border-border/70 bg-card text-muted-foreground hover:text-foreground hover:bg-muted/50'
        ]"
      >
        Officer
      </button>
      <button
        type="button"
        @click="customerTab = 'airman'"
        :class="[
          'px-4 py-1.5 text-xs rounded-full font-medium transition-colors cursor-pointer',
          customerTab === 'airman'
            ? 'bg-primary text-primary-foreground font-semibold shadow-xs'
            : 'border border-border/70 bg-card text-muted-foreground hover:text-foreground hover:bg-muted/50'
        ]"
      >
        Airman
      </button>
    </div>

    <!-- Search & Sort Row -->
    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
      <div class="relative flex-1">
        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <input
          v-model="search"
          type="text"
          placeholder="Search name, phone, or BD number..."
          class="w-full h-10 pl-10 pr-4 text-xs rounded-xl border border-border/70 bg-card text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all shadow-2xs"
        />
      </div>

      <div class="flex items-center gap-1.5 self-end sm:self-auto shrink-0">
        <ArrowUpDown class="h-3.5 w-3.5 text-muted-foreground" />
        <select
          v-model="sortBy"
          class="h-8 px-2.5 text-xs rounded-lg border border-border/70 bg-card text-foreground focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer shadow-2xs"
        >
          <option value="newest">Newest first</option>
          <option value="oldest">Oldest first</option>
          <option value="name_az">Name (A-Z)</option>
          <option value="name_za">Name (Z-A)</option>
          <option value="most_orders">Most Orders</option>
          <option value="highest_due">Highest Due</option>
          <option value="amount_high">Total Spent</option>
        </select>
      </div>
    </div>

    <!-- Counter -->
    <div class="text-xs text-muted-foreground font-medium">
      {{ filteredCustomers.length }} customers
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-16">
      <Loader2 class="h-8 w-8 animate-spin text-primary" />
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredCustomers.length === 0" class="text-center py-16 text-muted-foreground text-sm rounded-xl border border-dashed border-border bg-card/50">
      {{ t('noCustomersFound') }}
    </div>

    <!-- Customer Cards Grid (3 Columns) -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="c in visibleCustomers"
        :key="c.id"
        @click="goToCustomerHistory(c)"
        title="Click to view order history"
        class="rounded-2xl border border-border/70 bg-card p-4 shadow-2xs hover:border-primary/50 hover:shadow-xs transition-all flex flex-col justify-between gap-3.5 cursor-pointer group"
      >
        <!-- Top Row: Name, Phone, BD & Action Buttons -->
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0 flex-1">
            <h3 class="font-bold text-sm sm:text-base text-foreground group-hover:text-primary transition-colors truncate" :title="c.name">
              {{ (!c.phone || c.phone === '-') ? ('Airman ' + (ivNumberMap[c.id] || c.bd_number)) : c.name }}
            </h3>
            <p class="text-xs text-muted-foreground mt-0.5 truncate">
              {{ c.phone || '-' }}
            </p>
            <p class="text-xs font-mono text-muted-foreground truncate">
              BD: {{ c.bd_number || '-' }}
            </p>
          </div>

          <!-- Top Right Action Icons -->
          <div class="flex items-center gap-0.5 sm:gap-1 shrink-0" @click.stop>
            <!-- 0. Order History (History) -->
            <button
              type="button"
              @click="goToCustomerHistory(c)"
              title="Order History"
              class="h-7 w-7 inline-flex items-center justify-center rounded-lg hover:bg-muted text-muted-foreground hover:text-primary transition-colors cursor-pointer"
            >
              <History class="h-3.5 w-3.5" />
            </button>

            <!-- 1. New Order (ShoppingBag) -->
            <button
              type="button"
              @click="goToNewOrder(c)"
              title="New Order"
              class="h-7 w-7 inline-flex items-center justify-center rounded-lg hover:bg-muted text-primary transition-colors cursor-pointer"
            >
              <ShoppingBag class="h-3.5 w-3.5" />
            </button>

            <!-- 2. Edit Customer (Edit) -->
            <button
              type="button"
              @click="editCustomer(c)"
              title="Edit Customer"
              class="h-7 w-7 inline-flex items-center justify-center rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
            >
              <Edit class="h-3.5 w-3.5" />
            </button>

            <!-- 3. Delete Customer (Trash2) -->
            <button
              type="button"
              @click="confirmDelete(c)"
              title="Delete Customer"
              class="h-7 w-7 inline-flex items-center justify-center rounded-lg hover:bg-rose-50 text-rose-500 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
            >
              <Trash2 class="h-3.5 w-3.5" />
            </button>
          </div>
        </div>

        <!-- Bottom Row: 3 Stat Boxes (Orders, Spent, Due) -->
        <div class="grid grid-cols-3 gap-1.5 sm:gap-2 text-center pt-1">
          <!-- Orders Box -->
          <div class="p-2 sm:p-2.5 rounded-xl bg-muted/40 border border-border/40 min-w-0">
            <p class="text-sm sm:text-base font-bold text-foreground truncate">{{ c.total_orders || 0 }}</p>
            <p class="text-[10px] text-muted-foreground mt-0.5">Orders</p>
          </div>

          <!-- Spent Box -->
          <div class="p-2 sm:p-2.5 rounded-xl bg-muted/40 border border-border/40 min-w-0">
            <p class="text-xs sm:text-base font-bold text-foreground truncate" :title="'৳' + Number(c.total_spent || 0).toLocaleString()">
              ৳{{ Number(c.total_spent || 0).toLocaleString() }}
            </p>
            <p class="text-[10px] text-muted-foreground mt-0.5">Spent</p>
          </div>

          <!-- Due Box -->
          <div
            :class="[
              'p-2 sm:p-2.5 rounded-xl border min-w-0',
              Number(c.due_amount || 0) > 0
                ? 'bg-rose-50/60 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/40'
                : 'bg-muted/40 border-border/40'
            ]"
          >
            <p
              :class="[
                'text-xs sm:text-base font-bold truncate',
                Number(c.due_amount || 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-foreground'
              ]"
              :title="'৳' + Number(c.due_amount || 0).toLocaleString()"
            >
              ৳{{ Number(c.due_amount || 0).toLocaleString() }}
            </p>
            <p class="text-[10px] text-muted-foreground mt-0.5">Due</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Load More / Pagination -->
    <div v-if="hasMore" class="flex justify-center pt-4 pb-8">
      <button
        type="button"
        @click="visibleCount += PAGE_SIZE"
        class="h-9 px-5 text-xs rounded-xl border border-input bg-card hover:bg-accent font-medium text-foreground transition-colors shadow-2xs cursor-pointer"
      >
        Load More ({{ filteredCustomers.length - visibleCustomers.length }} remaining)
      </button>
    </div>

    <!-- Add / Edit Customer Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-card border border-border rounded-xl shadow-2xl max-w-sm w-full p-4 sm:p-6 animate-in zoom-in-95 space-y-4 max-h-[90dvh] overflow-y-auto">
          <div class="flex items-center justify-between pb-3 border-b border-border">
            <h3 class="text-base font-semibold">{{ editingId ? t('editCustomer') : t('addCustomer') }}</h3>
            <button type="button" @click="showModal = false" class="text-muted-foreground hover:text-foreground cursor-pointer">✕</button>
          </div>

          <form @submit.prevent="saveCustomer" class="space-y-3">
            <div>
              <label class="block text-xs font-medium text-foreground mb-1">{{ t('name') }} *</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full h-9 px-3 text-xs rounded-lg border border-input bg-background focus:outline-none focus:ring-1 focus:ring-primary"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-foreground mb-1">{{ t('phone') }}</label>
              <input
                v-model="form.phone"
                type="text"
                placeholder="017xxxxxxxx"
                class="w-full h-9 px-3 text-xs rounded-lg border border-input bg-background focus:outline-none focus:ring-1 focus:ring-primary"
              />
            </div>

            <div>
              <label class="block text-xs font-medium text-foreground mb-1">{{ t('bdNumber') }}</label>
              <input
                v-model="form.bd_number"
                type="text"
                placeholder="BD/xxxxx"
                class="w-full h-9 px-3 text-xs rounded-lg border border-input bg-background focus:outline-none focus:ring-1 focus:ring-primary"
              />
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-border mt-4">
              <button
                type="button"
                @click="showModal = false"
                class="h-8 px-3 text-xs border border-input rounded-md hover:bg-accent cursor-pointer"
              >
                {{ t('cancel') }}
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="h-8 px-4 text-xs bg-primary text-primary-foreground rounded-md hover:bg-primary/90 font-medium disabled:opacity-50 inline-flex items-center gap-1 cursor-pointer"
              >
                <Loader2 v-if="saving" class="h-3.5 w-3.5 animate-spin" />
                {{ t('save') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-xl shadow-2xl max-w-sm w-full p-6 animate-in zoom-in-95 space-y-4">
          <h3 class="text-base font-semibold">{{ t('deleteCustomer') }}</h3>
          <p class="text-xs text-muted-foreground">
            {{ t('deleteCustomerConfirm') }} "{{ customerToDelete?.name }}"?
          </p>
          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              @click="showDeleteModal = false"
              class="h-8 px-3 text-xs border border-input rounded-md hover:bg-accent cursor-pointer"
            >
              {{ t('cancel') }}
            </button>
            <button
              type="button"
              @click="executeDelete"
              class="h-8 px-3 text-xs bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90 cursor-pointer font-medium"
            >
              {{ t('remove') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  Plus, Search, Trash2, Edit, Loader2, Download, ChevronDown,
  ShoppingBag, ArrowUpDown, History
} from 'lucide-vue-next';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import { hindSiliguriBoldBase64 } from '@/lib/hindSiliguriBold';
import { t } from '@/lib/language';
import api from '@/lib/api';

const PAGE_SIZE = 24;
const router = useRouter();

const customers = ref([]);
const loading = ref(true);
const search = ref('');
const sortBy = ref('newest');
const customerTab = ref('officer');
const visibleCount = ref(PAGE_SIZE);
const exportOpen = ref(false);
const ivNumberMap = ref({});

// Add / Edit Modal
const showModal = ref(false);
const editingId = ref(null);
const form = ref({ name: '', phone: '', bd_number: '' });
const saving = ref(false);

// Delete Modal
const showDeleteModal = ref(false);
const customerToDelete = ref(null);

const filteredCustomers = computed(() => {
  let list = customers.value;

  // Filter tab (Officer has phone, Airman has '-' or empty phone)
  if (customerTab.value === 'airman') {
    list = list.filter(c => !c.phone || c.phone === '-');
  } else {
    list = list.filter(c => c.phone && c.phone !== '-');
  }

  // Filter search
  if (search.value.trim()) {
    const q = search.value.trim().toLowerCase();
    list = list.filter(c =>
      (c.name || '').toLowerCase().includes(q) ||
      (c.phone || '').includes(q) ||
      (c.bd_number || '').toLowerCase().includes(q) ||
      (ivNumberMap.value[c.id] || '').toLowerCase().includes(q)
    );
  }

  // Sort
  const res = [...list];
  switch (sortBy.value) {
    case 'oldest':
      res.reverse();
      break;
    case 'name_az':
      res.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
      break;
    case 'name_za':
      res.sort((a, b) => (b.name || '').localeCompare(a.name || ''));
      break;
    case 'most_orders':
      res.sort((a, b) => Number(b.total_orders || 0) - Number(a.total_orders || 0));
      break;
    case 'highest_due':
      res.sort((a, b) => Number(b.due_amount || 0) - Number(a.due_amount || 0));
      break;
    case 'amount_high':
      res.sort((a, b) => Number(b.total_spent || 0) - Number(a.total_spent || 0));
      break;
    case 'newest':
    default:
      // Preserves original order from API (ordered by created_at desc)
      break;
  }
  return res;
});

const visibleCustomers = computed(() => {
  return filteredCustomers.value.slice(0, visibleCount.value);
});

const hasMore = computed(() => {
  return visibleCount.value < filteredCustomers.value.length;
});

function goToCustomerHistory(customer) {
  if (!customer?.id) return;
  router.push({
    path: '/history',
    query: {
      customer: customer.id,
    },
  });
}

function goToNewOrder(customer) {
  const isAirman = !customer.phone || customer.phone === '-';
  const targetPath = isAirman ? '/airman' : '/orders';
  router.push({
    path: targetPath,
    query: {
      prefill_bd: customer.bd_number || '',
      prefill_category: isAirman ? 'airman' : 'officer',
    },
  });
}

async function fetchCustomers() {
  loading.value = true;
  try {
    const [custRes, ordRes] = await Promise.all([
      api.get('/customers'),
      api.get('/orders', { params: { category: 'airman' } }).catch(() => ({ data: { data: [] } })),
    ]);
    customers.value = custRes.data.data || [];

    const map = {};
    (ordRes.data.data || []).forEach(o => {
      if (o.iv_number && o.customer_id && !map[o.customer_id]) {
        map[o.customer_id] = o.iv_number;
      }
    });
    ivNumberMap.value = map;
  } catch (err) {
    console.error('Failed to load customers', err);
  } finally {
    loading.value = false;
  }
}

function openAddModal() {
  editingId.value = null;
  form.value = {
    name: '',
    phone: customerTab.value === 'airman' ? '-' : '',
    bd_number: '',
  };
  showModal.value = true;
}

function editCustomer(c) {
  editingId.value = c.id;
  form.value = {
    name: c.name || '',
    phone: c.phone || '',
    bd_number: c.bd_number || '',
  };
  showModal.value = true;
}

async function saveCustomer() {
  if (!form.value.name) return;
  saving.value = true;
  try {
    if (editingId.value) {
      await api.put(`/customers/${editingId.value}`, form.value);
    } else {
      await api.post('/customers', form.value);
    }
    showModal.value = false;
    await fetchCustomers();
  } catch (err) {
    console.error('Error saving customer', err);
  } finally {
    saving.value = false;
  }
}

function confirmDelete(c) {
  customerToDelete.value = c;
  showDeleteModal.value = true;
}

async function executeDelete() {
  if (!customerToDelete.value) return;
  try {
    await api.delete(`/customers/${customerToDelete.value.id}`);
    showDeleteModal.value = false;
    customerToDelete.value = null;
    await fetchCustomers();
  } catch (err) {
    console.error('Failed to delete customer', err);
  }
}

function exportCSV() {
  exportOpen.value = false;
  const headers = ['Name', 'Phone', 'BD Number', 'Total Orders', 'Total Spent', 'Due Amount'];
  const rows = filteredCustomers.value.map(c => [
    c.name,
    c.phone,
    c.bd_number,
    c.total_orders || 0,
    c.total_spent || 0,
    c.due_amount || 0,
  ]);
  const csv = [headers, ...rows].map(r => r.map(v => `"${v}"`).join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `customers-${new Date().toISOString().split('T')[0]}.csv`;
  a.click();
  URL.revokeObjectURL(url);
}

function exportPDF() {
  exportOpen.value = false;
  const doc = new jsPDF();
  const w = doc.internal.pageSize.getWidth();

  doc.setFillColor(15, 42, 74);
  doc.rect(0, 0, w, 40, 'F');
  doc.setFillColor(0, 128, 128);
  doc.rect(0, 36, w, 4, 'F');

  doc.setTextColor(255, 255, 255);
  doc.setFontSize(18);
  doc.text('Customer List Report', 14, 24);

  const tableData = filteredCustomers.value.map(c => [
    c.name,
    c.phone || '-',
    c.bd_number || '-',
    c.total_orders || 0,
    `৳${Number(c.total_spent || 0).toLocaleString()}`,
    `৳${Number(c.due_amount || 0).toLocaleString()}`,
  ]);

  autoTable(doc, {
    startY: 48,
    head: [['Name', 'Phone', 'BD #', 'Orders', 'Spent', 'Due']],
    body: tableData,
    theme: 'grid',
    styles: { fontSize: 8 },
  });

  doc.save(`customers-${new Date().toISOString().split('T')[0]}.pdf`);
}

onMounted(() => {
  fetchCustomers();
  window.addEventListener('click', () => {
    exportOpen.value = false;
  });
});
</script>
