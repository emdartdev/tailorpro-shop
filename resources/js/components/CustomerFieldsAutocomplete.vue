<template>
  <div class="relative grid grid-cols-1 sm:grid-cols-3 gap-4" ref="rootContainerRef">
    <!-- ── 1. BD Number Input ── -->
    <div class="relative" ref="bdInputRef">
      <div class="flex items-center justify-between mb-1">
        <label class="block text-xs font-medium text-foreground">
          {{ airmanMode ? 'BD-Number' : 'BD Number' }}
          <span v-if="airmanMode" class="text-destructive">*</span>
        </label>
        <span v-if="matchedCustomer && matchedCustomer.bd_number === bdNumber" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium inline-flex items-center gap-0.5">
          <Check class="h-2.5 w-2.5" /> Matched
        </span>
      </div>

      <div class="relative">
        <input
          :value="bdNumber"
          @input="onFieldInput('bd', $event.target.value)"
          @focus="onFieldFocus('bd')"
          @keydown="onKeyDown($event)"
          type="text"
          :placeholder="airmanMode ? 'Enter BD Number' : 'e.g. 10380'"
          :required="airmanMode"
          :disabled="disabled"
          autocomplete="off"
          class="w-full h-10 px-3.5 text-xs rounded-xl border border-border/80 bg-background text-foreground font-mono placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary shadow-2xs transition-all"
        />
        <div v-if="activeField === 'bd' && isSearching" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
          <Loader2 class="h-3.5 w-3.5 animate-spin text-muted-foreground" />
        </div>
      </div>

      <!-- Suggestions Dropdown for BD Number -->
      <div
        v-if="activeField === 'bd' && showDropdown"
        class="absolute z-50 left-0 right-0 top-full mt-1.5 bg-popover/95 backdrop-blur-md border border-border rounded-xl shadow-2xl overflow-hidden max-h-64 overflow-y-auto animate-in fade-in-50 zoom-in-95"
      >
        <div class="px-3 py-1.5 bg-muted/40 border-b border-border/50 text-[11px] font-semibold text-muted-foreground flex items-center justify-between">
          <span>Matching Customers ({{ suggestions.length }})</span>
          <span class="text-[10px] text-muted-foreground/80 font-normal">↑↓ to navigate, Enter to select</span>
        </div>

        <div v-if="suggestions.length === 0" class="p-3 text-center text-xs text-muted-foreground space-y-1">
          <p class="font-medium">No matching customer found</p>
          <p class="text-[10px] text-muted-foreground/80">Will be registered as a new customer</p>
        </div>

        <ul v-else class="divide-y divide-border/30">
          <li
            v-for="(cust, idx) in suggestions"
            :key="cust.id || cust.bd_number + idx"
            @mousedown.prevent="selectCustomer(cust)"
            :class="[
              'p-2.5 cursor-pointer text-xs transition-colors flex items-center justify-between gap-2',
              highlightIndex === idx ? 'bg-primary/15 text-primary' : 'hover:bg-muted/70 text-foreground'
            ]"
          >
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <span class="font-semibold truncate">{{ cust.name || 'Unnamed' }}</span>
                <span class="font-mono text-[11px] font-bold px-1.5 py-0.5 rounded-md bg-primary/10 text-primary shrink-0">
                  BD: {{ cust.bd_number || '-' }}
                </span>
              </div>
              <div class="flex items-center gap-3 text-[11px] text-muted-foreground mt-0.5">
                <span v-if="cust.phone && cust.phone !== '-'" class="flex items-center gap-1">
                  <Phone class="h-2.5 w-2.5" /> {{ cust.phone }}
                </span>
                <span v-if="cust.total_orders" class="text-[10px] text-muted-foreground/70">
                  ({{ cust.total_orders }} orders)
                </span>
              </div>
            </div>
            <button
              type="button"
              class="shrink-0 text-[11px] font-semibold text-primary px-2 py-1 rounded-lg bg-primary/10 hover:bg-primary hover:text-primary-foreground transition-colors"
            >
              Select
            </button>
          </li>
        </ul>
      </div>
    </div>

    <!-- ── 2. Customer Name Input ── -->
    <div class="relative" ref="nameInputRef">
      <div class="flex items-center justify-between mb-1">
        <label class="block text-xs font-medium text-foreground">
          {{ airmanMode ? 'Name' : 'Customer Name' }}
          <span v-if="!airmanMode" class="text-destructive">*</span>
          <span v-else class="text-[11px] text-muted-foreground font-normal">(optional)</span>
        </label>
        <span v-if="matchedCustomer && matchedCustomer.name === customerName" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium inline-flex items-center gap-0.5">
          <Check class="h-2.5 w-2.5" /> Matched
        </span>
      </div>

      <div class="relative">
        <input
          :value="customerName"
          @input="onFieldInput('name', $event.target.value)"
          @focus="onFieldFocus('name')"
          @keydown="onKeyDown($event)"
          type="text"
          :placeholder="airmanMode ? 'Name (optional)' : 'Type customer name...'"
          :required="!airmanMode"
          :disabled="disabled"
          autocomplete="off"
          class="w-full h-10 px-3.5 text-xs rounded-xl border border-border/80 bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary shadow-2xs transition-all"
        />
        <div v-if="activeField === 'name' && isSearching" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
          <Loader2 class="h-3.5 w-3.5 animate-spin text-muted-foreground" />
        </div>
      </div>

      <!-- Suggestions Dropdown for Customer Name -->
      <div
        v-if="activeField === 'name' && showDropdown"
        class="absolute z-50 left-0 right-0 top-full mt-1.5 bg-popover/95 backdrop-blur-md border border-border rounded-xl shadow-2xl overflow-hidden max-h-64 overflow-y-auto animate-in fade-in-50 zoom-in-95"
      >
        <div class="px-3 py-1.5 bg-muted/40 border-b border-border/50 text-[11px] font-semibold text-muted-foreground flex items-center justify-between">
          <span>Matching Customers ({{ suggestions.length }})</span>
          <span class="text-[10px] text-muted-foreground/80 font-normal">↑↓ to navigate, Enter to select</span>
        </div>

        <div v-if="suggestions.length === 0" class="p-3 text-center text-xs text-muted-foreground space-y-1">
          <p class="font-medium">No matching customer found</p>
          <p class="text-[10px] text-muted-foreground/80">Will be registered as a new customer</p>
        </div>

        <ul v-else class="divide-y divide-border/30">
          <li
            v-for="(cust, idx) in suggestions"
            :key="cust.id || cust.name + idx"
            @mousedown.prevent="selectCustomer(cust)"
            :class="[
              'p-2.5 cursor-pointer text-xs transition-colors flex items-center justify-between gap-2',
              highlightIndex === idx ? 'bg-primary/15 text-primary' : 'hover:bg-muted/70 text-foreground'
            ]"
          >
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <span class="font-semibold truncate">{{ cust.name || 'Unnamed' }}</span>
                <span v-if="cust.bd_number" class="font-mono text-[11px] font-bold px-1.5 py-0.5 rounded-md bg-primary/10 text-primary shrink-0">
                  BD: {{ cust.bd_number }}
                </span>
              </div>
              <div class="flex items-center gap-3 text-[11px] text-muted-foreground mt-0.5">
                <span v-if="cust.phone && cust.phone !== '-'" class="flex items-center gap-1">
                  <Phone class="h-2.5 w-2.5" /> {{ cust.phone }}
                </span>
                <span v-if="cust.total_orders" class="text-[10px] text-muted-foreground/70">
                  ({{ cust.total_orders }} orders)
                </span>
              </div>
            </div>
            <button
              type="button"
              class="shrink-0 text-[11px] font-semibold text-primary px-2 py-1 rounded-lg bg-primary/10 hover:bg-primary hover:text-primary-foreground transition-colors"
            >
              Select
            </button>
          </li>
        </ul>
      </div>
    </div>

    <!-- ── 3. Phone Input ── -->
    <div class="relative" ref="phoneInputRef">
      <div class="flex items-center justify-between mb-1">
        <label class="block text-xs font-medium text-foreground">
          {{ airmanMode ? 'Phone Number' : 'Phone' }}
          <span v-if="airmanMode" class="text-[11px] text-muted-foreground font-normal">(optional)</span>
        </label>
        <span v-if="matchedCustomer && matchedCustomer.phone === customerPhone && customerPhone" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium inline-flex items-center gap-0.5">
          <Check class="h-2.5 w-2.5" /> Matched
        </span>
      </div>

      <div class="relative">
        <input
          :value="customerPhone"
          @input="onFieldInput('phone', $event.target.value)"
          @focus="onFieldFocus('phone')"
          @keydown="onKeyDown($event)"
          type="text"
          :placeholder="airmanMode ? 'Phone (optional)' : '01XXXXXXXXX'"
          :disabled="disabled"
          autocomplete="off"
          class="w-full h-10 px-3.5 text-xs rounded-xl border border-border/80 bg-background text-foreground font-mono placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 focus:border-primary shadow-2xs transition-all"
        />
        <div v-if="activeField === 'phone' && isSearching" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
          <Loader2 class="h-3.5 w-3.5 animate-spin text-muted-foreground" />
        </div>
      </div>

      <!-- Suggestions Dropdown for Phone -->
      <div
        v-if="activeField === 'phone' && showDropdown"
        class="absolute z-50 left-0 right-0 top-full mt-1.5 bg-popover/95 backdrop-blur-md border border-border rounded-xl shadow-2xl overflow-hidden max-h-64 overflow-y-auto animate-in fade-in-50 zoom-in-95"
      >
        <div class="px-3 py-1.5 bg-muted/40 border-b border-border/50 text-[11px] font-semibold text-muted-foreground flex items-center justify-between">
          <span>Matching Customers ({{ suggestions.length }})</span>
          <span class="text-[10px] text-muted-foreground/80 font-normal">↑↓ to navigate, Enter to select</span>
        </div>

        <div v-if="suggestions.length === 0" class="p-3 text-center text-xs text-muted-foreground space-y-1">
          <p class="font-medium">No matching customer found</p>
          <p class="text-[10px] text-muted-foreground/80">Will be registered as a new customer</p>
        </div>

        <ul v-else class="divide-y divide-border/30">
          <li
            v-for="(cust, idx) in suggestions"
            :key="cust.id || cust.phone + idx"
            @mousedown.prevent="selectCustomer(cust)"
            :class="[
              'p-2.5 cursor-pointer text-xs transition-colors flex items-center justify-between gap-2',
              highlightIndex === idx ? 'bg-primary/15 text-primary' : 'hover:bg-muted/70 text-foreground'
            ]"
          >
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <span class="font-semibold truncate">{{ cust.name || 'Unnamed' }}</span>
                <span v-if="cust.bd_number" class="font-mono text-[11px] font-bold px-1.5 py-0.5 rounded-md bg-primary/10 text-primary shrink-0">
                  BD: {{ cust.bd_number }}
                </span>
              </div>
              <div class="flex items-center gap-3 text-[11px] text-muted-foreground mt-0.5">
                <span v-if="cust.phone && cust.phone !== '-'" class="flex items-center gap-1 font-mono font-medium text-foreground">
                  <Phone class="h-2.5 w-2.5 text-primary" /> {{ cust.phone }}
                </span>
                <span v-if="cust.total_orders" class="text-[10px] text-muted-foreground/70">
                  ({{ cust.total_orders }} orders)
                </span>
              </div>
            </div>
            <button
              type="button"
              class="shrink-0 text-[11px] font-semibold text-primary px-2 py-1 rounded-lg bg-primary/10 hover:bg-primary hover:text-primary-foreground transition-colors"
            >
              Select
            </button>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Phone, Check, Loader2 } from 'lucide-vue-next';
import api from '@/lib/api';

const props = defineProps({
  bdNumber: { type: String, default: '' },
  customerName: { type: String, default: '' },
  customerPhone: { type: String, default: '' },
  customerId: { type: [String, Number], default: null },
  customers: { type: Array, default: () => [] },
  airmanMode: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
});

const emit = defineEmits([
  'update:bdNumber',
  'update:customerName',
  'update:customerPhone',
  'update:customerId',
  'select',
  'change',
]);

const rootContainerRef = ref(null);
const bdInputRef = ref(null);
const nameInputRef = ref(null);
const phoneInputRef = ref(null);

const activeField = ref(null); // 'bd' | 'name' | 'phone' | null
const showDropdown = ref(false);
const highlightIndex = ref(0);
const isSearching = ref(false);
const serverResults = ref([]);
const matchedCustomer = ref(null);

let searchTimeout = null;

// Determine matching customers from local list + server results
const suggestions = computed(() => {
  if (!activeField.value) return [];

  let query = '';
  if (activeField.value === 'bd') {
    query = (props.bdNumber || '').trim();
  } else if (activeField.value === 'name') {
    query = (props.customerName || '').trim();
  } else if (activeField.value === 'phone') {
    query = (props.customerPhone || '').trim();
  }

  if (!query || query.length === 0) return [];

  const qLower = query.toLowerCase();
  const qDigits = query.replace(/\D/g, '');

  // Search local customers list
  const localMatches = (props.customers || []).filter((c) => {
    if (!c) return false;
    const bd = String(c.bd_number || '').toLowerCase();
    const name = String(c.name || '').toLowerCase();
    const phone = String(c.phone || '').toLowerCase();
    const phoneDigits = String(c.phone || '').replace(/\D/g, '');

    if (activeField.value === 'bd') {
      return bd.includes(qLower) || (qDigits && bd.includes(qDigits));
    }
    if (activeField.value === 'name') {
      return name.includes(qLower) || bd.includes(qLower);
    }
    if (activeField.value === 'phone') {
      return (qDigits && phoneDigits.includes(qDigits)) || phone.includes(qLower) || name.includes(qLower);
    }
    return false;
  });

  // Combine local matches + server results (deduplicating by id or bd_number)
  const combined = [...localMatches];
  for (const s of serverResults.value) {
    if (!combined.some((c) => (c.id && c.id === s.id) || (c.bd_number && c.bd_number === s.bd_number))) {
      combined.push(s);
    }
  }

  // Sort: exact matches first, then startsWith, then general includes
  combined.sort((a, b) => {
    const aBd = String(a.bd_number || '').toLowerCase();
    const bBd = String(b.bd_number || '').toLowerCase();
    const aName = String(a.name || '').toLowerCase();
    const bName = String(b.name || '').toLowerCase();

    if (activeField.value === 'bd') {
      if (aBd === qLower) return -1;
      if (bBd === qLower) return 1;
      if (aBd.startsWith(qLower) && !bBd.startsWith(qLower)) return -1;
      if (!aBd.startsWith(qLower) && bBd.startsWith(qLower)) return 1;
    } else if (activeField.value === 'name') {
      if (aName === qLower) return -1;
      if (bName === qLower) return 1;
      if (aName.startsWith(qLower) && !bName.startsWith(qLower)) return -1;
      if (!aName.startsWith(qLower) && bName.startsWith(qLower)) return 1;
    }
    return 0;
  });

  return combined.slice(0, 8);
});

function onFieldInput(field, val) {
  activeField.value = field;
  highlightIndex.value = 0;

  if (field === 'bd') {
    emit('update:bdNumber', val);
  } else if (field === 'name') {
    emit('update:customerName', val);
  } else if (field === 'phone') {
    emit('update:customerPhone', val);
  }

  emit('change');

  const trimmed = String(val || '').trim();
  if (trimmed.length >= 1) {
    showDropdown.value = true;
    triggerServerSearch(trimmed);
  } else {
    showDropdown.value = false;
  }
}

function onFieldFocus(field) {
  activeField.value = field;
  highlightIndex.value = 0;

  let val = '';
  if (field === 'bd') val = props.bdNumber;
  if (field === 'name') val = props.customerName;
  if (field === 'phone') val = props.customerPhone;

  if (String(val || '').trim().length >= 1) {
    showDropdown.value = true;
  }
}

function triggerServerSearch(query) {
  if (searchTimeout) clearTimeout(searchTimeout);
  if (!query || query.length < 2) {
    serverResults.value = [];
    isSearching.value = false;
    return;
  }

  isSearching.value = true;
  searchTimeout = setTimeout(async () => {
    try {
      const res = await api.get('/customers', { params: { search: query } });
      serverResults.value = res.data?.data || res.data || [];
    } catch (err) {
      // Ignore network errors on background autocomplete
    } finally {
      isSearching.value = false;
    }
  }, 250);
}

function selectCustomer(cust) {
  if (!cust) return;

  const bd = String(cust.bd_number || '').trim();
  const name = String(cust.name || '').trim();
  const phone = (cust.phone && cust.phone !== '-') ? String(cust.phone).trim() : '';
  const id = cust.id || null;

  matchedCustomer.value = cust;

  emit('update:bdNumber', bd);
  emit('update:customerName', name);
  emit('update:customerPhone', phone);
  emit('update:customerId', id);

  emit('select', cust);
  emit('change');

  showDropdown.value = false;
  activeField.value = null;
}

function onKeyDown(e) {
  if (!showDropdown.value || suggestions.value.length === 0) {
    if (e.key === 'Escape') {
      showDropdown.value = false;
      activeField.value = null;
    }
    return;
  }

  if (e.key === 'ArrowDown') {
    e.preventDefault();
    highlightIndex.value = (highlightIndex.value + 1) % suggestions.value.length;
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    highlightIndex.value = (highlightIndex.value - 1 + suggestions.value.length) % suggestions.value.length;
  } else if (e.key === 'Enter') {
    e.preventDefault();
    if (suggestions.value[highlightIndex.value]) {
      selectCustomer(suggestions.value[highlightIndex.value]);
    }
  } else if (e.key === 'Escape') {
    showDropdown.value = false;
    activeField.value = null;
  }
}

function handleClickOutside(e) {
  if (rootContainerRef.value && !rootContainerRef.value.contains(e.target)) {
    showDropdown.value = false;
    activeField.value = null;
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside);
  if (searchTimeout) clearTimeout(searchTimeout);
});
</script>
