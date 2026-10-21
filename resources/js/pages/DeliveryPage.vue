<template>
  <div class="space-y-4 max-w-5xl mx-auto pb-12">
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- VIEW 1: ORDERS LIST (WHEN ON /delivery)                            -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div v-if="viewMode === 'list'" class="space-y-4">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 class="text-2xl font-heading font-bold text-foreground">Delivery</h1>
          <p class="text-xs text-muted-foreground mt-0.5">
            Manage pending deliveries and complete handover
          </p>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full sm:w-80">
          <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <input
            v-model="search"
            type="text"
            placeholder="Search by name, phone, BD number, order #..."
            class="w-full h-10 pl-10 pr-4 text-xs rounded-xl border border-border/70 bg-card text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
          />
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
        <button
          v-for="tab in filterTabs"
          :key="tab.key"
          type="button"
          @click="activeTab = tab.key"
          :class="[
            'px-3.5 py-1 text-xs rounded-full font-medium transition-colors cursor-pointer whitespace-nowrap',
            activeTab === tab.key
              ? 'bg-primary text-primary-foreground font-semibold shadow-xs'
              : 'border border-border/70 bg-card text-muted-foreground hover:text-foreground hover:bg-muted/50'
          ]"
        >
          {{ tab.label }}
          <span class="ml-1 opacity-70">({{ tab.count }})</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-16">
        <Loader2 class="h-8 w-8 animate-spin text-primary" />
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredOrders.length === 0" class="text-center py-16 text-muted-foreground text-sm rounded-xl border border-dashed border-border bg-card/50">
        {{ orders.length === 0 ? 'No orders waiting for delivery' : 'No orders matching your search' }}
      </div>

      <!-- Single-Column Orders List Matching Mockup -->
      <div v-else class="space-y-2.5">
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          @click="openOrderDetails(order)"
          class="rounded-xl border border-border/70 bg-card p-4 shadow-2xs hover:border-primary/50 hover:shadow-xs transition-all cursor-pointer flex flex-col md:flex-row md:items-center justify-between gap-3 group"
        >
          <!-- Left Info: Stacked Lines -->
          <div class="space-y-1.5 flex-1 min-w-0">
            <!-- Line 1: Order #, Customer Name, BD, Badges -->
            <div class="flex flex-wrap items-center gap-2">
              <span class="font-mono font-bold text-sm text-primary group-hover:underline">
                #{{ order.order_number }}
              </span>

              <span class="font-bold text-sm text-foreground uppercase truncate max-w-[170px] sm:max-w-[280px]">
                {{ order.customer?.name || order.customer_name || 'UNKNOWN' }}
              </span>

              <span class="text-xs font-mono text-muted-foreground">
                BD: {{ (order.customer?.bd_number || order.bd_number || '-').trim() }}
              </span>

              <!-- Status Badge -->
              <span
                :class="[
                  'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border',
                  order.status === 'delivered'
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                    : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800'
                ]"
              >
                {{ order.status === 'delivered' ? 'Delivered' : 'Pending' }}
              </span>

              <!-- Category Badge -->
              <span
                :class="[
                  'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border',
                  (order.order_category || 'officer') === 'airman'
                    ? 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800'
                    : 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                ]"
              >
                {{ (order.order_category || 'officer') === 'airman' ? 'Airman' : 'Officer' }}
              </span>
            </div>

            <!-- Line 2: Order Date & Delivery Date -->
            <div class="text-xs text-muted-foreground flex flex-wrap items-center gap-3 font-mono">
              <span>📅 Order: {{ order.order_date || '-' }}</span>
              <span>•</span>
              <span>📅 Delivery: {{ order.delivery_date || '-' }}</span>
              <template v-if="order.customer?.phone">
                <span>•</span>
                <span>📞 {{ order.customer.phone }}</span>
              </template>
            </div>

            <!-- Line 3: Financials -->
            <div class="text-xs text-muted-foreground flex flex-wrap items-center gap-2">
              <span>Total: <strong class="text-foreground font-mono">৳{{ formatMoney(order.total_amount) }}</strong></span>
              <span>•</span>
              <span>Advance: <strong class="text-emerald-600 font-mono">৳{{ formatMoney(order.advance) }}</strong></span>
              <span>•</span>
              <span>
                Due:
                <strong
                  :class="[
                    'font-mono font-bold',
                    Number(order.due || 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600'
                  ]"
                >
                  ৳{{ formatMoney(order.due) }}
                </strong>
              </span>
            </div>
          </div>

          <!-- Right Action: Arrow / Button -->
          <div class="flex items-center gap-2 shrink-0 self-end md:self-center">
            <button
              type="button"
              @click.stop="openOrderDetails(order)"
              class="h-8 px-3.5 inline-flex items-center gap-1.5 rounded-xl bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 transition-colors shadow-2xs cursor-pointer"
            >
              <Package class="h-3.5 w-3.5" />
              {{ order.status === 'delivered' ? 'View Details' : 'Deliver' }}
            </button>
            <ChevronRight class="h-4 w-4 text-muted-foreground/60 group-hover:text-primary transition-colors hidden sm:block" />
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- VIEW 2: ORDER DETAILS (MATCHING ATTACHED SCREENSHOT EXACTLY)       -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div v-else-if="viewMode === 'details'" class="space-y-4">
      <!-- Loading for single order -->
      <div v-if="orderLoading" class="flex justify-center py-16">
        <Loader2 class="h-8 w-8 animate-spin text-primary" />
      </div>

      <template v-else-if="activeOrder">
        <!-- Top Navigation Header -->
        <div class="flex items-center gap-3">
          <button
            type="button"
            @click="goBackToList"
            class="h-9 w-9 inline-flex items-center justify-center rounded-xl border border-input bg-card hover:bg-accent text-foreground transition-colors shadow-2xs cursor-pointer shrink-0"
            title="Back"
          >
            <ArrowLeft class="h-4 w-4" />
          </button>
          <h1 class="text-xl sm:text-2xl font-heading font-bold text-foreground">
            Order Details
          </h1>
        </div>

        <!-- Main Card Matching Mockup -->
        <div class="rounded-2xl border border-border/70 bg-card p-5 sm:p-6 shadow-2xs space-y-4">
          <!-- Top Row: # #577 and Badges -->
          <div class="flex items-center justify-between pb-1">
            <div class="flex items-center gap-1.5">
              <span class="text-muted-foreground font-bold text-lg font-mono">#</span>
              <span class="text-primary font-mono text-xl sm:text-2xl font-bold">
                #{{ activeOrder.order_number }}
              </span>
            </div>

            <div class="flex items-center gap-2">
              <!-- Status Badge Dropdown -->
              <div class="relative">
                <button
                  type="button"
                  @click="statusDropdownOpen = !statusDropdownOpen"
                  :class="[
                    'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border shadow-2xs cursor-pointer transition-colors',
                    activeOrder.status === 'delivered'
                      ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                      : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800'
                  ]"
                >
                  {{ activeOrder.status === 'delivered' ? 'Delivered' : 'Pending' }}
                  <ChevronDown class="h-3 w-3 opacity-60 ml-0.5" />
                </button>

                <!-- Status Menu -->
                <div
                  v-if="statusDropdownOpen"
                  class="absolute right-0 mt-1.5 w-44 rounded-xl border border-border bg-popover text-popover-foreground shadow-lg py-1 z-30 animate-in fade-in zoom-in-95"
                >
                  <button
                    type="button"
                    @click="updateStatus('pending')"
                    class="w-full text-left px-3 py-1.5 text-xs font-medium hover:bg-muted cursor-pointer flex items-center justify-between"
                  >
                    <span>Pending</span>
                    <Check v-if="activeOrder.status === 'pending'" class="h-3.5 w-3.5 text-primary" />
                  </button>
                  <button
                    type="button"
                    @click="updateStatus('delivered')"
                    class="w-full text-left px-3 py-1.5 text-xs font-medium hover:bg-muted cursor-pointer flex items-center justify-between"
                  >
                    <span>Delivered</span>
                    <Check v-if="activeOrder.status === 'delivered'" class="h-3.5 w-3.5 text-primary" />
                  </button>
                </div>
              </div>

              <!-- Category Badge -->
              <span
                :class="[
                  'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border',
                  (activeOrder.order_category || 'officer') === 'airman'
                    ? 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800'
                    : 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                ]"
              >
                {{ (activeOrder.order_category || 'officer') === 'airman' ? 'Airman' : 'Officer' }}
              </span>
            </div>
          </div>

          <!-- Subcard 1: CUSTOMER DETAILS -->
          <div class="rounded-xl border border-border/70 bg-muted/10 p-4 space-y-1.5">
            <p class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
              CUSTOMER DETAILS
            </p>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1 text-xs">
              <div class="space-y-1">
                <p class="font-bold text-sm text-foreground uppercase flex items-center gap-1.5">
                  <User class="h-3.5 w-3.5 text-muted-foreground" />
                  {{ activeOrder.customer?.name || activeOrder.customer_name || 'UNKNOWN' }}
                </p>
                <p class="font-mono text-muted-foreground">
                  BD: {{ (activeOrder.customer?.bd_number || activeOrder.bd_number || '-').trim() }}
                </p>
              </div>

              <div class="sm:text-right font-mono text-foreground font-semibold flex items-center sm:justify-end gap-1.5">
                <Phone class="h-3.5 w-3.5 text-muted-foreground" />
                {{ activeOrder.customer?.phone || '-' }}
              </div>
            </div>
          </div>

          <!-- Subcard 2: ORDER INFO -->
          <div class="rounded-xl border border-border/70 bg-muted/10 p-4 space-y-1.5">
            <p class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
              ORDER INFO
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 text-xs">
              <div class="flex items-center gap-2">
                <Calendar class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-muted-foreground">Order:</span>
                <span class="font-semibold font-mono text-foreground">{{ activeOrder.order_date || '-' }}</span>
              </div>
              <div class="flex items-center gap-2">
                <Calendar class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-muted-foreground">Delivery:</span>
                <span class="font-semibold font-mono text-foreground">{{ activeOrder.delivery_date || '-' }}</span>
              </div>
            </div>
          </div>

          <!-- Subcard 3: PAYMENT -->
          <div class="rounded-xl border border-border/70 bg-muted/10 p-4 space-y-2">
            <p class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
              PAYMENT
            </p>
            <!-- 3 Columns with Clean Border / Divider matching Mockup -->
            <div class="grid grid-cols-3 rounded-lg border border-border/70 bg-card divide-x divide-border/70 text-center p-2.5 sm:p-3">
              <div class="min-w-0 px-1">
                <p class="text-xs text-muted-foreground truncate">Total</p>
                <p class="text-sm sm:text-lg font-bold text-foreground mt-0.5 font-mono truncate" :title="'৳' + formatMoney(activeOrder.total_amount)">
                  ৳{{ formatMoney(activeOrder.total_amount) }}
                </p>
              </div>
              <div class="min-w-0 px-1">
                <p class="text-xs text-muted-foreground truncate">Advance</p>
                <p class="text-sm sm:text-lg font-bold text-emerald-600 mt-0.5 font-mono truncate" :title="'৳' + formatMoney(activeOrder.advance)">
                  ৳{{ formatMoney(activeOrder.advance) }}
                </p>
              </div>
              <div class="min-w-0 px-1">
                <p class="text-xs text-muted-foreground truncate">Due</p>
                <p
                  :class="[
                    'text-sm sm:text-lg font-bold mt-0.5 font-mono truncate',
                    Number(activeOrder.due || 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600'
                  ]"
                  :title="'৳' + formatMoney(activeOrder.due)"
                >
                  ৳{{ formatMoney(activeOrder.due) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Big Blue Button: Deliver This Order -->
          <div class="pt-2">
            <button
              type="button"
              @click="goToConfirmDelivery"
              class="w-full h-11 rounded-xl bg-primary text-primary-foreground font-semibold text-sm hover:bg-primary/90 flex items-center justify-center gap-2 shadow-xs transition-colors cursor-pointer"
            >
              <Package class="h-4 w-4" />
              Deliver This Order
            </button>
          </div>
        </div>
      </template>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- VIEW 3: CONFIRM DELIVERY (MATCHING media_1792132533932.jpg)        -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div v-else-if="viewMode === 'confirm' && activeOrder" class="space-y-4">
      <!-- Top Navigation Header -->
      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="goToDetailsView"
          class="h-9 w-9 inline-flex items-center justify-center rounded-xl border border-input bg-card hover:bg-accent text-foreground transition-colors shadow-2xs cursor-pointer shrink-0"
          title="Back"
        >
          <ArrowLeft class="h-4 w-4" />
        </button>

        <div>
          <h1 class="text-xl sm:text-2xl font-heading font-bold text-foreground">
            Confirm Delivery
          </h1>
          <p class="text-xs text-muted-foreground mt-0.5 font-mono">
            Order #{{ activeOrder.order_number }}
          </p>
        </div>
      </div>

      <!-- Main Form Card -->
      <div class="rounded-2xl border border-border/70 bg-card p-5 sm:p-7 shadow-2xs space-y-4">
        <!-- Green Banner: Marking as Delivered -->
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 dark:bg-emerald-950/30 dark:border-emerald-800 p-3.5 flex items-center gap-2.5 text-emerald-800 dark:text-emerald-200 text-xs sm:text-sm font-semibold">
          <CheckCircle2 class="h-4 w-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
          <span>Marking as Delivered</span>
        </div>

        <!-- Form Fields -->
        <form @submit.prevent="executeDelivery" class="space-y-4 pt-1">
          <!-- 1. Delivered Date -->
          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Delivered Date</label>
            <input
              v-model="deliveryForm.delivered_date"
              type="date"
              required
              class="w-full h-10 px-3 text-xs rounded-xl border border-border/70 bg-card text-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs font-mono"
            />
          </div>

          <!-- 2. Total Amount (৳) -->
          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Total Amount (৳)</label>
            <input
              :value="cleanNumber(activeOrder.total_amount)"
              type="text"
              readonly
              class="w-full h-10 px-3 text-xs rounded-xl border border-slate-200 bg-slate-50/80 text-foreground font-mono font-medium cursor-not-allowed shadow-2xs"
            />
          </div>

          <!-- 3. Discount (৳) -->
          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Discount (৳)</label>
            <input
              v-model.number="deliveryForm.discount"
              type="number"
              min="0"
              step="any"
              @input="recalculateDue"
              placeholder="0"
              class="w-full h-10 px-3 text-xs rounded-xl border border-slate-200 bg-white text-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs font-mono"
            />
          </div>

          <!-- 4. Advance (৳) -->
          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Advance (৳)</label>
            <input
              :value="cleanNumber(activeOrder.advance)"
              type="text"
              readonly
              class="w-full h-10 px-3 text-xs rounded-xl border border-slate-200 bg-slate-50/80 text-foreground font-mono font-medium cursor-not-allowed shadow-2xs"
            />
          </div>

          <!-- 5. Cash Receive (৳) -->
          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Cash Receive (৳)</label>
            <input
              v-model.number="deliveryForm.cash_receive"
              type="number"
              min="0"
              step="any"
              @input="recalculateDue"
              placeholder="0"
              class="w-full h-10 px-3 text-xs rounded-xl border border-slate-200 bg-white text-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs font-mono font-semibold"
            />
          </div>

          <!-- 6. Due (৳) -->
          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Due (৳)</label>
            <input
              :value="cleanNumber(deliveryForm.due)"
              type="text"
              readonly
              :class="[
                'w-full h-10 px-3 text-xs rounded-xl border border-slate-200 bg-slate-50/80 font-mono font-medium cursor-not-allowed shadow-2xs',
                deliveryForm.due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-foreground'
              ]"
            />
          </div>

          <!-- 7. Receiver Name -->
          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Receiver Name</label>
            <input
              v-model="deliveryForm.receiver_name"
              type="text"
              placeholder="Who received the order?"
              class="w-full h-10 px-3 text-xs rounded-xl border border-border/70 bg-card text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
            />
          </div>

          <!-- 8. Receiver Phone Number -->
          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Receiver Phone Number</label>
            <input
              v-model="deliveryForm.receiver_phone"
              type="text"
              placeholder="Phone number of receiver"
              class="w-full h-10 px-3 text-xs rounded-xl border border-border/70 bg-card text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary shadow-2xs"
            />
          </div>

          <!-- Status & Due Summary Box (Right above buttons) -->
          <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 space-y-1">
            <p class="text-xs font-semibold text-foreground flex items-center gap-1.5">
              <span>Status:</span>
              <span class="text-foreground flex items-center gap-1">✓ Delivered</span>
            </p>
            <p class="text-xs font-semibold text-foreground">
              Due: <span class="font-mono">৳{{ cleanNumber(deliveryForm.due) }}</span>
            </p>
          </div>

          <!-- Action Buttons: Cancel and Confirm Delivery -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            <!-- Cancel Button -->
            <button
              type="button"
              @click="goToDetailsView"
              class="w-full h-11 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-foreground text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
            >
              Cancel
            </button>

            <!-- Confirm Delivery Button -->
            <button
              type="submit"
              :disabled="saving"
              class="w-full h-11 rounded-xl bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 flex items-center justify-center gap-2 shadow-xs transition-colors cursor-pointer disabled:opacity-50"
            >
              <Loader2 v-if="saving" class="h-4 w-4 animate-spin" />
              <CheckCircle2 v-else class="h-4 w-4" />
              Confirm Delivery
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- MODAL: DOWNLOAD UPDATED INVOICE AFTER DELIVERY                    -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div
        v-if="showDownloadInvoiceModal"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 animate-in fade-in duration-150"
      >
        <div
          class="bg-card text-card-foreground border border-border/80 rounded-2xl shadow-2xl max-w-lg w-full p-5 sm:p-7 space-y-4 animate-in zoom-in-95 duration-150 relative max-h-[90dvh] overflow-y-auto"
        >
          <!-- Top Row: Title and Close Button -->
          <div class="flex items-start justify-between gap-4">
            <h3 class="text-lg sm:text-xl font-heading font-bold text-foreground tracking-tight">
              {{ t('downloadUpdatedInvoice') || 'Download Updated Invoice?' }}
            </h3>
            <button
              type="button"
              @click="skipInvoiceDownload"
              class="h-7 w-7 inline-flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted/80 transition-colors cursor-pointer shrink-0 -mr-1 -mt-1"
              title="Close"
            >
              <X class="h-4 w-4" />
            </button>
          </div>

          <!-- Description Text -->
          <p class="text-xs sm:text-sm text-muted-foreground leading-relaxed">
            {{ t('orderMarkedDeliveredDesc') || 'The order has been marked as delivered. Would you like to download the updated invoice with final payment details?' }}
          </p>

          <!-- Action Buttons -->
          <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2.5 sm:gap-3 pt-2">
            <!-- No, Skip Button -->
            <button
              type="button"
              @click="skipInvoiceDownload"
              class="w-full sm:w-auto h-10 px-5 rounded-xl border border-input bg-card text-foreground text-xs sm:text-sm font-semibold hover:bg-accent transition-colors shadow-2xs cursor-pointer inline-flex items-center justify-center"
            >
              {{ t('noSkip') || 'No, Skip' }}
            </button>

            <!-- Download Invoice Button -->
            <button
              type="button"
              @click="downloadDeliveredInvoice"
              class="w-full sm:w-auto h-10 px-5 rounded-xl bg-primary text-primary-foreground text-xs sm:text-sm font-semibold hover:bg-primary/90 flex items-center justify-center gap-2 shadow-xs transition-colors cursor-pointer"
            >
              <Download class="h-4 w-4" />
              <span>{{ t('downloadInvoice') || 'Download Invoice' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  Search, Loader2, CheckCircle2, Package, ArrowLeft, ChevronDown,
  ChevronRight, User, Phone, Calendar, Check, X, FileDown
} from 'lucide-vue-next';
import api from '@/lib/api';
import { generateInvoicePDF } from '@/lib/pdf';
import { t } from '@/lib/language';

const route = useRoute();
const router = useRouter();

const orders = ref([]);
const loading = ref(true);
const orderLoading = ref(false);
const saving = ref(false);
const search = ref('');
const activeTab = ref('pending'); // 'pending' | 'delivered' | 'all'
const statusDropdownOpen = ref(false);

const activeOrder = ref(null);
const showDownloadInvoiceModal = ref(false);
const deliveredOrderForModal = ref(null);

const deliveryForm = ref({
  delivered_date: new Date().toISOString().split('T')[0],
  discount: 0,
  cash_receive: 0,
  due: 0,
  receiver_name: '',
  receiver_phone: '',
});

// View mode derived from route
const viewMode = computed(() => {
  if (route.name === 'DeliveryConfirm' || route.path.endsWith('/confirm')) {
    return 'confirm';
  }
  if (route.params.id) {
    return 'details';
  }
  return 'list';
});

const filterTabs = computed(() => {
  const pendingCount = orders.value.filter(o => o.status !== 'delivered').length;
  const deliveredCount = orders.value.filter(o => o.status === 'delivered').length;
  return [
    { key: 'pending', label: 'Pending Delivery', count: pendingCount },
    { key: 'delivered', label: 'Delivered', count: deliveredCount },
    { key: 'all', label: 'All Orders', count: orders.value.length },
  ];
});

const filteredOrders = computed(() => {
  let list = orders.value;

  if (activeTab.value === 'pending') {
    list = list.filter(o => o.status !== 'delivered');
  } else if (activeTab.value === 'delivered') {
    list = list.filter(o => o.status === 'delivered');
  }

  const q = search.value.trim().toLowerCase();
  if (!q) return list;

  return list.filter((o) => {
    return (
      String(o.order_number || '').includes(q) ||
      (o.customer?.name || o.customer_name || '').toLowerCase().includes(q) ||
      (o.customer?.phone || '').includes(q) ||
      (o.customer?.bd_number || o.bd_number || '').toLowerCase().includes(q)
    );
  });
});

function formatMoney(amount) {
  const num = Number(amount || 0);
  return num.toLocaleString();
}

function cleanNumber(val) {
  if (val == null || val === '') return '0';
  const num = Number(val);
  return isNaN(num) ? '0' : String(num);
}

function openOrderDetails(order) {
  activeOrder.value = order;
  initDeliveryForm(order);
  router.push(`/delivery/${order.id}`);
}

function goToConfirmDelivery() {
  if (!activeOrder.value) return;
  router.push(`/delivery/${activeOrder.value.id}/confirm`);
}

function goToDetailsView() {
  if (activeOrder.value) {
    router.push(`/delivery/${activeOrder.value.id}`);
  } else {
    router.push('/delivery');
  }
}

function goBackToList() {
  activeOrder.value = null;
  router.push('/delivery');
}

function initDeliveryForm(order) {
  deliveryForm.value = {
    delivered_date: new Date().toISOString().split('T')[0],
    discount: 0,
    cash_receive: 0,
    due: Number(order.due || 0),
    receiver_name: order.receiver_name || order.customer?.name || '',
    receiver_phone: order.receiver_phone || order.customer?.phone || '',
  };
}

function recalculateDue() {
  if (!activeOrder.value) return;
  const total = Number(activeOrder.value.total_amount || 0);
  const adv = Number(activeOrder.value.advance || 0);
  const discount = Number(deliveryForm.value.discount || 0);
  const cash = Number(deliveryForm.value.cash_receive || 0);
  deliveryForm.value.due = Math.max(0, total - discount - adv - cash);
}

async function updateStatus(newStatus) {
  statusDropdownOpen.value = false;
  if (!activeOrder.value || activeOrder.value.status === newStatus) return;

  try {
    const payload = { status: newStatus };
    if (newStatus === 'delivered' && !activeOrder.value.delivered_date) {
      payload.delivered_date = new Date().toISOString().split('T')[0];
    }
    const res = await api.put(`/orders/${activeOrder.value.id}`, payload);
    const updated = res.data?.data || res.data;
    if (updated) {
      activeOrder.value = updated;
    }
    await fetchOrders();

    if (newStatus === 'delivered') {
      deliveredOrderForModal.value = updated || activeOrder.value;
      showDownloadInvoiceModal.value = true;
    }
  } catch (err) {
    console.error('Failed to change status', err);
  }
}

async function fetchOrders() {
  loading.value = true;
  try {
    const res = await api.get('/orders');
    orders.value = res.data?.data || [];
  } catch (err) {
    console.error('Failed to load delivery orders', err);
  } finally {
    loading.value = false;
  }
}

async function fetchSingleOrder(id) {
  orderLoading.value = true;
  try {
    const res = await api.get(`/orders/${id}`);
    const ord = res.data?.data || res.data;
    activeOrder.value = ord;
    initDeliveryForm(ord);
  } catch (err) {
    console.error('Failed to load order for delivery', err);
    activeOrder.value = null;
  } finally {
    orderLoading.value = false;
  }
}

async function executeDelivery() {
  if (!activeOrder.value) return;
  saving.value = true;
  try {
    const res = await api.post(`/orders/${activeOrder.value.id}/deliver`, {
      delivered_date: deliveryForm.value.delivered_date,
      discount: Number(deliveryForm.value.discount || 0),
      cash_receive: Number(deliveryForm.value.cash_receive || 0),
      due: Number(deliveryForm.value.due || 0),
      receiver_name: deliveryForm.value.receiver_name,
      receiver_phone: deliveryForm.value.receiver_phone,
    });

    const updated = res.data?.data;
    const deliveredOrder = updated || activeOrder.value;
    if (updated) {
      activeOrder.value = updated;
    }

    await fetchOrders();
    deliveredOrderForModal.value = deliveredOrder;
    showDownloadInvoiceModal.value = true;
  } catch (err) {
    console.error('Failed to confirm delivery', err);
  } finally {
    saving.value = false;
  }
}

async function downloadDeliveredInvoice() {
  if (!deliveredOrderForModal.value) return;
  const ord = deliveredOrderForModal.value;

  let targetOrder = ord;
  if (!targetOrder.items || targetOrder.items.length === 0) {
    try {
      const res = await api.get(`/orders/${ord.id}`);
      if (res.data?.data) {
        targetOrder = res.data.data;
      }
    } catch (e) {
      console.warn('Could not fetch full order for delivery invoice', e);
    }
  }

  generateInvoicePDF(
    targetOrder,
    targetOrder.customer || {},
    targetOrder.items || [],
    true // isDeliveryInvoice = true
  );

  showDownloadInvoiceModal.value = false;
  deliveredOrderForModal.value = null;
  router.push('/delivery');
}

function skipInvoiceDownload() {
  showDownloadInvoiceModal.value = false;
  deliveredOrderForModal.value = null;
  router.push('/delivery');
}

watch(
  () => route.params.id,
  (newId) => {
    if (newId) {
      if (!activeOrder.value || (activeOrder.value.id !== newId && String(activeOrder.value.order_number) !== newId)) {
        fetchSingleOrder(newId);
      }
    } else {
      activeOrder.value = null;
    }
  },
  { immediate: true }
);

onMounted(() => {
  fetchOrders();
});
</script>
