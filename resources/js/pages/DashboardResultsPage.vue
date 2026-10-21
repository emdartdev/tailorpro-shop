<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
      <router-link
        to="/dashboard"
        class="h-9 w-9 inline-flex items-center justify-center rounded-lg border border-input bg-background hover:bg-accent text-foreground transition-colors"
      >
        <ArrowLeft class="h-4 w-4" />
      </router-link>
      <div>
        <h1 class="text-2xl font-heading font-bold text-foreground">{{ filterLabel }}</h1>
        <p class="text-xs text-muted-foreground">{{ t('dashboardReportResults') }}</p>
      </div>
    </div>

    <!-- Date Range Filter Bar -->
    <div class="rounded-xl border border-border bg-card p-4 shadow-sm flex flex-wrap items-center gap-3">
      <span class="text-xs font-medium text-muted-foreground">{{ t('filterByDate') }}:</span>
      <input
        v-model="dateFrom"
        type="date"
        class="h-8 px-2 text-xs rounded-md border border-input bg-background"
      />
      <span class="text-xs text-muted-foreground">→</span>
      <input
        v-model="dateTo"
        type="date"
        class="h-8 px-2 text-xs rounded-md border border-input bg-background"
      />
      <button
        v-if="dateFrom || dateTo"
        type="button"
        @click="clearDateFilter"
        class="h-8 px-2 text-xs text-muted-foreground hover:text-foreground inline-flex items-center gap-1"
      >
        <X class="h-3 w-3" /> {{ t('clearFilter') }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-16">
      <Loader2 class="h-8 w-8 animate-spin text-primary" />
    </div>

    <!-- Orders Table -->
    <div v-else class="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
      <div class="p-4 border-b border-border flex items-center justify-between">
        <span class="text-xs font-semibold text-muted-foreground uppercase">
          {{ filteredOrders.length }} {{ t('ordersFound') }}
        </span>
        <span class="text-xs font-bold text-foreground">
          {{ t('total') }}: ৳{{ totalAmount.toLocaleString() }}
        </span>
      </div>

      <!-- Mobile Card List (< md) -->
      <div class="md:hidden divide-y divide-border/60">
        <div v-if="filteredOrders.length === 0" class="text-center py-8 text-muted-foreground text-xs p-4">
          {{ t('noOrdersMatchingSearch') }}
        </div>
        <div
          v-else
          v-for="order in filteredOrders"
          :key="'mob-' + order.id"
          class="p-4 space-y-3 hover:bg-muted/30 transition-colors"
        >
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
              <span class="font-mono font-bold text-sm text-foreground">#{{ order.order_number }}</span>
              <span class="block text-xs font-semibold text-foreground mt-0.5 truncate">{{ order.customer?.name || 'Unknown' }}</span>
              <span v-if="order.customer?.bd_number" class="text-[11px] text-muted-foreground font-mono block truncate">BD: {{ order.customer?.bd_number }}</span>
            </div>
            <OrderStatusBadge :status="order.status" class="shrink-0" />
          </div>

          <div class="grid grid-cols-3 gap-2 py-2 px-3 rounded-xl bg-muted/40 text-xs text-center">
            <div class="min-w-0">
              <span class="text-[10px] text-muted-foreground uppercase font-semibold block truncate">Total</span>
              <span class="font-bold text-foreground truncate block">৳{{ (order.total_amount || 0).toLocaleString() }}</span>
            </div>
            <div class="min-w-0">
              <span class="text-[10px] text-muted-foreground uppercase font-semibold block truncate">Paid</span>
              <span class="font-semibold text-emerald-600 dark:text-emerald-400 truncate block">৳{{ (order.paid_amount || 0).toLocaleString() }}</span>
            </div>
            <div class="min-w-0">
              <span class="text-[10px] text-muted-foreground uppercase font-semibold block truncate">Due</span>
              <span
                class="font-bold truncate block"
                :class="order.due_amount > 0 ? 'text-destructive' : 'text-foreground'"
              >
                ৳{{ (order.due_amount || 0).toLocaleString() }}
              </span>
            </div>
          </div>

          <div class="flex items-center justify-between text-xs text-muted-foreground pt-0.5">
            <span>{{ order.order_date }}</span>
            <router-link
              :to="'/orders?search=' + order.order_number"
              class="text-primary hover:underline font-semibold"
            >
              {{ t('view') }} →
            </router-link>
          </div>
        </div>
      </div>

      <!-- Desktop Table (md+) -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-xs text-left">
          <thead class="bg-muted/50 text-muted-foreground uppercase text-[11px] font-semibold border-b border-border">
            <tr>
              <th class="px-4 py-3">{{ t('orderNo') }}</th>
              <th class="px-4 py-3">{{ t('customer') }}</th>
              <th class="px-4 py-3">{{ t('date') }}</th>
              <th class="px-4 py-3 text-right">{{ t('total') }}</th>
              <th class="px-4 py-3 text-right">{{ t('paid') }}</th>
              <th class="px-4 py-3 text-right">{{ t('due') }}</th>
              <th class="px-4 py-3 text-center">{{ t('status') }}</th>
              <th class="px-4 py-3 text-center">{{ t('actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/60">
            <tr v-if="filteredOrders.length === 0">
              <td colspan="8" class="text-center py-8 text-muted-foreground">
                {{ t('noOrdersMatchingSearch') }}
              </td>
            </tr>
            <tr
              v-for="order in filteredOrders"
              :key="order.id"
              class="hover:bg-muted/30 transition-colors"
            >
              <td class="px-4 py-3 font-mono font-bold text-foreground">#{{ order.order_number }}</td>
              <td class="px-4 py-3 font-medium text-foreground">
                <span class="block font-semibold">{{ order.customer?.name || 'Unknown' }}</span>
                <span class="text-[11px] text-muted-foreground font-mono">{{ order.customer?.bd_number || '' }}</span>
              </td>
              <td class="px-4 py-3 text-muted-foreground">{{ order.order_date }}</td>
              <td class="px-4 py-3 text-right font-medium">৳{{ (order.total_amount || 0).toLocaleString() }}</td>
              <td class="px-4 py-3 text-right text-green-600 font-medium">৳{{ (order.paid_amount || 0).toLocaleString() }}</td>
              <td class="px-4 py-3 text-right font-bold" :class="order.due_amount > 0 ? 'text-destructive' : 'text-foreground'">
                ৳{{ (order.due_amount || 0).toLocaleString() }}
              </td>
              <td class="px-4 py-3 text-center">
                <OrderStatusBadge :status="order.status" />
              </td>
              <td class="px-4 py-3 text-center">
                <router-link
                  :to="'/orders?search=' + order.order_number"
                  class="text-primary hover:underline font-medium"
                >
                  {{ t('view') }}
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { ArrowLeft, Loader2, X } from 'lucide-vue-next';
import { t, currentLang } from '@/lib/language';
import OrderStatusBadge from '@/components/OrderStatusBadge.vue';
import api from '@/lib/api';

const route = useRoute();
const filter = ref(route.query.filter || 'all');
const orders = ref([]);
const loading = ref(true);

const dateFrom = ref('');
const dateTo = ref('');

const FILTER_LABELS = {
  today: { en: "Today's Orders", bn: "আজকের অর্ডার" },
  today_due: { en: "Today's Due", bn: "আজকের বকেয়া" },
  today_collection: { en: "Today's Collection", bn: "আজকের আদায়" },
  today_sales: { en: "Today's Sales", bn: "আজকের বিক্রি" },
  weekly: { en: "Weekly Orders", bn: "সাপ্তাহিক অর্ডার" },
  weekly_due: { en: "Weekly Due", bn: "সাপ্তাহিক বকেয়া" },
  weekly_collection: { en: "Weekly Collection", bn: "সাপ্তাহিক আদায়" },
  weekly_sales: { en: "Weekly Sales", bn: "সাপ্তাহিক বিক্রি" },
  monthly: { en: "Monthly Orders", bn: "মাসিক অর্ডার" },
  monthly_due: { en: "Monthly Due", bn: "মাসিক বকেয়া" },
  monthly_collection: { en: "Monthly Collection", bn: "মাসিক আদায়" },
  monthly_sales: { en: "Monthly Sales", bn: "মাসিক বিক্রি" },
  all: { en: "Total Orders", bn: "মোট অর্ডার" },
  total_due: { en: "Total Due", bn: "মোট বকেয়া" },
  total_collection: { en: "Total Collection", bn: "মোট আদায়" },
  total_sales: { en: "Total Sales", bn: "মোট বিক্রি" },
  upcoming: { en: "Upcoming Deliveries", bn: "আসন্ন ডেলিভারি" },
  delivered_all: { en: "Delivered Orders", bn: "ডেলিভারড অর্ডার" },
};

const filterLabel = computed(() => {
  const item = FILTER_LABELS[filter.value];
  if (!item) return filter.value;
  return currentLang.value === 'bn' ? item.bn : item.en;
});

const filteredOrders = computed(() => {
  return orders.value.filter(o => {
    if (dateFrom.value && o.order_date < dateFrom.value) return false;
    if (dateTo.value && o.order_date > dateTo.value) return false;
    return true;
  });
});

const totalAmount = computed(() => {
  return filteredOrders.value.reduce((s, o) => s + Number(o.total_amount || 0), 0);
});

function clearDateFilter() {
  dateFrom.value = '';
  dateTo.value = '';
}

async function fetchOrders() {
  loading.value = true;
  try {
    const res = await api.get('/dashboard/results', { params: { filter: filter.value } });
    orders.value = res.data.data || [];
  } catch (err) {
    console.error('Failed to load dashboard report results', err);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchOrders();
});
</script>
