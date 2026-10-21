<template>
  <aside
    :class="[
      'h-screen sticky top-0 flex flex-col border-r border-sidebar-border bg-sidebar text-sidebar-foreground transition-all duration-300 z-30 select-none',
      collapsed ? 'w-16' : 'w-64'
    ]"
  >
    <!-- Brand Header -->
    <div class="h-14 flex items-center justify-between px-4 border-b border-sidebar-border">
      <router-link to="/" class="flex items-center gap-3 overflow-hidden">
        <img src="/logo.png" alt="One Raymond" class="w-8 h-8 rounded-lg shrink-0 object-contain" />
        <div v-if="!collapsed" class="truncate leading-tight">
          <span class="font-heading font-bold text-sm block text-sidebar-foreground truncate">{{ t('oneRaymond') }}</span>
          <span class="text-[11px] text-muted-foreground block truncate">{{ t('tailorPro') }}</span>
        </div>
      </router-link>
      <button
        type="button"
        @click="collapsed = !collapsed"
        class="text-muted-foreground hover:text-foreground p-1 rounded-md hover:bg-sidebar-accent/50 cursor-pointer hidden md:block"
        :title="collapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
        </svg>
      </button>
    </div>

    <!-- Navigation Menu Items -->
    <div class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
      <div v-if="!collapsed" class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
        {{ t('menu') }}
      </div>

      <router-link
        v-for="item in visibleMenuItems"
        :key="item.path"
        :to="item.path"
        :title="collapsed ? item.label : ''"
        :class="[
          'flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-medium transition-all group cursor-pointer',
          isItemActive(item.path)
            ? 'bg-sidebar-accent text-sidebar-primary font-semibold shadow-xs'
            : 'text-sidebar-foreground/80 hover:bg-sidebar-accent/60 hover:text-sidebar-foreground'
        ]"
      >
        <component :is="item.icon" class="h-4 w-4 shrink-0" />
        <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
      </router-link>
    </div>

    <!-- Bottom User Info -->
    <div class="p-3 border-t border-sidebar-border" v-if="user">
      <div class="flex items-center gap-2.5 p-1 rounded-lg">
        <div class="w-7 h-7 rounded-full bg-primary/20 text-primary font-bold text-xs flex items-center justify-center uppercase shrink-0">
          {{ user.name ? user.name[0] : (user.email ? user.email[0] : 'U') }}
        </div>
        <div v-if="!collapsed" class="truncate leading-tight">
          <div class="text-xs font-semibold text-foreground truncate">{{ user.name || user.email }}</div>
          <div class="text-[10px] text-muted-foreground capitalize">{{ user.role }}</div>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, h } from 'vue';
import { useRoute } from 'vue-router';
import { useLanguage } from '@/lib/language';
import { useAuth } from '@/lib/auth';

const route = useRoute();
const { t } = useLanguage();
const { user, isViewer } = useAuth();
const collapsed = ref(false);

function isItemActive(itemPath) {
  if (!route) return false;
  if (itemPath === '/') {
    return route.path === '/' || route.path.startsWith('/orders');
  }
  return route.path === itemPath || route.path.startsWith(itemPath + '/');
}

// SVG Icon components
const ShoppingBagIcon = { render: () => h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [h('path', { strokeLinecap: 'round', strokeLinejoin: 'round', strokeWidth: '2', d: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' })]) };
const UserCheckIcon = { render: () => h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [h('path', { strokeLinecap: 'round', strokeLinejoin: 'round', strokeWidth: '2', d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' })]) };
const UsersIcon = { render: () => h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [h('path', { strokeLinecap: 'round', strokeLinejoin: 'round', strokeWidth: '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })]) };
const HistoryIcon = { render: () => h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [h('path', { strokeLinecap: 'round', strokeLinejoin: 'round', strokeWidth: '2', d: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' })]) };
const UsersRoundIcon = { render: () => h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [h('path', { strokeLinecap: 'round', strokeLinejoin: 'round', strokeWidth: '2', d: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' })]) };
const ClipboardListIcon = { render: () => h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [h('path', { strokeLinecap: 'round', strokeLinejoin: 'round', strokeWidth: '2', d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' })]) };
const TruckIcon = { render: () => h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [h('path', { strokeLinecap: 'round', strokeLinejoin: 'round', strokeWidth: '2', d: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0' })]) };
const LayoutDashboardIcon = { render: () => h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [h('path', { strokeLinecap: 'round', strokeLinejoin: 'round', strokeWidth: '2', d: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' })]) };
const SettingsIcon = { render: () => h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [h('path', { strokeLinecap: 'round', strokeLinejoin: 'round', strokeWidth: '2', d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }), h('path', { strokeLinecap: 'round', strokeLinejoin: 'round', strokeWidth: '2', d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' })]) };

const allMenuItems = computed(() => [
  { label: t('orders'), path: '/', icon: ShoppingBagIcon },
  { label: t('airmanOrders'), path: '/airman', icon: UserCheckIcon },
  { label: t('customers'), path: '/customers', icon: UsersIcon },
  { label: t('customerHistory'), path: '/history', icon: HistoryIcon },
  { label: t('staffManagement'), path: '/staff', icon: UsersRoundIcon },
  { label: t('staffSummary'), path: '/staff/summary', icon: ClipboardListIcon },
  { label: 'Delivery', path: '/delivery', icon: TruckIcon },
  { label: t('dashboardReport'), path: '/dashboard', icon: LayoutDashboardIcon },
  { label: t('settings'), path: '/settings', icon: SettingsIcon },
]);

const visibleMenuItems = computed(() => {
  if (isViewer.value) {
    const viewerAllowed = ['/staff', '/staff/summary', '/settings'];
    return allMenuItems.value.filter((i) => viewerAllowed.includes(i.path));
  }
  return allMenuItems.value;
});
</script>
