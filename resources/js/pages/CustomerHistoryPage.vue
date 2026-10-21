<template>
  <div class="space-y-4">
    <!-- Header with Back Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <!-- Back Arrow Button -->
        <button
          type="button"
          @click="handleBack"
          class="h-9 w-9 inline-flex items-center justify-center rounded-xl border border-input bg-card hover:bg-accent text-foreground transition-colors shadow-2xs cursor-pointer shrink-0"
          title="Back"
        >
          <ArrowLeft class="h-4 w-4" />
        </button>

        <div>
          <h1 class="text-2xl font-heading font-bold text-foreground">
            {{ selectedCustomer ? getCustomerDisplayName(selectedCustomer) : t('customerHistory') }}
          </h1>
          <p class="text-xs text-muted-foreground mt-0.5">
            {{ selectedCustomer ? t('orderHistory') : t('viewHistory') || 'View complete history for a customer' }}
          </p>
        </div>
      </div>

      <!-- Detail View Action: + New Order Button -->
      <div v-if="selectedCustomer" class="shrink-0 self-start sm:self-auto">
        <button
          type="button"
          @click="goToNewOrder(selectedCustomer)"
          class="inline-flex items-center gap-1.5 h-9 px-4 rounded-xl bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 transition-colors shadow-xs cursor-pointer"
        >
          <Plus class="h-4 w-4" /> {{ t('newOrder') }}
        </button>
      </div>
    </div>

    <!-- ── LIST VIEW (WHEN NO CUSTOMER SELECTED) ── -->
    <div v-if="!selectedCustomer" class="space-y-3.5">
      <!-- Search Input Bar -->
      <div class="relative w-full sm:max-w-md">
        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <input
          v-model="searchTerm"
          type="text"
          :placeholder="t('searchHistoryPlaceholder') || 'Search by name, phone, BD number...'"
          class="w-full h-10 pl-10 pr-4 text-xs rounded-xl border border-border/70 bg-card text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
        />
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

      <!-- Slim Customer Row Cards List (Matching Attached Mockup) -->
      <div v-else class="space-y-2">
        <div
          v-for="c in visibleCustomers"
          :key="c.id"
          @click="selectCustomer(c)"
          class="rounded-xl border border-border/70 bg-card p-3 px-4 shadow-2xs hover:border-primary/40 hover:shadow-xs transition-all cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-4"
        >
          <!-- Left: Initial Avatar Circle & Customer Details -->
          <div class="flex items-center gap-3.5 min-w-0">
            <!-- Initial Circle -->
            <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 flex items-center justify-center shrink-0 font-bold text-xs">
              {{ getCustomerInitial(c) }}
            </div>

            <!-- Name and BD Subtext -->
            <div class="min-w-0 flex-1">
              <h3 class="font-bold text-sm text-foreground truncate" :title="getCustomerDisplayName(c)">
                {{ getCustomerDisplayName(c) }}
              </h3>
              <p class="text-xs text-muted-foreground font-mono truncate">
                {{ getCustomerSubtext(c) }}
              </p>
            </div>
          </div>

          <!-- Right: Horizontal 3 Stats (Orders, Spent, Due) & Eye Icon -->
          <div class="grid grid-cols-3 sm:flex sm:items-center justify-between sm:justify-end gap-2 sm:gap-6 w-full sm:w-auto shrink-0 text-center sm:text-right pt-2 sm:pt-0 border-t border-border/40 sm:border-0">
            <div class="min-w-0">
              <p class="font-bold text-sm text-foreground truncate">{{ c.total_orders || 0 }}</p>
              <p class="text-[10px] text-muted-foreground mt-0.5">Orders</p>
            </div>

            <div class="min-w-0">
              <p class="font-bold text-xs sm:text-sm text-foreground truncate" :title="'৳' + Number(c.total_spent || 0).toLocaleString()">
                ৳{{ Number(c.total_spent || 0).toLocaleString() }}
              </p>
              <p class="text-[10px] text-muted-foreground mt-0.5">Spent</p>
            </div>

            <div class="min-w-0">
              <p
                :class="['font-bold text-xs sm:text-sm truncate', Number(c.due_amount || 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-foreground']"
                :title="'৳' + Number(c.due_amount || 0).toLocaleString()"
              >
                ৳{{ Number(c.due_amount || 0).toLocaleString() }}
              </p>
              <p class="text-[10px] text-muted-foreground mt-0.5">Due</p>
            </div>

            <Eye class="h-4 w-4 text-muted-foreground/80 hover:text-foreground transition-colors ml-1 hidden sm:block shrink-0" />
          </div>
        </div>

        <!-- Load More / Pagination -->
        <div v-if="hasMore" class="flex justify-center pt-3 pb-8">
          <button
            type="button"
            @click="visibleCount += PAGE_SIZE"
            class="h-9 px-5 text-xs rounded-xl border border-input bg-card hover:bg-accent font-medium text-foreground transition-colors shadow-2xs cursor-pointer"
          >
            Load More ({{ filteredCustomers.length - visibleCustomers.length }} remaining)
          </button>
        </div>
      </div>
    </div>

    <!-- ── CUSTOMER DETAIL VIEW (WHEN A CUSTOMER IS SELECTED) ── -->
    <div v-else class="space-y-5">
      <!-- Customer Info KPI Card -->
      <div class="rounded-xl border border-border/70 bg-card p-5 shadow-2xs grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 text-sm">
        <div>
          <p class="text-xs text-muted-foreground">{{ t('name') }}</p>
          <p class="font-semibold text-foreground mt-0.5 truncate">{{ getCustomerDisplayName(selectedCustomer) }}</p>
        </div>
        <div>
          <p class="text-xs text-muted-foreground">{{ t('phone') }}</p>
          <p class="font-semibold text-foreground mt-0.5">{{ selectedCustomer.phone || '-' }}</p>
        </div>
        <div>
          <p class="text-xs text-muted-foreground">{{ t('bdNumber') }}</p>
          <p class="font-semibold font-mono text-foreground mt-0.5">{{ selectedCustomer.bd_number || '-' }}</p>
        </div>
        <div>
          <p class="text-xs text-muted-foreground">{{ t('totalOrders') }}</p>
          <p class="font-semibold text-foreground mt-0.5">{{ selectedCustomer.total_orders || orders.length }}</p>
        </div>
        <div>
          <p class="text-xs text-muted-foreground">{{ t('totalRevenue') || 'Total Spent' }}</p>
          <p class="font-semibold text-foreground mt-0.5">৳{{ Number(selectedCustomer.total_spent || 0).toLocaleString() }}</p>
        </div>
        <div>
          <p class="text-xs text-muted-foreground">{{ t('due') }}</p>
          <p
            class="font-semibold mt-0.5"
            :class="Number(selectedCustomer.due_amount || 0) > 0 ? 'text-destructive font-bold' : 'text-foreground'"
          >
            ৳{{ Number(selectedCustomer.due_amount || 0).toLocaleString() }}
          </p>
        </div>
      </div>

      <!-- Orders Timeline / Cards -->
      <div class="space-y-3">
        <h2 class="text-lg font-heading font-bold text-foreground">
          {{ t('orderHistory') }} ({{ orders.length }})
        </h2>

        <div v-if="ordersLoading" class="flex justify-center py-12">
          <Loader2 class="h-8 w-8 animate-spin text-primary" />
        </div>

        <div v-else-if="orders.length === 0" class="text-center py-12 text-muted-foreground text-sm rounded-xl border border-dashed border-border bg-card/50">
          {{ t('noOrdersYet') }}
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="order in orders"
            :key="order.id"
            @click="goToOrderDetails(order)"
            class="rounded-xl border border-border/70 bg-card p-4 shadow-2xs space-y-3 hover:border-primary/50 hover:shadow-xs transition-all cursor-pointer"
          >
            <!-- Top Header in Order Card -->
            <div class="flex flex-wrap items-center justify-between gap-2 pb-2.5 border-b border-border/60">
              <div>
                <span class="font-mono font-bold text-sm text-foreground mr-2">#{{ order.order_number }}</span>
                <span class="text-xs text-muted-foreground font-mono">
                  {{ order.order_date }}<template v-if="order.delivery_date"> → {{ order.delivery_date }}</template>
                </span>
                <p class="text-xs font-mono text-muted-foreground mt-0.5">
                  BD: {{ order.bd_number || selectedCustomer.bd_number }}
                </p>
              </div>

              <div class="flex items-center gap-2">
                <span
                  :class="[
                    'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border',
                    (order.order_category || 'officer') === 'airman'
                      ? 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800'
                      : 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                  ]"
                >
                  {{ (order.order_category || 'officer') === 'airman' ? 'Airman' : 'Officer' }}
                </span>

                <OrderStatusBadge
                  :order-id="String(order.id)"
                  :status="order.status"
                  @status-change="() => selectCustomer(selectedCustomer)"
                />
              </div>
            </div>

            <!-- Financials Summary Bar -->
            <div class="grid grid-cols-3 gap-2 py-1 text-xs">
              <div>
                <span class="text-muted-foreground">{{ t('total') }}:</span>
                <span class="font-bold text-foreground ml-1">৳{{ Number(order.total_amount || 0).toLocaleString() }}</span>
              </div>
              <div>
                <span class="text-muted-foreground">{{ t('advance') }}:</span>
                <span class="font-bold text-foreground ml-1">৳{{ Number(order.advance ?? order.paid_amount ?? 0).toLocaleString() }}</span>
              </div>
              <div>
                <span class="text-muted-foreground">{{ t('due') }}:</span>
                <span :class="['font-bold ml-1', Number(order.due ?? order.due_amount ?? 0) > 0 ? 'text-destructive' : 'text-foreground']">
                  ৳{{ Number(order.due ?? order.due_amount ?? 0).toLocaleString() }}
                </span>
              </div>
            </div>

            <!-- Order Items & Measurements Breakdown -->
            <div v-if="order.items && order.items.length > 0" class="space-y-2 pt-2 border-t border-border/60">
              <p class="text-xs font-medium text-muted-foreground">{{ t('items') }}:</p>
              <div
                v-for="(item, idx) in order.items"
                :key="idx"
                class="text-xs bg-muted/40 p-2.5 rounded-lg space-y-2 border border-border/40"
              >
                <div class="flex items-center justify-between flex-wrap gap-2">
                  <span class="font-semibold text-foreground">
                    {{ item.item_name || item.item_type }} - Qty: {{ item.quantity || 1 }}
                  </span>
                  <div class="flex items-center gap-2">
                    <span v-if="item.iv_number" class="px-1.5 py-0.5 rounded bg-muted text-[10px] font-mono border">
                      IV: {{ item.iv_number }}
                    </span>
                    <span class="font-bold text-foreground">
                      ৳{{ (Number(item.price || 0) * Number(item.quantity || 1)).toLocaleString() }}
                    </span>
                  </div>
                </div>

                <!-- Measurement Grid if available -->
                <div v-if="item.measurements && Object.keys(item.measurements).length > 0" class="pt-1.5 border-t border-border/40 space-y-1.5">
                  <span class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide block">
                    {{ t('measurements') }}
                  </span>
                  <div class="grid grid-cols-3 sm:grid-cols-6 md:grid-cols-9 gap-1 text-[11px]">
                    <div
                      v-for="(val, key) in item.measurements"
                      :key="key"
                      class="p-1 rounded bg-background border text-center"
                    >
                      <span class="block text-[9px] text-muted-foreground capitalize">{{ key }}</span>
                      <span class="font-medium text-foreground">{{ val }}</span>
                    </div>
                  </div>
                </div>

                <!-- Item Notes -->
                <div v-if="item.notes" class="text-[11px] text-muted-foreground pt-1">
                  <span class="font-medium text-foreground">{{ t('notes') }}:</span> {{ item.notes }}
                </div>
              </div>
            </div>

            <!-- Extra Notes -->
            <div v-if="order.extra_notes" class="pt-2 text-xs text-muted-foreground border-t border-border/60">
              <span class="font-medium text-foreground">{{ t('extraNotes') || 'Notes' }}:</span> {{ order.extra_notes }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ArrowLeft, Search, Loader2, Eye, Plus } from 'lucide-vue-next';
import { t } from '@/lib/language';
import OrderStatusBadge from '@/components/OrderStatusBadge.vue';
import api from '@/lib/api';

const PAGE_SIZE = 40;

const route = useRoute();
const router = useRouter();

const allCustomers = ref([]);
const selectedCustomer = ref(null);
const orders = ref([]);
const loading = ref(true);
const ordersLoading = ref(false);
const searchTerm = ref('');
const visibleCount = ref(PAGE_SIZE);
const ivNumberMap = ref({});

const filteredCustomers = computed(() => {
  const q = searchTerm.value.trim().toLowerCase();
  if (!q) return allCustomers.value;
  return allCustomers.value.filter(c =>
    (c.name || '').toLowerCase().includes(q) ||
    (c.phone || '').includes(q) ||
    (c.bd_number || '').toLowerCase().includes(q) ||
    (ivNumberMap.value[c.id] || '').toLowerCase().includes(q)
  );
});

const visibleCustomers = computed(() => {
  return filteredCustomers.value.slice(0, visibleCount.value);
});

const hasMore = computed(() => {
  return visibleCount.value < filteredCustomers.value.length;
});

function isAirmanCustomer(c) {
  return !c.phone || c.phone === '-' || c.phone === 'Lac' || c.phone === 'sgr' || c.phone === 'sgt' || c.phone === 'Wo' || c.phone === 'Faruk';
}

function getCustomerDisplayName(c) {
  if (!c) return '';
  const isAirman = !c.phone || c.phone === '-';
  if (isAirman) {
    return `Airman ${ivNumberMap.value[c.id] || c.bd_number || ''}`;
  }
  return c.name || '';
}

function getCustomerSubtext(c) {
  if (!c) return '';
  const phone = c.phone || '';
  if (phone) {
    return `${phone} · BD: ${c.bd_number || ''}`;
  }
  return ` · BD: ${c.bd_number || ''}`;
}

function getCustomerInitial(c) {
  if (!c) return '?';
  const isAirman = !c.phone || c.phone === '-';
  if (isAirman) {
    return 'A';
  }
  return c.name?.trim()?.charAt(0)?.toUpperCase() || '?';
}

function handleBack() {
  if (selectedCustomer.value) {
    if (route.query.customer || route.query.bd) {
      router.push('/customers');
    } else {
      goBackToList();
    }
  } else {
    router.push('/customers');
  }
}

function goBackToList() {
  selectedCustomer.value = null;
  orders.value = [];
  router.replace({ query: {} });
}

function goToNewOrder(c) {
  const isAirman = !c.phone || c.phone === '-';
  const targetPath = isAirman ? '/airman' : '/orders';
  router.push({
    path: targetPath,
    query: {
      prefill_bd: c.bd_number || '',
      prefill_category: isAirman ? 'airman' : 'officer',
    },
  });
}

async function fetchAllCustomers() {
  loading.value = true;
  try {
    const [custRes, ordRes] = await Promise.all([
      api.get('/customers'),
      api.get('/orders', { params: { category: 'airman' } }).catch(() => ({ data: { data: [] } })),
    ]);
    allCustomers.value = custRes.data.data || [];

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

async function selectCustomer(cust) {
  selectedCustomer.value = cust;
  ordersLoading.value = true;
  try {
    const res = await api.get(`/customers/${cust.id}/history`);
    orders.value = res.data.data || [];
  } catch (err) {
    console.error('Failed to fetch customer order history', err);
  } finally {
    ordersLoading.value = false;
  }
}

function goToOrderDetails(order) {
  router.push(`/orders/${order.id}`);
}

async function loadCustomerFromQuery() {
  const customerId = route.query.customer;
  const bdNumber = route.query.bd;

  if (customerId) {
    let found = allCustomers.value.find(c => c.id === customerId);
    if (!found && allCustomers.value.length === 0) {
      await fetchAllCustomers();
      found = allCustomers.value.find(c => c.id === customerId);
    }
    if (found) {
      await selectCustomer(found);
    } else {
      try {
        const res = await api.get(`/customers/${customerId}`);
        if (res.data.data) {
          await selectCustomer(res.data.data);
        }
      } catch (err) {
        // ignore
      }
    }
  } else if (bdNumber) {
    let found = allCustomers.value.find(c => c.bd_number === bdNumber);
    if (!found && allCustomers.value.length === 0) {
      await fetchAllCustomers();
      found = allCustomers.value.find(c => c.bd_number === bdNumber);
    }
    if (found) {
      await selectCustomer(found);
    }
  } else {
    selectedCustomer.value = null;
    orders.value = [];
  }
}

watch(
  () => [route.query.customer, route.query.bd],
  () => {
    loadCustomerFromQuery();
  }
);

onMounted(async () => {
  await fetchAllCustomers();
  await loadCustomerFromQuery();
});
</script>
