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
            {{ isEditing ? 'Edit Airman Order' : 'New Airman Order' }}
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

        <!-- Airman Item Selector Bar matching Mockup -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
          <!-- IV-Number Input -->
          <div class="w-full sm:w-44">
            <input
              v-model="formData.iv_number"
              type="text"
              placeholder="IV-Number"
              class="w-full h-10 px-3.5 text-xs rounded-xl border border-border/80 bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs font-medium"
            />
          </div>

          <!-- Item Name Dropdown with Search & Manage Items Modal -->
          <div class="w-full sm:w-auto sm:min-w-[220px] sm:max-w-[280px]">
            <ItemCatalogDropdown
              v-model="selectedItemName"
              catalog-type="airman"
              :show-price="false"
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
              <ChevronDown class="h-3.5 w-3.5 text-muted-foreground absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none" />
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
            <button
              type="button"
              @click="removeItem(idx)"
              class="text-muted-foreground hover:text-destructive text-sm leading-none p-0.5 cursor-pointer"
              title="Remove"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- ── DUAL MEASUREMENTS CONTAINER ── -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-2">
          <!-- LEFT CARD: SHIRT MEASUREMENTS -->
          <div class="rounded-xl border border-border/70 p-4 sm:p-5 space-y-4 bg-muted/5">
            <h3 class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
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

          <!-- RIGHT CARD: PANT MEASUREMENTS -->
          <div class="rounded-xl border border-border/70 p-4 sm:p-5 space-y-4 bg-muted/5">
            <h3 class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
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

      <!-- ── SECTION 2: CUSTOMER & AIRMAN INFORMATION ── -->
      <div class="rounded-2xl border border-border/70 bg-card p-5 sm:p-6 shadow-2xs space-y-4">
        <!-- Row 1: BD-Number, Name, Phone Number (with instant match suggestions & auto-fill) -->
        <CustomerFieldsAutocomplete
          v-model:bdNumber="formData.bd_number"
          v-model:customerName="formData.customer_name"
          v-model:customerPhone="formData.customer_phone"
          v-model:customerId="formData.customer_id"
          :customers="customers"
          :airmanMode="true"
          @select="onCustomerSelected"
          @change="saveDraftToLocalStorage"
        />

        <!-- Row 2: Trade, Ribon, Office -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <!-- Trade -->
          <div>
            <label class="block text-xs font-medium text-foreground mb-1.5">Trade</label>
            <input
              v-model="formData.trade"
              type="text"
              placeholder=""
              class="w-full h-10 px-3.5 text-xs rounded-xl border border-border/80 bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
            />
          </div>

          <!-- Ribon -->
          <div>
            <label class="block text-xs font-medium text-foreground mb-1.5">Ribon</label>
            <div class="relative">
              <select
                v-model="formData.ribon"
                class="w-full h-10 px-3.5 pr-8 text-xs rounded-xl border border-border/80 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs cursor-pointer appearance-none font-medium"
              >
                <option :value="null">Select Ribon</option>
                <option v-for="r in ribonOptions" :key="r" :value="r">
                  {{ r }}
                </option>
              </select>
              <ChevronDown class="h-4 w-4 text-muted-foreground absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            </div>
          </div>

          <!-- Office -->
          <div>
            <label class="block text-xs font-medium text-foreground mb-1.5">Office</label>
            <input
              v-model="formData.office"
              type="text"
              placeholder=""
              class="w-full h-10 px-3.5 text-xs rounded-xl border border-border/80 bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
            />
          </div>
        </div>

        <!-- Row 3: Running Year Dropdown with Manage & Quick Select -->
        <div class="space-y-2 max-w-md">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-medium text-foreground">
              Running Year <span class="text-muted-foreground font-normal">(রানিং ইয়ার)</span>
            </label>
            <span v-if="formData.running_year" class="text-[11px] font-mono text-primary font-medium">
              Selected: {{ formData.running_year }}
            </span>
          </div>

          <div class="flex items-center gap-2">
            <div class="relative flex-1">
              <select
                v-model="formData.running_year"
                class="w-full h-10 px-3.5 pr-8 text-xs rounded-xl border border-border/80 bg-background text-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs cursor-pointer appearance-none font-medium font-mono"
              >
                <option v-if="runningYears.length === 0" value="">No running years available</option>
                <option v-for="yr in runningYears" :key="yr.id || yr.year_label" :value="yr.year_label">
                  {{ yr.year_label }}
                </option>
              </select>
              <ChevronDown class="h-4 w-4 text-muted-foreground absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            </div>

            <!-- Manage Years Button -->
            <button
              type="button"
              @click="openManageYearsModal"
              class="h-10 px-3.5 inline-flex items-center gap-1.5 rounded-xl border border-border/80 bg-background hover:bg-accent hover:border-primary/40 text-foreground text-xs font-semibold shadow-2xs transition-all cursor-pointer shrink-0 group"
              title="Create, edit, update and delete running years"
            >
              <Settings2 class="h-3.5 w-3.5 text-muted-foreground group-hover:text-primary transition-colors" />
              <span>Manage Years</span>
            </button>
          </div>

          <!-- Quick Select Chips -->
          <div v-if="runningYears.length > 0" class="flex flex-wrap items-center gap-1.5 pt-0.5">
            <span class="text-[10px] text-muted-foreground mr-1">Quick:</span>
            <button
              v-for="yr in runningYears.slice(0, 5)"
              :key="'quick-' + (yr.id || yr.year_label)"
              type="button"
              @click="selectRunningYear(yr.year_label)"
              :class="[
                'px-2 py-0.5 text-[11px] rounded-md font-mono transition-all cursor-pointer border',
                formData.running_year === yr.year_label
                  ? 'bg-primary text-primary-foreground border-primary font-semibold shadow-2xs'
                  : 'bg-muted/40 hover:bg-muted text-muted-foreground hover:text-foreground border-border/60'
              ]"
            >
              {{ yr.year_label }}
            </button>
          </div>
        </div>
      </div>

      <!-- ── SECTION 3: BOTTOM ACTION BAR ── -->
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

    <!-- ── MANAGE RUNNING YEARS MODAL (Create, Edit, Update, Delete) ── -->
    <Teleport to="body">
      <div
        v-if="showManageYearsModal"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 animate-in fade-in duration-200"
      >
        <div
          class="bg-card border border-border rounded-2xl shadow-2xl max-w-lg w-full max-h-[90dvh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-200"
        >
          <!-- Modal Header -->
          <div class="px-4 sm:px-6 py-4 border-b border-border/80 flex items-center justify-between bg-muted/20">
            <div class="flex items-center gap-3">
              <div class="h-9 w-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20 shrink-0">
                <Calendar class="h-5 w-5" />
              </div>
              <div>
                <h3 class="text-sm font-bold text-foreground flex items-center gap-2">
                  <span>Manage Running Years</span>
                  <span class="text-xs font-normal text-muted-foreground hidden sm:inline">(রানিং ইয়ার)</span>
                </h3>
                <p class="text-[11px] text-muted-foreground">
                  Create, update, or remove running year records.
                </p>
              </div>
            </div>
            <button
              type="button"
              @click="showManageYearsModal = false"
              class="h-8 w-8 rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground flex items-center justify-center transition-colors cursor-pointer shrink-0"
            >
              <X class="h-4 w-4" />
            </button>
          </div>

          <!-- Modal Body (Scrollable) -->
          <div class="p-4 sm:p-6 overflow-y-auto space-y-4 sm:space-y-5 flex-1">
            <!-- Error Banner -->
            <div
              v-if="yearError"
              class="p-3 rounded-xl bg-destructive/10 border border-destructive/20 text-destructive text-xs flex items-center gap-2 animate-in fade-in"
            >
              <AlertCircle class="h-4 w-4 shrink-0" />
              <span>{{ yearError }}</span>
            </div>

            <!-- Create New Running Year Box -->
            <div class="p-4 rounded-xl border border-border/80 bg-muted/30 space-y-3">
              <div class="flex items-center justify-between">
                <label class="text-xs font-semibold text-foreground flex items-center gap-1.5">
                  <Plus class="h-3.5 w-3.5 text-primary" />
                  <span>Create New Running Year</span>
                </label>
                <span class="text-[10px] text-muted-foreground">Format: YYYY-YYYY</span>
              </div>

              <div class="flex items-center gap-2">
                <input
                  v-model="newYearLabel"
                  type="text"
                  placeholder="e.g. 2026-2027"
                  @keyup.enter="submitNewYear"
                  class="flex-1 h-9 px-3 text-xs rounded-xl border border-border/80 bg-background text-foreground font-mono focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
                />
                <button
                  type="button"
                  @click="submitNewYear"
                  :disabled="addingYear || !newYearLabel.trim()"
                  class="h-9 px-4 text-xs font-semibold bg-primary text-primary-foreground rounded-xl hover:bg-primary/90 disabled:opacity-50 cursor-pointer inline-flex items-center gap-1.5 shadow-xs transition-colors shrink-0"
                >
                  <Loader2 v-if="addingYear" class="h-3.5 w-3.5 animate-spin" />
                  <Plus v-else class="h-3.5 w-3.5" />
                  <span>Add Year</span>
                </button>
              </div>

              <!-- Quick Suggested Suggestions (Click to fill) -->
              <div v-if="suggestedYears.length > 0" class="flex flex-wrap items-center gap-1.5 pt-1">
                <span class="text-[10px] text-muted-foreground flex items-center gap-1">
                  <Sparkles class="h-3 w-3 text-primary" />
                  Suggestions:
                </span>
                <button
                  v-for="sug in suggestedYears"
                  :key="'sug-' + sug"
                  type="button"
                  @click="newYearLabel = sug"
                  class="px-2 py-0.5 text-[10px] rounded-md font-mono bg-background hover:bg-accent border border-border/80 text-foreground transition-colors cursor-pointer"
                  title="Click to fill input"
                >
                  + {{ sug }}
                </button>
              </div>
            </div>

            <!-- Existing Years List (Edit / Update / Delete) -->
            <div class="space-y-2.5">
              <div class="flex items-center justify-between text-xs">
                <span class="font-semibold text-foreground">
                  Existing Running Years ({{ runningYears.length }})
                </span>
                <span class="text-[11px] text-muted-foreground">
                  Click to select, edit, or delete
                </span>
              </div>

              <div v-if="runningYears.length === 0" class="py-8 text-center text-xs text-muted-foreground border border-dashed border-border rounded-xl">
                No running years created yet. Add one above.
              </div>

              <div v-else class="space-y-2 max-h-60 overflow-y-auto pr-1">
                <div
                  v-for="yr in runningYears"
                  :key="yr.id || yr.year_label"
                  class="p-2.5 rounded-xl border border-border/80 bg-background/60 hover:border-border transition-colors flex items-center justify-between gap-3 group"
                  :class="{
                    'ring-1 ring-primary/40 bg-primary/5': formData.running_year === yr.year_label && editingYearId !== yr.id && confirmDeleteYearId !== yr.id
                  }"
                >
                  <!-- Mode 1: Edit Mode -->
                  <div v-if="editingYearId === yr.id" class="flex-1 flex items-center gap-2">
                    <input
                      v-model="editingYearLabel"
                      type="text"
                      @keyup.enter="submitEditYear(yr)"
                      @keyup.esc="cancelEditYear"
                      class="flex-1 h-8 px-2.5 text-xs rounded-lg border border-primary bg-background font-mono text-foreground focus:outline-none focus:ring-1 focus:ring-primary"
                      placeholder="e.g. 2026-2027"
                      autofocus
                    />
                    <button
                      type="button"
                      @click="submitEditYear(yr)"
                      :disabled="updatingYearId === yr.id || !editingYearLabel.trim()"
                      class="h-8 px-2.5 rounded-lg bg-primary text-primary-foreground text-xs font-medium hover:bg-primary/90 flex items-center gap-1 cursor-pointer disabled:opacity-50"
                      title="Save Changes"
                    >
                      <Loader2 v-if="updatingYearId === yr.id" class="h-3 w-3 animate-spin" />
                      <Check v-else class="h-3.5 w-3.5" />
                      <span>Save</span>
                    </button>
                    <button
                      type="button"
                      @click="cancelEditYear"
                      class="h-8 px-2 rounded-lg border border-border hover:bg-accent text-muted-foreground hover:text-foreground text-xs cursor-pointer"
                      title="Cancel Edit"
                    >
                      <X class="h-3.5 w-3.5" />
                    </button>
                  </div>

                  <!-- Mode 2: Confirm Delete Mode -->
                  <div v-else-if="confirmDeleteYearId === yr.id" class="flex-1 flex items-center justify-between gap-2 bg-destructive/10 border border-destructive/20 rounded-lg px-2.5 py-1">
                    <span class="text-xs text-destructive font-medium">
                      Delete <strong>{{ yr.year_label }}</strong>?
                    </span>
                    <div class="flex items-center gap-1.5">
                      <button
                        type="button"
                        @click="submitDeleteYear(yr)"
                        :disabled="deletingYearId === yr.id"
                        class="h-7 px-2.5 rounded-md bg-destructive text-destructive-foreground text-[11px] font-semibold hover:bg-destructive/90 flex items-center gap-1 cursor-pointer disabled:opacity-50"
                      >
                        <Loader2 v-if="deletingYearId === yr.id" class="h-3 w-3 animate-spin" />
                        <span>Confirm</span>
                      </button>
                      <button
                        type="button"
                        @click="cancelDeleteYear"
                        class="h-7 px-2 rounded-md border border-border bg-background hover:bg-accent text-foreground text-[11px] cursor-pointer"
                      >
                        Cancel
                      </button>
                    </div>
                  </div>

                  <!-- Mode 3: Normal Display Row -->
                  <template v-else>
                    <div class="flex items-center gap-2.5 min-w-0">
                      <span class="font-mono text-xs font-semibold text-foreground tracking-wide">
                        {{ yr.year_label }}
                      </span>
                      <span
                        v-if="formData.running_year === yr.year_label"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-primary/15 text-primary border border-primary/20"
                      >
                        <Check class="h-3 w-3" />
                        Active
                      </span>
                    </div>

                    <!-- Row Action Buttons -->
                    <div class="flex items-center gap-1 shrink-0">
                      <!-- Select Button -->
                      <button
                        v-if="formData.running_year !== yr.year_label"
                        type="button"
                        @click="selectRunningYear(yr.year_label)"
                        class="h-7 px-2 text-[11px] font-medium rounded-md border border-border/80 hover:bg-primary/10 hover:border-primary/40 hover:text-primary transition-colors cursor-pointer"
                        title="Select for current order"
                      >
                        Select
                      </button>

                      <!-- Edit Button -->
                      <button
                        type="button"
                        @click="startEditYear(yr)"
                        class="h-7 w-7 rounded-md hover:bg-muted text-muted-foreground hover:text-foreground flex items-center justify-center transition-colors cursor-pointer"
                        title="Edit running year label"
                      >
                        <Edit2 class="h-3.5 w-3.5" />
                      </button>

                      <!-- Delete Button -->
                      <button
                        type="button"
                        @click="startDeleteYear(yr)"
                        class="h-7 w-7 rounded-md hover:bg-destructive/10 text-muted-foreground hover:text-destructive flex items-center justify-center transition-colors cursor-pointer"
                        title="Delete running year"
                      >
                        <Trash2 class="h-3.5 w-3.5" />
                      </button>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-3 border-t border-border/80 flex items-center justify-between bg-muted/20">
            <span class="text-[11px] text-muted-foreground">
              Total {{ runningYears.length }} running {{ runningYears.length === 1 ? 'year' : 'years' }} registered.
            </span>
            <button
              type="button"
              @click="showManageYearsModal = false"
              class="h-8 px-4 text-xs font-semibold bg-secondary text-secondary-foreground hover:bg-secondary/80 rounded-xl cursor-pointer transition-colors shadow-2xs"
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
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  ArrowLeft,
  FileText,
  ChevronDown,
  Plus,
  Printer,
  Scissors,
  Loader2,
  Calendar,
  Settings2,
  Edit2,
  Trash2,
  Check,
  X,
  AlertCircle,
  Sparkles
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
const runningYears = ref([]);

// Running Year Management state
const showManageYearsModal = ref(false);
const newYearLabel = ref('');
const addingYear = ref(false);
const yearError = ref('');
const editingYearId = ref(null);
const editingYearLabel = ref('');
const updatingYearId = ref(null);
const confirmDeleteYearId = ref(null);
const deletingYearId = ref(null);

// Computed suggested years (e.g. recent & upcoming years not yet created)
const suggestedYears = computed(() => {
  const currentYear = new Date().getFullYear();
  const list = [];
  for (let y = currentYear - 2; y <= currentYear + 3; y++) {
    const label = `${y}-${y + 1}`;
    const exists = runningYears.value.some(ry => ry.year_label === label);
    if (!exists) {
      list.push(label);
    }
  }
  return list;
});

// Ribon options (1 to 20)
const ribonOptions = Array.from({ length: 20 }, (_, i) => i + 1);

// Selected catalog item in dropdown
const selectedItemName = ref('Battle Uniform');
const selectedCatalogItem = ref(null);
const selectedQty = ref(1);

function onCatalogItemSelect(name, price, item) {
  selectedItemName.value = name;
  selectedCatalogItem.value = item || { name, price: 0 };
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
    price: 0,
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

// Main airman order form data
const formData = ref({
  customer_id: null,
  bd_number: '',
  customer_name: '',
  customer_phone: '',
  iv_number: '',
  trade: '',
  ribon: null,
  office: '',
  running_year: '2026-2027',
  order_date: new Date().toISOString().split('T')[0],
  delivery_date: '',
});

const isEditing = computed(() => {
  return Boolean(route.params.id && (route.name === 'EditAirmanOrder' || route.path.endsWith('/edit') || route.path.includes('/edit')));
});

function addSelectedItem() {
  const name = selectedItemName.value || selectedCatalogItem.value?.name;
  if (!name) return;
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
      price: 0,
    });
  }
  saveDraftToLocalStorage();
}

function removeItem(idx) {
  orderItems.value.splice(idx, 1);
  saveDraftToLocalStorage();
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
          if (lastOrder.trade && !formData.value.trade) formData.value.trade = lastOrder.trade;
          if (lastOrder.office && !formData.value.office) formData.value.office = lastOrder.office;
          if (lastOrder.ribon && !formData.value.ribon) formData.value.ribon = lastOrder.ribon;
          saveDraftToLocalStorage();
        }
      }
    } catch (e) {
      console.warn('Could not auto-load past measurements', e);
    }
  }
}

async function fetchCatalogAndYears() {
  try {
    const [catRes, custRes, yearsRes] = await Promise.all([
      api.get('/catalogs/airman'),
      api.get('/customers'),
      api.get('/running-years'),
    ]);

    catalogItems.value = catRes.data?.data || catRes.data || [];
    customers.value = custRes.data?.data || custRes.data || [];
    runningYears.value = yearsRes.data?.data || yearsRes.data || [];

    // Fallback catalog if empty
    if (catalogItems.value.length === 0) {
      catalogItems.value = [
        { name: 'Battle Uniform', price: 0 },
        { name: 'Half Sleeve', price: 0 },
        { name: 'Full Sleeve', price: 0 },
        { name: 'Summer Ceremonial', price: 0 },
        { name: 'Winter Ceremonial', price: 0 },
        { name: 'MODC Uniform', price: 0 },
      ];
    }

    // Default select Battle Uniform
    const battleUniform = catalogItems.value.find(i => i.name.toLowerCase() === 'battle uniform') || catalogItems.value[0];
    if (battleUniform && !selectedCatalogItem.value) {
      selectedCatalogItem.value = battleUniform;
      selectedItemName.value = battleUniform.name;
    }

    // Default select 2026-2027 if available (only when creating new order)
    if (!isEditing.value) {
      const y2026 = runningYears.value.find(y => y.year_label === '2026-2027');
      if (y2026) {
        formData.value.running_year = '2026-2027';
      } else if (runningYears.value.length > 0) {
        formData.value.running_year = runningYears.value[0].year_label;
      }
    }
  } catch (err) {
    console.error('Failed to load airman catalogs/customers/years', err);
  }
}

function openManageYearsModal() {
  newYearLabel.value = '';
  yearError.value = '';
  editingYearId.value = null;
  confirmDeleteYearId.value = null;
  showManageYearsModal.value = true;
}

function selectRunningYear(label) {
  formData.value.running_year = label;
  saveDraftToLocalStorage();
}

async function submitNewYear() {
  const label = newYearLabel.value.trim();
  yearError.value = '';
  if (!label) {
    yearError.value = 'Please enter a running year label (e.g., 2026-2027).';
    return;
  }
  if (runningYears.value.some(y => y.year_label.toLowerCase() === label.toLowerCase())) {
    yearError.value = `Year "${label}" already exists!`;
    return;
  }

  addingYear.value = true;
  try {
    const res = await api.post('/running-years', { year_label: label });
    const created = res.data?.data || res.data;
    runningYears.value.unshift(created);
    formData.value.running_year = created.year_label;
    newYearLabel.value = '';
    saveDraftToLocalStorage();
  } catch (err) {
    console.error('Failed to create running year', err);
    yearError.value = err.response?.data?.message || 'Failed to add running year.';
  } finally {
    addingYear.value = false;
  }
}

function startEditYear(yr) {
  editingYearId.value = yr.id;
  editingYearLabel.value = yr.year_label;
  confirmDeleteYearId.value = null;
  yearError.value = '';
}

function cancelEditYear() {
  editingYearId.value = null;
  editingYearLabel.value = '';
}

async function submitEditYear(yr) {
  const newLabel = editingYearLabel.value.trim();
  if (!newLabel) return;
  if (newLabel === yr.year_label) {
    editingYearId.value = null;
    return;
  }

  const duplicate = runningYears.value.find(y => y.id !== yr.id && y.year_label.toLowerCase() === newLabel.toLowerCase());
  if (duplicate) {
    alert(`Year "${newLabel}" already exists!`);
    return;
  }

  updatingYearId.value = yr.id;
  try {
    const res = await api.put(`/running-years/${yr.id}`, { year_label: newLabel });
    const updated = res.data?.data || res.data;
    const oldLabel = yr.year_label;

    yr.year_label = updated.year_label || newLabel;

    if (formData.value.running_year === oldLabel) {
      formData.value.running_year = yr.year_label;
    }
    editingYearId.value = null;
    saveDraftToLocalStorage();
  } catch (err) {
    console.error('Failed to update running year', err);
    alert('Failed to update year: ' + (err.response?.data?.message || err.message));
  } finally {
    updatingYearId.value = null;
  }
}

function startDeleteYear(yr) {
  confirmDeleteYearId.value = yr.id;
  editingYearId.value = null;
}

function cancelDeleteYear() {
  confirmDeleteYearId.value = null;
}

async function submitDeleteYear(yr) {
  deletingYearId.value = yr.id;
  try {
    await api.delete(`/running-years/${yr.id}`);
    const deletedLabel = yr.year_label;
    runningYears.value = runningYears.value.filter(y => y.id !== yr.id);

    if (formData.value.running_year === deletedLabel) {
      formData.value.running_year = runningYears.value[0]?.year_label || '';
    }
    confirmDeleteYearId.value = null;
    saveDraftToLocalStorage();
  } catch (err) {
    console.error('Failed to delete running year', err);
    alert('Failed to delete year: ' + (err.response?.data?.message || err.message));
  } finally {
    deletingYearId.value = null;
  }
}

async function loadExistingOrder(id) {
  try {
    const res = await api.get(`/orders/${id}`);
    const order = res.data?.data || res.data;
    if (!order) return;

    formData.value = {
      customer_id: order.customer_id,
      bd_number: order.customer?.bd_number || order.bd_number || '',
      customer_name: order.customer?.name || order.customer_name || '',
      customer_phone: order.customer?.phone || order.customer_phone || '',
      iv_number: order.iv_number || (order.items && order.items[0]?.iv_number) || '',
      trade: order.trade || '',
      ribon: order.ribon || null,
      office: order.office || '',
      running_year: order.running_year || '',
      order_date: order.order_date || new Date().toISOString().split('T')[0],
      delivery_date: order.delivery_date || '',
    };

    if (order.items && order.items.length > 0) {
      orderItems.value = order.items.map(it => ({
        item_name: it.item_name || it.item_type,
        item_type: it.item_type || 'shirt',
        quantity: Number(it.quantity || 1),
        price: 0,
      }));

      const firstItem = order.items[0];
      const match = catalogItems.value.find(c => c.name === (firstItem.item_name || firstItem.item_type));
      if (match) selectedCatalogItem.value = match;

      // Extract all measurements from all items
      for (const it of order.items) {
        if (it.measurements) {
          const m = typeof it.measurements === 'string' ? JSON.parse(it.measurements) : it.measurements;
          Object.keys(measurements.value).forEach(k => {
            if (m[k] && !measurements.value[k]) {
              measurements.value[k] = m[k];
            }
          });
        }
      }

      if (order.measurement_notes) {
        if (order.measurement_notes.includes(' | ')) {
          const parts = order.measurement_notes.split(' | ');
          shirtNotes.value = parts[0] || '';
          pantNotes.value = parts[1] || '';
        } else {
          shirtNotes.value = order.measurement_notes;
        }
      } else if (order.items && order.items[0]?.notes) {
        shirtNotes.value = order.items[0].notes;
      }

      if (order.extra_notes) {
        if (order.extra_notes.includes(' | ')) {
          const parts = order.extra_notes.split(' | ');
          shirtExtraNotes.value = parts[0] || '';
          pantExtraNotes.value = parts[1] || '';
        } else {
          shirtExtraNotes.value = order.extra_notes;
        }
      }
    }
  } catch (err) {
    console.error('Failed to load existing order for editing', err);
  }
}

const LOCAL_STORAGE_DRAFT_KEY = 'tailorpro_airman_order_draft';

function saveDraftToLocalStorage() {
  if (isEditing.value) return;
  const draft = {
    formData: formData.value,
    measurements: measurements.value,
    shirtNotes: shirtNotes.value,
    shirtExtraNotes: shirtExtraNotes.value,
    pantNotes: pantNotes.value,
    pantExtraNotes: pantExtraNotes.value,
    orderItems: orderItems.value,
  };
  try {
    localStorage.setItem(LOCAL_STORAGE_DRAFT_KEY, JSON.stringify(draft));
  } catch (e) {
    // Ignore quota errors
  }
}

function loadDraftFromLocalStorage() {
  if (isEditing.value) return;
  try {
    const raw = localStorage.getItem(LOCAL_STORAGE_DRAFT_KEY);
    if (!raw) return;
    const parsed = JSON.parse(raw);
    if (parsed.formData) formData.value = { ...formData.value, ...parsed.formData };
    if (parsed.measurements) measurements.value = { ...measurements.value, ...parsed.measurements };
    if (parsed.shirtNotes) shirtNotes.value = parsed.shirtNotes;
    if (parsed.shirtExtraNotes) shirtExtraNotes.value = parsed.shirtExtraNotes;
    if (parsed.pantNotes) pantNotes.value = parsed.pantNotes;
    if (parsed.pantExtraNotes) pantExtraNotes.value = parsed.pantExtraNotes;
    if (parsed.orderItems && parsed.orderItems.length > 0) orderItems.value = parsed.orderItems;
  } catch (e) {
    console.error('Failed to load draft from localStorage', e);
  }
}

function clearDraft() {
  if (isEditing.value) return;
  try {
    localStorage.removeItem(LOCAL_STORAGE_DRAFT_KEY);
  } catch (e) {}
}

async function saveOrder() {
  if (!formData.value.bd_number.trim()) {
    alert('Please enter BD-Number');
    return;
  }

  saving.value = true;

  try {
    const allItems = orderItems.value.length > 0 ? orderItems.value : [
      {
        item_name: selectedCatalogItem.value?.name || 'Battle Uniform',
        item_type: 'shirt',
        quantity: selectedQty.value || 1,
        price: 0,
      }
    ];

    const itemsPayload = allItems.map(it => ({
      item_name: it.item_name,
      item_type: it.item_type || 'shirt',
      quantity: Number(it.quantity || 1),
      price: 0,
      iv_number: formData.value.iv_number || null,
      measurements: {
        ...measurements.value,
      },
      notes: shirtNotes.value || pantNotes.value || null,
    }));

    const payload = {
      order_category: 'airman',
      customer_id: formData.value.customer_id,
      customer_name: formData.value.customer_name || ('Airman ' + formData.value.bd_number),
      customer_phone: formData.value.customer_phone || '-',
      bd_number: formData.value.bd_number,
      trade: formData.value.trade || null,
      ribon: formData.value.ribon ? Number(formData.value.ribon) : null,
      office: formData.value.office || null,
      running_year: formData.value.running_year || null,
      order_date: formData.value.order_date,
      delivery_date: formData.value.delivery_date || null,
      total_amount: 0,
      advance: 0,
      due: 0,
      measurement_notes: shirtNotes.value || pantNotes.value || null,
      extra_notes: shirtExtraNotes.value || pantExtraNotes.value || null,
      items: itemsPayload,
    };

    let response;
    if (isEditing.value) {
      response = await api.put(`/orders/${route.params.id}`, payload);
    } else {
      response = await api.post('/orders', payload);
    }

    clearDraft();
    const createdId = response.data?.id || response.data?.data?.id || (isEditing.value ? route.params.id : null);
    if (createdId) {
      router.push(`/orders/${createdId}`);
    } else {
      router.push('/airman');
    }
  } catch (err) {
    console.error('Failed to save airman order', err);
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
    trade: formData.value.trade,
    ribon: formData.value.ribon,
    office: formData.value.office,
    running_year: formData.value.running_year,
    iv_number: formData.value.iv_number,
    total_amount: 0,
    advance: 0,
    due: 0,
  };
  const customerPreview = {
    name: formData.value.customer_name || ('Airman ' + formData.value.bd_number),
    phone: formData.value.customer_phone || '-',
    bd_number: formData.value.bd_number,
  };
  const itemsWithMeasurements = orderItems.value.map(it => ({
    ...it,
    measurements: measurements.value,
    notes: shirtNotes.value || pantNotes.value,
  }));
  generateInvoicePDF(orderPreview, customerPreview, itemsWithMeasurements);
}

function printSlip() {
  const orderPreview = {
    order_number: isEditing.value ? route.params.id : 'NEW',
    order_date: formData.value.order_date,
    delivery_date: formData.value.delivery_date,
    trade: formData.value.trade,
    ribon: formData.value.ribon,
    office: formData.value.office,
    running_year: formData.value.running_year,
    iv_number: formData.value.iv_number,
  };
  const customerPreview = {
    name: formData.value.customer_name || ('Airman ' + formData.value.bd_number),
    phone: formData.value.customer_phone || '-',
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
    router.push('/airman');
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
  await fetchCatalogAndYears();
  if (isEditing.value) {
    await loadExistingOrder(route.params.id);
  } else {
    loadDraftFromLocalStorage();
  }
});
</script>
