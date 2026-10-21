<template>
  <div class="space-y-6">
    <!-- Header with Back Button and Worker Details -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <router-link
          to="/staff"
          class="h-9 w-9 inline-flex items-center justify-center rounded-lg border border-input bg-background hover:bg-accent text-foreground transition-colors"
        >
          <ArrowLeft class="h-4 w-4" />
        </router-link>
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-2xl font-heading font-bold text-foreground">
              {{ worker ? (showLang === 'bn' ? worker.name_bn : worker.name_en) : t('workerEntries') }}
            </h1>
            <span
              v-if="currentMonthBadge"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all shadow-xs"
              :style="{
                backgroundColor: `hsl(var(--month-${activeMonthNumber}))`,
                color: `hsl(var(--month-${activeMonthNumber}-fg))`,
                border: `1px solid hsl(var(--month-${activeMonthNumber}-fg) / 0.3)`,
              }"
            >
              <Folder class="h-3.5 w-3.5" />
              <span>{{ currentMonthBadge }}</span>
            </span>

            <span
              v-if="worker?.deleted_at"
              class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30"
            >
              <Archive class="h-3.5 w-3.5" />
              <span>{{ showLang === 'bn' ? 'আর্কাইভকৃত স্টাফ (হিস্ট্রি সংরক্ষিত)' : 'Archived Staff (History Preserved)' }}</span>
            </span>
          </div>
          <p class="text-muted-foreground text-xs mt-0.5">
            {{ worker ? (showLang === 'bn' ? worker.name_en : worker.name_bn) : '' }}
          </p>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button
          v-if="worker?.deleted_at"
          type="button"
          @click="restoreCurrentWorker"
          :disabled="restoringWorker"
          class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/25 text-xs font-bold transition-colors"
          title="Restore worker to active staff list"
        >
          <Loader2 v-if="restoringWorker" class="h-4 w-4 animate-spin" />
          <RotateCcw v-else class="h-4 w-4" />
          {{ showLang === 'bn' ? 'স্টাফ সক্রিয় করুন' : 'Restore Staff' }}
        </button>

        <SaveStatusBadge :status="autoSaveStatus" />

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
          @click="downloadPDF"
          class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-input bg-background hover:bg-accent text-xs font-medium transition-colors"
        >
          <FileText class="h-4 w-4 text-primary" />
          PDF
        </button>

        <button
          type="button"
          @click="saveAll"
          :disabled="saving"
          class="inline-flex items-center gap-1.5 h-9 px-4 rounded-lg bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 transition-colors disabled:opacity-50"
        >
          <Loader2 v-if="saving" class="h-4 w-4 animate-spin" />
          <Save v-else class="h-4 w-4" />
          {{ t('save') }}
        </button>
      </div>
    </div>

    <!-- Prominent Month Soft-Colored Hero Banner & Quick Switcher -->
    <div
      class="rounded-2xl p-4 sm:p-5 border transition-all duration-300 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4"
      :style="{
        backgroundColor: `hsl(var(--month-${activeMonthNumber}))`,
        color: `hsl(var(--month-${activeMonthNumber}-fg))`,
        borderColor: `hsl(var(--month-${activeMonthNumber}-fg) / 0.28)`,
      }"
    >
      <!-- Left: Month Name & Worker Info -->
      <div class="flex items-center gap-3.5">
        <div
          class="h-12 w-12 rounded-xl flex items-center justify-center shrink-0 shadow-xs"
          :style="{
            backgroundColor: `hsl(var(--month-${activeMonthNumber}-fg) / 0.12)`,
            color: `hsl(var(--month-${activeMonthNumber}-fg))`,
            border: `1px solid hsl(var(--month-${activeMonthNumber}-fg) / 0.25)`,
          }"
        >
          <Folder class="h-6 w-6" />
        </div>
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="text-xl sm:text-2xl font-heading font-extrabold tracking-tight">
              {{ activeMonthInfo.label }}
            </h2>
            <span
              class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider"
              :style="{
                backgroundColor: `hsl(var(--month-${activeMonthNumber}-fg) / 0.15)`,
                color: `hsl(var(--month-${activeMonthNumber}-fg))`,
              }"
            >
              {{ showLang === 'bn' ? 'স্টাফ এন্ট্রি শিট' : 'Staff Entry Sheet' }}
            </span>
          </div>
          <div class="flex items-center gap-3 text-xs mt-1 font-medium opacity-90 flex-wrap">
            <span>{{ worker ? (showLang === 'bn' ? worker.name_bn : worker.name_en) : '' }}</span>
            <span>•</span>
            <span>{{ filledEntriesCount }} {{ showLang === 'bn' ? 'টি এন্ট্রি' : 'entries' }}</span>
            <span>•</span>
            <span>{{ showLang === 'bn' ? 'মোট কাজ:' : 'Total Work:' }} ৳{{ totals.totalAmount.toLocaleString() }}</span>
            <span>•</span>
            <span class="font-bold underline decoration-current/40">
              {{ showLang === 'bn' ? 'নীট পাওনা:' : 'Net Payable:' }} ৳{{ totals.netPayable.toLocaleString() }}
            </span>
          </div>
        </div>
      </div>

      <!-- Right: Year & 12 Month Quick Switcher -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 self-stretch lg:self-auto">
        <!-- Year Navigator -->
        <div
          class="flex items-center rounded-xl px-2 py-1 gap-1 border shrink-0 text-xs font-bold"
          :style="{
            backgroundColor: `hsl(var(--month-${activeMonthNumber}-fg) / 0.1)`,
            borderColor: `hsl(var(--month-${activeMonthNumber}-fg) / 0.25)`,
            color: `hsl(var(--month-${activeMonthNumber}-fg))`,
          }"
        >
          <button
            type="button"
            @click="prevYear"
            title="Previous Year"
            class="h-6 w-6 inline-flex items-center justify-center rounded hover:bg-black/10 dark:hover:bg-white/10 transition-colors"
          >
            <ChevronLeft class="h-3.5 w-3.5" />
          </button>
          <span class="px-1 min-w-[36px] text-center font-extrabold">{{ selectedYear }}</span>
          <button
            type="button"
            @click="nextYear"
            title="Next Year"
            class="h-6 w-6 inline-flex items-center justify-center rounded hover:bg-black/10 dark:hover:bg-white/10 transition-colors"
          >
            <ChevronRight class="h-3.5 w-3.5" />
          </button>
        </div>

        <!-- 12 Month Pill Switcher -->
        <div class="grid grid-cols-6 sm:flex sm:flex-wrap items-center gap-1 w-full sm:w-auto">
          <button
            v-for="(m, i) in (showLang === 'bn' ? MONTHS_SHORT_BN : MONTHS_SHORT_EN)"
            :key="i"
            type="button"
            @click="selectMonth(i, selectedYear)"
            class="px-2 py-1 rounded-lg text-[11px] font-bold text-center transition-all cursor-pointer whitespace-nowrap"
            :class="activeMonthIndex === i ? 'ring-2 ring-current shadow-xs scale-105' : 'opacity-75 hover:opacity-100 hover:scale-102'"
            :style="{
              backgroundColor: `hsl(var(--month-${i + 1}))`,
              color: `hsl(var(--month-${i + 1}-fg))`,
              border: `1px solid hsl(var(--month-${i + 1}-fg) / ${activeMonthIndex === i ? '0.6' : '0.2'})`,
            }"
            :title="showLang === 'bn' ? MONTHS_BN[i] : MONTHS_EN[i]"
          >
            {{ m }}
          </button>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="rounded-xl border border-border bg-card p-4 shadow-sm flex flex-wrap items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-1.5 text-xs">
          <label class="font-medium text-muted-foreground">{{ t('from') }}:</label>
          <input
            v-model="filterFrom"
            type="date"
            class="h-8 px-2 rounded-md border border-input bg-background text-xs"
          />
        </div>
        <div class="flex items-center gap-1.5 text-xs">
          <label class="font-medium text-muted-foreground">{{ t('to') }}:</label>
          <input
            v-model="filterTo"
            type="date"
            class="h-8 px-2 rounded-md border border-input bg-background text-xs"
          />
        </div>
        <button
          v-if="filterFrom || filterTo"
          type="button"
          @click="clearDateFilter"
          class="h-8 px-2 text-xs text-muted-foreground hover:text-foreground inline-flex items-center gap-1 cursor-pointer"
        >
          <X class="h-3 w-3" /> {{ t('clearFilter') }}
        </button>

        <!-- Sort Control -->
        <div class="flex items-center gap-1.5 text-xs pl-2 border-l border-border/70">
          <label class="font-medium text-muted-foreground flex items-center gap-1">
            <ArrowUpDown class="h-3.5 w-3.5 text-primary" />
            <span>{{ showLang === 'bn' ? 'সাজান' : 'Sort' }}:</span>
          </label>
          <select
            v-model="sortOption"
            @change="applySortOption"
            class="h-8 px-2.5 text-xs rounded-lg border border-input bg-background text-foreground font-medium focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer"
          >
            <option value="date_asc_serial_asc">
              {{ showLang === 'bn' ? 'তারিখ ও সিরিয়াল (১, ২, ৩...)' : 'Date & Serial (Ascending)' }}
            </option>
            <option value="date_desc_serial_asc">
              {{ showLang === 'bn' ? 'তারিখ (নতুন আগে) ও সিরিয়াল' : 'Date (Newest) & Serial' }}
            </option>
            <option value="serial_asc">
              {{ showLang === 'bn' ? 'শুধু সিরিয়াল (১ → ৯৯)' : 'Serial (1 → 99)' }}
            </option>
            <option value="serial_desc">
              {{ showLang === 'bn' ? 'শুধু সিরিয়াল (৯৯ → ১)' : 'Serial (99 → 1)' }}
            </option>
          </select>
          <button
            type="button"
            @click="sortEntriesInPlace"
            title="Sort sheet rows in order"
            class="h-8 px-2.5 rounded-lg border border-border bg-muted/40 hover:bg-muted text-xs font-semibold inline-flex items-center gap-1 text-foreground transition-colors cursor-pointer"
          >
            <CheckCheck class="h-3.5 w-3.5 text-primary" />
            <span>{{ showLang === 'bn' ? 'শিট সাজান' : 'Sort Sheet' }}</span>
          </button>
        </div>
      </div>

      <div class="relative w-full sm:w-64">
        <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
        <input
          v-model="nameSearch"
          :placeholder="showLang === 'bn' ? 'কাস্টমার নাম খুঁজুন...' : 'Search customer...'"
          class="w-full h-8 pl-8 pr-3 text-xs rounded-lg border border-input bg-background focus:outline-none focus:ring-1 focus:ring-ring"
        />
      </div>
    </div>

    <!-- Entries Spreadsheet Grid -->
    <div
      class="rounded-xl border shadow-sm overflow-hidden transition-colors"
      :style="{
        borderColor: `hsl(var(--month-${activeMonthNumber}-fg) / 0.28)`,
      }"
    >
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left border-collapse">
          <thead
            class="border-b uppercase text-[11px] font-bold select-none transition-colors"
            :style="{
              backgroundColor: `hsl(var(--month-${activeMonthNumber}))`,
              color: `hsl(var(--month-${activeMonthNumber}-fg))`,
              borderColor: `hsl(var(--month-${activeMonthNumber}-fg) / 0.28)`,
            }"
          >
            <tr>
              <!-- Date Header (Sortable) -->
              <th
                @click="toggleSort('date')"
                class="px-2 py-2.5 w-28 cursor-pointer hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                title="Click to sort by date"
              >
                <div class="flex items-center gap-1">
                  <span>{{ t('date') }}</span>
                  <component :is="getSortIcon('date')" class="h-3 w-3" :class="activeSortKey === 'date' || activeSortKey === 'date_serial' ? 'opacity-100 font-bold' : 'opacity-40'" />
                </div>
              </th>

              <!-- Serial Header (Sortable) -->
              <th
                @click="toggleSort('serial')"
                class="px-2 py-2.5 w-20 text-center cursor-pointer hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                title="Click to sort by serial"
              >
                <div class="flex items-center justify-center gap-1">
                  <span>{{ t('serial') }}</span>
                  <component :is="getSortIcon('serial')" class="h-3 w-3" :class="activeSortKey === 'serial' ? 'opacity-100 font-bold' : 'opacity-40'" />
                </div>
              </th>

              <!-- Customer Name Header (Sortable) -->
              <th
                @click="toggleSort('name')"
                class="px-2 py-2.5 min-w-[140px] cursor-pointer hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                title="Click to sort by customer name"
              >
                <div class="flex items-center gap-1">
                  <span>{{ t('customerName') }}</span>
                  <component :is="getSortIcon('name')" class="h-3 w-3" :class="activeSortKey === 'name' ? 'opacity-100 font-bold' : 'opacity-40'" />
                </div>
              </th>

              <!-- Item Header (Sortable) -->
              <th
                @click="toggleSort('item')"
                class="px-2 py-2.5 min-w-[150px] cursor-pointer hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                title="Click to sort by item"
              >
                <div class="flex items-center gap-1">
                  <span>{{ t('item') }}</span>
                  <component :is="getSortIcon('item')" class="h-3 w-3" :class="activeSortKey === 'item' ? 'opacity-100 font-bold' : 'opacity-40'" />
                </div>
              </th>

              <th class="px-2 py-2.5 w-16 text-right">{{ t('qty') }}</th>
              <th class="px-2 py-2.5 w-20 text-right">{{ t('rate') }}</th>

              <!-- Total Header (Sortable) -->
              <th
                @click="toggleSort('total')"
                class="px-2 py-2.5 w-24 text-right cursor-pointer hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                title="Click to sort by total"
              >
                <div class="flex items-center justify-end gap-1">
                  <span>{{ t('total') }}</span>
                  <component :is="getSortIcon('total')" class="h-3 w-3" :class="activeSortKey === 'total' ? 'opacity-100 font-bold' : 'opacity-40'" />
                </div>
              </th>

              <th class="px-2 py-2.5 w-24 text-right">{{ t('khoroch') }}</th>
              <th class="px-2 py-2.5 w-28">{{ t('khorochDate') }}</th>
              <th class="px-2 py-2.5 w-10 text-center"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/60">
            <tr v-if="loading">
              <td colspan="10" class="text-center py-12">
                <Loader2 class="h-6 w-6 animate-spin text-primary mx-auto" />
              </td>
            </tr>
            <tr
              v-else
              v-for="(row, idx) in visibleEntries"
              :key="row.id || idx"
              class="hover:bg-muted/40 transition-colors"
            >
              <!-- Date -->
              <td class="p-1">
                <input
                  type="date"
                  v-model="row.entry_date"
                  @change="onRowChange(row)"
                  class="w-full h-7 px-1.5 rounded border border-input/60 bg-transparent text-xs"
                />
              </td>

              <!-- Serial -->
              <td class="p-1">
                <input
                  type="number"
                  v-model.number="row.serial"
                  @input="onRowChange(row)"
                  class="w-full h-7 px-1 text-center rounded border border-input/60 bg-transparent text-xs"
                />
              </td>

              <!-- Name -->
              <td class="p-1">
                <input
                  type="text"
                  v-model="row.name"
                  placeholder="Customer name"
                  @input="onRowChange(row)"
                  class="w-full h-7 px-2 rounded border border-input/60 bg-transparent text-xs"
                />
              </td>

              <!-- Item Catalog Dropdown -->
              <td class="p-1">
                <WorkerItemCatalogDropdown
                  :value="row.item"
                  @select="(itemName, price) => handleItemSelect(row, itemName, price)"
                  class-name="h-7 text-xs"
                />
              </td>

              <!-- Quantity -->
              <td class="p-1">
                <input
                  type="number"
                  v-model.number="row.quantity"
                  @input="recalculateRow(row)"
                  class="w-full h-7 px-1.5 text-right rounded border border-input/60 bg-transparent text-xs"
                />
              </td>

              <!-- Rate / Unit Price -->
              <td class="p-1">
                <input
                  type="number"
                  step="any"
                  v-model.number="row.unit_price"
                  @input="recalculateRow(row)"
                  class="w-full h-7 px-1.5 text-right rounded border border-input/60 bg-transparent text-xs"
                />
              </td>

              <!-- Total Taka (Read only / calculated) -->
              <td class="p-1 text-right font-medium px-2 text-foreground">
                ৳{{ (Number(row.quantity || 0) * Number(row.unit_price || 0)).toLocaleString() }}
              </td>

              <!-- Khoroch -->
              <td class="p-1">
                <input
                  type="number"
                  step="any"
                  v-model.number="row.khoroch"
                  @input="onRowChange(row)"
                  placeholder="0"
                  class="w-full h-7 px-1.5 text-right rounded border border-input/60 bg-transparent text-xs text-amber-600 font-medium"
                />
              </td>

              <!-- Khoroch Date -->
              <td class="p-1">
                <input
                  type="date"
                  v-model="row.khoroch_date"
                  @change="onRowChange(row)"
                  class="w-full h-7 px-1.5 rounded border border-input/60 bg-transparent text-xs"
                />
              </td>

              <!-- Delete Action -->
              <td class="p-1 text-center">
                <button
                  type="button"
                  @click="deleteRow(row, idx)"
                  class="h-7 w-7 inline-flex items-center justify-center rounded text-muted-foreground hover:text-destructive hover:bg-destructive/10"
                >
                  <Trash2 class="h-3.5 w-3.5" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add Rows Bar -->
      <div
        class="p-3 border-t flex items-center justify-between transition-colors"
        :style="{
          backgroundColor: `hsl(var(--month-${activeMonthNumber}) / 0.18)`,
          borderColor: `hsl(var(--month-${activeMonthNumber}-fg) / 0.2)`,
        }"
      >
        <button
          type="button"
          @click="addRows(10)"
          class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-input bg-card hover:bg-accent text-xs font-semibold transition-colors shadow-xs"
        >
          <Plus class="h-3.5 w-3.5" /> {{ t('add10Rows') }}
        </button>

        <span class="text-xs font-medium" :style="{ color: `hsl(var(--month-${activeMonthNumber}-fg))` }">
          {{ visibleEntries.length }} {{ t('entriesCount') }}
        </span>
      </div>
    </div>

    <!-- Financial Balance Footer -->
    <div
      class="rounded-xl border p-4 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 transition-all mb-4"
      :style="{
        backgroundColor: `hsl(var(--month-${activeMonthNumber}) / 0.22)`,
        borderColor: `hsl(var(--month-${activeMonthNumber}-fg) / 0.3)`,
      }"
    >
      <div class="grid grid-cols-3 gap-2 sm:flex sm:flex-wrap sm:items-center sm:gap-6 text-center sm:text-left">
        <div class="min-w-0">
          <span class="text-xs text-muted-foreground block truncate">{{ t('totalWork') }}</span>
          <span class="text-sm sm:text-lg font-bold text-foreground font-mono truncate block" :title="'৳' + totals.totalAmount.toLocaleString()">৳{{ totals.totalAmount.toLocaleString() }}</span>
        </div>
        <div class="min-w-0">
          <span class="text-xs text-muted-foreground block truncate">{{ t('totalKhoroch') }}</span>
          <span class="text-sm sm:text-lg font-bold text-amber-600 font-mono truncate block" :title="'৳' + totals.totalKhoroch.toLocaleString()">৳{{ totals.totalKhoroch.toLocaleString() }}</span>
        </div>
        <div class="min-w-0">
          <span class="text-xs text-muted-foreground block truncate">{{ t('netPayable') }}</span>
          <span
            class="text-sm sm:text-xl font-extrabold font-mono truncate block"
            :style="{
              color: `hsl(var(--month-${activeMonthNumber}-fg))`,
            }"
            :title="'৳' + totals.netPayable.toLocaleString()"
          >
            ৳{{ totals.netPayable.toLocaleString() }}
          </span>
        </div>
      </div>

      <div class="flex items-center gap-2 w-full sm:w-auto">
        <button
          type="button"
          @click="saveAll"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 h-10 sm:h-9 px-5 rounded-lg bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 transition-colors shadow-xs cursor-pointer"
        >
          <Save class="h-4 w-4" /> {{ t('saveAll') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  ArrowLeft, Save, Globe, Search, Loader2, Plus, Trash2, X, FileText,
  ArrowUpDown, ArrowUp, ArrowDown, CheckCheck, Calendar, Folder,
  ChevronLeft, ChevronRight, Archive, RotateCcw
} from 'lucide-vue-next';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import { hindSiliguriBoldBase64 } from '@/lib/hindSiliguriBold';
import { t, currentLang } from '@/lib/language';
import { computeWorkerTotals, isFilledEntry, WORKER_MONTH_FILTER_KEY } from '@/lib/workerTotals';
import SaveStatusBadge from '@/components/SaveStatusBadge.vue';
import WorkerItemCatalogDropdown from '@/components/WorkerItemCatalogDropdown.vue';
import api from '@/lib/api';

const MONTHS_EN = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
];
const MONTHS_BN = [
  'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন',
  'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'
];

const MONTHS_SHORT_EN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const MONTHS_SHORT_BN = ['জানু', 'ফেব', 'মার্চ', 'এপ্রি', 'মে', 'জুন', 'জুলা', 'আগস্ট', 'সেপ্টে', 'অক্টো', 'নভে', 'ডিসে'];

const MONTH_PDF_COLORS = [
  { headBg: [218, 235, 252], headFg: [28, 64, 110] }, // Month 1: Jan (Soft Blue)
  { headBg: [253, 226, 233], headFg: [128, 32, 58] }, // Month 2: Feb (Soft Red)
  { headBg: [216, 246, 225], headFg: [32, 95, 52] },  // Month 3: Mar (Soft Green)
  { headBg: [254, 247, 214], headFg: [118, 86, 22] }, // Month 4: Apr (Soft Yellow)
  { headBg: [254, 233, 220], headFg: [118, 55, 30] }, // Month 5: May (Soft Peach)
  { headBg: [252, 224, 245], headFg: [118, 35, 95] }, // Month 6: Jun (Soft Rose)
  { headBg: [228, 246, 219], headFg: [48, 92, 35] },  // Month 7: Jul (Soft Sage)
  { headBg: [240, 228, 253], headFg: [85, 48, 122] }, // Month 8: Aug (Soft Lavender)
  { headBg: [254, 238, 216], headFg: [118, 70, 28] }, // Month 9: Sep (Soft Apricot)
  { headBg: [219, 248, 246], headFg: [32, 90, 88] },  // Month 10: Oct (Soft Teal)
  { headBg: [253, 228, 224], headFg: [122, 42, 35] }, // Month 11: Nov (Soft Terracotta)
  { headBg: [228, 233, 253], headFg: [42, 58, 122] }, // Month 12: Dec (Soft Periwinkle)
];

const route = useRoute();
const router = useRouter();
const workerId = route.params.id;

const showLang = ref(currentLang.value === 'bn' ? 'bn' : 'en');
const worker = ref(null);
const entries = ref([]);
const loading = ref(true);
const saving = ref(false);
const autoSaveStatus = ref('idle'); // 'idle' | 'saving' | 'saved' | 'error'

const filterFrom = ref('');
const filterTo = ref('');
const nameSearch = ref('');
const selectedYear = ref(new Date().getFullYear());

function getActiveMonthInfo(isBn = false) {
  // 1. Check if route.query.month is set (e.g. "2026-3" for April)
  const queryMonth = route.query.month;
  if (queryMonth && queryMonth !== 'all') {
    const parts = queryMonth.split('-');
    if (parts.length === 2) {
      const y = Number(parts[0]);
      const m = Number(parts[1]);
      if (!isNaN(y) && !isNaN(m) && m >= 0 && m < 12) {
        const monthName = isBn ? MONTHS_BN[m] : MONTHS_EN[m];
        return {
          monthIndex: m,
          year: y,
          monthName,
          label: `${monthName} ${y}`,
        };
      }
    }
  }

  // 2. Check filterFrom and filterTo
  if (filterFrom.value) {
    const partsFrom = filterFrom.value.split('-').map(Number);
    if (partsFrom.length >= 2 && !isNaN(partsFrom[0]) && !isNaN(partsFrom[1])) {
      const yFrom = partsFrom[0];
      const mFrom = partsFrom[1] - 1;

      if (filterTo.value) {
        const partsTo = filterTo.value.split('-').map(Number);
        if (partsTo.length >= 2 && !isNaN(partsTo[0]) && !isNaN(partsTo[1])) {
          const yTo = partsTo[0];
          const mTo = partsTo[1] - 1;

          if (mFrom === mTo && yFrom === yTo && mFrom >= 0 && mFrom < 12) {
            const monthName = isBn ? MONTHS_BN[mFrom] : MONTHS_EN[mFrom];
            return {
              monthIndex: mFrom,
              year: yFrom,
              monthName,
              label: `${monthName} ${yFrom}`,
            };
          } else if (mFrom >= 0 && mFrom < 12 && mTo >= 0 && mTo < 12) {
            const fromName = isBn ? MONTHS_BN[mFrom] : MONTHS_EN[mFrom];
            const toName = isBn ? MONTHS_BN[mTo] : MONTHS_EN[mTo];
            return {
              monthIndex: mFrom,
              year: yFrom,
              monthName: `${fromName} - ${toName}`,
              label: `${fromName} ${yFrom} - ${toName} ${yTo}`,
            };
          }
        }
      }

      if (mFrom >= 0 && mFrom < 12) {
        const monthName = isBn ? MONTHS_BN[mFrom] : MONTHS_EN[mFrom];
        return {
          monthIndex: mFrom,
          year: yFrom,
          monthName,
          label: `${monthName} ${yFrom}`,
        };
      }
    }
  }

  // 3. Fallback: inspect first filled entry date
  const filled = entries.value.filter(isFilledEntry);
  if (filled.length > 0 && filled[0].entry_date) {
    const parts = filled[0].entry_date.split('-').map(Number);
    if (parts.length >= 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
      const y = parts[0];
      const m = parts[1] - 1;
      if (m >= 0 && m < 12) {
        const monthName = isBn ? MONTHS_BN[m] : MONTHS_EN[m];
        return {
          monthIndex: m,
          year: y,
          monthName,
          label: `${monthName} ${y}`,
        };
      }
    }
  }

  return {
    monthIndex: null,
    year: null,
    monthName: isBn ? 'সকল মাস' : 'All Months',
    label: isBn ? 'সকল মাস' : 'All Months',
  };
}

const activeMonthInfo = computed(() => {
  return getActiveMonthInfo(showLang.value === 'bn');
});

const activeMonthIndex = computed(() => {
  return (activeMonthInfo.value.monthIndex !== null && activeMonthInfo.value.monthIndex !== undefined)
    ? activeMonthInfo.value.monthIndex
    : null;
});

const activeMonthNumber = computed(() => {
  if (activeMonthIndex.value !== null && activeMonthIndex.value >= 0 && activeMonthIndex.value < 12) {
    return activeMonthIndex.value + 1;
  }
  return 1; // Default to 1 (January soft blue)
});

const currentMonthBadge = computed(() => {
  return activeMonthInfo.value.label || '';
});

const filledEntriesCount = computed(() => {
  return visibleEntries.value.filter(isFilledEntry).length;
});

function selectMonth(mIndex, year = selectedYear.value) {
  selectedYear.value = year;
  const value = `${year}-${mIndex}`;
  localStorage.setItem(WORKER_MONTH_FILTER_KEY, value);
  const mm = String(mIndex + 1).padStart(2, '0');
  filterFrom.value = `${year}-${mm}-01`;
  const lastDay = new Date(year, mIndex + 1, 0).getDate();
  filterTo.value = `${year}-${mm}-${String(lastDay).padStart(2, '0')}`;
  router.replace({ query: { ...route.query, month: value } });
}

function prevYear() {
  selectedYear.value--;
  const targetMonth = activeMonthIndex.value !== null ? activeMonthIndex.value : 0;
  selectMonth(targetMonth, selectedYear.value);
}

function nextYear() {
  selectedYear.value++;
  const targetMonth = activeMonthIndex.value !== null ? activeMonthIndex.value : 0;
  selectMonth(targetMonth, selectedYear.value);
}

// Sorting State
const sortOption = ref('date_asc_serial_asc');
const activeSortKey = ref('date_serial');
const sortDirection = ref('asc'); // 'asc' | 'desc'

let autoSaveTimer = null;

function getSortIcon(col) {
  if (activeSortKey.value === col) {
    return sortDirection.value === 'asc' ? ArrowUp : ArrowDown;
  }
  if (activeSortKey.value === 'date_serial' && col === 'date') {
    return sortDirection.value === 'asc' ? ArrowUp : ArrowDown;
  }
  return ArrowUpDown;
}

function toggleSort(col) {
  if (activeSortKey.value === col) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    activeSortKey.value = col;
    sortDirection.value = 'asc';
  }

  if (col === 'date') {
    sortOption.value = sortDirection.value === 'asc' ? 'date_asc_serial_asc' : 'date_desc_serial_asc';
  } else if (col === 'serial') {
    sortOption.value = sortDirection.value === 'asc' ? 'serial_asc' : 'serial_desc';
  }

  sortEntriesInPlace();
}

function applySortOption() {
  if (sortOption.value === 'date_asc_serial_asc') {
    activeSortKey.value = 'date_serial';
    sortDirection.value = 'asc';
  } else if (sortOption.value === 'date_desc_serial_asc') {
    activeSortKey.value = 'date_serial';
    sortDirection.value = 'desc';
  } else if (sortOption.value === 'serial_asc') {
    activeSortKey.value = 'serial';
    sortDirection.value = 'asc';
  } else if (sortOption.value === 'serial_desc') {
    activeSortKey.value = 'serial';
    sortDirection.value = 'desc';
  }
  sortEntriesInPlace();
}

function sortComparator(a, b) {
  const isFilledA = isFilledEntry(a);
  const isFilledB = isFilledEntry(b);

  // Empty rows always stay at the bottom
  if (!isFilledA && !isFilledB) return 0;
  if (!isFilledA) return 1;
  if (!isFilledB) return -1;

  const dir = sortDirection.value === 'asc' ? 1 : -1;

  if (activeSortKey.value === 'date_serial') {
    const dateA = a.entry_date || '';
    const dateB = b.entry_date || '';
    if (dateA !== dateB) {
      return dateA.localeCompare(dateB) * dir;
    }
    const serA = Number(a.serial || 0);
    const serB = Number(b.serial || 0);
    return serA - serB;
  }

  if (activeSortKey.value === 'date') {
    const dateA = a.entry_date || '';
    const dateB = b.entry_date || '';
    if (dateA !== dateB) {
      return dateA.localeCompare(dateB) * dir;
    }
    const serA = Number(a.serial || 0);
    const serB = Number(b.serial || 0);
    return serA - serB;
  }

  if (activeSortKey.value === 'serial') {
    const serA = Number(a.serial || 0);
    const serB = Number(b.serial || 0);
    if (serA !== serB) {
      return (serA - serB) * dir;
    }
    const dateA = a.entry_date || '';
    const dateB = b.entry_date || '';
    return dateA.localeCompare(dateB);
  }

  if (activeSortKey.value === 'name') {
    const nameA = (a.name || '').trim().toLowerCase();
    const nameB = (b.name || '').trim().toLowerCase();
    return nameA.localeCompare(nameB) * dir;
  }

  if (activeSortKey.value === 'item') {
    const itemA = (a.item || '').trim().toLowerCase();
    const itemB = (b.item || '').trim().toLowerCase();
    return itemA.localeCompare(itemB) * dir;
  }

  if (activeSortKey.value === 'total') {
    const totA = Number(a.quantity || 0) * Number(a.unit_price || 0);
    const totB = Number(b.quantity || 0) * Number(b.unit_price || 0);
    return (totA - totB) * dir;
  }

  return 0;
}

function sortEntriesInPlace() {
  entries.value.sort(sortComparator);
}

const visibleEntries = computed(() => {
  const filtered = entries.value.filter((e) => {
    // Name filter
    if (nameSearch.value.trim()) {
      const q = nameSearch.value.trim().toLowerCase();
      if (!(e.name || '').toLowerCase().includes(q)) return false;
    }
    // Date from
    if (filterFrom.value && e.entry_date < filterFrom.value) return false;
    // Date to
    if (filterTo.value && e.entry_date > filterTo.value) return false;
    return true;
  });

  return [...filtered].sort(sortComparator);
});

const totals = computed(() => {
  return computeWorkerTotals(visibleEntries.value);
});

function generateEmptyRows(count = 10) {
  let defaultDate = new Date().toISOString().split('T')[0];
  if (filterFrom.value) {
    defaultDate = filterFrom.value;
  }
  const maxSerial = entries.value.reduce((max, e) => {
    const s = Number(e.serial || 0);
    return isFilledEntry(e) && s > max ? s : max;
  }, 0);

  const rows = [];
  for (let i = 0; i < count; i++) {
    rows.push({
      id: crypto.randomUUID(),
      worker_id: workerId,
      entry_date: defaultDate,
      serial: maxSerial + i + 1,
      name: '',
      item: '',
      quantity: 1,
      unit_price: 0,
      total_taka: 0,
      khoroch: 0,
      khoroch_date: null,
    });
  }
  return rows;
}

function addRows(count = 10) {
  entries.value.push(...generateEmptyRows(count));
}

function handleItemSelect(row, itemName, price) {
  row.item = itemName;
  if (!row.unit_price || row.unit_price === 0) {
    row.unit_price = price;
  }
  recalculateRow(row);
}

function recalculateRow(row) {
  row.total_taka = Number(row.quantity || 0) * Number(row.unit_price || 0);
  triggerAutoSave();
}

function onRowChange(row) {
  triggerAutoSave();
}

function triggerAutoSave() {
  if (autoSaveTimer) clearTimeout(autoSaveTimer);
  autoSaveStatus.value = 'pending';
  autoSaveTimer = setTimeout(async () => {
    await saveFilled();
  }, 2000);
}

async function saveFilled() {
  const filled = entries.value.filter(isFilledEntry);
  if (filled.length === 0) {
    autoSaveStatus.value = 'idle';
    return;
  }

  autoSaveStatus.value = 'saving';
  try {
    const res = await api.post('/worker-entries/bulk-upsert', {
      worker_id: workerId,
      entries: filled,
    });
    if (res.data?.data && Array.isArray(res.data.data)) {
      res.data.data.forEach((saved, idx) => {
        if (filled[idx] && saved.id) {
          filled[idx].id = saved.id;
        }
      });
    }
    autoSaveStatus.value = 'saved';
    setTimeout(() => {
      if (autoSaveStatus.value === 'saved') autoSaveStatus.value = 'idle';
    }, 2500);
  } catch (err) {
    console.error('Failed to auto-save worker entries', err);
    autoSaveStatus.value = 'error';
  }
}

async function saveAll() {
  saving.value = true;
  await saveFilled();
  saving.value = false;
}

async function deleteRow(row, idx) {
  if (row.id) {
    try {
      await api.delete(`/worker-entries/${row.id}`);
    } catch (err) {
      // ignore
    }
  }
  const realIdx = entries.value.indexOf(row);
  if (realIdx !== -1) {
    entries.value.splice(realIdx, 1);
  }
}

function clearDateFilter() {
  filterFrom.value = '';
  filterTo.value = '';
}

const restoringWorker = ref(false);

async function restoreCurrentWorker() {
  if (!worker.value?.id) return;
  restoringWorker.value = true;
  try {
    await api.post(`/workers/${worker.value.id}/restore`);
    await fetchWorker();
  } catch (err) {
    console.error('Failed to restore worker', err);
  } finally {
    restoringWorker.value = false;
  }
}

async function fetchWorker() {
  try {
    const res = await api.get(`/workers/${workerId}`);
    worker.value = res.data.data;
  } catch (err) {
    console.error('Failed to fetch worker details', err);
  }
}

async function fetchEntries() {
  loading.value = true;
  try {
    const res = await api.get('/worker-entries', {
      params: {
        worker_id: workerId,
        sort_by: 'date_serial',
        sort_dir: 'asc',
      }
    });
    const fetched = res.data.data || [];
    if (fetched.length === 0) {
      entries.value = generateEmptyRows(15);
    } else {
      // Sort fetched entries serial-wise & date-wise
      entries.value = [...fetched].sort(sortComparator);
    }
  } catch (err) {
    console.error('Failed to fetch worker entries', err);
    entries.value = generateEmptyRows(15);
  } finally {
    loading.value = false;
  }
}

function downloadPDF() {
  const doc = new jsPDF();
  const pageWidth = doc.internal.pageSize.getWidth();
  const pageHeight = doc.internal.pageSize.getHeight();

  // Register Hind Siliguri Bold for Bengali text
  doc.addFileToVFS('HindSiliguri-Bold.ttf', hindSiliguriBoldBase64);
  doc.addFont('HindSiliguri-Bold.ttf', 'HindSiliguri', 'normal');
  doc.addFont('HindSiliguri-Bold.ttf', 'HindSiliguri', 'bold');

  const isBn = showLang.value === 'bn';
  const monthInfo = getActiveMonthInfo(isBn);
  const monthColors = MONTH_PDF_COLORS[(activeMonthNumber.value || 1) - 1] || MONTH_PDF_COLORS[0];

  // Worker name (Left side)
  const workerName = worker.value
    ? isBn
      ? (worker.value.name_bn || worker.value.name_en)
      : (worker.value.name_en || worker.value.name_bn)
    : '';

  // Month Name (PROMINENT & BOLD in month color)
  const monthLabel = isBn ? `মাস: ${monthInfo.label}` : `Month: ${monthInfo.label}`;

  // Business branding (Page 1)
  doc.setFontSize(14);
  doc.setFont('helvetica', 'bold');
  doc.setTextColor(15, 23, 42);
  doc.text('ONE RAYMOND TAILORS & FABRICS', pageWidth / 2, 13, { align: 'center' });
  doc.setFontSize(8);
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(100, 116, 139);
  doc.text('Base tailor shop. BAF base Bashar, Dhaka', pageWidth / 2, 17.5, { align: 'center' });

  // Divider line
  doc.setDrawColor(210, 218, 230);
  doc.setLineWidth(0.4);
  doc.line(14, 21, pageWidth - 14, 21);

  // Worker name (Left side - Page 1)
  doc.setFontSize(13);
  if (/[\u0980-\u09FF]/.test(workerName)) {
    doc.setFont('HindSiliguri', 'bold');
  } else {
    doc.setFont('helvetica', 'bold');
  }
  doc.setTextColor(15, 23, 42);
  doc.text(workerName, 14, 28);

  // Subtitle under worker name (Left side - Page 1)
  doc.setFontSize(8.5);
  if (isBn) {
    doc.setFont('HindSiliguri', 'normal');
    doc.setTextColor(100, 116, 139);
    doc.text('স্টাফ কাজের হিসাব বিবরণী', 14, 33);
  } else {
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(100, 116, 139);
    doc.text('Staff Work Entry Sheet', 14, 33);
  }

  // Month Name on Page 1 Top Right
  doc.setFontSize(11);
  if (/[\u0980-\u09FF]/.test(monthLabel)) {
    doc.setFont('HindSiliguri', 'bold');
  } else {
    doc.setFont('helvetica', 'bold');
  }
  doc.setTextColor(monthColors.headFg[0], monthColors.headFg[1], monthColors.headFg[2]);
  doc.text(monthLabel, pageWidth - 14, 28, { align: 'right' });

  // Date range under month (Right side - Page 1)
  doc.setFontSize(8);
  doc.setFont('helvetica', 'normal');
  doc.setTextColor(100, 116, 139);
  if (filterFrom.value && filterTo.value) {
    doc.text(`Period: ${filterFrom.value} - ${filterTo.value}`, pageWidth - 14, 33, { align: 'right' });
  } else {
    const printDate = new Date().toISOString().split('T')[0];
    doc.text(`Printed: ${printDate}`, pageWidth - 14, 33, { align: 'right' });
  }

  const tableStartY = 38;

  const head = [[
    isBn ? 'তারিখ' : 'Date',
    isBn ? 'সিরিয়াল' : 'Sl',
    isBn ? 'কাস্টমার নাম' : 'Customer Name',
    isBn ? 'আইটেম' : 'Item',
    isBn ? 'পরিমাণ' : 'Qty',
    isBn ? 'রেট' : 'Rate',
    isBn ? 'মোট' : 'Total',
    isBn ? 'খরচ তারিখ' : 'Khoroch Date',
    isBn ? 'খরচ' : 'Khoroch',
    isBn ? 'নীট পাওনা' : 'Net Payable',
  ]];

  const filledFiltered = visibleEntries.value.filter(isFilledEntry);
  const body = filledFiltered.map((e) => [
    e.entry_date || '',
    e.serial || '',
    e.name || '',
    e.item || '',
    e.quantity || 0,
    Number(e.unit_price || 0).toLocaleString(),
    (Number(e.quantity || 0) * Number(e.unit_price || 0)).toLocaleString(),
    e.khoroch_date || '',
    Number(e.khoroch || 0).toLocaleString(),
    ((Number(e.quantity || 0) * Number(e.unit_price || 0)) - Number(e.khoroch || 0)).toLocaleString(),
  ]);

  body.push([
    '', '', '', '', '',
    isBn ? 'সর্বমোট:' : 'Total:',
    totals.value.totalAmount.toLocaleString(),
    '',
    totals.value.totalKhoroch.toLocaleString(),
    totals.value.netPayable.toLocaleString(),
  ]);

  autoTable(doc, {
    head,
    body,
    startY: tableStartY,
    margin: { top: 22, bottom: 16, left: 14, right: 14 },
    styles: {
      fontSize: 8,
      font: isBn ? 'HindSiliguri' : 'helvetica',
      cellPadding: 2,
    },
    headStyles: {
      fillColor: monthColors.headBg,
      textColor: monthColors.headFg,
      fontStyle: 'bold',
      lineColor: monthColors.headFg,
      lineWidth: 0.1,
    },
    alternateRowStyles: {
      fillColor: [248, 250, 252],
    },
    didDrawPage: (data) => {
      // Header for Page 2 and subsequent pages
      if (data.pageNumber > 1) {
        // Left: Worker name & subtitle
        doc.setFontSize(10);
        if (/[\u0980-\u09FF]/.test(workerName)) {
          doc.setFont('HindSiliguri', 'bold');
        } else {
          doc.setFont('helvetica', 'bold');
        }
        doc.setTextColor(15, 23, 42);
        doc.text(workerName, 14, 14);

        const subTitleText = ` • ${isBn ? 'স্টাফ কাজের হিসাব বিবরণী' : 'Staff Work Entry Sheet'}`;
        doc.setFontSize(8);
        doc.setFont(isBn ? 'HindSiliguri' : 'helvetica', 'normal');
        doc.setTextColor(100, 116, 139);
        doc.text(subTitleText, 14 + doc.getTextWidth(workerName) + 1, 14);

        // Right: Prominent Month Name on every page!
        doc.setFontSize(10.5);
        if (/[\u0980-\u09FF]/.test(monthLabel)) {
          doc.setFont('HindSiliguri', 'bold');
        } else {
          doc.setFont('helvetica', 'bold');
        }
        doc.setTextColor(monthColors.headFg[0], monthColors.headFg[1], monthColors.headFg[2]);
        doc.text(monthLabel, pageWidth - 14, 14, { align: 'right' });

        // Running header divider line
        doc.setDrawColor(210, 218, 230);
        doc.setLineWidth(0.3);
        doc.line(14, 17.5, pageWidth - 14, 17.5);
      }
    },
  });

  // Footer on EVERY PAGE (Page 1, 2, 3...) mentioning Month Name, Shop, and Page Number
  const totalPages = doc.internal.getNumberOfPages();
  for (let i = 1; i <= totalPages; i++) {
    doc.setPage(i);
    const footerY = pageHeight - 8;

    // Divider line above footer
    doc.setDrawColor(226, 232, 240);
    doc.setLineWidth(0.3);
    doc.line(14, footerY - 4, pageWidth - 14, footerY - 4);

    // Left: Business name
    doc.setFontSize(7.5);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(100, 116, 139);
    doc.text('One Raymond Tailors & Fabrics', 14, footerY);

    // Center: PROMINENT MONTH NAME IN FOOTER ON EVERY PAGE!
    doc.setFontSize(8.5);
    if (/[\u0980-\u09FF]/.test(monthLabel)) {
      doc.setFont('HindSiliguri', 'bold');
    } else {
      doc.setFont('helvetica', 'bold');
    }
    doc.setTextColor(monthColors.headFg[0], monthColors.headFg[1], monthColors.headFg[2]);
    doc.text(monthLabel, pageWidth / 2, footerY, { align: 'center' });

    // Right: Page number
    doc.setFontSize(7.5);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(100, 116, 139);
    const pageText = isBn ? `পৃষ্ঠা ${i} / ${totalPages}` : `Page ${i} of ${totalPages}`;
    doc.text(pageText, pageWidth - 14, footerY, { align: 'right' });
  }

  doc.setDocumentProperties({
    title: `${workerName || 'Worker'} - ${monthInfo.label} - Work Sheet`,
  });

  const pdfBlob = doc.output('blob');
  const url = URL.createObjectURL(pdfBlob);
  const printWindow = window.open(url);
  if (printWindow) {
    printWindow.addEventListener('load', () => {
      printWindow.print();
    });
  }
}

onMounted(() => {
  // Check if month query param was passed
  const queryMonth = route.query.month;
  if (queryMonth && queryMonth !== 'all') {
    const [y, m] = queryMonth.split('-').map(Number);
    if (!isNaN(y) && !isNaN(m)) {
      selectedYear.value = y;
      const mm = String(m + 1).padStart(2, '0');
      filterFrom.value = `${y}-${mm}-01`;
      const lastDay = new Date(y, m + 1, 0).getDate();
      filterTo.value = `${y}-${mm}-${String(lastDay).padStart(2, '0')}`;
    }
  } else {
    // Check localStorage
    const saved = localStorage.getItem(WORKER_MONTH_FILTER_KEY);
    if (saved && saved !== 'all') {
      const [y, m] = saved.split('-').map(Number);
      if (!isNaN(y) && !isNaN(m)) {
        selectedYear.value = y;
        const mm = String(m + 1).padStart(2, '0');
        filterFrom.value = `${y}-${mm}-01`;
        const lastDay = new Date(y, m + 1, 0).getDate();
        filterTo.value = `${y}-${mm}-${String(lastDay).padStart(2, '0')}`;
        router.replace({ query: { ...route.query, month: saved } });
      }
    } else {
      // Default to current year & month
      const now = new Date();
      const y = now.getFullYear();
      const m = now.getMonth();
      selectedYear.value = y;
      const mm = String(m + 1).padStart(2, '0');
      filterFrom.value = `${y}-${mm}-01`;
      const lastDay = new Date(y, m + 1, 0).getDate();
      filterTo.value = `${y}-${mm}-${String(lastDay).padStart(2, '0')}`;
      router.replace({ query: { ...route.query, month: `${y}-${m}` } });
    }
  }

  fetchWorker();
  fetchEntries();
});

onUnmounted(() => {
  if (autoSaveTimer) clearTimeout(autoSaveTimer);
});
</script>
