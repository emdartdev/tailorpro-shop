<template>
  <div class="relative inline-block w-full" ref="rootRef">
    <!-- Trigger Button -->
    <button
      type="button"
      @click="toggleDropdown"
      :class="[
        triggerClassName ? triggerClassName : 'h-10 justify-between text-xs font-medium px-3 w-full min-w-0 sm:min-w-[180px] rounded-xl border border-border/80 bg-background text-foreground shadow-2xs hover:bg-accent/60 transition-colors',
        'inline-flex items-center cursor-pointer focus:outline-none focus:ring-1 focus:ring-primary truncate',
        className
      ]"
    >
      <span class="truncate font-medium">{{ selectedItemName || 'Select item...' }}</span>
      <ChevronDown class="ml-2 h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': isOpen }" />
    </button>

    <!-- Popover Content matching Screenshot -->
    <div
      v-if="isOpen"
      class="absolute left-0 z-50 mt-1.5 w-[calc(100vw-2rem)] max-w-[300px] sm:w-[300px] rounded-2xl border border-border/80 bg-popover text-popover-foreground shadow-xl outline-none overflow-hidden animate-in fade-in-50 zoom-in-95"
    >
      <!-- Search items box -->
      <div class="p-2.5 border-b border-border/60 bg-muted/20">
        <div class="relative">
          <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground/70" />
          <input
            ref="searchInputRef"
            v-model="search"
            type="text"
            placeholder="Search items..."
            class="w-full h-8 text-xs pl-8 pr-2.5 rounded-xl border border-border/80 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary placeholder:text-muted-foreground/60 shadow-2xs"
          />
        </div>
      </div>

      <!-- Items List -->
      <div class="max-h-[260px] overflow-y-auto divide-y divide-border/30">
        <div v-if="loading" class="flex items-center justify-center py-6 text-xs text-muted-foreground gap-2">
          <Loader2 class="h-4 w-4 animate-spin text-primary" />
          <span>Loading items...</span>
        </div>
        <div v-else-if="filteredCatalog.length === 0" class="text-xs text-muted-foreground text-center py-6">
          No items found
        </div>
        <div v-else>
          <button
            v-for="item in filteredCatalog"
            :key="item.id || item.name"
            type="button"
            @click="handleSelect(item)"
            :class="[
              'flex items-center justify-between w-full px-3.5 py-2.5 text-xs transition-colors cursor-pointer text-left',
              isSelected(item)
                ? 'bg-primary/10 text-primary font-semibold'
                : 'hover:bg-accent/70 text-foreground font-medium'
            ]"
          >
            <span class="truncate">{{ item.name }}</span>
            <span v-if="showPrice && item.price !== null && item.price !== undefined" class="font-mono text-primary/90 font-semibold ml-2 shrink-0">
              ৳{{ formatMoney(item.price) }}
            </span>
          </button>
        </div>
      </div>

      <!-- Manage Items bottom bar matching Screenshot -->
      <div class="border-t border-border/60 p-2 bg-muted/10">
        <button
          type="button"
          class="w-full h-8 text-xs font-medium flex items-center justify-center gap-1.5 rounded-xl hover:bg-accent text-foreground hover:text-primary transition-colors cursor-pointer"
          @click="openManageModal"
        >
          <Edit2 class="h-3.5 w-3.5 text-muted-foreground" />
          <span>Manage Items</span>
        </button>
      </div>
    </div>

    <!-- Manage Items Modal -->
    <Teleport to="body">
      <div v-if="isManageOpen" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="bg-card border border-border/80 rounded-2xl shadow-2xl max-w-lg w-full max-h-[90dvh] flex flex-col p-4 sm:p-6 animate-in zoom-in-95">
          <!-- Modal Header -->
          <div class="flex items-start justify-between pb-4 border-b border-border/70">
            <div>
              <h3 class="text-base font-bold text-foreground">{{ catalogTitle }}</h3>
              <p class="text-xs text-muted-foreground mt-0.5">
                {{ showPrice ? 'Create, edit price/name, or delete catalog items' : 'Create new items, rename, or delete items from the catalog' }}
              </p>
            </div>
            <button
              type="button"
              @click="closeManageModal"
              class="h-7 w-7 inline-flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-accent transition-colors cursor-pointer"
            >
              ✕
            </button>
          </div>

          <!-- Alert / Status Banner -->
          <div
            v-if="statusMessage.text"
            class="mt-3 px-3 py-2 rounded-xl text-xs flex items-center justify-between"
            :class="statusMessage.type === 'error' ? 'bg-destructive/15 text-destructive border border-destructive/30' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30'"
          >
            <span>{{ statusMessage.text }}</span>
            <button type="button" @click="statusMessage.text = ''" class="text-xs font-bold ml-2">✕</button>
          </div>

          <!-- Quick Add Section -->
          <div class="py-3 border-b border-border/60">
            <div class="text-xs font-semibold text-foreground mb-2 flex items-center gap-1.5">
              <Plus class="h-3.5 w-3.5 text-primary" />
              <span>Add New Item</span>
            </div>
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
              <input
                v-model="newItemName"
                type="text"
                :placeholder="catalogType === 'airman' ? 'Item name (e.g. MODC Uniform)' : 'Item name (e.g. Safari Suit)'"
                class="h-8 text-xs flex-1 rounded-xl border border-border/80 bg-background px-3 text-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                @keyup.enter="addNewItem"
              />
              <div v-if="showPrice" class="relative w-28">
                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-muted-foreground font-mono">৳</span>
                <input
                  v-model.number="newItemPrice"
                  type="number"
                  placeholder="Price"
                  min="0"
                  class="h-8 text-xs w-full pl-6 pr-2 rounded-xl border border-border/80 bg-background text-foreground font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                  @keyup.enter="addNewItem"
                />
              </div>
              <button
                type="button"
                @click="addNewItem"
                :disabled="!newItemName.trim() || isSubmitting"
                class="h-8 px-4 text-xs font-semibold bg-primary text-primary-foreground rounded-xl hover:bg-primary/90 transition-colors disabled:opacity-50 cursor-pointer inline-flex items-center gap-1 shrink-0"
              >
                <Loader2 v-if="isSubmitting && addingMode" class="h-3.5 w-3.5 animate-spin" />
                <Plus v-else class="h-3.5 w-3.5" />
                <span>Add</span>
              </button>
            </div>
          </div>

          <!-- Search Catalog in Modal -->
          <div class="relative my-3">
            <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground/70" />
            <input
              v-model="manageSearch"
              type="text"
              placeholder="Filter existing items..."
              class="w-full h-8 text-xs pl-8 pr-2.5 rounded-xl border border-border/80 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
            />
          </div>

          <!-- Items List with Inline Edit/Delete -->
          <div class="flex-1 overflow-y-auto space-y-2 pr-1 min-h-[160px]">
            <div
              v-for="item in filteredManageCatalog"
              :key="item.id || item.name"
              class="p-2.5 rounded-xl border transition-all"
              :class="editingItemId === item.id ? 'bg-primary/5 border-primary/40 shadow-xs' : 'bg-muted/30 border-border/50 hover:border-border'"
            >
              <!-- Editing View -->
              <div v-if="editingItemId === item.id" class="space-y-2">
                <div class="flex items-center gap-2">
                  <input
                    v-model="editItemName"
                    type="text"
                    placeholder="Item name"
                    class="h-8 text-xs flex-1 rounded-lg border border-border/80 px-2.5 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary font-medium"
                    @keyup.enter="saveItemEdit(item)"
                    autofocus
                  />
                  <div v-if="showPrice" class="relative w-28">
                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-muted-foreground font-mono">৳</span>
                    <input
                      v-model.number="editItemPrice"
                      type="number"
                      placeholder="Price"
                      min="0"
                      class="h-8 text-xs w-full pl-6 pr-2 rounded-lg border border-border/80 bg-background text-foreground font-mono focus:outline-none focus:ring-1 focus:ring-primary"
                      @keyup.enter="saveItemEdit(item)"
                    />
                  </div>
                </div>
                <div class="flex items-center justify-end gap-2">
                  <button
                    type="button"
                    @click="cancelEdit"
                    class="h-7 px-3 text-xs rounded-lg border border-border/70 hover:bg-accent text-foreground transition-colors cursor-pointer"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    @click="saveItemEdit(item)"
                    :disabled="!editItemName.trim() || isSubmitting"
                    class="h-7 px-3 text-xs bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50 cursor-pointer inline-flex items-center gap-1"
                  >
                    <Loader2 v-if="isSubmitting" class="h-3 w-3 animate-spin" />
                    <span>Save Changes</span>
                  </button>
                </div>
              </div>

              <!-- Normal Display View -->
              <div v-else class="flex items-center justify-between gap-3">
                <div class="flex-1 min-w-0">
                  <span class="text-xs font-bold text-foreground block truncate">{{ item.name }}</span>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                  <span v-if="showPrice && item.price !== null && item.price !== undefined" class="text-xs font-mono font-bold text-primary px-2 py-0.5 rounded-md bg-primary/10">
                    ৳{{ formatMoney(item.price) }}
                  </span>

                  <!-- Inline Confirmation when deleting -->
                  <div v-if="deletingItemId === item.id" class="flex items-center gap-1.5 bg-destructive/10 px-2 py-1 rounded-lg border border-destructive/30">
                    <span class="text-[11px] text-destructive font-semibold">Delete?</span>
                    <button
                      type="button"
                      @click="executeDeleteItem(item)"
                      :disabled="isSubmitting"
                      class="h-5 px-1.5 text-[10px] font-bold bg-destructive text-destructive-foreground rounded hover:bg-destructive/90 transition-colors cursor-pointer"
                    >
                      Yes
                    </button>
                    <button
                      type="button"
                      @click="deletingItemId = null"
                      class="h-5 px-1.5 text-[10px] rounded border border-border/80 bg-background text-foreground hover:bg-accent transition-colors cursor-pointer"
                    >
                      No
                    </button>
                  </div>

                  <!-- Action Buttons -->
                  <div v-else class="flex items-center gap-1">
                    <button
                      type="button"
                      @click="startEdit(item)"
                      :title="showPrice ? 'Edit Item Price & Name' : 'Edit Item Name'"
                      class="h-7 w-7 inline-flex items-center justify-center rounded-lg border border-border/60 hover:bg-accent text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
                    >
                      <Edit2 class="h-3.5 w-3.5" />
                    </button>
                    <button
                      type="button"
                      @click="deletingItemId = item.id"
                      title="Delete Item"
                      class="h-7 w-7 inline-flex items-center justify-center rounded-lg border border-destructive/20 hover:bg-destructive/10 text-destructive transition-colors cursor-pointer"
                    >
                      <Trash2 class="h-3.5 w-3.5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="filteredManageCatalog.length === 0" class="text-xs text-muted-foreground text-center py-8">
              No catalog items match your search.
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-between pt-4 border-t border-border/70 mt-3">
            <span class="text-xs text-muted-foreground">
              Total {{ catalog.length }} items
            </span>
            <button
              type="button"
              @click="closeManageModal"
              class="h-8 px-5 text-xs font-semibold bg-primary text-primary-foreground rounded-xl hover:bg-primary/90 transition-colors cursor-pointer shadow-2xs"
            >
              Done
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { ChevronDown, Search, Loader2, Edit2, Plus, Trash2 } from 'lucide-vue-next';
import api from '@/lib/api';

const props = defineProps({
  value: { type: String, default: '' },
  modelValue: { type: String, default: '' },
  catalogType: { type: String, default: 'officer' }, // 'officer' | 'airman' | 'worker'
  className: { type: String, default: '' },
  triggerClassName: { type: String, default: '' },
  showPrice: { type: Boolean, default: true },
});

const emit = defineEmits(['select', 'update:value', 'update:modelValue', 'items-updated']);

const selectedItemName = computed(() => {
  return props.modelValue || props.value || '';
});

const rootRef = ref(null);
const searchInputRef = ref(null);
const isOpen = ref(false);
const isManageOpen = ref(false);
const search = ref('');
const manageSearch = ref('');
const catalog = ref([]);
const loading = ref(false);
const isSubmitting = ref(false);
const addingMode = ref(false);

// Status / feedback message
const statusMessage = ref({ type: '', text: '' });
let statusTimeout = null;

function showStatus(text, type = 'success') {
  if (statusTimeout) clearTimeout(statusTimeout);
  statusMessage.value = { text, type };
  statusTimeout = setTimeout(() => {
    statusMessage.value = { text: '', type: '' };
  }, 4000);
}

// Quick add new item state
const newItemName = ref('');
const newItemPrice = ref(null);

// Inline edit state
const editingItemId = ref(null);
const editItemName = ref('');
const editItemPrice = ref(0);

// Inline delete state
const deletingItemId = ref(null);

const catalogTitle = computed(() => {
  if (props.catalogType === 'airman') return 'Manage Airman Catalog Items';
  if (props.catalogType === 'worker') return 'Manage Worker Catalog Items';
  return 'Manage Officer Catalog Items';
});

const filteredCatalog = computed(() => {
  const q = search.value.toLowerCase().trim();
  if (!q) return catalog.value;
  return catalog.value.filter(item => (item.name || '').toLowerCase().includes(q));
});

const filteredManageCatalog = computed(() => {
  const q = manageSearch.value.toLowerCase().trim();
  if (!q) return catalog.value;
  return catalog.value.filter(item => (item.name || '').toLowerCase().includes(q));
});

function isSelected(item) {
  if (!selectedItemName.value) return false;
  return item.name?.toLowerCase().trim() === selectedItemName.value.toLowerCase().trim();
}

function formatMoney(amount) {
  const num = Number(amount || 0);
  return num.toLocaleString();
}

async function fetchCatalog() {
  loading.value = true;
  try {
    const res = await api.get('/catalogs', { params: { type: props.catalogType } });
    if (res.data && res.data.data) {
      catalog.value = res.data.data;
    } else if (Array.isArray(res.data)) {
      catalog.value = res.data;
    }
    emit('items-updated', catalog.value);

    // If currently selected value exists, update price with current catalog item price
    if (selectedItemName.value) {
      const current = catalog.value.find(i => i.name?.toLowerCase().trim() === selectedItemName.value.toLowerCase().trim());
      if (current) {
        emit('select', current.name, Number(current.price || 0), current);
      }
    } else if (catalog.value.length > 0) {
      // Pick first item by default if nothing selected
      const first = catalog.value.find(i => i.name.toLowerCase().includes('battle')) || catalog.value[0];
      emit('select', first.name, Number(first.price || 0), first);
      emit('update:value', first.name);
      emit('update:modelValue', first.name);
    }
  } catch (err) {
    console.error('Failed to fetch catalog', err);
  } finally {
    loading.value = false;
  }
}

function handleSelect(item) {
  emit('select', item.name, Number(item.price || 0), item);
  emit('update:value', item.name);
  emit('update:modelValue', item.name);
  isOpen.value = false;
}

function toggleDropdown() {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    search.value = '';
    nextTick(() => {
      if (searchInputRef.value) {
        searchInputRef.value.focus();
      }
    });
  }
}

function openManageModal() {
  isOpen.value = false;
  isManageOpen.value = true;
  editingItemId.value = null;
  deletingItemId.value = null;
  newItemName.value = '';
  newItemPrice.value = null;
  statusMessage.value = { text: '', type: '' };
}

function closeManageModal() {
  isManageOpen.value = false;
  editingItemId.value = null;
  deletingItemId.value = null;
}

function startEdit(item) {
  editingItemId.value = item.id;
  deletingItemId.value = null;
  editItemName.value = item.name;
  editItemPrice.value = item.price !== null && item.price !== undefined ? Number(item.price) : 0;
}

function cancelEdit() {
  editingItemId.value = null;
  editItemName.value = '';
  editItemPrice.value = 0;
}

async function saveItemEdit(item) {
  if (!editItemName.value.trim() || !item?.id) return;
  isSubmitting.value = true;

  try {
    const updated = {
      type: props.catalogType,
      name: editItemName.value.trim(),
      sort_order: item.sort_order ?? 0,
    };
    if (props.showPrice) {
      updated.price = Number(editItemPrice.value || 0);
    }
    await api.put(`/catalogs/${item.id}`, updated);
    
    // If the item being edited was currently selected in parent, update parent selection immediately
    if (selectedItemName.value.toLowerCase().trim() === item.name.toLowerCase().trim()) {
      emit('select', updated.name, Number(updated.price || 0), { ...item, ...updated });
      emit('update:value', updated.name);
      emit('update:modelValue', updated.name);
    }

    showStatus(`"${updated.name}" updated successfully.`);
    editingItemId.value = null;
    await fetchCatalog();
  } catch (err) {
    console.error('Error updating catalog item', err);
    showStatus('Failed to update catalog item.', 'error');
  } finally {
    isSubmitting.value = false;
  }
}

async function addNewItem() {
  if (!newItemName.value.trim()) return;
  isSubmitting.value = true;
  addingMode.value = true;

  try {
    const maxOrder = catalog.value.length > 0 ? Math.max(...catalog.value.map(c => c.sort_order || 0)) : 0;
    const payload = {
      type: props.catalogType,
      name: newItemName.value.trim(),
      sort_order: maxOrder + 1,
    };
    if (props.showPrice && newItemPrice.value !== null && newItemPrice.value !== undefined) {
      payload.price = Number(newItemPrice.value || 0);
    }
    const res = await api.post('/catalogs', payload);
    const createdItem = res.data;

    // Auto select the newly created item
    if (createdItem && createdItem.name) {
      emit('select', createdItem.name, Number(createdItem.price || 0), createdItem);
      emit('update:value', createdItem.name);
      emit('update:modelValue', createdItem.name);
    }

    showStatus(`"${payload.name}" added to catalog.`);
    newItemName.value = '';
    newItemPrice.value = null;
    await fetchCatalog();
  } catch (err) {
    console.error('Error adding catalog item', err);
    showStatus('Failed to add catalog item.', 'error');
  } finally {
    isSubmitting.value = false;
    addingMode.value = false;
  }
}

async function executeDeleteItem(item) {
  if (!item?.id) return;
  isSubmitting.value = true;

  try {
    await api.delete(`/catalogs/${item.id}`, { params: { type: props.catalogType } });
    
    // If deleted item was the currently selected item, switch selection
    if (selectedItemName.value.toLowerCase().trim() === item.name.toLowerCase().trim()) {
      const remaining = catalog.value.filter(c => c.id !== item.id);
      if (remaining.length > 0) {
        const next = remaining[0];
        emit('select', next.name, Number(next.price || 0), next);
        emit('update:value', next.name);
        emit('update:modelValue', next.name);
      } else {
        emit('select', '', 0, null);
        emit('update:value', '');
        emit('update:modelValue', '');
      }
    }

    showStatus(`"${item.name}" deleted from catalog.`);
    deletingItemId.value = null;
    await fetchCatalog();
  } catch (err) {
    console.error('Error deleting catalog item', err);
    showStatus('Failed to delete catalog item.', 'error');
  } finally {
    isSubmitting.value = false;
  }
}

// Click outside handler
function handleClickOutside(event) {
  if (rootRef.value && !rootRef.value.contains(event.target)) {
    isOpen.value = false;
  }
}

onMounted(() => {
  fetchCatalog();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
