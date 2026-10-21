<template>
  <div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold font-heading text-foreground">Dashboard</h1>
        <p class="text-xs text-muted-foreground mt-0.5">Business overview at a glance</p>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="fetchDashboard"
          class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-border bg-card hover:bg-muted text-xs font-medium text-foreground transition-colors cursor-pointer"
        >
          <span :class="{'animate-spin inline-block': loading}">↻</span>
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Section 1: TODAY'S OVERVIEW -->
    <div class="space-y-2.5">
      <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-muted-foreground">
        <Calendar class="h-3.5 w-3.5 text-blue-500" />
        <span>TODAY'S OVERVIEW</span>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
        <!-- Today's Orders -->
        <div
          @click="navigateToResult('today')"
          class="bg-card border border-border/80 border-l-4 border-l-blue-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 flex items-center justify-center shrink-0">
            <ShoppingBag class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Today's Orders</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">{{ stats.todaysOrders || 0 }}</p>
          </div>
        </div>

        <!-- Today's Sales -->
        <div
          @click="navigateToResult('today_sales')"
          class="bg-card border border-border/80 border-l-4 border-l-sky-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400 flex items-center justify-center shrink-0">
            <DollarSign class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Today's Sales</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">৳{{ (stats.todaysSales || 0).toLocaleString() }}</p>
          </div>
        </div>

        <!-- Today's Collection -->
        <div
          @click="navigateToResult('today_collection')"
          class="bg-card border border-border/80 border-l-4 border-l-emerald-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
            <Wallet class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Today's Collection</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">৳{{ (stats.todaysCollection || 0).toLocaleString() }}</p>
          </div>
        </div>

        <!-- Today's Due -->
        <div
          @click="navigateToResult('today_due')"
          class="bg-card border border-border/80 border-l-4 border-l-rose-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center shrink-0">
            <CreditCard class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Today's Due</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">৳{{ (stats.todaysDue || 0).toLocaleString() }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 2: WEEKLY OVERVIEW -->
    <div class="space-y-2.5">
      <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-muted-foreground">
        <Package class="h-3.5 w-3.5 text-amber-500" />
        <span>WEEKLY OVERVIEW</span>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
        <!-- Weekly Orders -->
        <div
          @click="navigateToResult('weekly')"
          class="bg-card border border-border/80 border-l-4 border-l-blue-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 flex items-center justify-center shrink-0">
            <Package class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Weekly Orders</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">{{ stats.weeklyOrders || 0 }}</p>
          </div>
        </div>

        <!-- Weekly Sales -->
        <div
          @click="navigateToResult('weekly_sales')"
          class="bg-card border border-border/80 border-l-4 border-l-sky-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400 flex items-center justify-center shrink-0">
            <DollarSign class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Weekly Sales</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">৳{{ (stats.weeklySales || 0).toLocaleString() }}</p>
          </div>
        </div>

        <!-- Weekly Collection -->
        <div
          @click="navigateToResult('weekly_collection')"
          class="bg-card border border-border/80 border-l-4 border-l-emerald-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
            <Wallet class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Weekly Collection</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">৳{{ (stats.weeklyCollection || 0).toLocaleString() }}</p>
          </div>
        </div>

        <!-- Weekly Due -->
        <div
          @click="navigateToResult('weekly_due')"
          class="bg-card border border-border/80 border-l-4 border-l-rose-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center shrink-0">
            <CreditCard class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Weekly Due</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">৳{{ (stats.weeklyDue || 0).toLocaleString() }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 3: MONTHLY OVERVIEW -->
    <div class="space-y-2.5">
      <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-muted-foreground">
        <TrendingUp class="h-3.5 w-3.5 text-blue-500" />
        <span>MONTHLY OVERVIEW</span>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
        <!-- Monthly Orders -->
        <div
          @click="navigateToResult('monthly')"
          class="bg-card border border-border/80 border-l-4 border-l-blue-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 flex items-center justify-center shrink-0">
            <Package class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Monthly Orders</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">{{ stats.monthlyOrders || 0 }}</p>
          </div>
        </div>

        <!-- Monthly Sales -->
        <div
          @click="navigateToResult('monthly_sales')"
          class="bg-card border border-border/80 border-l-4 border-l-sky-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400 flex items-center justify-center shrink-0">
            <DollarSign class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Monthly Sales</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">৳{{ (stats.monthlySales || 0).toLocaleString() }}</p>
          </div>
        </div>

        <!-- Monthly Collection -->
        <div
          @click="navigateToResult('monthly_collection')"
          class="bg-card border border-border/80 border-l-4 border-l-emerald-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
            <Wallet class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Monthly Collection</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">৳{{ (stats.monthlyCollection || 0).toLocaleString() }}</p>
          </div>
        </div>

        <!-- Monthly Due -->
        <div
          @click="navigateToResult('monthly_due')"
          class="bg-card border border-border/80 border-l-4 border-l-rose-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center shrink-0">
            <CreditCard class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Monthly Due</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">৳{{ (stats.monthlyDue || 0).toLocaleString() }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 4: TOTAL OVERVIEW -->
    <div class="space-y-2.5">
      <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-muted-foreground">
        <FileText class="h-3.5 w-3.5 text-emerald-500" />
        <span>TOTAL OVERVIEW</span>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
        <!-- Total Orders -->
        <div
          @click="navigateToResult('all')"
          class="bg-card border border-border/80 border-l-4 border-l-blue-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 flex items-center justify-center shrink-0">
            <ShoppingBag class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Total Orders</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">{{ stats.totalOrders || 0 }}</p>
          </div>
        </div>

        <!-- Total Sales -->
        <div
          @click="navigateToResult('all')"
          class="bg-card border border-border/80 border-l-4 border-l-sky-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400 flex items-center justify-center shrink-0">
            <DollarSign class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Total Sales</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">৳{{ (stats.totalSales || 0).toLocaleString() }}</p>
          </div>
        </div>

        <!-- Total Collection -->
        <div
          @click="navigateToResult('all')"
          class="bg-card border border-border/80 border-l-4 border-l-emerald-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
            <Wallet class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Total Collection</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">৳{{ (stats.totalCollection || 0).toLocaleString() }}</p>
          </div>
        </div>

        <!-- Total Due -->
        <div
          @click="navigateToResult('total_due')"
          class="bg-card border border-border/80 border-l-4 border-l-rose-500 rounded-xl p-3 sm:p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3.5"
        >
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center shrink-0">
            <CreditCard class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium truncate">Total Due</p>
            <p class="text-lg sm:text-xl font-bold font-bangla text-foreground leading-tight">৳{{ (stats.totalDue || 0).toLocaleString() }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 5: QUICK STATS -->
    <div class="space-y-2.5">
      <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-muted-foreground">
        <Compass class="h-3.5 w-3.5 text-amber-500" />
        <span>QUICK STATS</span>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Total Customers -->
        <div
          @click="$router.push('/customers')"
          class="bg-card border border-border/80 border-l-4 border-l-blue-500 rounded-xl p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex items-center gap-3.5"
        >
          <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 flex items-center justify-center shrink-0">
            <Users class="h-5 w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[11px] text-muted-foreground font-medium truncate">Total Customers</p>
            <p class="text-xl font-bold font-bangla text-foreground leading-tight">{{ stats.totalCustomers || 0 }}</p>
          </div>
        </div>

        <!-- Upcoming Deliveries -->
        <div
          @click="navigateToResult('upcoming')"
          class="bg-card border border-border/80 border-l-4 border-l-amber-500 rounded-xl p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex items-center gap-3.5"
        >
          <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 flex items-center justify-center shrink-0">
            <Truck class="h-5 w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[11px] text-muted-foreground font-medium truncate">Upcoming Deliveries</p>
            <p class="text-xl font-bold font-bangla text-foreground leading-tight">{{ stats.upcomingDeliveries || 0 }}</p>
          </div>
        </div>

        <!-- Delivered Orders -->
        <div
          @click="navigateToResult('delivered')"
          class="bg-card border border-border/80 border-l-4 border-l-emerald-500 rounded-xl p-4 shadow-2xs hover:shadow-sm transition-all cursor-pointer flex items-center gap-3.5"
        >
          <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center shrink-0">
            <CheckCircle2 class="h-5 w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[11px] text-muted-foreground font-medium truncate">Delivered Orders</p>
            <p class="text-xl font-bold font-bangla text-foreground leading-tight">{{ stats.deliveredOrders || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 6: CHARTS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Orders & Revenue Bar Chart (Col 8) -->
      <div class="lg:col-span-8 bg-card border border-border/80 rounded-2xl p-5 shadow-2xs flex flex-col justify-between">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <TrendingUp class="h-4 w-4 text-primary" />
            <h3 class="text-sm font-bold text-foreground">Orders & Revenue</h3>
          </div>
          <div class="flex items-center gap-4 text-xs">
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded-xs bg-blue-600 inline-block"></span>
              <span class="text-muted-foreground font-medium">Sales</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded-xs bg-emerald-500 inline-block"></span>
              <span class="text-muted-foreground font-medium">Collection</span>
            </div>
          </div>
        </div>

        <!-- SVG Bar Chart -->
        <div class="w-full overflow-x-auto">
          <div class="min-w-[500px] h-64 flex flex-col">
            <div class="flex-1 relative flex">
              <!-- Y Axis labels -->
              <div class="w-16 h-full flex flex-col justify-between text-[10px] text-muted-foreground text-right pr-2 select-none">
                <span>220000</span>
                <span>165000</span>
                <span>110000</span>
                <span>55000</span>
                <span>0</span>
              </div>

              <!-- Chart Area with grid lines and bars -->
              <div class="flex-1 h-full relative">
                <!-- Grid Lines -->
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
                  <div class="border-b border-border/40 w-full h-0"></div>
                  <div class="border-b border-border/40 w-full h-0"></div>
                  <div class="border-b border-border/40 w-full h-0"></div>
                  <div class="border-b border-border/40 w-full h-0"></div>
                  <div class="border-b border-border w-full h-0"></div>
                </div>

                <!-- Monthly Bars -->
                <div class="absolute inset-0 flex items-end justify-around px-2">
                  <div
                    v-for="(item, idx) in monthlyChart"
                    :key="idx"
                    class="flex items-end justify-center gap-1.5 h-full relative group pb-0"
                  >
                    <!-- Sales Bar (Blue) -->
                    <div
                      class="w-6 sm:w-8 bg-blue-600 rounded-t-sm hover:brightness-110 transition-all cursor-pointer relative"
                      :style="{ height: `${Math.min(100, (item.sales / 220000) * 100)}%` }"
                    >
                      <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-2 py-0.5 rounded shadow pointer-events-none whitespace-nowrap z-20">
                        Sales: ৳{{ item.sales.toLocaleString() }}
                      </div>
                    </div>

                    <!-- Collection Bar (Green) -->
                    <div
                      class="w-6 sm:w-8 bg-emerald-500 rounded-t-sm hover:brightness-110 transition-all cursor-pointer relative"
                      :style="{ height: `${Math.min(100, (item.collection / 220000) * 100)}%` }"
                    >
                      <div class="opacity-0 group-hover:opacity-100 transition-opacity absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] px-2 py-0.5 rounded shadow pointer-events-none whitespace-nowrap z-20">
                        Collection: ৳{{ item.collection.toLocaleString() }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- X Axis month labels -->
            <div class="flex items-center justify-around pl-16 pt-2 text-[11px] font-medium text-muted-foreground select-none">
              <span v-for="(item, idx) in monthlyChart" :key="idx">{{ item.month }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Status Donut Chart (Col 4) -->
      <div class="lg:col-span-4 bg-card border border-border/80 rounded-2xl p-5 shadow-2xs flex flex-col justify-between">
        <h3 class="text-sm font-bold text-foreground mb-2">Order Status</h3>

        <!-- Donut SVG -->
        <div class="flex-1 flex items-center justify-center py-4">
          <div class="relative w-48 h-48 flex items-center justify-center">
            <svg class="w-full h-full -rotate-90 transform" viewBox="0 0 100 100">
              <!-- Background Ring -->
              <circle
                cx="50"
                cy="50"
                r="38"
                fill="transparent"
                stroke="currentColor"
                class="text-muted/20"
                stroke-width="15"
              />

              <!-- Delivered Segment (Green) -->
              <circle
                cx="50"
                cy="50"
                r="38"
                fill="transparent"
                stroke="#22c55e"
                stroke-width="15"
                :stroke-dasharray="`${deliveredDash} ${circumference}`"
                :stroke-dashoffset="0"
                class="transition-all duration-700 ease-out"
              />

              <!-- Due Segment (Red) -->
              <circle
                cx="50"
                cy="50"
                r="38"
                fill="transparent"
                stroke="#ef4444"
                stroke-width="15"
                :stroke-dasharray="`${dueDash} ${circumference}`"
                :stroke-dashoffset="`-${deliveredDash}`"
                class="transition-all duration-700 ease-out"
              />

              <!-- Pending Segment (Orange) -->
              <circle
                cx="50"
                cy="50"
                r="38"
                fill="transparent"
                stroke="#f59e0b"
                stroke-width="15"
                :stroke-dasharray="`${pendingDash} ${circumference}`"
                :stroke-dashoffset="`-${deliveredDash + dueDash}`"
                class="transition-all duration-700 ease-out"
              />
            </svg>

            <!-- Center Cutout / Total Count -->
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
              <span class="text-xs text-muted-foreground font-medium">Total Orders</span>
              <span class="text-xl font-bold font-bangla text-foreground">{{ totalStatusOrders }}</span>
            </div>
          </div>
        </div>

        <!-- Donut Legend -->
        <div class="flex items-center justify-center gap-6 pt-2 border-t border-border/60 text-xs">
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-xs bg-[#f59e0b] inline-block"></span>
            <span class="text-muted-foreground font-medium">Pending ({{ statusCounts.pending || 0 }})</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-xs bg-[#22c55e] inline-block"></span>
            <span class="text-muted-foreground font-medium">Delivered ({{ statusCounts.delivered || 0 }})</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-xs bg-[#ef4444] inline-block"></span>
            <span class="text-muted-foreground font-medium">Due ({{ statusCounts.due || 0 }})</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 7: RECENT ORDERS TABLE -->
    <div class="bg-card border border-border/80 rounded-2xl p-6 shadow-2xs space-y-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <ClipboardList class="h-4 w-4 text-primary" />
          <h2 class="text-base font-bold text-foreground">Recent Orders</h2>
        </div>
        <router-link to="/orders" class="text-xs text-primary hover:underline font-semibold">
          View all orders →
        </router-link>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
          <thead class="text-muted-foreground uppercase text-[11px] font-semibold border-b border-border/60">
            <tr>
              <th class="py-3 px-3 font-semibold">#</th>
              <th class="py-3 px-3 font-semibold">BD Number</th>
              <th class="py-3 px-3 font-semibold">Order Date</th>
              <th class="py-3 px-3 font-semibold text-right">Total Amount</th>
              <th class="py-3 px-3 font-semibold text-right">Due</th>
              <th class="py-3 px-3 font-semibold text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/40">
            <tr
              v-for="order in recentOrders"
              :key="order.id"
              class="hover:bg-muted/40 transition-colors"
            >
              <td class="py-3.5 px-3 font-bold text-foreground">
                #{{ order.order_number }}
              </td>
              <td class="py-3.5 px-3 text-muted-foreground font-medium">
                {{ order.bd_number || (order.customer?.bd_number) || '—' }}
              </td>
              <td class="py-3.5 px-3 text-muted-foreground">
                {{ order.order_date || '—' }}
              </td>
              <td class="py-3.5 px-3 text-right font-bold text-foreground">
                ৳{{ Number(order.total_amount || 0).toLocaleString() }}
              </td>
              <td class="py-3.5 px-3 text-right font-bold text-rose-500">
                ৳{{ Number(order.due || 0).toLocaleString() }}
              </td>
              <td class="py-3.5 px-3 text-center">
                <span
                  v-if="order.status === 'delivered'"
                  class="bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 font-semibold px-2.5 py-1 text-[11px] rounded-full inline-block"
                >
                  Delivered
                </span>
                <span
                  v-else
                  class="bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 font-semibold px-2.5 py-1 text-[11px] rounded-full inline-block"
                >
                  Pending
                </span>
              </td>
            </tr>

            <tr v-if="!recentOrders || recentOrders.length === 0">
              <td colspan="6" class="py-8 text-center text-muted-foreground text-xs">
                No recent orders found.
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
import { useRouter } from 'vue-router';
import {
  Calendar,
  Package,
  TrendingUp,
  FileText,
  Compass,
  ShoppingBag,
  DollarSign,
  Wallet,
  CreditCard,
  Users,
  Truck,
  CheckCircle2,
  ClipboardList
} from 'lucide-vue-next';
import api from '@/lib/api';

const router = useRouter();

const loading = ref(true);
const stats = ref({});
const statusCounts = ref({ pending: 0, delivered: 0, due: 0 });
const monthlyChart = ref([]);
const recentOrders = ref([]);

function navigateToResult(filterKey) {
  router.push(`/report?filter=${filterKey}`);
}

async function fetchDashboard() {
  loading.value = true;
  try {
    const res = await api.get('/dashboard');
    if (res.data?.data) {
      stats.value = res.data.data.stats || {};
      statusCounts.value = res.data.data.statusCounts || { pending: 0, delivered: 0, due: 0 };
      monthlyChart.value = res.data.data.monthlyChart || [];
      recentOrders.value = res.data.data.recentOrders || [];
    }
  } catch (err) {
    console.error('Failed to load dashboard data', err);
  } finally {
    loading.value = false;
  }
}

// Donut math
const radius = 38;
const circumference = 2 * Math.PI * radius; // ~238.76

const totalStatusOrders = computed(() => {
  const p = statusCounts.value.pending || 0;
  const d = statusCounts.value.delivered || 0;
  const du = statusCounts.value.due || 0;
  return p + d + du || 1;
});

const deliveredDash = computed(() => {
  return ((statusCounts.value.delivered || 0) / totalStatusOrders.value) * circumference;
});

const dueDash = computed(() => {
  return ((statusCounts.value.due || 0) / totalStatusOrders.value) * circumference;
});

const pendingDash = computed(() => {
  return ((statusCounts.value.pending || 0) / totalStatusOrders.value) * circumference;
});

onMounted(() => {
  fetchDashboard();
});
</script>
