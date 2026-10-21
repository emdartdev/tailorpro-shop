<template>
  <div ref="containerRef" class="relative">
    <!-- Desktop Search Input (Inline) -->
    <div class="hidden md:block relative w-64 md:w-80">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted-foreground">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <input
        type="search"
        v-model="query"
        @focus="open = true"
        :placeholder="t('globalSearchPlaceholder')"
        class="w-full pl-9 pr-8 py-1.5 text-xs bg-muted/50 focus:bg-background border border-border rounded-lg text-foreground focus:outline-none focus:ring-1 focus:ring-primary transition-all"
      />
      <button
        v-if="query"
        type="button"
        @click="query = ''; results = [];"
        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-muted-foreground hover:text-foreground cursor-pointer"
        aria-label="Clear search"
      >
        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>

    <!-- Mobile Search Trigger Button -->
    <button
      type="button"
      @click="openMobileSearch"
      class="md:hidden h-8 w-8 sm:h-8.5 sm:w-8.5 rounded-lg text-foreground/80 hover:bg-muted hover:text-foreground transition-colors cursor-pointer inline-flex items-center justify-center shrink-0"
      aria-label="Search Customers"
      title="Search Customers"
    >
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
    </button>

    <!-- Mobile Full-Width Search Overlay Bar -->
    <Teleport to="body">
      <div
        v-if="mobileSearchOpen"
        class="fixed inset-x-0 top-0 z-50 h-14 bg-card/98 backdrop-blur-md border-b border-border px-3 flex items-center gap-2 shadow-md md:hidden"
        :style="{
          paddingTop: 'env(safe-area-inset-top, 0px)',
        }"
      >
        <button
          type="button"
          @click="closeMobileSearch"
          class="h-9 w-9 rounded-lg inline-flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-muted cursor-pointer shrink-0"
          aria-label="Back"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </button>

        <div class="relative flex-1 min-w-0">
          <input
            ref="mobileInputRef"
            type="search"
            v-model="query"
            @focus="open = true"
            :placeholder="t('globalSearchPlaceholder')"
            class="w-full pl-3 pr-8 py-2 text-xs bg-muted/60 focus:bg-background border border-border rounded-xl text-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
          />
          <button
            v-if="query"
            type="button"
            @click="query = ''; results = [];"
            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-muted-foreground hover:text-foreground cursor-pointer"
            aria-label="Clear search"
          >
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>

        <button
          type="button"
          @click="closeMobileSearch"
          class="px-2.5 py-1.5 text-xs font-semibold text-primary hover:text-primary/80 cursor-pointer shrink-0"
        >
          {{ lang === 'bn' ? 'বন্ধ' : 'Close' }}
        </button>
      </div>

      <!-- Mobile Dropdown Results Container -->
      <div
        v-if="mobileSearchOpen && open && query.trim()"
        class="fixed inset-x-2 top-16 z-50 max-w-lg mx-auto rounded-2xl bg-card border border-border shadow-2xl overflow-hidden text-xs md:hidden max-h-[75vh] flex flex-col animate-in fade-in zoom-in-95"
      >
        <div v-if="loading" class="p-4 text-center text-muted-foreground">
          <svg class="animate-spin h-5 w-5 mx-auto text-primary" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
          </svg>
          <span class="mt-2 block text-xs">Searching customers...</span>
        </div>

        <div v-else-if="results.length === 0" class="p-4 text-center text-muted-foreground">
          {{ t('noResults') }}
        </div>

        <div v-else class="divide-y divide-border/50 overflow-y-auto max-h-[70vh]">
          <div
            v-for="c in results"
            :key="c.id"
            @click="selectCustomer(c)"
            class="p-3 hover:bg-muted/60 transition-colors cursor-pointer flex items-center justify-between"
          >
            <div class="min-w-0 flex-1 mr-2">
              <div class="font-semibold text-foreground truncate">{{ c.name }}</div>
              <div class="text-[11px] text-muted-foreground flex items-center gap-2 mt-0.5 truncate">
                <span class="font-mono">{{ c.bd_number || '-' }}</span>
                <span>•</span>
                <span class="font-mono">{{ c.phone || '-' }}</span>
              </div>
            </div>
            <div class="text-right shrink-0">
              <span class="inline-block px-1.5 py-0.5 rounded bg-primary/10 text-primary font-medium text-[10px]">
                {{ c.total_orders || 0 }} orders
              </span>
              <div v-if="c.due_amount > 0" class="text-destructive font-semibold text-[10px] mt-0.5 font-mono">
                Due: {{ c.due_amount }}৳
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Desktop Dropdown Results -->
    <div
      v-if="open && query.trim() && !mobileSearchOpen"
      class="hidden md:block absolute left-0 mt-1 w-80 md:w-96 rounded-xl bg-card border border-border shadow-xl z-50 overflow-hidden text-xs"
    >
      <div v-if="loading" class="p-4 text-center text-muted-foreground">
        <svg class="animate-spin h-5 w-5 mx-auto text-primary" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        <span class="mt-2 block text-xs">Searching customers...</span>
      </div>

      <div v-else-if="results.length === 0" class="p-4 text-center text-muted-foreground">
        {{ t('noResults') }}
      </div>

      <div v-else class="divide-y divide-border/50 max-h-80 overflow-y-auto">
        <div
          v-for="c in results"
          :key="c.id"
          @click="selectCustomer(c)"
          class="p-3 hover:bg-muted/60 transition-colors cursor-pointer flex items-center justify-between"
        >
          <div class="min-w-0 flex-1 mr-2">
            <div class="font-semibold text-foreground truncate">{{ c.name }}</div>
            <div class="text-[11px] text-muted-foreground flex items-center gap-2 mt-0.5 truncate">
              <span class="font-mono">{{ c.bd_number || '-' }}</span>
              <span>•</span>
              <span class="font-mono">{{ c.phone || '-' }}</span>
            </div>
          </div>
          <div class="text-right shrink-0">
            <span class="inline-block px-1.5 py-0.5 rounded bg-primary/10 text-primary font-medium text-[10px]">
              {{ c.total_orders || 0 }} orders
            </span>
            <div v-if="c.due_amount > 0" class="text-destructive font-semibold text-[10px] mt-0.5 font-mono">
              Due: {{ c.due_amount }}৳
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import { useLanguage } from '@/lib/language';
import api from '@/lib/api';

const { t, lang } = useLanguage();
const router = useRouter();

const query = ref('');
const results = ref([]);
const loading = ref(false);
const open = ref(false);
const containerRef = ref(null);
const mobileSearchOpen = ref(false);
const mobileInputRef = ref(null);

let debounceTimer = null;

function openMobileSearch() {
  mobileSearchOpen.value = true;
  open.value = true;
  nextTick(() => {
    if (mobileInputRef.value) {
      mobileInputRef.value.focus();
    }
  });
}

function closeMobileSearch() {
  mobileSearchOpen.value = false;
  open.value = false;
  query.value = '';
  results.value = [];
}

watch(query, (val) => {
  clearTimeout(debounceTimer);
  if (!val.trim()) {
    results.value = [];
    return;
  }
  debounceTimer = setTimeout(async () => {
    loading.value = true;
    try {
      const res = await api.get('/customers', { params: { search: val.trim() } });
      results.value = (res.data || []).slice(0, 8);
    } catch (err) {
      console.error(err);
      results.value = [];
    } finally {
      loading.value = false;
    }
  }, 250);
});

function selectCustomer(customer) {
  open.value = false;
  mobileSearchOpen.value = false;
  query.value = '';
  router.push(`/history?customer=${customer.id}`);
}

function handleClickOutside(e) {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    open.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
