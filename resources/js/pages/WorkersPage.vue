<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-heading font-bold text-foreground">{{ t('workersTitle') }}</h1>
        <p class="text-muted-foreground text-sm mt-1">{{ t('workersSubtitle') }}</p>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="showLang = showLang === 'bn' ? 'en' : 'bn'"
          class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-input bg-background hover:bg-accent text-xs font-medium transition-colors"
        >
          <Globe class="h-4 w-4" />
          {{ showLang === 'bn' ? 'English' : 'বাংলা' }}
        </button>
        <button
          type="button"
          @click="showAddForm = true"
          class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-primary text-primary-foreground text-xs font-medium hover:bg-primary/90 transition-colors"
        >
          <Plus class="h-4 w-4" /> {{ t('workersAdd') }}
        </button>
      </div>
    </div>

    <!-- Add Worker Card -->
    <div v-if="showAddForm" class="rounded-xl border border-border bg-card p-5 shadow-sm animate-in fade-in-50">
      <h3 class="text-base font-semibold mb-3">{{ t('workersAddNew') }}</h3>
      <div class="flex flex-col sm:flex-row gap-3">
        <input
          v-model="newNameBn"
          placeholder="বাংলা নাম"
          class="flex-1 h-10 px-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-1 focus:ring-ring"
        />
        <input
          v-model="newNameEn"
          placeholder="English Name"
          class="flex-1 h-10 px-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-1 focus:ring-ring"
        />
        <div class="flex gap-2">
          <button
            type="button"
            @click="handleAdd"
            :disabled="adding"
            class="h-10 px-4 inline-flex items-center justify-center rounded-lg bg-primary text-primary-foreground font-medium hover:bg-primary/90 disabled:opacity-50"
          >
            <Loader2 v-if="adding" class="h-4 w-4 animate-spin" />
            <Check v-else class="h-4 w-4" />
          </button>
          <button
            type="button"
            @click="showAddForm = false; newNameBn = ''; newNameEn = ''"
            class="h-10 px-4 inline-flex items-center justify-center rounded-lg hover:bg-accent text-muted-foreground"
          >
            <X class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Workers List Card -->
    <div class="rounded-xl border border-border bg-card shadow-sm overflow-hidden">
      <div class="p-4 sm:p-5 border-b border-border flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <h2 class="font-semibold text-lg">{{ t('workersList') }}</h2>
          <span class="inline-flex items-center rounded-full bg-secondary px-2.5 py-0.5 text-xs font-semibold text-secondary-foreground">
            {{ filteredWorkers.length }} {{ t('workersCount') }}
          </span>
        </div>

        <!-- Status Filter Tabs: Active / Archived / All -->
        <div class="flex items-center p-1 rounded-xl bg-muted/60 border border-border/80 self-start sm:self-auto overflow-x-auto max-w-full">
          <button
            type="button"
            @click="setTab('active')"
            class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer"
            :class="statusTab === 'active' ? 'bg-card text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'"
          >
            <span>{{ showLang === 'bn' ? 'বর্তমান স্টাফ' : 'Active Staff' }}</span>
            <span
              class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
              :class="statusTab === 'active' ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'"
            >
              {{ activeCount }}
            </span>
          </button>

          <button
            type="button"
            @click="setTab('archived')"
            class="flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer"
            :class="statusTab === 'archived' ? 'bg-card text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'"
          >
            <Archive class="h-3.5 w-3.5" />
            <span>{{ showLang === 'bn' ? 'আর্কাইভ / সাবেক' : 'Archived' }}</span>
            <span
              class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
              :class="statusTab === 'archived' ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400' : 'bg-muted text-muted-foreground'"
            >
              {{ archivedCount }}
            </span>
          </button>

          <button
            type="button"
            @click="setTab('all')"
            class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer"
            :class="statusTab === 'all' ? 'bg-card text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'"
          >
            <span>{{ showLang === 'bn' ? 'সব' : 'All' }}</span>
          </button>
        </div>
      </div>

      <!-- Info banner for Archived Staff tab -->
      <div
        v-if="statusTab === 'archived'"
        class="bg-amber-500/10 border-b border-amber-500/20 px-4 py-2.5 flex items-center gap-2 text-xs text-amber-800 dark:text-amber-300 font-medium"
      >
        <ShieldCheck class="h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400" />
        <span>
          {{ showLang === 'bn'
            ? 'আর্কাইভকৃত স্টাফদের পূর্বের সকল কাজের হিসাব ও মাসিক এন্ট্রি শিট সম্পূর্ণ সুরক্ষিত আছে। যেকোনো মাসে ক্লিক করে পূর্বের বিবরণী দেখতে পারবেন বা স্টাফকে পুনরায় সক্রিয় করতে পারবেন।'
            : 'All past work history and monthly entry sheets for archived staff are safely preserved. Click any month folder to view records or restore the worker.'
          }}
        </span>
      </div>

      <div class="p-4 sm:p-5">
        <div v-if="!loading && workers.length > 0" class="mb-4">
          <div class="relative w-full max-w-sm">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <input
              v-model="search"
              :placeholder="showLang === 'bn' ? 'নাম খুঁজুন...' : 'Search worker...'"
              class="w-full h-9 pl-9 pr-3 text-xs rounded-lg border border-input bg-background focus:outline-none focus:ring-1 focus:ring-ring"
            />
          </div>
        </div>

        <div v-if="loading" class="flex justify-center py-12">
          <Loader2 class="h-8 w-8 animate-spin text-primary" />
        </div>

        <p v-else-if="filteredWorkers.length === 0" class="text-center text-muted-foreground py-8 text-xs">
          {{ statusTab === 'archived'
            ? (showLang === 'bn' ? 'কোনো আর্কাইভকৃত কর্মী নেই' : 'No archived workers')
            : (workers.length === 0 ? t('workersEmpty') : (showLang === 'bn' ? 'কোনো কর্মী পাওয়া যায়নি' : 'No workers found'))
          }}
        </p>

        <div v-else class="space-y-2">
          <div
            v-for="(w, idx) in filteredWorkers"
            :key="w.id"
            class="rounded-xl border border-border bg-card overflow-hidden transition-all shadow-2xs"
            :class="{ 'opacity-90 border-amber-500/30': w.deleted_at }"
          >
            <div
              class="flex items-center justify-between gap-3 p-3.5 hover:bg-muted/50 transition-colors cursor-pointer"
              @click="editId !== w.id ? toggleOpenWorker(w.id) : null"
            >
              <!-- Edit mode -->
              <div v-if="editId === w.id" class="flex flex-col sm:flex-row gap-2 flex-1" @click.stop>
                <input
                  v-model="editNameBn"
                  placeholder="বাংলা নাম"
                  class="flex-1 h-8 px-2 text-xs rounded border border-input bg-background"
                />
                <input
                  v-model="editNameEn"
                  placeholder="English Name"
                  class="flex-1 h-8 px-2 text-xs rounded border border-input bg-background"
                />
                <div class="flex gap-1">
                  <button
                    type="button"
                    @click="handleUpdate(w.id)"
                    :disabled="saving"
                    class="h-8 w-8 inline-flex items-center justify-center bg-primary text-primary-foreground rounded hover:bg-primary/90"
                  >
                    <Loader2 v-if="saving" class="h-3.5 w-3.5 animate-spin" />
                    <Check v-else class="h-3.5 w-3.5" />
                  </button>
                  <button
                    type="button"
                    @click="editId = null"
                    class="h-8 w-8 inline-flex items-center justify-center hover:bg-accent rounded"
                  >
                    <X class="h-3.5 w-3.5" />
                  </button>
                </div>
              </div>

              <!-- Display mode -->
              <template v-else>
                <div class="flex items-center gap-3 min-w-0">
                  <component
                    :is="openWorkerId === w.id ? ChevronDown : ChevronRight"
                    class="h-4 w-4 text-muted-foreground shrink-0"
                  />
                  <span class="text-xs text-muted-foreground w-6 text-right shrink-0">{{ idx + 1 }}.</span>
                  <div class="flex items-center gap-2 flex-wrap min-w-0">
                    <span class="font-semibold truncate text-sm text-foreground">
                      {{ showLang === 'bn' ? w.name_bn : w.name_en }}
                    </span>
                    <span class="text-xs text-muted-foreground truncate hidden sm:inline">
                      ({{ showLang === 'bn' ? w.name_en : w.name_bn }})
                    </span>
                    <span
                      v-if="w.deleted_at"
                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/25"
                    >
                      <Archive class="h-3 w-3" />
                      <span>{{ showLang === 'bn' ? 'হিস্ট্রি সংরক্ষিত (আর্কাইভ)' : 'Archived (Saved)' }}</span>
                    </span>
                  </div>
                </div>

                <div class="flex items-center gap-1 shrink-0" @click.stop>
                  <!-- Restore button for archived staff -->
                  <button
                    v-if="w.deleted_at"
                    type="button"
                    @click="handleRestore(w)"
                    :disabled="restoringId === w.id"
                    class="h-8 px-2.5 inline-flex items-center gap-1 text-xs font-semibold rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 transition-colors"
                    title="Restore staff to active list"
                  >
                    <Loader2 v-if="restoringId === w.id" class="h-3.5 w-3.5 animate-spin" />
                    <RotateCcw v-else class="h-3.5 w-3.5" />
                    <span>{{ showLang === 'bn' ? 'সক্রিয় করুন' : 'Restore' }}</span>
                  </button>

                  <button
                    v-if="!w.deleted_at"
                    type="button"
                    @click="startEdit(w)"
                    class="h-8 w-8 inline-flex items-center justify-center hover:bg-accent rounded text-muted-foreground hover:text-foreground"
                    title="Edit name"
                  >
                    <Pencil class="h-3.5 w-3.5" />
                  </button>

                  <button
                    v-if="!w.deleted_at"
                    type="button"
                    @click="confirmDeleteWorker(w)"
                    class="h-8 w-8 inline-flex items-center justify-center hover:bg-destructive/10 rounded text-destructive"
                    title="Archive staff (Keep work history)"
                  >
                    <Trash2 class="h-3.5 w-3.5" />
                  </button>
                </div>
              </template>
            </div>

            <!-- Month folder grid (Works for both Active and Archived staff) -->
            <div
              v-if="openWorkerId === w.id && editId !== w.id"
              class="border-t border-border bg-muted/20 p-4 space-y-3 animate-in fade-in-50"
            >
              <div class="flex items-center justify-between gap-2">
                <span class="text-xs text-muted-foreground font-medium flex items-center gap-1.5">
                  <Folder class="h-3.5 w-3.5 text-primary" />
                  <span>{{ showLang === 'bn' ? 'মাস অনুযায়ী কাজের হিসাব ও এন্ট্রি শিট' : 'Month-wise work entries & sheet' }}</span>
                </span>
                <div class="flex items-center gap-1 bg-background px-2 py-0.5 rounded-lg border border-border">
                  <button
                    type="button"
                    class="h-6 w-6 inline-flex items-center justify-center hover:bg-accent rounded"
                    @click="folderYear--"
                  >
                    <ChevronRight class="h-3.5 w-3.5 rotate-180" />
                  </button>
                  <span class="text-xs font-semibold tabular-nums px-1">{{ folderYear }}</span>
                  <button
                    type="button"
                    class="h-6 w-6 inline-flex items-center justify-center hover:bg-accent rounded"
                    @click="folderYear++"
                  >
                    <ChevronRight class="h-3.5 w-3.5" />
                  </button>
                </div>
              </div>

              <!-- 12 Month Soft Color Folders -->
              <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                <button
                  v-for="(m, i) in MONTHS_EN"
                  :key="m"
                  type="button"
                  @click="openMonth(w.id, i)"
                  class="flex items-center gap-2 rounded-xl px-2.5 sm:px-3 py-2.5 text-xs font-semibold text-left transition-transform hover:scale-[1.02] active:scale-[0.99] shadow-xs cursor-pointer min-w-0"
                  :style="{
                    backgroundColor: `hsl(var(--month-${i + 1}))`,
                    color: `hsl(var(--month-${i + 1}-fg))`,
                    border: `1px solid hsl(var(--month-${i + 1}-fg) / 0.2)`,
                  }"
                >
                  <Folder class="h-4 w-4 shrink-0" />
                  <span class="truncate">{{ showLang === 'bn' ? MONTHS_BN[i] : m }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reassuring Archive / Delete Confirmation Modal -->
    <Teleport to="body">
      <div v-if="deleteModalOpen" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-card border border-border rounded-2xl shadow-2xl max-w-md w-full p-4 sm:p-6 animate-in zoom-in-95 space-y-4 max-h-[90dvh] overflow-y-auto">
          <div class="flex items-start gap-3">
            <div class="h-10 w-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20">
              <ShieldCheck class="h-5 w-5" />
            </div>
            <div>
              <h3 class="text-base font-bold text-foreground">
                {{ showLang === 'bn' ? 'স্টাফ সরান / আর্কাইভ করুন' : 'Remove / Archive Staff' }}
              </h3>
              <p class="text-xs text-muted-foreground mt-0.5">
                {{ showLang === 'bn' ? 'কাজের ইতিহাস ও মাসিক হিসাব সম্পূর্ণ সংরক্ষিত থাকবে' : 'Work history & monthly records will remain safely preserved' }}
              </p>
            </div>
          </div>

          <div class="rounded-xl p-3.5 bg-muted/50 border border-border text-xs text-foreground/90 space-y-2">
            <p>
              {{ showLang === 'bn'
                ? `আপনি কি "${workerToDelete ? (showLang === 'bn' ? workerToDelete.name_bn : workerToDelete.name_en) : ''}" কর্মীকে তালিকা থেকে সরাতে চান?`
                : `Are you sure you want to remove "${workerToDelete?.name_en || workerToDelete?.name_bn}"?`
              }}
            </p>
            <p class="text-muted-foreground">
              {{ showLang === 'bn'
                ? '⚠️ কর্মীকে সরিয়ে নিলেও তার পূর্বের সকল কাজের হিসাব, মাসিক এন্ট্রি শিট ও টাকা-পয়সার রেকর্ডস ডেটাবেজে সম্পূর্ণ সংরক্ষিত থাকবে। "আর্কাইভকৃত স্টাফ" ট্যাবে গিয়ে আপনি যেকোনো সময় তার পূর্বের রেকর্ড দেখতে বা কর্মীকে পুনরায় সক্রিয় করতে পারবেন।'
                : 'All past work history, monthly entries, and financial records will remain 100% saved in the database. You can view their history or restore them anytime under the "Archived" tab.'
              }}
            </p>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              @click="deleteModalOpen = false"
              class="h-9 px-4 text-xs font-semibold border border-input rounded-xl hover:bg-accent transition-colors"
            >
              {{ t('cancel') }}
            </button>
            <button
              type="button"
              @click="handleDelete"
              class="h-9 px-4 text-xs font-semibold bg-amber-600 hover:bg-amber-700 text-white rounded-xl transition-colors shadow-xs"
            >
              {{ showLang === 'bn' ? 'আর্কাইভ করুন (হিস্ট্রি সেভ থাকবে)' : 'Archive (Keep History)' }}
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
  Globe, Plus, Pencil, Trash2, X, Check, Search, Folder,
  ChevronDown, ChevronRight, Loader2, RotateCcw, Archive, ShieldCheck
} from 'lucide-vue-next';
import { t, currentLang } from '@/lib/language';
import { WORKER_MONTH_FILTER_KEY } from '@/lib/workerTotals';
import api from '@/lib/api';

const MONTHS_EN = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const MONTHS_BN = ['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];

const router = useRouter();

const workers = ref([]);
const loading = ref(true);
const showLang = ref(currentLang.value === 'bn' ? 'bn' : 'en');
const search = ref('');
const openWorkerId = ref(null);
const folderYear = ref(new Date().getFullYear());

const statusTab = ref('active'); // 'active' | 'archived' | 'all'
const activeCount = ref(0);
const archivedCount = ref(0);
const restoringId = ref(null);

const showAddForm = ref(false);
const newNameBn = ref('');
const newNameEn = ref('');
const adding = ref(false);

const editId = ref(null);
const editNameBn = ref('');
const editNameEn = ref('');
const saving = ref(false);

const deleteModalOpen = ref(false);
const workerToDelete = ref(null);

const filteredWorkers = computed(() => {
  const q = search.value.trim().toLowerCase();
  if (!q) return workers.value;
  return workers.value.filter(w =>
    (w.name_bn || '').toLowerCase().includes(q) ||
    (w.name_en || '').toLowerCase().includes(q)
  );
});

function setTab(tab) {
  statusTab.value = tab;
  fetchWorkers();
}

async function fetchWorkers() {
  loading.value = true;
  try {
    const res = await api.get('/workers', {
      params: { status: statusTab.value }
    });
    workers.value = res.data.data || [];
    activeCount.value = res.data.active_count ?? 0;
    archivedCount.value = res.data.archived_count ?? 0;
  } catch (err) {
    console.error('Failed to load workers', err);
  } finally {
    loading.value = false;
  }
}

function toggleOpenWorker(id) {
  openWorkerId.value = openWorkerId.value === id ? null : id;
}

function openMonth(workerId, monthIdx) {
  const value = `${folderYear.value}-${monthIdx}`;
  localStorage.setItem(WORKER_MONTH_FILTER_KEY, value);
  router.push(`/staff/${workerId}?month=${value}`);
}

async function handleAdd() {
  if (!newNameBn.value.trim() || !newNameEn.value.trim()) return;
  adding.value = true;
  try {
    await api.post('/workers', {
      name_bn: newNameBn.value.trim(),
      name_en: newNameEn.value.trim(),
    });
    newNameBn.value = '';
    newNameEn.value = '';
    showAddForm.value = false;
    await fetchWorkers();
  } catch (err) {
    console.error('Error adding worker', err);
  } finally {
    adding.value = false;
  }
}

function startEdit(w) {
  editId.value = w.id;
  editNameBn.value = w.name_bn;
  editNameEn.value = w.name_en;
}

async function handleUpdate(id) {
  if (!editNameBn.value.trim() || !editNameEn.value.trim()) return;
  saving.value = true;
  try {
    await api.put(`/workers/${id}`, {
      name_bn: editNameBn.value.trim(),
      name_en: editNameEn.value.trim(),
    });
    editId.value = null;
    await fetchWorkers();
  } catch (err) {
    console.error('Error updating worker', err);
  } finally {
    saving.value = false;
  }
}

function confirmDeleteWorker(w) {
  workerToDelete.value = w;
  deleteModalOpen.value = true;
}

async function handleDelete() {
  if (!workerToDelete.value) return;
  try {
    await api.delete(`/workers/${workerToDelete.value.id}`);
    deleteModalOpen.value = false;
    workerToDelete.value = null;
    await fetchWorkers();
  } catch (err) {
    console.error('Error deleting worker', err);
  }
}

async function handleRestore(w) {
  restoringId.value = w.id;
  try {
    await api.post(`/workers/${w.id}/restore`);
    await fetchWorkers();
  } catch (err) {
    console.error('Failed to restore worker', err);
  } finally {
    restoringId.value = null;
  }
}

onMounted(() => {
  fetchWorkers();
});
</script>
