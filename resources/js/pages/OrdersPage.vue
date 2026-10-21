<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-heading font-bold text-foreground">
          {{ airmanMode ? 'Airman Orders' : 'Orders' }}
        </h1>
        <p class="text-xs text-muted-foreground mt-0.5">
          {{ airmanMode ? 'Manage all tailoring orders' : 'Manage all tailoring orders' }}
        </p>
      </div>

      <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
        <!-- New Order Button (Solid Blue) -->
        <button
          type="button"
          @click="openCreateOrder"
          class="inline-flex items-center gap-1.5 h-9 px-3.5 sm:px-4 rounded-xl bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 transition-colors shadow-xs cursor-pointer"
        >
          <Plus class="h-4 w-4" /> {{ airmanMode ? 'New Airman Order' : 'New Order' }}
        </button>

        <!-- New Airman Button (Only on Officer page) -->
        <button
          v-if="!airmanMode"
          type="button"
          @click="navigateTo('/airman')"
          class="inline-flex items-center gap-1.5 h-9 px-3 sm:px-3.5 rounded-xl border border-input bg-card text-foreground text-xs font-medium hover:bg-accent transition-colors shadow-xs cursor-pointer"
        >
          <Plus class="h-4 w-4" /> New Airman
        </button>

        <!-- Delivery Button (Outline with Package Icon) -->
        <button
          type="button"
          @click="navigateTo('/delivery')"
          class="inline-flex items-center gap-1.5 h-9 px-3 sm:px-3.5 rounded-xl border border-input bg-card text-foreground text-xs font-medium hover:bg-accent transition-colors shadow-xs cursor-pointer"
        >
          <Package class="h-4 w-4" /> Delivery
        </button>
      </div>
    </div>

    <!-- Full Width Search -->
    <div class="relative w-full">
      <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
      <input
        v-model="search"
        type="text"
        placeholder="Search by name, phone, BD number, order #..."
        class="w-full h-10 pl-10 pr-4 text-xs rounded-xl border border-border/70 bg-card text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition-all shadow-2xs"
      />
    </div>

    <!-- Airman Mode: Clothing Category Cards (6 Cards in 3x2 Grid) -->
    <div v-if="airmanMode" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 sm:gap-3">
      <div
        v-for="cat in airmanCategoryCards"
        :key="cat.key"
        @click="toggleClothingFilter(cat.key)"
        :class="[
          'rounded-xl border border-border/70 bg-card p-3 sm:p-4 shadow-2xs hover:shadow-xs transition-all cursor-pointer flex flex-col items-center justify-center text-center border-l-4 gap-1',
          cat.color,
          clothingFilter === cat.key ? `ring-2 ${cat.ringColor} ${cat.bgColor} scale-[1.02] shadow-sm` : ''
        ]"
      >
        <div :class="['p-2 rounded-xl flex items-center justify-center shrink-0 mb-0.5', cat.bgColor, cat.textColor]">
          <Shirt class="h-4 w-4 sm:h-5 sm:w-5" />
        </div>
        <p :class="['text-[11px] sm:text-xs font-semibold leading-tight text-foreground truncate max-w-full', clothingFilter === cat.key ? cat.textColor : '']">
          {{ cat.label }}
        </p>
        <p :class="['text-base sm:text-lg font-bold text-foreground', clothingFilter === cat.key ? cat.textColor : '']">
          {{ cat.count }}
        </p>
      </div>
    </div>

    <!-- Officer Mode: KPI Summary Cards (Upcoming & Due After Delivery) -->
    <div v-if="!airmanMode" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <!-- Upcoming to Deliver -->
      <div
        @click="toggleSpecialFilter('upcoming')"
        :class="[
          'rounded-2xl border border-border/70 bg-card p-4 shadow-2xs hover:shadow-xs transition-all cursor-pointer flex items-center gap-3.5 border-l-4 border-l-amber-500',
          specialFilter === 'upcoming' ? 'ring-2 ring-amber-500 bg-amber-500/5' : ''
        ]"
      >
        <div class="h-10 w-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
          <Truck class="h-5 w-5" />
        </div>
        <div>
          <p class="text-xs text-muted-foreground font-medium">Upcoming to Deliver</p>
          <p class="text-xl font-bold text-foreground mt-0.5">
            {{ upcomingToDeliver.length }}
            <span class="text-xs font-normal text-muted-foreground ml-1">orders</span>
          </p>
        </div>
      </div>

      <!-- Due After Delivery -->
      <div
        @click="toggleSpecialFilter('due_after_delivery')"
        :class="[
          'rounded-2xl border border-border/70 bg-card p-4 shadow-2xs hover:shadow-xs transition-all cursor-pointer flex items-center gap-3.5 border-l-4 border-l-rose-500',
          specialFilter === 'due_after_delivery' ? 'ring-2 ring-rose-500 bg-rose-500/5' : ''
        ]"
      >
        <div class="h-10 w-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
          <CircleDollarSign class="h-5 w-5" />
        </div>
        <div>
          <p class="text-xs text-muted-foreground font-medium">Due After Delivery</p>
          <p class="text-xl font-bold text-foreground mt-0.5">
            ৳{{ dueAfterDeliveryTotal.toLocaleString() }}
            <span class="text-xs font-normal text-muted-foreground ml-1">({{ dueAfterDelivery.length }} orders)</span>
          </p>
        </div>
      </div>
    </div>

    <!-- Tabs and Sort Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Status Tabs Pills -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
        <button
          v-for="tab in currentTabs"
          :key="tab.key"
          type="button"
          @click="selectTab(tab.key)"
          :class="[
            'px-3.5 py-1 text-xs rounded-full font-medium transition-colors whitespace-nowrap cursor-pointer',
            activeStatus === tab.key && specialFilter === 'all'
              ? 'bg-primary text-primary-foreground font-semibold shadow-xs'
              : 'border border-border/70 bg-card text-muted-foreground hover:text-foreground hover:bg-muted/50'
          ]"
        >
          {{ tab.label }}
        </button>

        <!-- Airman Category Reset Button -->
        <button
          v-if="airmanMode && clothingFilter !== 'all' && hasDownloadedInCategory"
          type="button"
          @click="showResetConfirm = true"
          class="inline-flex items-center gap-1 px-3 py-1 text-xs rounded-full border border-amber-300 text-amber-600 bg-amber-50 hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-400 transition-colors cursor-pointer"
        >
          <RotateCcw class="h-3 w-3" /> Reset
        </button>
      </div>

      <!-- Controls Right: Running Year & Sort Selection -->
      <div class="flex items-center gap-2 self-end sm:self-auto shrink-0 flex-wrap">
        <!-- Running Year Select (in Airman Mode) -->
        <select
          v-if="airmanMode"
          v-model="runningYearFilter"
          class="h-8 px-2.5 text-xs rounded-lg border border-border/70 bg-card text-foreground focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer shadow-2xs"
        >
          <option value="all">All Years</option>
          <option v-for="ry in runningYears" :key="ry.id" :value="ry.year_label">
            {{ ry.year_label }}
          </option>
        </select>

        <!-- Sort Select -->
        <div class="flex items-center gap-1.5">
          <ArrowUpDown class="h-3.5 w-3.5 text-muted-foreground" />
          <select
            v-model="sortBy"
            class="h-8 px-2.5 text-xs rounded-lg border border-border/70 bg-card text-foreground focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer shadow-2xs"
          >
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="delivery_asc">Delivery Date</option>
            <option value="due_desc">Due Amount</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Bulk Download Actions (Airman Mode: For selected category or all visible orders) -->
    <div
      v-if="airmanMode && filteredOrders.length > 0"
      class="flex flex-wrap items-center justify-between gap-2 p-2.5 rounded-xl border border-border/60 bg-muted/20"
    >
      <div class="flex flex-wrap items-center gap-2">
        <button
          type="button"
          @click="downloadCategorySlips(false)"
          class="inline-flex items-center gap-1.5 h-7 px-3 text-xs rounded-lg border border-border bg-card hover:bg-accent text-foreground font-medium shadow-2xs cursor-pointer"
        >
          <Download class="h-3.5 w-3.5 text-primary" />
          <span>{{ clothingFilter !== 'all' ? `Download All ${currentClothingLabel} Slips` : 'Download All Cutting Slips' }}</span>
        </button>

        <button
          type="button"
          @click="downloadCategorySlips(true)"
          class="inline-flex items-center gap-1.5 h-7 px-3 text-xs rounded-lg border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300 font-medium shadow-2xs cursor-pointer"
        >
          <Download class="h-3.5 w-3.5 text-amber-600" />
          <span>Download Not Downloaded</span>
        </button>
      </div>
    </div>

    <!-- Order Count Summary -->
    <div class="text-xs text-muted-foreground font-medium">
      {{ filteredOrders.length }} orders
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-16">
      <Loader2 class="h-8 w-8 animate-spin text-primary" />
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredOrders.length === 0" class="text-center py-16 text-muted-foreground text-sm rounded-xl border border-dashed border-border bg-card/50">
      {{ t('noOrdersMatchingSearch') }}
    </div>

    <!-- Slim Order Rows List (Matching Attached Mockup) -->
    <div v-else class="space-y-2.5">
      <div
        v-for="order in visibleOrders"
        :key="order.id"
        @click="goToOrderDetails(order)"
        class="rounded-xl border border-border/70 bg-card p-3.5 px-4 shadow-2xs hover:border-primary/50 hover:shadow-xs transition-all cursor-pointer flex flex-col md:flex-row md:items-center justify-between gap-3"
      >
        <!-- Left Side: Order Details (Stacked Lines) -->
        <div class="space-y-1.5 flex-1 min-w-0">
          <!-- Line 1: Order #, Category Badge, Customer Name, Status Badge Dropdown -->
          <div class="flex flex-wrap items-center gap-2">
            <span class="font-mono font-bold text-sm text-foreground">#{{ order.order_number }}</span>

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

            <span class="font-bold text-sm text-foreground truncate max-w-[160px] sm:max-w-[280px]">
              {{ (order.customer?.name || 'UNKNOWN').toUpperCase() }}
            </span>

            <OrderStatusBadge
              :order-id="String(order.id)"
              :status="order.status"
              @click.stop
              @status-change="fetchOrders"
            />
          </div>

          <!-- Line 2: BD & Order Date -> Delivery Date -->
          <div class="text-xs text-muted-foreground font-mono">
            BD: {{ order.customer?.bd_number || order.bd_number || '-' }} • {{ order.order_date }}
            <template v-if="order.delivery_date"> -> {{ order.delivery_date }}</template>
          </div>

          <!-- Line 3 (Airman Mode): Clothing Items Pills with IV Number & Download Check -->
          <div v-if="airmanMode" class="pt-0.5">
            <div v-if="getGroupedAirmanItems(order).length === 0" class="text-[11px] text-muted-foreground italic">
              No items added yet
            </div>
            <div v-else class="flex flex-wrap gap-1 sm:gap-1.5">
              <div
                v-for="(row, idx) in getGroupedAirmanItems(order)"
                :key="idx"
                :class="[
                  'inline-flex items-center gap-1.5 max-w-full rounded-md border pl-2 pr-1.5 py-0.5 text-[10px] sm:text-xs transition-colors',
                  isAirmanItemDownloaded(order, row.name)
                    ? 'border-green-300 bg-green-50 text-green-900 dark:bg-green-950/40 dark:border-green-800 dark:text-green-200'
                    : 'border-sky-200 bg-sky-50 text-sky-900 dark:bg-sky-950/40 dark:border-sky-800 dark:text-sky-200'
                ]"
                :title="isAirmanItemDownloaded(order, row.name) ? 'Cutting slip downloaded. Click checkmark to clear.' : 'Cutting slip not downloaded. Click scissors to download.'"
              >
                <span class="truncate font-medium">{{ row.name }}</span>
                <span class="font-semibold shrink-0">×{{ row.qty }}</span>
                <span
                  v-if="row.iv"
                  :class="[
                    'shrink-0 rounded px-1.5 py-0.5 text-[9px] sm:text-[10px] font-mono leading-none',
                    isAirmanItemDownloaded(order, row.name)
                      ? 'bg-green-200/80 text-green-900 dark:bg-green-800/70 dark:text-green-100'
                      : 'bg-sky-200/80 text-sky-900 dark:bg-sky-800/70 dark:text-sky-100'
                  ]"
                >
                  IV: {{ row.iv }}
                </span>
                <button
                  v-if="isAirmanItemDownloaded(order, row.name)"
                  type="button"
                  title="Cutting slip downloaded. Click to clear this mark"
                  class="shrink-0 rounded p-0.5 hover:bg-green-200/70 dark:hover:bg-green-800/60 cursor-pointer"
                  @click.stop="unmarkAirmanItemDownloaded(order, row.name)"
                >
                  <CheckCircle class="h-3 w-3 text-green-600 dark:text-green-400" />
                </button>
                <button
                  v-else
                  type="button"
                  title="Click to download cutting slip for this item"
                  class="shrink-0 rounded p-0.5 hover:bg-sky-200/70 dark:hover:bg-sky-800/60 text-sky-600 dark:text-sky-400 cursor-pointer flex items-center justify-center"
                  @click.stop="downloadSingleItemSlip(order, row.name)"
                >
                  <Scissors class="h-3 w-3" />
                </button>
              </div>
            </div>
          </div>

          <!-- Line 4: Financials -->
          <div class="text-xs text-muted-foreground">
            Total: <span class="text-foreground font-medium">৳{{ Number(order.total_amount || 0).toLocaleString() }}</span>
            • Advance: <span class="text-foreground font-medium">৳{{ Number(order.advance ?? order.paid_amount ?? 0).toLocaleString() }}</span>
            • Due: <span class="font-semibold text-rose-600 dark:text-rose-400">৳{{ Number(order.due ?? order.due_amount ?? 0).toLocaleString() }}</span>
          </div>
        </div>

        <!-- Right Side: 6 Action Icons -->
        <div class="flex items-center gap-1 sm:gap-1.5 shrink-0 self-end md:self-center pt-2 md:pt-0 border-t border-border/40 md:border-0 w-full md:w-auto justify-end" @click.stop>
          <!-- 1. Print Cutting Slip (Printer) -->
          <button
            type="button"
            @click="printCuttingSlip(order)"
            title="Print Cutting Slip"
            class="h-8.5 w-8.5 sm:h-8 sm:w-8 inline-flex items-center justify-center rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
          >
            <Printer class="h-4 w-4" />
          </button>

          <!-- 2. Print Invoice (FileText) -->
          <button
            type="button"
            @click="printInvoice(order)"
            title="Print Invoice"
            class="h-8.5 w-8.5 sm:h-8 sm:w-8 inline-flex items-center justify-center rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
          >
            <FileText class="h-4 w-4" />
          </button>

          <!-- 3. Mark Delivered (CheckCircle) -->
          <button
            v-if="order.status !== 'delivered'"
            type="button"
            @click="openDeliveryModal(order)"
            title="Mark Delivered"
            class="h-8.5 w-8.5 sm:h-8 sm:w-8 inline-flex items-center justify-center rounded-lg hover:bg-emerald-50 text-emerald-600 dark:hover:bg-emerald-950/40 transition-colors cursor-pointer"
          >
            <CheckCircle class="h-4 w-4" />
          </button>

          <!-- 4. Mark Due (AlertCircle) -->
          <button
            v-if="order.status !== 'due_payment' && order.status !== 'delivered'"
            type="button"
            @click="markDuePayment(order.id)"
            title="Mark Due"
            class="h-8.5 w-8.5 sm:h-8 sm:w-8 inline-flex items-center justify-center rounded-lg hover:bg-rose-50 text-rose-500 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
          >
            <AlertCircle class="h-4 w-4" />
          </button>

          <!-- 5. Edit Order (Edit) -->
          <button
            type="button"
            @click="openEditOrder(order)"
            title="Edit Order"
            class="h-8.5 w-8.5 sm:h-8 sm:w-8 inline-flex items-center justify-center rounded-lg hover:bg-muted text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
          >
            <Edit class="h-4 w-4" />
          </button>

          <!-- 6. Delete Order (Trash2) -->
          <button
            type="button"
            @click="confirmDelete(order)"
            title="Delete Order"
            class="h-8.5 w-8.5 sm:h-8 sm:w-8 inline-flex items-center justify-center rounded-lg hover:bg-rose-50 text-rose-500 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
          >
            <Trash2 class="h-4 w-4" />
          </button>
        </div>
      </div>

      <!-- Load More / Pagination Button -->
      <div v-if="visibleOrders.length < filteredOrders.length" class="text-center pt-3 pb-6">
        <button
          type="button"
          @click="loadMore"
          class="h-9 px-5 text-xs rounded-xl border border-input bg-card hover:bg-accent font-medium text-foreground transition-colors shadow-2xs cursor-pointer"
        >
          Load More ({{ filteredOrders.length - visibleOrders.length }} remaining)
        </button>
      </div>
    </div>

    <!-- Reset Cutting Slip Badges Modal -->
    <Teleport to="body">
      <div v-if="showResetConfirm" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-xl shadow-2xl max-w-sm w-full p-5 space-y-4 animate-in zoom-in-95">
          <h3 class="text-base font-semibold text-foreground">Reset Cutting Slip Badges</h3>
          <p class="text-xs text-muted-foreground">
            Are you sure you want to reset all cutting slip badges for <strong>{{ currentClothingLabel }}</strong>? All orders will be marked as "Not Downloaded".
          </p>
          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              @click="showResetConfirm = false"
              class="h-8 px-3 text-xs border border-input rounded-md hover:bg-accent cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="button"
              @click="executeResetBadges"
              class="h-8 px-3 text-xs bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90 cursor-pointer font-medium"
            >
              Reset
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Quick Delivery Modal -->
    <Teleport to="body">
      <div v-if="deliveryModalOpen" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-xl shadow-2xl max-w-sm w-full p-5 space-y-4 animate-in zoom-in-95">
          <div class="flex items-center justify-between pb-2 border-b border-border">
            <h3 class="text-sm font-bold text-foreground">
              Deliver Order <span class="font-mono text-primary">#{{ orderToDeliver?.order_number }}</span>
            </h3>
            <button type="button" @click="deliveryModalOpen = false" class="text-muted-foreground hover:text-foreground">✕</button>
          </div>
          <div>
            <label class="block text-xs font-medium text-foreground mb-1">Delivery Date</label>
            <input
              v-model="deliveryDate"
              type="date"
              class="w-full h-8 px-2.5 text-xs rounded-lg border border-input bg-background focus:outline-none focus:ring-1 focus:ring-primary"
            />
          </div>
          <div class="flex items-center justify-end gap-2 pt-2">
            <button
              type="button"
              @click="deliveryModalOpen = false"
              class="h-8 px-3 text-xs rounded-lg border border-input hover:bg-muted text-foreground font-medium"
            >
              Cancel
            </button>
            <button
              type="button"
              @click="executeDelivery"
              :disabled="savingDelivery"
              class="h-8 px-3.5 text-xs rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 font-semibold inline-flex items-center gap-1.5"
            >
              <Loader2 v-if="savingDelivery" class="h-3 w-3 animate-spin" />
              Confirm Delivery
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Create / Edit Order Modal Drawer -->
    <Teleport to="body">
      <div v-if="orderModalOpen" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
        <div class="bg-card border border-border rounded-xl shadow-2xl max-w-4xl w-full max-h-[92vh] flex flex-col p-6 animate-in zoom-in-95">
          <!-- Modal Top -->
          <div class="flex items-center justify-between pb-4 border-b border-border">
            <div class="flex items-center gap-3">
              <h2 class="text-lg font-heading font-bold text-foreground">
                {{ editingOrderId ? t('editOrder') : t('newOrder') }}
                <span class="font-mono text-primary ml-1">#{{ orderForm.order_number }}</span>
              </h2>
              <span v-if="airmanMode" class="text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-semibold">
                Airman Mode
              </span>
            </div>
            <button type="button" @click="orderModalOpen = false" class="text-muted-foreground hover:text-foreground text-lg cursor-pointer">✕</button>
          </div>

          <!-- Modal Scroll Body -->
          <div class="flex-1 overflow-y-auto py-4 space-y-5 pr-1">
            <!-- Customer Information Card -->
            <div class="rounded-xl border border-border p-4 bg-muted/20 space-y-3">
              <h3 class="text-xs font-bold uppercase tracking-wider text-muted-foreground">{{ t('customerInfo') }}</h3>
              <!-- Customer Inputs (with instant match suggestions & auto-fill) -->
              <CustomerFieldsAutocomplete
                v-model:bdNumber="orderForm.customer_bd_number"
                v-model:customerName="orderForm.customer_name"
                v-model:customerPhone="orderForm.customer_phone"
                v-model:customerId="orderForm.customer_id"
                :customers="customers"
                :airmanMode="airmanMode"
              />

              <!-- IV Number & Running Year in Airman Mode -->
              <div v-if="airmanMode" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <div>
                  <label class="block text-xs font-medium text-foreground mb-1">IV Number</label>
                  <input
                    v-model="orderForm.iv_number"
                    type="text"
                    placeholder="IV-12345"
                    class="w-full h-8 px-2.5 text-xs rounded-md border border-input bg-background focus:outline-none focus:ring-1 focus:ring-ring font-mono"
                  />
                </div>
                <div>
                  <label class="block text-xs font-medium text-foreground mb-1">Running Year</label>
                  <select
                    v-model="orderForm.running_year"
                    class="w-full h-8 px-2.5 text-xs rounded-md border border-input bg-background focus:outline-none focus:ring-1 focus:ring-ring"
                  >
                    <option value="">Select Running Year</option>
                    <option v-for="ry in runningYears" :key="ry.id" :value="ry.year_label">
                      {{ ry.year_label }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Dates & Status Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-medium text-foreground mb-1">{{ t('orderDate') }}</label>
                <input
                  v-model="orderForm.order_date"
                  type="date"
                  class="w-full h-8 px-2 text-xs rounded-md border border-input bg-background"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-foreground mb-1">{{ t('deliveryDate') }}</label>
                <input
                  v-model="orderForm.delivery_date"
                  type="date"
                  class="w-full h-8 px-2 text-xs rounded-md border border-input bg-background"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-foreground mb-1">{{ t('status') }}</label>
                <select
                  v-model="orderForm.status"
                  class="w-full h-8 px-2 text-xs rounded-md border border-input bg-background"
                >
                  <option value="pending">Pending</option>
                  <option value="due_payment">Cutting Slip ~ Done</option>
                  <option value="delivered">Delivered</option>
                </select>
              </div>
            </div>

            <!-- Order Items & Measurement Grid -->
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-foreground">{{ t('orderItems') }}</h3>
                <button
                  type="button"
                  @click="addItem"
                  class="h-7 px-3 text-xs inline-flex items-center gap-1 rounded bg-secondary hover:bg-secondary/80 font-medium cursor-pointer"
                >
                  <Plus class="h-3 w-3" /> {{ t('addItem') }}
                </button>
              </div>

              <!-- Items List -->
              <div v-for="(item, itemIdx) in orderForm.items" :key="itemIdx" class="rounded-xl border border-border p-4 bg-card space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                  <div class="flex-1 max-w-xs">
                    <label class="block text-[11px] font-medium text-muted-foreground mb-1">{{ t('item') }}</label>
                    <ItemCatalogDropdown
                      :value="item.item_name"
                      :catalog-type="airmanMode ? 'airman' : 'officer'"
                      @select="(name, price) => handleItemSelect(item, name, price)"
                    />
                  </div>

                  <div class="flex items-center gap-3">
                    <div class="w-20">
                      <label class="block text-[11px] font-medium text-muted-foreground mb-1">{{ t('qty') }}</label>
                      <input
                        v-model.number="item.quantity"
                        @input="recalculateTotals"
                        type="number"
                        min="1"
                        class="w-full h-8 px-2 text-xs text-right rounded-md border border-input bg-background"
                      />
                    </div>
                    <div class="w-24">
                      <label class="block text-[11px] font-medium text-muted-foreground mb-1">{{ t('price') }} (৳)</label>
                      <input
                        v-model.number="item.price"
                        @input="recalculateTotals"
                        type="number"
                        class="w-full h-8 px-2 text-xs text-right rounded-md border border-input bg-background"
                      />
                    </div>
                    <div class="w-24 text-right pt-4 font-bold text-sm">
                      ৳{{ (Number(item.quantity || 1) * Number(item.price || 0)).toLocaleString() }}
                    </div>
                    <button
                      type="button"
                      @click="removeItem(itemIdx)"
                      class="h-8 w-8 mt-3 inline-flex items-center justify-center rounded text-muted-foreground hover:text-destructive hover:bg-destructive/10 cursor-pointer"
                    >
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <!-- Measurement Input Grid for Item -->
                <div class="space-y-3 pt-3 border-t border-border/60">
                  <span class="text-xs font-semibold text-muted-foreground block">
                    {{ t('measurements') }} (Shirt & Pant):
                  </span>

                  <!-- Shirt Fields -->
                  <div class="space-y-1">
                    <span class="text-[11px] font-medium text-foreground">Shirt / Top:</span>
                    <div class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-9 gap-1.5 text-xs">
                      <div v-for="field in SHIRT_FIELDS" :key="field">
                        <label class="block text-[10px] text-muted-foreground capitalize">{{ field }}</label>
                        <input
                          v-model="item.measurements[field]"
                          type="text"
                          class="w-full h-7 px-1.5 text-xs rounded border border-input bg-background text-center"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Pant Fields -->
                  <div class="space-y-1">
                    <span class="text-[11px] font-medium text-foreground">Pant / Bottom:</span>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5 text-xs">
                      <div v-for="field in PANT_FIELDS" :key="field">
                        <label class="block text-[10px] text-muted-foreground capitalize">{{ field }}</label>
                        <input
                          v-model="item.measurements[field]"
                          type="text"
                          class="w-full h-7 px-1.5 text-xs rounded border border-input bg-background text-center"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Item notes -->
                  <div>
                    <label class="block text-[10px] text-muted-foreground mb-0.5">{{ t('notes') }}</label>
                    <input
                      v-model="item.notes"
                      type="text"
                      placeholder="Special instructions..."
                      class="w-full h-7 px-2 text-xs rounded border border-input bg-background"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Financial Calculation Card -->
            <div class="rounded-xl border border-border p-4 bg-muted/20 space-y-3">
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                  <span class="text-muted-foreground block">{{ t('totalAmount') }}</span>
                  <span class="text-lg font-bold text-foreground">৳{{ orderForm.total_amount.toLocaleString() }}</span>
                </div>
                <div>
                  <label class="block text-muted-foreground mb-0.5">{{ t('advance') }} (৳)</label>
                  <input
                    v-model.number="orderForm.advance"
                    @input="recalculateTotals"
                    type="number"
                    class="w-full h-8 px-2 text-xs rounded border border-input bg-background font-semibold text-green-600"
                  />
                </div>
                <div>
                  <label class="block text-muted-foreground mb-0.5">{{ t('discount') }} (৳)</label>
                  <input
                    v-model.number="orderForm.discount"
                    @input="recalculateTotals"
                    type="number"
                    class="w-full h-8 px-2 text-xs rounded border border-input bg-background font-semibold text-amber-600"
                  />
                </div>
                <div>
                  <span class="text-muted-foreground block">{{ t('due') }}</span>
                  <span class="text-lg font-bold text-destructive">৳{{ orderForm.due.toLocaleString() }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Bottom Actions -->
          <div class="flex items-center justify-between pt-4 border-t border-border mt-3">
            <button
              type="button"
              @click="orderModalOpen = false"
              class="h-9 px-4 text-xs border border-input rounded-lg hover:bg-accent cursor-pointer"
            >
              {{ t('cancel') }}
            </button>
            <button
              type="button"
              @click="saveOrder"
              :disabled="saving"
              class="h-9 px-5 text-xs bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/90 disabled:opacity-50 inline-flex items-center gap-1.5 shadow-xs cursor-pointer"
            >
              <Loader2 v-if="saving" class="h-3.5 w-3.5 animate-spin" />
              <CheckCircle v-else class="h-3.5 w-3.5" />
              {{ t('saveOrder') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div v-if="deleteModalOpen" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card border border-border rounded-xl shadow-2xl max-w-sm w-full p-6 animate-in zoom-in-95">
          <h3 class="text-base font-semibold">{{ t('deleteOrder') }}</h3>
          <p class="text-xs text-muted-foreground mt-2">
            Are you sure you want to delete order #{{ orderToDelete?.order_number }}?
          </p>
          <div class="flex justify-end gap-2 mt-5">
            <button
              type="button"
              @click="deleteModalOpen = false"
              class="h-8 px-3 text-xs border border-input rounded-md hover:bg-accent cursor-pointer"
            >
              {{ t('cancel') }}
            </button>
            <button
              type="button"
              @click="executeDelete"
              class="h-8 px-3 text-xs bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90 cursor-pointer"
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
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  Plus, Search, Printer, FileText, Truck, CircleDollarSign, ArrowUpDown,
  Edit, Trash2, Loader2, CheckCircle, AlertCircle, Package, Shirt, RotateCcw, Download, Scissors
} from 'lucide-vue-next';
import { t } from '@/lib/language';
import { generateInvoicePDF, generateSlipPDF, generateBulkCuttingSlipPDF } from '@/lib/pdf';
import OrderStatusBadge from '@/components/OrderStatusBadge.vue';
import ItemCatalogDropdown from '@/components/ItemCatalogDropdown.vue';
import CustomerFieldsAutocomplete from '@/components/CustomerFieldsAutocomplete.vue';
import api from '@/lib/api';

const props = defineProps({
  airmanMode: { type: Boolean, default: false },
});

const route = useRoute();
const router = useRouter();

const SHIRT_FIELDS = ['length', 'chest', 'belly', 'hip', 'shoulder', 'sleeve', 'collar', 'front', 'back'];
const PANT_FIELDS = ['length', 'waist', 'hip', 'high', 'thai', 'leg_open'];

const orders = ref([]);
const customers = ref([]);
const airmanCatalog = ref([]);
const runningYears = ref([]);
const loading = ref(true);
const saving = ref(false);

const search = ref(route.query.search || '');
const debouncedSearch = ref(search.value);
let searchDebounceTimer = null;
watch(search, (val) => {
  clearTimeout(searchDebounceTimer);
  searchDebounceTimer = setTimeout(() => {
    debouncedSearch.value = val;
  }, 150);
});
const sortBy = ref('newest');
const activeStatus = ref('all');
const specialFilter = ref('all'); // 'all', 'upcoming', 'due_after_delivery'
const clothingFilter = ref('all');
const runningYearFilter = ref('all');

// Reset modal
const showResetConfirm = ref(false);

// Pagination state
const pageSize = ref(40);

// Order Create / Edit Modal State
const orderModalOpen = ref(false);
const editingOrderId = ref(null);
const orderForm = ref({
  order_number: '',
  customer_id: null,
  customer_name: '',
  customer_phone: '',
  customer_bd_number: '',
  iv_number: '',
  running_year: '',
  order_date: new Date().toISOString().split('T')[0],
  delivery_date: '',
  status: 'pending',
  items: [],
  total_amount: 0,
  advance: 0,
  discount: 0,
  due: 0,
});

// Delete Modal
const deleteModalOpen = ref(false);
const orderToDelete = ref(null);

// Delivery Modal
const deliveryModalOpen = ref(false);
const orderToDeliver = ref(null);
const deliveryDate = ref(new Date().toISOString().split('T')[0]);
const savingDelivery = ref(false);

const currentTabs = computed(() => {
  if (props.airmanMode && clothingFilter.value !== 'all') {
    return [
      { key: 'all', label: 'All' },
      { key: 'not_done', label: 'Not Downloaded' },
      { key: 'due_payment', label: 'Downloaded' },
    ];
  }
  return [
    { key: 'all', label: 'All' },
    { key: 'pending', label: 'Pending' },
    { key: 'delivered', label: 'Delivered' },
    { key: 'due_payment', label: 'Cutting Slip ~ Done' },
  ];
});

const currentClothingLabel = computed(() => {
  const match = airmanCatalog.value.find(c => c.name.toLowerCase().trim() === clothingFilter.value);
  return match?.name || clothingFilter.value;
});

function navigateTo(path) {
  router.push(path);
}

function selectTab(key) {
  activeStatus.value = key;
  specialFilter.value = 'all';
}

function toggleSpecialFilter(filterType) {
  if (specialFilter.value === filterType) {
    specialFilter.value = 'all';
  } else {
    specialFilter.value = filterType;
    activeStatus.value = 'all';
  }
}

function toggleClothingFilter(key) {
  if (clothingFilter.value === key) {
    clothingFilter.value = 'all';
  } else {
    clothingFilter.value = key;
    activeStatus.value = 'all';
  }
}

// 6 Airman Clothing Category Cards
const airmanCategoryCards = computed(() => {
  const palette = [
    { color: 'border-l-blue-500', bgColor: 'bg-blue-500/10', textColor: 'text-blue-500', ringColor: 'ring-blue-500' },
    { color: 'border-l-emerald-500', bgColor: 'bg-emerald-500/10', textColor: 'text-emerald-500', ringColor: 'ring-emerald-500' },
    { color: 'border-l-amber-600', bgColor: 'bg-amber-600/10', textColor: 'text-amber-600', ringColor: 'ring-amber-600' },
    { color: 'border-l-rose-500', bgColor: 'bg-rose-500/10', textColor: 'text-rose-500', ringColor: 'ring-rose-500' },
    { color: 'border-l-violet-500', bgColor: 'bg-violet-500/10', textColor: 'text-violet-500', ringColor: 'ring-violet-500' },
    { color: 'border-l-cyan-500', bgColor: 'bg-cyan-500/10', textColor: 'text-cyan-500', ringColor: 'ring-cyan-500' },
  ];

  const cats = airmanCatalog.value.length > 0
    ? airmanCatalog.value
    : [
        { name: 'Battle Uniform', sort_order: 1 },
        { name: 'Half Sleeve', sort_order: 2 },
        { name: 'Full Sleeve', sort_order: 3 },
        { name: 'Summer Ceremonial', sort_order: 4 },
        { name: 'Winter Ceremonial', sort_order: 5 },
        { name: 'MODC Uniform', sort_order: 6 },
      ];

  return cats.map((c, i) => {
    const matchName = c.name.toLowerCase().trim();
    const count = orders.value.filter(o => {
      if ((o.order_category || 'officer') !== 'airman') return false;
      const items = o.items || [];
      return items.some(item => String(item.item_name || '').toLowerCase().trim() === matchName);
    }).length;

    return {
      key: matchName,
      label: c.name,
      matchName,
      count,
      ...palette[i % palette.length],
    };
  });
});

const hasDownloadedInCategory = computed(() => {
  if (clothingFilter.value === 'all') return false;
  return filteredOrders.value.some(o => {
    const cats = Array.isArray(o.downloaded_categories) ? o.downloaded_categories : [];
    return cats.includes(clothingFilter.value);
  });
});

// KPI: Upcoming to Deliver (for Officer mode)
const upcomingToDeliver = computed(() => {
  const today = new Date().toISOString().split('T')[0];
  return orders.value.filter(o =>
    (o.order_category || 'officer') === 'officer' &&
    o.status !== 'delivered' &&
    o.delivery_date &&
    o.delivery_date >= today
  );
});

// KPI: Due After Delivery (for Officer mode)
const dueAfterDelivery = computed(() => {
  return orders.value.filter(o =>
    (o.order_category || 'officer') === 'officer' &&
    o.status === 'delivered' &&
    Number(o.due ?? o.due_amount ?? 0) > 0
  );
});

const dueAfterDeliveryTotal = computed(() => {
  return dueAfterDelivery.value.reduce((s, o) => s + Number(o.due ?? o.due_amount ?? 0), 0);
});

const filteredOrders = computed(() => {
  let list = orders.value;

  // Filter category
  const targetCategory = props.airmanMode ? 'airman' : 'officer';
  list = list.filter(o => (o.order_category || 'officer') === targetCategory);

  // Airman Running Year Filter
  if (props.airmanMode && runningYearFilter.value !== 'all') {
    list = list.filter(o => o.running_year === runningYearFilter.value);
  }

  // Airman Clothing Category Filter
  if (props.airmanMode && clothingFilter.value !== 'all') {
    list = list.filter(o => {
      const items = o.items || [];
      return items.some(i => String(i.item_name || '').toLowerCase().trim() === clothingFilter.value);
    });

    if (activeStatus.value === 'not_done') {
      list = list.filter(o => {
        const cats = Array.isArray(o.downloaded_categories) ? o.downloaded_categories : [];
        return !cats.includes(clothingFilter.value);
      });
    } else if (activeStatus.value === 'due_payment') {
      list = list.filter(o => {
        const cats = Array.isArray(o.downloaded_categories) ? o.downloaded_categories : [];
        return cats.includes(clothingFilter.value);
      });
    }
  } else {
    // Normal Status Tabs
    if (activeStatus.value !== 'all') {
      list = list.filter(o => o.status === activeStatus.value);
    }
  }

  // Special KPI Filters (Officer Mode)
  if (!props.airmanMode) {
    if (specialFilter.value === 'upcoming') {
      const today = new Date().toISOString().split('T')[0];
      list = list.filter(o => o.status !== 'delivered' && o.delivery_date && o.delivery_date >= today);
    } else if (specialFilter.value === 'due_after_delivery') {
      list = list.filter(o => o.status === 'delivered' && Number(o.due ?? o.due_amount ?? 0) > 0);
    }
  }

  // Search filter (debounced for smooth 60fps typing)
  if (debouncedSearch.value.trim()) {
    const q = debouncedSearch.value.trim().toLowerCase();
    list = list.filter(o =>
      String(o.order_number || '').includes(q) ||
      (o.iv_number || '').toLowerCase().includes(q) ||
      (o.customer?.name || '').toLowerCase().includes(q) ||
      (o.customer?.phone || '').includes(q) ||
      (o.customer?.bd_number || o.bd_number || '').toLowerCase().includes(q) ||
      (o.items || []).some(i => String(i.item_name || i.item_type || '').toLowerCase().includes(q))
    );
  }

  // Sort
  const res = [...list];
  switch (sortBy.value) {
    case 'oldest':
      res.sort((a, b) => Number(a.order_number || 0) - Number(b.order_number || 0));
      break;
    case 'delivery_asc':
      res.sort((a, b) => (a.delivery_date || '9999-99-99').localeCompare(b.delivery_date || '9999-99-99'));
      break;
    case 'due_desc':
      res.sort((a, b) => Number(b.due ?? b.due_amount ?? 0) - Number(a.due ?? a.due_amount ?? 0));
      break;
    case 'newest':
    default:
      res.sort((a, b) => Number(b.order_number || 0) - Number(a.order_number || 0));
      break;
  }
  return res;
});

const visibleOrders = computed(() => {
  return filteredOrders.value.slice(0, pageSize.value);
});

function loadMore() {
  pageSize.value += 40;
}

// Group airman items for pill badges
function getGroupedAirmanItems(order) {
  const items = order.items || [];
  const grouped = new Map();
  items.forEach(item => {
    const name = String(item.item_name || item.item_type || '').trim();
    const iv = String(item.iv_number || order.iv_number || '').trim();
    const key = `${name}__${iv}`;
    const qty = Number(item.quantity) || 1;
    const existing = grouped.get(key);
    if (existing) {
      existing.qty += qty;
    } else if (name) {
      grouped.set(key, { name, iv, qty });
    }
  });
  return Array.from(grouped.values());
}

function getDownloadedCategories(order) {
  if (!order || !order.downloaded_categories) return [];
  if (Array.isArray(order.downloaded_categories)) {
    return order.downloaded_categories.map(c => String(c).toLowerCase().trim());
  }
  if (typeof order.downloaded_categories === 'string') {
    try {
      const parsed = JSON.parse(order.downloaded_categories);
      return Array.isArray(parsed) ? parsed.map(c => String(c).toLowerCase().trim()) : [];
    } catch {
      return [order.downloaded_categories.toLowerCase().trim()];
    }
  }
  return [];
}

function isAirmanItemDownloaded(order, itemName) {
  const cats = getDownloadedCategories(order);
  const target = String(itemName || '').toLowerCase().trim();
  return cats.includes(target);
}

async function unmarkAirmanItemDownloaded(order, itemName) {
  const target = String(itemName || '').toLowerCase().trim();
  const existing = getDownloadedCategories(order);
  const updated = existing.filter(c => c !== target);
  order.downloaded_categories = updated;
  try {
    await api.put(`/orders/${order.id}`, { downloaded_categories: updated });
  } catch (err) {
    console.error('Failed to unmark item', err);
  }
}

async function downloadSingleItemSlip(order, itemName) {
  let targetOrder = order;
  if (!targetOrder.items || targetOrder.items.length === 0) {
    try {
      const res = await api.get(`/orders/${order.id}`);
      targetOrder = res.data?.data || res.data || order;
    } catch (e) {
      console.warn('Could not fetch full order details for slip', e);
    }
  }

  const allItems = targetOrder.items || [];
  const normalizedTarget = String(itemName || '').toLowerCase().trim();
  const matchedItems = allItems.filter(i =>
    String(i.item_name || i.item_type || '').toLowerCase().trim() === normalizedTarget
  );

  const itemsToGenerate = matchedItems.length > 0 ? matchedItems : [{
    item_name: itemName,
    item_type: itemName.toLowerCase().includes('pant') ? 'pant' : 'shirt',
    quantity: 1,
    measurements: targetOrder.items?.[0]?.measurements || {},
  }];

  generateSlipPDF(targetOrder, targetOrder.customer || {}, itemsToGenerate);

  // Mark this specific item as downloaded
  const existing = getDownloadedCategories(targetOrder);
  if (!existing.includes(normalizedTarget)) {
    const updated = [...existing, normalizedTarget];
    targetOrder.downloaded_categories = updated;
    order.downloaded_categories = updated;
    try {
      await api.put(`/orders/${targetOrder.id}`, { downloaded_categories: updated });
    } catch (err) {
      console.error('Failed to update downloaded_categories for single item', err);
    }
  }
}

async function executeResetBadges() {
  const cat = clothingFilter.value;
  if (cat === 'all') return;

  const toReset = filteredOrders.value.filter(o => {
    const cats = getDownloadedCategories(o);
    return cats.includes(cat);
  });

  try {
    const updatePromises = toReset.map(ord => {
      const existing = getDownloadedCategories(ord);
      const updated = existing.filter(c => c !== cat);
      ord.downloaded_categories = updated;
      return api.put(`/orders/${ord.id}`, { downloaded_categories: updated }).catch(e => console.error(e));
    });
    await Promise.allSettled(updatePromises);
  } catch (err) {
    console.error('Failed to reset badges', err);
  } finally {
    showResetConfirm.value = false;
  }
}

async function downloadCategorySlips(onlyNotDownloaded = false) {
  const keyword = clothingFilter.value;
  const isAllCategories = keyword === 'all';
  const label = isAllCategories ? 'All Airman Uniforms' : currentClothingLabel.value;

  let targetOrders = filteredOrders.value;

  if (onlyNotDownloaded) {
    targetOrders = targetOrders.filter(o => {
      const cats = getDownloadedCategories(o);
      if (isAllCategories) {
        const orderItems = o.items || [];
        return orderItems.some(i => !cats.includes(String(i.item_name || i.item_type || '').toLowerCase().trim()));
      }
      return !cats.includes(keyword);
    });
  }

  const ordersDataForPdf = [];
  for (const order of targetOrders) {
    let ord = order;
    if (!ord.items || ord.items.length === 0) {
      try {
        const res = await api.get(`/orders/${ord.id}`);
        ord = res.data?.data || res.data || ord;
      } catch (e) {
        console.warn('Failed to load full items for order #' + ord.order_number, e);
      }
    }

    const cust = customers.value.find(c => c.id === ord.customer_id) || ord.customer;
    const allItems = ord.items || [];
    let matchedItems = allItems;

    if (!isAllCategories) {
      matchedItems = allItems.filter(item =>
        String(item.item_name || item.item_type || '').toLowerCase().trim() === keyword
      );
    } else if (onlyNotDownloaded) {
      const cats = getDownloadedCategories(ord);
      matchedItems = allItems.filter(item =>
        !cats.includes(String(item.item_name || item.item_type || '').toLowerCase().trim())
      );
    }

    if (matchedItems.length > 0) {
      ordersDataForPdf.push({
        order: ord,
        items: matchedItems,
        customerName: cust?.name || '',
      });
    }
  }

  if (ordersDataForPdf.length === 0) {
    alert(onlyNotDownloaded ? 'No not-downloaded cutting slips found' : 'No items found for this category');
    return;
  }

  generateBulkCuttingSlipPDF(ordersDataForPdf, label + (onlyNotDownloaded ? ' (Not Downloaded)' : ''));

  // Mark all generated items as downloaded immediately in state and persist to database
  const updatePromises = [];
  for (const d of ordersDataForPdf) {
    const existing = getDownloadedCategories(d.order);
    const itemNamesToMark = d.items
      .map(i => String(i.item_name || i.item_type || '').toLowerCase().trim())
      .filter(Boolean);

    let changed = false;
    const updated = [...existing];
    for (const name of itemNamesToMark) {
      if (!updated.includes(name)) {
        updated.push(name);
        changed = true;
      }
    }

    if (changed) {
      d.order.downloaded_categories = updated;
      const inList = orders.value.find(o => o.id === d.order.id);
      if (inList) {
        inList.downloaded_categories = updated;
      }

      updatePromises.push(
        api.put(`/orders/${d.order.id}`, { downloaded_categories: updated }).catch(e => {
          console.error(`Failed to update downloaded status for order #${d.order.order_number}`, e);
        })
      );
    }
  }

  await Promise.allSettled(updatePromises);
}

async function fetchOrders() {
  loading.value = true;
  try {
    const promises = [
      api.get('/orders', { params: { category: props.airmanMode ? 'airman' : 'officer' } }),
      api.get('/customers'),
    ];

    if (props.airmanMode) {
      promises.push(api.get('/catalogs', { params: { type: 'airman' } }));
      promises.push(api.get('/running-years'));
    }

    const results = await Promise.all(promises);
    orders.value = results[0].data.data || [];
    customers.value = results[1].data.data || [];

    if (props.airmanMode && results[2]) {
      airmanCatalog.value = results[2].data.data || results[2].data || [];
    }
    if (props.airmanMode && results[3]) {
      runningYears.value = results[3].data.data || results[3].data || [];
    }
  } catch (err) {
    console.error('Failed to load orders', err);
  } finally {
    loading.value = false;
  }
}

async function openCreateOrder() {
  if (props.airmanMode) {
    router.push('/airman/new');
    return;
  }
  router.push('/orders/new');
}

function openEditOrder(order) {
  if (props.airmanMode || (order.order_category || 'officer') === 'airman') {
    router.push(`/airman/${order.id}/edit`);
    return;
  }
  router.push(`/orders/${order.id}/edit`);
}

function addItem() {
  orderForm.value.items.push({
    item_name: props.airmanMode ? 'PT Dress' : 'Pant (Uniform)',
    item_type: 'pant',
    quantity: 1,
    price: props.airmanMode ? 0 : 200,
    measurements: {},
    notes: '',
  });
  recalculateTotals();
}

function removeItem(idx) {
  orderForm.value.items.splice(idx, 1);
  recalculateTotals();
}

function handleItemSelect(item, name, price) {
  item.item_name = name;
  item.price = price;
  recalculateTotals();
}

function recalculateTotals() {
  let subtotal = 0;
  orderForm.value.items.forEach(i => {
    subtotal += Number(i.quantity || 1) * Number(i.price || 0);
  });
  orderForm.value.total_amount = subtotal;
  const adv = Number(orderForm.value.advance || 0);
  const disc = Number(orderForm.value.discount || 0);
  orderForm.value.due = Math.max(0, subtotal - adv - disc);
}

function searchCustomerLookup() {
  const name = orderForm.value.customer_name.trim().toLowerCase();
  if (!name) return;
  const match = customers.value.find(c => (c.name || '').toLowerCase() === name);
  if (match) {
    orderForm.value.customer_id = match.id;
    if (!orderForm.value.customer_phone || orderForm.value.customer_phone === '-') {
      orderForm.value.customer_phone = match.phone;
    }
    if (!orderForm.value.customer_bd_number) {
      orderForm.value.customer_bd_number = match.bd_number;
    }
  }
}

async function saveOrder() {
  if (!orderForm.value.customer_name.trim()) return;
  saving.value = true;

  try {
    const payload = {
      order_number: Number(orderForm.value.order_number),
      order_category: props.airmanMode ? 'airman' : 'officer',
      customer_id: orderForm.value.customer_id,
      customer_name: orderForm.value.customer_name,
      customer_phone: orderForm.value.customer_phone,
      bd_number: orderForm.value.customer_bd_number || 'N/A',
      iv_number: orderForm.value.iv_number,
      running_year: orderForm.value.running_year || null,
      order_date: orderForm.value.order_date,
      delivery_date: orderForm.value.delivery_date || null,
      status: orderForm.value.status,
      items: orderForm.value.items,
      total_amount: orderForm.value.total_amount,
      advance: orderForm.value.advance,
      due: orderForm.value.due,
      discount: orderForm.value.discount,
    };

    if (editingOrderId.value) {
      await api.put(`/orders/${editingOrderId.value}`, payload);
    } else {
      await api.post('/orders', payload);
    }

    orderModalOpen.value = false;
    await fetchOrders();
  } catch (err) {
    console.error('Failed to save order', err);
  } finally {
    saving.value = false;
  }
}

function openDeliveryModal(order) {
  orderToDeliver.value = order;
  deliveryDate.value = order.delivery_date || new Date().toISOString().split('T')[0];
  deliveryModalOpen.value = true;
}

async function executeDelivery() {
  if (!orderToDeliver.value) return;
  savingDelivery.value = true;
  try {
    await api.put(`/orders/${orderToDeliver.value.id}`, {
      status: 'delivered',
      delivered_date: deliveryDate.value,
    });
    deliveryModalOpen.value = false;
    orderToDeliver.value = null;
    await fetchOrders();
  } catch (err) {
    console.error('Failed to mark delivered', err);
  } finally {
    savingDelivery.value = false;
  }
}

async function markDuePayment(id) {
  try {
    await api.put(`/orders/${id}`, { status: 'due_payment' });
    await fetchOrders();
  } catch (err) {
    console.error('Failed to mark due', err);
  }
}

function goToOrderDetails(order) {
  router.push(`/orders/${order.id}`);
}

async function printInvoice(order) {
  let targetOrder = order;
  if (!targetOrder.items || targetOrder.items.length === 0) {
    try {
      const res = await api.get(`/orders/${order.id}`);
      targetOrder = res.data?.data || res.data || order;
    } catch (e) {
      console.warn('Could not fetch full order details for invoice', e);
    }
  }
  generateInvoicePDF(targetOrder, targetOrder.customer || {}, targetOrder.items || []);
}

async function printCuttingSlip(order) {
  let targetOrder = order;
  if (!targetOrder.items || targetOrder.items.length === 0) {
    try {
      const res = await api.get(`/orders/${order.id}`);
      targetOrder = res.data?.data || res.data || order;
    } catch (e) {
      console.warn('Could not fetch full order details for slip', e);
    }
  }

  let itemsToPrint = targetOrder.items || [];
  if (props.airmanMode && clothingFilter.value !== 'all') {
    const matched = itemsToPrint.filter(i =>
      String(i.item_name || i.item_type || '').toLowerCase().trim() === clothingFilter.value
    );
    if (matched.length > 0) {
      itemsToPrint = matched;
    }
  }

  generateSlipPDF(targetOrder, targetOrder.customer || {}, itemsToPrint);

  // Mark all printed items as downloaded if airman order
  if (props.airmanMode || (targetOrder.order_category || 'officer') === 'airman') {
    const existing = getDownloadedCategories(targetOrder);
    const printedCats = itemsToPrint
      .map(i => String(i.item_name || i.item_type || '').toLowerCase().trim())
      .filter(Boolean);

    let changed = false;
    const updated = [...existing];
    for (const cat of printedCats) {
      if (!updated.includes(cat)) {
        updated.push(cat);
        changed = true;
      }
    }

    if (changed) {
      targetOrder.downloaded_categories = updated;
      order.downloaded_categories = updated;
      const inList = orders.value.find(o => o.id === targetOrder.id);
      if (inList) {
        inList.downloaded_categories = updated;
      }

      try {
        await api.put(`/orders/${targetOrder.id}`, { downloaded_categories: updated });
      } catch (e) {
        console.error('Failed to update downloaded_categories for order', e);
      }
    }
  }
}

function confirmDelete(order) {
  orderToDelete.value = order;
  deleteModalOpen.value = true;
}

async function executeDelete() {
  if (!orderToDelete.value) return;
  try {
    await api.delete(`/orders/${orderToDelete.value.id}`);
    deleteModalOpen.value = false;
    orderToDelete.value = null;
    await fetchOrders();
  } catch (err) {
    console.error('Failed to delete order', err);
  }
}

onMounted(() => {
  fetchOrders();
});
</script>
