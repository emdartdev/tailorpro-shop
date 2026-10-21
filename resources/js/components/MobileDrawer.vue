<template>
  <Teleport to="body">
    <!-- Backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs md:hidden"
        @click="close"
        aria-hidden="true"
      />
    </Transition>

    <!-- Drawer Panel -->
    <Transition
      enter-active-class="transition-transform duration-300 ease-out"
      enter-from-class="-translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-200 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="-translate-x-full"
    >
      <div
        v-if="isOpen"
        class="fixed inset-y-0 left-0 z-50 w-full max-w-xs bg-sidebar text-sidebar-foreground border-r border-sidebar-border shadow-2xl flex flex-col md:hidden select-none"
        :style="{
          paddingTop: 'env(safe-area-inset-top, 0px)',
          paddingBottom: 'env(safe-area-inset-bottom, 0px)',
        }"
        role="dialog"
        aria-modal="true"
        aria-label="Navigation Menu"
      >
        <!-- Header -->
        <div class="h-16 flex items-center justify-between px-4 border-b border-sidebar-border shrink-0">
          <router-link to="/" @click="close" class="flex items-center gap-3 overflow-hidden">
            <img src="/logo.png" alt="One Raymond" class="w-8 h-8 rounded-lg shrink-0 object-contain" />
            <div class="leading-tight">
              <span class="font-heading font-bold text-sm block text-sidebar-foreground">{{ t('oneRaymond') }}</span>
              <span class="text-[11px] text-muted-foreground block">{{ t('tailorPro') }}</span>
            </div>
          </router-link>

          <button
            type="button"
            @click="close"
            class="h-9 w-9 rounded-lg inline-flex items-center justify-center hover:bg-sidebar-accent text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
            aria-label="Close Navigation"
          >
            <X class="h-5 w-5" />
          </button>
        </div>

        <!-- User Profile Card -->
        <div v-if="user" class="p-4 border-b border-sidebar-border bg-sidebar-accent/30 shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary/20 text-primary font-bold text-sm flex items-center justify-center uppercase shrink-0 border border-primary/30">
              {{ user.name ? user.name[0] : (user.email ? user.email[0] : 'U') }}
            </div>
            <div class="min-w-0 flex-1 leading-tight">
              <div class="text-xs font-bold text-foreground truncate">{{ user.name || user.email }}</div>
              <div class="text-[11px] text-muted-foreground capitalize mt-0.5">{{ user.role || 'Tailor' }}</div>
            </div>
          </div>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 overflow-y-auto p-3 space-y-1">
          <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
            {{ t('menu') }}
          </div>

          <router-link
            v-for="item in visibleMenuItems"
            :key="item.path"
            :to="item.path"
            @click="close"
            :class="[
              'flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-xs font-semibold transition-all cursor-pointer min-h-[44px]',
              isItemActive(item.path)
                ? 'bg-primary text-primary-foreground shadow-xs'
                : 'text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-foreground'
            ]"
          >
            <component :is="item.icon" class="h-4.5 w-4.5 shrink-0" />
            <span class="truncate flex-1">{{ item.label }}</span>
            <ChevronRight class="h-3.5 w-3.5 opacity-40 shrink-0" />
          </router-link>
        </div>

        <!-- Bottom Quick Actions Footer -->
        <div class="p-3 border-t border-sidebar-border bg-sidebar space-y-2 shrink-0">
          <div class="grid grid-cols-2 gap-2">
            <!-- Language Switcher -->
            <button
              type="button"
              @click="toggleLang"
              class="h-9 px-3 rounded-xl border border-sidebar-border bg-sidebar-accent/40 hover:bg-sidebar-accent text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
            >
              <Globe class="h-4 w-4" />
              <span>{{ lang === 'en' ? 'বাংলা' : 'English' }}</span>
            </button>

            <!-- Theme Toggle -->
            <button
              type="button"
              @click="toggleTheme"
              class="h-9 px-3 rounded-xl border border-sidebar-border bg-sidebar-accent/40 hover:bg-sidebar-accent text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
            >
              <Sun v-if="theme === 'dark'" class="h-4 w-4" />
              <Moon v-else class="h-4 w-4" />
              <span>{{ theme === 'dark' ? 'Light' : 'Dark' }}</span>
            </button>
          </div>

          <!-- Quick App Install Button if applicable -->
          <button
            v-if="!isStandalone"
            type="button"
            @click="handleInstallClick"
            class="w-full h-9 px-3 rounded-xl bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground border border-primary/20 text-xs font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer"
          >
            <Download class="h-3.5 w-3.5" />
            <span>{{ lang === 'bn' ? 'অ্যাপ ইনস্টল করুন' : 'Install TailorPro App' }}</span>
          </button>

          <!-- Logout Button -->
          <button
            type="button"
            @click="handleLogout"
            class="w-full h-9 px-3 rounded-xl border border-destructive/20 text-destructive hover:bg-destructive/10 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
          >
            <LogOut class="h-4 w-4" />
            <span>{{ lang === 'bn' ? 'লগ আউট' : 'Log Out' }}</span>
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, watch, onMounted, onUnmounted, h } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  X, ChevronRight, Globe, Sun, Moon, Download, LogOut,
  ShoppingBag, CheckCircle, Users, History, UsersRound,
  ClipboardList, Truck, LayoutDashboard, Settings
} from 'lucide-vue-next';
import { useLanguage } from '@/lib/language';
import { useTheme } from '@/lib/theme';
import { useAuth } from '@/lib/auth';
import { usePwa } from '@/lib/pwa';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

const route = useRoute();
const router = useRouter();
const { lang, setLang, t } = useLanguage();
const { theme, toggleTheme } = useTheme();
const { user, isViewer, logout } = useAuth();
const { isStandalone, promptInstall, openInstallModal, canPromptNative, deferredPrompt } = usePwa();

function close() {
  emit('close');
}

// Close drawer on route change
watch(() => route.path, () => {
  close();
});

// Close drawer on Escape key
function handleKeyDown(e) {
  if (e.key === 'Escape' && props.isOpen) {
    close();
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

function isItemActive(itemPath) {
  if (!route) return false;
  if (itemPath === '/') {
    return route.path === '/' || (route.path.startsWith('/orders') && !route.path.startsWith('/orders/new'));
  }
  return route.path === itemPath || route.path.startsWith(itemPath + '/');
}

function toggleLang() {
  setLang(lang.value === 'en' ? 'bn' : 'en');
}

async function handleInstallClick() {
  close();
  if (canPromptNative.value && deferredPrompt.value) {
    try {
      deferredPrompt.value.prompt();
      const choice = await deferredPrompt.value.userChoice;
      if (choice.outcome === 'accepted') {
        deferredPrompt.value = null;
        isStandalone.value = true;
        return;
      }
    } catch (e) {
      console.warn('Native prompt failed:', e);
    }
  }
  openInstallModal();
}

function handleLogout() {
  close();
  logout();
}

const allMenuItems = computed(() => [
  { label: t('orders'), path: '/', icon: ShoppingBag },
  { label: t('airmanOrders'), path: '/airman', icon: CheckCircle },
  { label: t('customers'), path: '/customers', icon: Users },
  { label: t('customerHistory'), path: '/history', icon: History },
  { label: t('staffManagement'), path: '/staff', icon: UsersRound },
  { label: t('staffSummary'), path: '/staff/summary', icon: ClipboardList },
  { label: 'Delivery Queue', path: '/delivery', icon: Truck },
  { label: t('dashboardReport'), path: '/dashboard', icon: LayoutDashboard },
  { label: t('settings'), path: '/settings', icon: Settings },
]);

const visibleMenuItems = computed(() => {
  if (isViewer.value) {
    const viewerAllowed = ['/staff', '/staff/summary', '/settings'];
    return allMenuItems.value.filter((i) => viewerAllowed.includes(i.path));
  }
  return allMenuItems.value;
});
</script>
