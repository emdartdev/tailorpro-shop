<template>
  <div class="min-h-screen flex w-full bg-background text-foreground antialiased">
    <!-- Desktop Persistent Sidebar -->
    <div class="hidden md:block shrink-0">
      <AppSidebar />
    </div>

    <!-- Main Workspace Area -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
      <!-- Top Application Bar -->
      <header
        class="h-14 flex items-center justify-between border-b border-border px-3 sm:px-4 bg-card/95 backdrop-blur-md sticky top-0 z-20 gap-2 select-none"
        :style="{
          paddingTop: 'env(safe-area-inset-top, 0px)',
        }"
      >
        <!-- Left: Mobile Hamburger & Brand / Desktop Global Search -->
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
          <!-- Mobile Hamburger Menu Button -->
          <button
            type="button"
            @click="drawerOpen = true"
            class="md:hidden h-9 w-9 rounded-lg inline-flex items-center justify-center hover:bg-muted text-foreground/80 hover:text-foreground transition-colors cursor-pointer shrink-0"
            aria-label="Open Navigation Menu"
          >
            <Menu class="h-5 w-5" />
          </button>

          <!-- Mobile Brand Logo -->
          <router-link to="/" class="md:hidden flex items-center gap-2 truncate">
            <img src="/logo.png" alt="One Raymond" class="w-7 h-7 rounded-lg object-contain shrink-0" />
            <div class="truncate leading-tight">
              <span class="font-heading font-bold text-xs leading-tight block text-foreground truncate">{{ t('oneRaymond') }}</span>
              <span class="text-[10px] text-muted-foreground leading-tight block truncate">{{ t('tailorPro') }}</span>
            </div>
          </router-link>

          <!-- Desktop Global Search -->
          <div class="hidden md:block">
            <GlobalSearch />
          </div>
        </div>

        <!-- Right: Actions (Viewer badge, Mobile Search, Install, Lang, Theme, Logout) -->
        <div class="flex items-center gap-1 sm:gap-1.5 shrink-0">
          <!-- View only indicator for viewer mode -->
          <span
            v-if="isViewer"
            class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-muted text-muted-foreground text-[11px] font-semibold"
          >
            <Eye class="h-3 w-3" />
            <span class="hidden sm:inline">View only</span>
          </span>

          <!-- Mobile Search Trigger -->
          <div class="md:hidden">
            <GlobalSearch />
          </div>

          <!-- Quick Install App Button (visible when not in standalone) -->
          <button
            v-if="!isStandalone"
            id="header-install-app-btn"
            type="button"
            @click="handleHeaderInstall"
            class="flex items-center gap-1 px-2 sm:px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground border border-primary/20 transition-all cursor-pointer shadow-2xs shrink-0"
            :title="lang === 'bn' ? 'মোবাইলে অ্যাপ ইনস্টল করুন' : 'Install App on Mobile / Desktop'"
          >
            <Download class="h-3.5 w-3.5 animate-bounce shrink-0" />
            <span class="hidden sm:inline text-[11px] font-semibold">{{ lang === 'bn' ? 'ইনস্টল' : 'Install' }}</span>
          </button>

          <!-- Language Switcher Button -->
          <button
            type="button"
            @click="setLang(lang === 'en' ? 'bn' : 'en')"
            class="h-8 sm:h-8.5 px-2 sm:px-2.5 rounded-lg text-xs font-semibold text-foreground/80 hover:bg-muted hover:text-foreground transition-colors cursor-pointer flex items-center gap-1"
            :title="lang === 'en' ? 'Switch to Bengali' : 'Switch to English'"
          >
            <Languages class="h-4 w-4 shrink-0" />
            <span class="text-xs">{{ lang === 'en' ? 'বাং' : 'EN' }}</span>
          </button>

          <!-- Theme Switcher Button -->
          <button
            type="button"
            @click="toggleTheme"
            class="h-8 w-8 sm:h-8.5 sm:w-8.5 rounded-lg text-foreground/80 hover:bg-muted hover:text-foreground transition-colors cursor-pointer inline-flex items-center justify-center shrink-0"
            :title="theme === 'light' ? 'Switch to Dark Mode' : 'Switch to Light Mode'"
          >
            <Moon v-if="theme === 'light'" class="h-4 w-4" />
            <Sun v-else class="h-4 w-4" />
          </button>

          <!-- Logout Button (Hidden on smallest mobile, accessible via drawer) -->
          <button
            type="button"
            @click="logout"
            class="hidden sm:inline-flex h-8.5 w-8.5 rounded-lg text-destructive hover:bg-destructive/10 transition-colors cursor-pointer items-center justify-center shrink-0"
            title="Log Out"
          >
            <LogOut class="h-4 w-4" />
          </button>
        </div>
      </header>

      <!-- Main Content Slot with Mobile Bottom Nav Padding -->
      <main
        class="flex-1 p-3 sm:p-4 md:p-6 overflow-x-hidden overflow-y-auto viewer-lock-root"
        :style="{
          paddingBottom: 'calc(4.5rem + env(safe-area-inset-bottom, 0px))',
        }"
      >
        <div class="max-w-7xl mx-auto w-full">
          <slot />
        </div>
      </main>
    </div>

    <!-- Mobile Slide-over Navigation Drawer -->
    <MobileDrawer :is-open="drawerOpen" @close="drawerOpen = false" />

    <!-- Mobile Bottom Navigation Bar -->
    <MobileBottomNav @open-drawer="drawerOpen = true" />

    <!-- Mobile / Universal App Install Modal -->
    <MobileInstallModal />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import {
  Menu, Download, Languages, Moon, Sun, LogOut, Eye
} from 'lucide-vue-next';
import { useLanguage } from '@/lib/language';
import { useTheme } from '@/lib/theme';
import { useAuth } from '@/lib/auth';
import { usePwa } from '@/lib/pwa';
import AppSidebar from './AppSidebar.vue';
import MobileBottomNav from './MobileBottomNav.vue';
import MobileDrawer from './MobileDrawer.vue';
import GlobalSearch from './GlobalSearch.vue';
import MobileInstallModal from './MobileInstallModal.vue';

const router = useRouter();
const { lang, setLang, t } = useLanguage();
const { theme, toggleTheme } = useTheme();
const { isViewer, logout } = useAuth();
const { isStandalone, promptInstall, openInstallModal, canPromptNative, deferredPrompt } = usePwa();

const drawerOpen = ref(false);

async function handleHeaderInstall() {
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
</script>
