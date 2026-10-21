<template>
  <div
    v-if="!isStandalone && !dismissed"
    class="fixed bottom-20 md:bottom-5 left-4 right-4 md:left-auto md:right-5 md:w-96 z-50 animate-in slide-in-from-bottom-5 duration-300"
  >
    <div class="rounded-2xl border border-primary/20 bg-card/95 backdrop-blur-md p-4 shadow-xl ring-1 ring-black/5 flex items-start gap-3.5">
      <!-- App Icon -->
      <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary to-blue-700 flex items-center justify-center shrink-0 shadow-md shadow-primary/20 ring-2 ring-primary/20">
        <img src="/logo.png" alt="One Raymond" class="w-8 h-8 object-contain" />
      </div>

      <!-- Text Details -->
      <div class="flex-1 min-w-0 space-y-1">
        <div class="flex items-center gap-1.5">
          <p class="text-sm font-semibold text-foreground leading-tight">Install One Raymond</p>
          <span class="rounded bg-primary/10 text-primary px-1.5 py-0.2 text-[10px] font-bold">APP</span>
        </div>
        <p class="text-xs text-muted-foreground leading-snug">
          {{ isIos
            ? 'Tap Share ⎋ → Add to Home Screen'
            : (deferredPrompt
                ? 'Add to home screen for fast offline access'
                : 'Install as desktop or mobile application') }}
        </p>

        <!-- Action Row -->
        <div class="flex items-center gap-2 pt-1.5">
          <button
            v-if="deferredPrompt"
            type="button"
            @click="handleInstall"
            class="h-7 px-3 rounded-lg bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 transition-all flex items-center gap-1 cursor-pointer shadow-xs"
          >
            <Download class="h-3.5 w-3.5" />
            <span>Install</span>
          </button>

          <button
            v-else
            type="button"
            @click="openGuide"
            class="h-7 px-3 rounded-lg bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 transition-all flex items-center gap-1 cursor-pointer shadow-xs"
          >
            <Compass class="h-3.5 w-3.5" />
            <span>How to install</span>
          </button>

          <button
            type="button"
            @click="handleDismiss"
            class="h-7 px-2 text-xs text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
          >
            Not now
          </button>
        </div>
      </div>

      <!-- Close button -->
      <button
        type="button"
        @click="handleDismiss"
        class="text-muted-foreground hover:text-foreground transition-colors p-1 rounded-md hover:bg-muted cursor-pointer shrink-0"
        title="Dismiss"
      >
        <X class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Download, Compass, X } from 'lucide-vue-next';
import { usePwa } from '@/lib/pwa';

const {
  deferredPrompt,
  isStandalone,
  isIos,
  promptInstall,
  openInstallModal,
} = usePwa();

const dismissed = ref(false);

async function handleInstall() {
  const result = await promptInstall();
  if (result) {
    handleDismiss();
  }
}

function openGuide() {
  openInstallModal();
}

function handleDismiss() {
  dismissed.value = true;
  if (typeof localStorage !== 'undefined') {
    localStorage.setItem('pwa-install-dismissed', String(Date.now()));
  }
}

onMounted(() => {
  if (typeof localStorage !== 'undefined') {
    const dismissedAt = localStorage.getItem('pwa-install-dismissed');
    if (dismissedAt && Date.now() - Number(dismissedAt) < 12 * 60 * 60 * 1000) {
      dismissed.value = true;
    }
  }
});
</script>
