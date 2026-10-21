<template>
  <div class="space-y-5 max-w-7xl mx-auto pb-16">
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- TOP HEADER                                                         -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="goBack"
          class="h-9 w-9 inline-flex items-center justify-center rounded-xl border border-input bg-card hover:bg-accent text-foreground transition-colors shadow-2xs cursor-pointer shrink-0"
          title="Back"
        >
          <ArrowLeft class="h-4 w-4" />
        </button>

        <div>
          <h1 class="text-xl sm:text-2xl font-heading font-bold text-foreground">
            {{ isEditing ? 'Edit Order' : 'Create New Order' }}
          </h1>
          <p class="text-xs text-muted-foreground mt-0.5">
            Fill in the order details below
          </p>
        </div>
      </div>

      <!-- Draft Saved Indicator -->
      <div class="flex items-center gap-2 self-start sm:self-auto">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border border-border/70 bg-card text-muted-foreground shadow-2xs">
          <FileText class="h-3.5 w-3.5 text-muted-foreground/70" />
          Draft saved locally
        </span>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- MAIN FORM BODY                                                     -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <form @submit.prevent="saveOrder" class="space-y-5">
      <!-- ── SECTION 1: ITEMS ── -->
      <div class="rounded-2xl border border-border/70 bg-card p-5 sm:p-6 shadow-2xs space-y-4">
        <h2 class="text-sm font-bold text-foreground">Items</h2>

        <!-- Item Selector Bar matching Mockup -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
          <!-- Item Name Dropdown with Search & Manage Items Modal -->
          <div class="w-full sm:w-auto sm:min-w-[220px] sm:max-w-[280px]">
            <ItemCatalogDropdown
              v-model="selectedItemName"
              catalog-type="officer"
              :show-price="true"
              @select="onCatalogItemSelect"
              @items-updated="onCatalogItemsUpdated"
            />
          </div>

          <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
            <!-- Quantity Dropdown -->
            <div class="relative w-20">
              <select
                v-model.number="selectedQty"
                class="w-full h-10 px-3 pr-7 text-xs rounded-xl border border-border/80 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs cursor-pointer appearance-none font-medium text-center"
              >
                <option v-for="q in [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]" :key="q" :value="q">
                  {{ q }}
                </option>
              </select>
              <ChevronDown class="h-4 w-4 text-muted-foreground absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none" />
            </div>

            <!-- Price Tag Badge (Matches Screenshot) -->
            <div class="text-blue-600 dark:text-blue-400 font-bold text-base px-2 font-mono">
              ৳{{ formatMoney(selectedItemPrice * selectedQty) }}
            </div>

            <!-- + Add Item Button -->
            <button
              type="button"
              @click="addSelectedItem"
              class="h-10 px-4 inline-flex items-center gap-1.5 rounded-xl border border-border/80 bg-background hover:bg-accent text-foreground text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
            >
              <Plus class="h-3.5 w-3.5" />
              Add Item
            </button>
          </div>
        </div>

        <!-- Added Items Chips List (Shown when multiple items are added) -->
        <div v-if="orderItems.length > 1" class="flex flex-wrap gap-2 pt-1">
          <div
            v-for="(item, idx) in orderItems"
            :key="idx"
            class="inline-flex items-center gap-2 pl-3 pr-2 py-1 rounded-lg border border-border/70 bg-muted/20 text-xs font-medium text-foreground shadow-2xs"
          >
            <span>{{ item.item_name }} × {{ item.quantity }}</span>
            <span class="text-primary font-mono font-semibold">৳{{ formatMoney(item.price * item.quantity) }}</span>
            <button
              type="button"
              @click="removeItem(idx)"
              class="h-4 w-4 inline-flex items-center justify-center rounded hover:bg-muted text-muted-foreground hover:text-foreground cursor-pointer"
              title="Remove"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Measurements: Side-by-Side Cards matching Mockup -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-2">
          <!-- Left: SHIRT MEASUREMENTS -->
          <div class="rounded-xl border border-border/70 p-4 sm:p-5 space-y-4 bg-muted/5">
            <h3 class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
              SHIRT MEASUREMENTS
            </h3>

            <!-- Row 1: Length, Chest, Belly, Hip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">Length</label>
                <input
                  v-model="measurements.length"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">Chest</label>
                <input
                  v-model="measurements.chest"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">Belly</label>
                <input
                  v-model="measurements.belly"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">Hip</label>
                <input
                  v-model="measurements.hip"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
            </div>

            <!-- Row 2: Shoulder, Sleeve, Collar, Front -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">Shoulder</label>
                <input
                  v-model="measurements.shoulder"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">Sleeve</label>
                <input
                  v-model="measurements.sleeve"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">Collar</label>
                <input
                  v-model="measurements.collar"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">Front</label>
                <input
                  v-model="measurements.front"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
            </div>

            <!-- Row 3: Back -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">Back</label>
                <input
                  v-model="measurements.back"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
            </div>

            <!-- Hand Written Notes -->
            <div>
              <label class="block text-xs font-medium text-foreground mb-1">Hand Written Notes</label>
              <textarea
                v-model="shirtNotes"
                rows="2"
                placeholder="Shirt hand written notes..."
                class="w-full p-2.5 text-xs rounded-xl border border-border/70 bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
              ></textarea>
            </div>

            <!-- Extra Notes -->
            <div>
              <label class="block text-xs font-medium text-foreground mb-1">Extra Notes</label>
              <textarea
                v-model="shirtExtraNotes"
                rows="2"
                placeholder="Shirt extra notes..."
                class="w-full p-2.5 text-xs rounded-xl border border-border/70 bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
              ></textarea>
            </div>
          </div>

          <!-- Right: PANT MEASUREMENTS -->
          <div class="rounded-xl border border-border/70 p-4 sm:p-5 space-y-4 bg-muted/5">
            <h3 class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
              PANT MEASUREMENTS
            </h3>

            <!-- Row 1: Length, Waist, Hip -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">Length</label>
                <input
                  v-model="measurements.pant_length"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">Waist</label>
                <input
                  v-model="measurements.pant_waist"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
              <div class="col-span-2 sm:col-span-1">
                <label class="block text-[11px] text-muted-foreground mb-1">Hip</label>
                <input
                  v-model="measurements.pant_hip"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
            </div>

            <!-- Row 2: High, Thai, Leg Open -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">High</label>
                <input
                  v-model="measurements.pant_high"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
              <div>
                <label class="block text-[11px] text-muted-foreground mb-1">Thai</label>
                <input
                  v-model="measurements.pant_thai"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
              <div class="col-span-2 sm:col-span-1">
                <label class="block text-[11px] text-muted-foreground mb-1">Leg Open</label>
                <input
                  v-model="measurements.pant_leg_open"
                  type="text"
                  placeholder="-"
                  class="w-full h-10 sm:h-9 px-2 text-xs rounded-lg border border-border/70 bg-background text-foreground text-center font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
              </div>
            </div>

            <!-- Hand Written Notes -->
            <div>
              <label class="block text-xs font-medium text-foreground mb-1">Hand Written Notes</label>
              <textarea
                v-model="pantNotes"
                rows="2"
                placeholder="Pant hand written notes..."
                class="w-full p-2.5 text-xs rounded-xl border border-border/70 bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
              ></textarea>
            </div>

            <!-- Extra Notes -->
            <div>
              <label class="block text-xs font-medium text-foreground mb-1">Extra Notes</label>
              <textarea
                v-model="pantExtraNotes"
                rows="2"
                placeholder="Pant extra notes..."
                class="w-full p-2.5 text-xs rounded-xl border border-border/70 bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- ── SECTION 2: CUSTOMER & ORDER INFORMATION ── -->
      <div class="space-y-4">
        <!-- Row 1: BD Number, Customer Name, Phone (with instant match suggestions & auto-fill) -->
        <CustomerFieldsAutocomplete
          v-model:bdNumber="formData.bd_number"
          v-model:customerName="formData.customer_name"
          v-model:customerPhone="formData.customer_phone"
          v-model:customerId="formData.customer_id"
          :customers="customers"
          @select="onCustomerSelected"
          @change="saveDraftToLocalStorage"
        />

        <!-- Row 2: Branch & Order Date -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Branch</label>
            <div class="relative">
              <select
                v-model="formData.branch"
                class="w-full h-10 px-3 pr-8 text-xs rounded-xl border border-border/70 bg-card text-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs cursor-pointer appearance-none"
              >
                <option value="">Select Branch</option>
                <option value="GDP">GDP</option>
                <option value="NON-GDP">NON-GDP</option>
                <option value="HALF_WING">HALF_WING</option>
              </select>
              <ChevronDown class="h-4 w-4 text-muted-foreground absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Order Date</label>
            <input
              v-model="formData.order_date"
              type="date"
              class="w-full h-10 px-3 text-xs rounded-xl border border-border/70 bg-card text-foreground font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
            />
          </div>
        </div>

        <!-- Row 3: Ribon & Delivery Date -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Ribon</label>
            <div class="relative">
              <select
                v-model="formData.ribon"
                class="w-full h-10 px-3 pr-8 text-xs rounded-xl border border-border/70 bg-card text-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs cursor-pointer appearance-none"
              >
                <option :value="null">Select Ribon</option>
                <option v-for="r in [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20]" :key="r" :value="r">
                  {{ r }}
                </option>
              </select>
              <ChevronDown class="h-4 w-4 text-muted-foreground absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Delivery Date</label>
            <input
              v-model="formData.delivery_date"
              type="date"
              class="w-full h-10 px-3 text-xs rounded-xl border border-border/70 bg-card text-foreground font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
            />
          </div>
        </div>
      </div>

      <!-- ── SECTION 3: FINANCIALS ── -->
      <div class="flex justify-end pt-2">
        <div class="w-full sm:w-80 space-y-2.5 text-xs">
          <!-- Total Amount -->
          <div class="flex items-center justify-between gap-4">
            <span class="text-muted-foreground font-medium">Total Amount (৳)</span>
            <input
              :value="formData.total_amount"
              type="text"
              readonly
              class="w-32 sm:w-36 h-9 px-3 rounded-lg border border-border/70 bg-muted/30 text-foreground font-mono font-bold text-right shadow-2xs cursor-not-allowed"
            />
          </div>

          <!-- Discount -->
          <div class="flex items-center justify-between gap-4">
            <span class="text-muted-foreground font-medium">Discount (৳)</span>
            <input
              v-model.number="formData.discount"
              @input="recalculateTotals"
              type="number"
              min="0"
              placeholder="Optional"
              class="w-32 sm:w-36 h-9 px-3 rounded-lg border border-border/70 bg-card text-foreground font-mono text-right shadow-2xs focus:outline-none focus:ring-1 focus:ring-primary"
            />
          </div>

          <!-- Advance -->
          <div class="flex items-center justify-between gap-4">
            <span class="text-muted-foreground font-medium">Advance (৳)</span>
            <input
              v-model.number="formData.advance"
              @input="recalculateTotals"
              type="number"
              min="0"
              placeholder="0"
              class="w-32 sm:w-36 h-9 px-3 rounded-lg border border-border/70 bg-card text-foreground font-mono font-semibold text-right shadow-2xs focus:outline-none focus:ring-1 focus:ring-primary"
            />
          </div>

          <!-- Due -->
          <div class="flex items-center justify-between gap-4">
            <span class="text-muted-foreground font-medium">Due (৳)</span>
            <input
              :value="`৳${formData.due}`"
              type="text"
              readonly
              class="w-32 sm:w-36 h-9 px-3 rounded-lg border border-border/70 bg-muted/30 font-mono font-bold text-right shadow-2xs cursor-not-allowed"
              :class="formData.due > 0 ? 'text-foreground' : 'text-emerald-600'"
            />
          </div>
        </div>
      </div>

      <!-- ── SECTION 4: BOTTOM ACTION BAR ── -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-border/70">
        <!-- Save Order (Primary on mobile, center on desktop) -->
        <button
          type="submit"
          :disabled="saving"
          class="w-full sm:flex-1 sm:max-w-2xl h-11 sm:h-10 px-8 rounded-xl bg-primary text-primary-foreground text-xs sm:text-xs font-semibold hover:bg-primary/90 flex items-center justify-center gap-2 shadow-xs transition-colors cursor-pointer disabled:opacity-50 order-1 sm:order-2"
        >
          <Loader2 v-if="saving" class="h-4 w-4 animate-spin" />
          <span>Save Order</span>
        </button>

        <!-- Cancel (Left on desktop, stacked on mobile) -->
        <button
          type="button"
          @click="goBack"
          class="w-full sm:w-auto h-10 px-6 rounded-xl border border-border/80 bg-card hover:bg-accent text-foreground text-xs font-semibold shadow-2xs transition-colors cursor-pointer shrink-0 order-3 sm:order-1"
        >
          Cancel
        </button>

        <!-- Right: Print Invoice and Cutting Slip -->
        <div class="grid grid-cols-2 sm:flex items-center gap-2.5 w-full sm:w-auto shrink-0 order-2 sm:order-3">
          <button
            type="button"
            @click="printInvoice"
            class="h-10 px-3 sm:px-4 inline-flex items-center justify-center gap-1.5 rounded-xl border border-border/80 bg-card hover:bg-accent text-foreground text-xs font-medium shadow-2xs transition-colors cursor-pointer"
          >
            <Printer class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
            <span class="truncate">Print Invoice</span>
          </button>

          <button
            type="button"
            @click="printSlip"
            class="h-10 px-3 sm:px-4 inline-flex items-center justify-center gap-1.5 rounded-xl border border-border/80 bg-card hover:bg-accent text-foreground text-xs font-medium shadow-2xs transition-colors cursor-pointer"
          >
            <Scissors class="h-3.5 w-3.5 text-muted-foreground shrink-0" />
            <span class="truncate">Cutting Slip</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  ArrowLeft, FileText, ChevronDown, Plus, Printer, Scissors, Save, Loader2
} from 'lucide-vue-next';
import api from '@/lib/api';
import { generateInvoicePDF, generateSlipPDF } from '@/lib/pdf';
import CustomerFieldsAutocomplete from '@/components/CustomerFieldsAutocomplete.vue';
import ItemCatalogDropdown from '@/components/ItemCatalogDropdown.vue';

const route = useRoute();
const router = useRouter();

const saving = ref(false);
const customers = ref([]);
const catalogItems = ref([]);

// Selected catalog item in dropdown
const selectedItemName = ref('Battle Uniform');
const selectedCatalogItem = ref(null);
const selectedQty = ref(1);

const selectedItemPrice = computed(() => {
  return Number(selectedCatalogItem.value?.price || 0);
});

function onCatalogItemSelect(name, price, item) {
  selectedItemName.value = name;
  selectedCatalogItem.value = item || { name, price };
}

function onCatalogItemsUpdated(items) {
  catalogItems.value = items || [];
  if (selectedItemName.value) {
    const current = items.find(i => i.name?.toLowerCase().trim() === selectedItemName.value.toLowerCase().trim());
    if (current) {
      selectedCatalogItem.value = current;
    }
  }
}

// Added items list
const orderItems = ref([
  {
    item_name: 'Battle Uniform',
    item_type: 'shirt',
    quantity: 1,
    price: 800,
  }
]);

// Measurements state
const measurements = ref({
  length: '',
  chest: '',
  belly: '',
  hip: '',
  shoulder: '',
  sleeve: '',
  collar: '',
  front: '',
  back: '',
  pant_length: '',
  pant_waist: '',
  pant_hip: '',
  pant_high: '',
  pant_thai: '',
  pant_leg_open: '',
});

const shirtNotes = ref('');
const shirtExtraNotes = ref('');
const pantNotes = ref('');
const pantExtraNotes = ref('');

// Main order form data
const formData = ref({
  customer_id: null,
  bd_number: '',
  customer_name: '',
  customer_phone: '',
  branch: '',
  ribon: null,
  order_date: new Date().toISOString().split('T')[0],
  delivery_date: '',
  total_amount: 800,
  discount: null,
  advance: 0,
  due: 800,
});

const isEditing = computed(() => {
  return Boolean(route.params.id && (route.name === 'EditOrder' || route.path.endsWith('/edit') || route.path.includes('/edit')));
});

function formatMoney(amount) {
  const num = Number(amount || 0);
  return num.toLocaleString();
}

function onCatalogItemChange() {
  // auto updates price badge
}

function addSelectedItem() {
  const name = selectedItemName.value || selectedCatalogItem.value?.name;
  if (!name) return;
  const price = Number(selectedCatalogItem.value?.price || 0);
  const qty = Number(selectedQty.value || 1);

  // Check if item already exists in list
  const existing = orderItems.value.find(i => i.item_name === name);
  if (existing) {
    existing.quantity += qty;
  } else {
    orderItems.value.push({
      item_name: name,
      item_type: name.toLowerCase().includes('pant') ? 'pant' : 'shirt',
      quantity: qty,
      price,
    });
  }

  recalculateTotals();
  saveDraftToLocalStorage();
}

function removeItem(idx) {
  orderItems.value.splice(idx, 1);
  recalculateTotals();
  saveDraftToLocalStorage();
}

function recalculateTotals() {
  let subtotal = 0;
  orderItems.value.forEach((item) => {
    subtotal += Number(item.quantity || 1) * Number(item.price || 0);
  });

  if (subtotal > 0 || !isEditing.value) {
    formData.value.total_amount = subtotal;
  }
  const currentTotal = Number(formData.value.total_amount || 0);
  const adv = Number(formData.value.advance || 0);
  const disc = Number(formData.value.discount || 0);
  formData.value.due = Math.max(0, currentTotal - adv - disc);
}


async function onCustomerSelected(customer) {
  if (!customer) return;
  saveDraftToLocalStorage();

  // If measurements are currently blank, auto-load past measurements from customer's previous order
  const hasMeasurements = Object.values(measurements.value || {}).some(v => v && String(v).trim() !== '');
  if (!hasMeasurements && customer.id) {
    try {
      const res = await api.get(`/customers/${customer.id}`);
      const custData = res.data?.data || res.data;
      const lastOrder = (custData?.orders || []).find(o => o.items && o.items.some(i => i.measurements && Object.keys(i.measurements).length > 0));
      if (lastOrder) {
        const itemWithMeas = lastOrder.items.find(i => i.measurements && Object.keys(i.measurements).length > 0);
        if (itemWithMeas && itemWithMeas.measurements) {
          const m = typeof itemWithMeas.measurements === 'string' ? JSON.parse(itemWithMeas.measurements) : itemWithMeas.measurements;
          Object.keys(measurements.value).forEach(k => {
            if (m[k] && !measurements.value[k]) measurements.value[k] = m[k];
          });
          if (m.__pant_notes && !pantNotes.value) pantNotes.value = m.__pant_notes;
          if (m.__pant_extra_notes && !pantExtraNotes.value) pantExtraNotes.value = m.__pant_extra_notes;
          if (lastOrder.measurement_notes && !shirtNotes.value) shirtNotes.value = lastOrder.measurement_notes;
          if (lastOrder.extra_notes && !shirtExtraNotes.value) shirtExtraNotes.value = lastOrder.extra_notes;
          if (lastOrder.branch && !formData.value.branch) formData.value.branch = lastOrder.branch;
          if (lastOrder.ribon && !formData.value.ribon) formData.value.ribon = lastOrder.ribon;
          saveDraftToLocalStorage();
        }
      }
    } catch (e) {
      console.warn('Could not auto-load past measurements', e);
    }
  }
}

function saveDraftToLocalStorage() {
  if (isEditing.value) return;
  try {
    const draft = {
      orderItems: orderItems.value,
      measurements: measurements.value,
      shirtNotes: shirtNotes.value,
      shirtExtraNotes: shirtExtraNotes.value,
      pantNotes: pantNotes.value,
      pantExtraNotes: pantExtraNotes.value,
      formData: formData.value,
    };
    localStorage.setItem('tailorpro_officer_order_draft', JSON.stringify(draft));
  } catch (err) {
    // ignore
  }
}

function loadDraftFromLocalStorage() {
  try {
    const raw = localStorage.getItem('tailorpro_officer_order_draft');
    if (raw && !isEditing.value) {
      const draft = JSON.parse(raw);
      if (draft.orderItems && draft.orderItems.length > 0) {
        orderItems.value = draft.orderItems;
      }
      if (draft.measurements) {
        measurements.value = { ...measurements.value, ...draft.measurements };
      }
      if (draft.shirtNotes) shirtNotes.value = draft.shirtNotes;
      if (draft.shirtExtraNotes) shirtExtraNotes.value = draft.shirtExtraNotes;
      if (draft.pantNotes) pantNotes.value = draft.pantNotes;
      if (draft.pantExtraNotes) pantExtraNotes.value = draft.pantExtraNotes;
      if (draft.formData) {
        formData.value = {
          ...formData.value,
          ...draft.formData,
          order_date: draft.formData.order_date || new Date().toISOString().split('T')[0],
        };
      }
      recalculateTotals();
    }
  } catch (err) {
    // ignore
  }
}

async function fetchCatalogAndCustomers() {
  try {
    const [catRes, custRes] = await Promise.all([
      api.get('/catalogs/officer'),
      api.get('/customers'),
    ]);

    catalogItems.value = catRes.data?.data || catRes.data || [];
    customers.value = custRes.data?.data || custRes.data || [];

    // Set default item to Battle Uniform or first item
    const battle = catalogItems.value.find(i => i.name.toLowerCase().includes('battle'));
    const initialItem = battle || catalogItems.value[0] || null;
    if (initialItem && !selectedCatalogItem.value) {
      selectedCatalogItem.value = initialItem;
      selectedItemName.value = initialItem.name;
    }
  } catch (err) {
    console.error('Failed to load catalogs or customers', err);
  }
}

async function loadExistingOrder(id) {
  try {
    const res = await api.get(`/orders/${id}`);
    const ord = res.data?.data || res.data;
    if (!ord) return;

    formData.value = {
      customer_id: ord.customer_id,
      bd_number: (ord.customer?.bd_number || ord.bd_number || '').trim(),
      customer_name: ord.customer?.name || ord.customer_name || '',
      customer_phone: ord.customer?.phone || ord.customer_phone || '',
      branch: ord.branch || '',
      ribon: ord.ribon,
      order_date: ord.order_date || '',
      delivery_date: ord.delivery_date || '',
      total_amount: Number(ord.total_amount || 0),
      discount: Number(ord.discount || 0),
      advance: Number(ord.advance || 0),
      due: Number(ord.due || 0),
    };

    if (ord.items && ord.items.length > 0) {
      // Map items with catalog prices or fallback
      const totalQty = ord.items.reduce((sum, it) => sum + Number(it.quantity || 1), 0) || 1;
      const fallbackPrice = Number(ord.total_amount || 0) / totalQty;

      orderItems.value = ord.items.map(it => {
        const cat = catalogItems.value.find(c =>
          c.name?.toLowerCase().trim() === (it.item_name || it.item_type)?.toLowerCase().trim()
        );
        const itemPrice = cat ? Number(cat.price || 0) : (it.price ? Number(it.price) : fallbackPrice);
        return {
          item_name: it.item_name || it.item_type,
          item_type: it.item_type || 'shirt',
          quantity: Number(it.quantity || 1),
          price: itemPrice,
        };
      });

      // Pull measurements from all items having them

      for (const it of ord.items) {
        if (it.measurements) {
          const m = typeof it.measurements === 'string' ? JSON.parse(it.measurements) : it.measurements;
          measurements.value = { ...measurements.value, ...m };
        }
        if (it.notes && !shirtNotes.value) {
          shirtNotes.value = it.notes;
        }
      }
    }

    if (ord.measurement_notes) {
      if (ord.measurement_notes.includes(' | ')) {
        const parts = ord.measurement_notes.split(' | ');
        shirtNotes.value = parts[0] || '';
        pantNotes.value = parts[1] || '';
      } else {
        shirtNotes.value = ord.measurement_notes;
      }
    }

    if (ord.extra_notes) {
      if (ord.extra_notes.includes(' | ')) {
        const parts = ord.extra_notes.split(' | ');
        shirtExtraNotes.value = parts[0] || '';
        pantExtraNotes.value = parts[1] || '';
      } else {
        shirtExtraNotes.value = ord.extra_notes.trim();
      }
    }
  } catch (err) {
    console.error('Failed to load existing order for edit', err);
  }
}

async function saveOrder() {
  if (!formData.value.customer_name.trim()) {
    alert('Please enter a customer name.');
    return;
  }
  if (orderItems.value.length === 0) {
    alert('Please add at least one item.');
    return;
  }

  saving.value = true;
  try {
    // Combine notes
    const combinedNotes = [shirtNotes.value, pantNotes.value].filter(Boolean).join(' | ');
    const combinedExtraNotes = [shirtExtraNotes.value, pantExtraNotes.value].filter(Boolean).join(' | ');

    // Prepare items with attached measurements
    const itemsPayload = orderItems.value.map(it => ({
      item_name: it.item_name,
      item_type: it.item_type || 'shirt',
      quantity: it.quantity,
      price: it.price,
      measurements: measurements.value,
      notes: shirtNotes.value || pantNotes.value || '',
    }));

    const payload = {
      order_category: 'officer',
      customer_id: formData.value.customer_id,
      customer_name: formData.value.customer_name,
      customer_phone: formData.value.customer_phone,
      bd_number: formData.value.bd_number || 'N/A',
      branch: formData.value.branch || null,
      ribon: formData.value.ribon || null,
      order_date: formData.value.order_date,
      delivery_date: formData.value.delivery_date || null,
      items: itemsPayload,
      total_amount: formData.value.total_amount,
      advance: formData.value.advance,
      due: formData.value.due,
      discount: formData.value.discount,
      measurement_notes: combinedNotes,
      extra_notes: combinedExtraNotes,
    };

    let res;
    if (isEditing.value) {
      res = await api.put(`/orders/${route.params.id}`, payload);
    } else {
      res = await api.post('/orders', payload);
      // Clear local draft only upon successful creation of a new order
      localStorage.removeItem('tailorpro_officer_order_draft');
    }

    const createdOrder = res.data?.data || res.data;
    if (createdOrder && createdOrder.id) {
      router.push(`/orders/${createdOrder.id}`);
    } else {
      router.push('/orders');
    }
  } catch (err) {
    console.error('Failed to save order', err);
    alert('Failed to save order: ' + (err.response?.data?.message || err.message));
  } finally {
    saving.value = false;
  }
}

function printInvoice() {
  const orderPreview = {
    order_number: isEditing.value ? route.params.id : 'NEW',
    order_date: formData.value.order_date,
    delivery_date: formData.value.delivery_date,
    total_amount: formData.value.total_amount,
    advance: formData.value.advance,
    due: formData.value.due,
  };
  const customerPreview = {
    name: formData.value.customer_name,
    phone: formData.value.customer_phone,
    bd_number: formData.value.bd_number,
  };
  generateInvoicePDF(orderPreview, customerPreview, orderItems.value);
}

function printSlip() {
  const orderPreview = {
    order_number: isEditing.value ? route.params.id : 'NEW',
    order_date: formData.value.order_date,
    delivery_date: formData.value.delivery_date,
    branch: formData.value.branch,
    ribon: formData.value.ribon,
  };
  const customerPreview = {
    name: formData.value.customer_name,
    phone: formData.value.customer_phone,
    bd_number: formData.value.bd_number,
  };
  const itemsWithMeasurements = orderItems.value.map(it => ({
    ...it,
    measurements: measurements.value,
    notes: shirtNotes.value || pantNotes.value,
  }));
  generateSlipPDF(orderPreview, customerPreview, itemsWithMeasurements);
}

function goBack() {
  if (window.history.length > 1) {
    router.back();
  } else {
    router.push('/orders');
  }
}

watch(
  () => measurements.value,
  () => {
    saveDraftToLocalStorage();
  },
  { deep: true }
);

watch(
  () => formData.value,
  () => {
    saveDraftToLocalStorage();
  },
  { deep: true }
);

onMounted(async () => {
  await fetchCatalogAndCustomers();
  if (isEditing.value) {
    await loadExistingOrder(route.params.id);
  } else {
    loadDraftFromLocalStorage();
  }
});
</script>
