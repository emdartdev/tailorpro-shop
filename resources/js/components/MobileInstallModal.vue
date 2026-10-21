<template>
  <div
    v-if="showInstallModal"
    class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200"
    @click.self="closeInstallModal"
  >
    <div
      class="relative w-full max-w-xl max-h-[92vh] flex flex-col rounded-2xl bg-card border border-border shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200"
    >
      <!-- Header Banner -->
      <div class="relative bg-gradient-to-r from-primary/15 via-primary/5 to-transparent p-5 border-b border-border flex items-start justify-between gap-4">
        <div class="flex items-center gap-3.5">
          <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-primary to-blue-700 flex items-center justify-center shadow-lg shadow-primary/25 ring-2 ring-primary/20 shrink-0 overflow-hidden">
            <img src="/logo.png" alt="One Raymond" class="w-10 h-10 object-contain drop-shadow" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-base sm:text-lg font-heading font-bold text-foreground">
                {{ lang === 'bn' ? 'মোবাইলে ওয়ান রেমন্ড অ্যাপ ইনস্টল করুন' : 'Install One Raymond Mobile App' }}
              </h3>
              <span class="rounded bg-primary/10 text-primary px-2 py-0.5 text-[10px] font-bold border border-primary/20">
                PWA APP
              </span>
            </div>
            <p class="text-xs text-muted-foreground mt-0.5">
              {{ lang === 'bn'
                ? 'হোম স্ক্রিনে রাখুন, অফলাইনে ব্যবহার করুন এবং দ্রুত কাজ করুন।'
                : 'Add to home screen for instant launch, offline support & full-screen view.' }}
            </p>
          </div>
        </div>

        <button
          type="button"
          @click="closeInstallModal"
          class="text-muted-foreground hover:text-foreground p-1.5 rounded-lg hover:bg-muted transition-colors cursor-pointer shrink-0"
          title="Close"
        >
          <X class="h-5 w-5" />
        </button>
      </div>

      <!-- Platform Navigation Tabs -->
      <div class="flex items-center border-b border-border bg-muted/40 px-3 overflow-x-auto no-scrollbar">
        <button
          type="button"
          @click="activePlatform = 'android'"
          :class="[
            'flex items-center gap-2 px-3 py-2.5 text-xs font-semibold whitespace-nowrap transition-all border-b-2 -mb-px cursor-pointer',
            activePlatform === 'android'
              ? 'border-primary text-primary bg-background/60 rounded-t-lg'
              : 'border-transparent text-muted-foreground hover:text-foreground'
          ]"
        >
          <Smartphone class="h-4 w-4" />
          <span>{{ lang === 'bn' ? 'অ্যান্ড্রয়েড (Android)' : 'Android' }}</span>
        </button>

        <button
          type="button"
          @click="activePlatform = 'ios'"
          :class="[
            'flex items-center gap-2 px-3 py-2.5 text-xs font-semibold whitespace-nowrap transition-all border-b-2 -mb-px cursor-pointer',
            activePlatform === 'ios'
              ? 'border-primary text-primary bg-background/60 rounded-t-lg'
              : 'border-transparent text-muted-foreground hover:text-foreground'
          ]"
        >
          <Apple class="h-4 w-4" />
          <span>{{ lang === 'bn' ? 'আইফোন / আইপ্যাড (iOS)' : 'iPhone / iPad' }}</span>
        </button>

        <button
          type="button"
          @click="activePlatform = 'qr'"
          :class="[
            'flex items-center gap-2 px-3 py-2.5 text-xs font-semibold whitespace-nowrap transition-all border-b-2 -mb-px cursor-pointer',
            activePlatform === 'qr'
              ? 'border-primary text-primary bg-background/60 rounded-t-lg'
              : 'border-transparent text-muted-foreground hover:text-foreground'
          ]"
        >
          <QrCode class="h-4 w-4" />
          <span>{{ lang === 'bn' ? 'QR স্ক্যান' : 'QR Scan' }}</span>
        </button>

        <button
          type="button"
          @click="activePlatform = 'desktop'"
          :class="[
            'flex items-center gap-2 px-3 py-2.5 text-xs font-semibold whitespace-nowrap transition-all border-b-2 -mb-px cursor-pointer',
            activePlatform === 'desktop'
              ? 'border-primary text-primary bg-background/60 rounded-t-lg'
              : 'border-transparent text-muted-foreground hover:text-foreground'
          ]"
        >
          <Monitor class="h-4 w-4" />
          <span>{{ lang === 'bn' ? 'ডেস্কটপ' : 'Desktop' }}</span>
        </button>
      </div>

      <!-- Modal Body (Scrollable) -->
      <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4">
        <!-- 1-Click Install Button if Native Prompt Ready -->
        <div
          v-if="canPromptNative && activePlatform !== 'ios'"
          class="rounded-xl border border-primary/30 bg-primary/10 p-3.5 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs"
        >
          <div class="flex items-center gap-2.5">
            <span class="flex h-2.5 w-2.5 rounded-full bg-primary animate-ping"></span>
            <div class="text-xs">
              <span class="font-bold text-foreground">{{ lang === 'bn' ? 'ওয়ান-ক্লিক সরাসরি ইনস্টল প্রস্তুত!' : '1-Click Direct Install Ready!' }}</span>
              <p class="text-muted-foreground text-[11px]">{{ lang === 'bn' ? 'নিচের বাটনে ট্যাপ করে সাথে সাথে অ্যাপটি ইনস্টল করে নিন।' : 'Tap below to add the application straight to your home screen.' }}</p>
            </div>
          </div>
          <button
            type="button"
            @click="handleNativeInstall"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-primary text-primary-foreground text-xs font-bold shadow-md shadow-primary/25 hover:bg-primary/90 transition-all cursor-pointer"
          >
            <Download class="h-4 w-4" />
            <span>{{ lang === 'bn' ? 'অ্যাপ ইনস্টল করুন' : 'Install App Now' }}</span>
          </button>
        </div>

        <!-- TAB: ANDROID -->
        <div v-if="activePlatform === 'android'" class="space-y-3.5">
          <div class="text-xs font-medium text-muted-foreground">
            {{ lang === 'bn'
              ? 'গুগল ক্রোম বা স্যামসাং ইন্টারনেট দিয়ে সাইটটি ওপেন করে নিচের ৩টি সহজ ধাপ অনুসরণ করুন:'
              : 'Open this page in Google Chrome or Samsung Internet, then follow these 3 simple steps:' }}
          </div>

          <!-- Step 1 -->
          <div class="flex items-start gap-3 p-3 rounded-xl bg-muted/40 border border-border/80">
            <div class="w-7 h-7 rounded-lg bg-primary/15 text-primary flex items-center justify-center font-bold text-xs shrink-0">
              1
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1.5 font-semibold text-xs text-foreground">
                <span>{{ lang === 'bn' ? 'ক্রোম মেনু বাটন চাপুন' : 'Tap Chrome Menu' }}</span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-muted text-[11px] font-mono border border-border">⋮</span>
              </div>
              <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed">
                {{ lang === 'bn'
                  ? 'ব্রাউজারের ওপরের ডান কোণে তিনটি ডট (⋮) অপশনে ট্যাপ করুন।'
                  : 'Tap the three dots (⋮) in the top-right corner of Google Chrome on your phone.' }}
              </p>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="flex items-start gap-3 p-3 rounded-xl bg-muted/40 border border-border/80">
            <div class="w-7 h-7 rounded-lg bg-primary/15 text-primary flex items-center justify-center font-bold text-xs shrink-0">
              2
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1.5 font-semibold text-xs text-foreground">
                <span>{{ lang === 'bn' ? '"Install app" সিলেক্ট করুন' : 'Select "Install app"' }}</span>
                <Download class="h-3.5 w-3.5 text-primary" />
              </div>
              <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed">
                {{ lang === 'bn'
                  ? 'ড্রপডাউন তালিকায় "Install app" অথবা "Add to Home screen" অপশনে ট্যাপ করুন।'
                  : 'From the menu list, tap "Install app" (or "Add to Home screen").' }}
              </p>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="flex items-start gap-3 p-3 rounded-xl bg-muted/40 border border-border/80">
            <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">
              3
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-xs text-foreground">
                {{ lang === 'bn' ? 'কনফার্ম করে ইনস্টল সম্পন্ন করুন' : 'Confirm & Enjoy Standalone Mode' }}
              </div>
              <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed">
                {{ lang === 'bn'
                  ? 'পপ-আপে "Install" বাটনে চাপুন। আপনার মোবাইলের হোম স্ক্রিনে ও অ্যাপ ড্রয়ারে One Raymond যুক্ত হয়ে যাবে।'
                  : 'Tap "Install" on the confirmation prompt. The One Raymond app icon will appear directly on your home screen.' }}
              </p>
            </div>
          </div>
        </div>

        <!-- TAB: IOS (iPhone / iPad) -->
        <div v-else-if="activePlatform === 'ios'" class="space-y-3.5">
          <div class="text-xs font-medium text-muted-foreground">
            {{ lang === 'bn'
              ? 'আইফোনে Apple Safari ব্রাউজারের শেয়ার বাটন দিয়ে নিচের ধাপগুলো অনুসরণ করুন:'
              : 'On iPhone or iPad, use Apple Safari and follow these 3 steps:' }}
          </div>

          <!-- Step 1 -->
          <div class="flex items-start gap-3 p-3 rounded-xl bg-muted/40 border border-border/80">
            <div class="w-7 h-7 rounded-lg bg-primary/15 text-primary flex items-center justify-center font-bold text-xs shrink-0">
              1
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1.5 font-semibold text-xs text-foreground">
                <span>{{ lang === 'bn' ? 'Apple Safari দিয়ে ওপেন করুন' : 'Open in Apple Safari' }}</span>
                <Compass class="h-3.5 w-3.5 text-primary" />
              </div>
              <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed">
                {{ lang === 'bn'
                  ? 'লিঙ্কটি অবশ্যই আইফোনের মূল Safari ব্রাউজারে চালু থাকতে হবে।'
                  : 'Ensure you are viewing this page in the native Apple Safari browser on iOS.' }}
              </p>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="flex items-start gap-3 p-3 rounded-xl bg-muted/40 border border-border/80">
            <div class="w-7 h-7 rounded-lg bg-primary/15 text-primary flex items-center justify-center font-bold text-xs shrink-0">
              2
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1.5 font-semibold text-xs text-foreground">
                <span>{{ lang === 'bn' ? 'শেয়ার (Share) বাটনে ট্যাপ করুন' : 'Tap the Share Button' }}</span>
                <!-- iOS Share Icon representation -->
                <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400">
                  <Share2 class="h-3.5 w-3.5" />
                </span>
              </div>
              <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed">
                {{ lang === 'bn'
                  ? 'সাফারির নিচের বার থেকে তীরচিহ্নযুক্ত শেয়ার আইকন (⎋ / Share icon) এ ট্যাপ করুন।'
                  : 'Tap the Share icon (a square with an upward arrow) in Safari’s bottom toolbar.' }}
              </p>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="flex items-start gap-3 p-3 rounded-xl bg-muted/40 border border-border/80">
            <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0">
              3
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1.5 font-semibold text-xs text-foreground">
                <span>{{ lang === 'bn' ? '"Add to Home Screen" এ চাপুন' : 'Tap "Add to Home Screen"' }}</span>
                <PlusSquare class="h-3.5 w-3.5 text-emerald-500" />
              </div>
              <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed">
                {{ lang === 'bn'
                  ? 'নিচে স্ক্রল করে "Add to Home Screen" (+) এ ট্যাপ করুন, এরপর ওপরের ডান কোণে "Add" চাপুন।'
                  : 'Scroll down the share sheet, tap "Add to Home Screen", then tap "Add" at the top right.' }}
              </p>
            </div>
          </div>

          <div class="p-3 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-start gap-2.5 text-xs text-muted-foreground">
            <Sparkles class="h-4 w-4 text-primary shrink-0 mt-0.5" />
            <span>
              {{ lang === 'bn'
                ? 'আইফোনের হোম স্ক্রিন থেকে চালু করলে কোনো ব্রাউজার বার থাকবে না এবং অ্যাপটি ফুলস্ক্রিন চলবে।'
                : 'Launching from your iOS home screen opens in dedicated full-screen mode without Safari navigation bars.' }}
            </span>
          </div>
        </div>

        <!-- TAB: QR SCAN FOR MOBILE -->
        <div v-else-if="activePlatform === 'qr'" class="space-y-3.5">
          <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-xl border border-border bg-muted/20">
            <div class="p-2.5 rounded-2xl bg-white border border-border shadow-sm flex items-center justify-center shrink-0">
              <img
                v-if="qrDataUrl"
                :src="qrDataUrl"
                alt="QR Code"
                class="w-36 h-36 object-contain rounded-lg"
              />
              <div v-else class="w-36 h-36 flex items-center justify-center">
                <Loader2 class="h-6 w-6 animate-spin text-primary" />
              </div>
            </div>

            <div class="space-y-2.5 flex-1 min-w-0 text-center sm:text-left">
              <div>
                <h4 class="text-xs sm:text-sm font-semibold text-foreground">
                  {{ lang === 'bn' ? 'ফোনের ক্যামেরা দিয়ে স্ক্যান করুন' : 'Scan with Mobile Camera' }}
                </h4>
                <p class="text-xs text-muted-foreground mt-0.5">
                  {{ lang === 'bn'
                    ? 'আপনার মোবাইল ক্যামেরা বা গুগল লেন্স দিয়ে স্ক্যান করে সরাসরি ফোনে সাইট ওপেন করুন।'
                    : 'Use your phone camera or QR scanner to open the application on your mobile device.' }}
                </p>
              </div>

              <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <button
                  type="button"
                  @click="copyAppUrl"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border bg-card hover:bg-muted text-xs font-semibold transition-colors cursor-pointer"
                >
                  <Check v-if="copied" class="h-3.5 w-3.5 text-emerald-500" />
                  <Copy v-else class="h-3.5 w-3.5" />
                  <span>{{ copied ? (lang === 'bn' ? 'কপি হয়েছে' : 'Copied!') : (lang === 'bn' ? 'লিঙ্ক কপি' : 'Copy link') }}</span>
                </button>

                <a
                  v-if="qrDataUrl"
                  :href="qrDataUrl"
                  download="one-raymond-app-qr.png"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border bg-card hover:bg-muted text-xs font-semibold transition-colors cursor-pointer"
                >
                  <Download class="h-3.5 w-3.5" />
                  <span>{{ lang === 'bn' ? 'QR সেভ' : 'Save QR' }}</span>
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB: DESKTOP -->
        <div v-else-if="activePlatform === 'desktop'" class="space-y-3.5">
          <div class="flex items-start gap-3 p-3 rounded-xl bg-muted/40 border border-border/80">
            <div class="w-7 h-7 rounded-lg bg-primary/15 text-primary flex items-center justify-center font-bold text-xs shrink-0">
              1
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-xs text-foreground">
                {{ lang === 'bn' ? 'ব্রাউজার অ্যাড্রেস বারের ডান পাশ দেখুন' : 'Check Address Bar Right Side' }}
              </div>
              <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed">
                {{ lang === 'bn'
                  ? 'Chrome বা Edge ব্রাউজারের অ্যাড্রেস বারের ডান পাশে "Install" বা ডাউনলোড আইকন (⊕ বা ⬇) দেখতে পাবেন।'
                  : 'Look for the "Install" or App icon (⊕ or ⬇) in the right end of the Chrome or Edge URL bar.' }}
              </p>
            </div>
          </div>

          <div class="flex items-start gap-3 p-3 rounded-xl bg-muted/40 border border-border/80">
            <div class="w-7 h-7 rounded-lg bg-primary/15 text-primary flex items-center justify-center font-bold text-xs shrink-0">
              2
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-xs text-foreground">
                {{ lang === 'bn' ? 'Install বাটনে ক্লিক করুন' : 'Click Install' }}
              </div>
              <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed">
                {{ lang === 'bn'
                  ? 'আইকনে ক্লিক করে "Install" সিলেক্ট করুন। অ্যাপটি উইন্ডোজ টাস্কবারে বা ম্যাক ডকে পিন করে রাখা যাবে।'
                  : 'Click the install prompt to install. The application runs in a clean window and can be pinned to your Taskbar or Dock.' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Highlights -->
      <div class="p-3.5 bg-muted/30 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
        <div class="flex items-center gap-2">
          <CheckCircle2 class="h-4 w-4 text-emerald-500 shrink-0" />
          <span>{{ lang === 'bn' ? 'সকল মোবাইল ও ট্যাবলেটে সম্পূর্ণ সাপোর্টেড' : 'Works on Android, iOS & Tablets' }}</span>
        </div>

        <button
          type="button"
          @click="closeInstallModal"
          class="px-3 py-1.5 rounded-lg border border-border hover:bg-muted font-medium text-foreground transition-colors cursor-pointer"
        >
          {{ lang === 'bn' ? 'ঠিক আছে' : 'Got it' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import QRCode from 'qrcode';
import {
  X,
  Smartphone,
  Apple,
  QrCode,
  Monitor,
  Download,
  Compass,
  Share2,
  PlusSquare,
  Sparkles,
  Check,
  Copy,
  Loader2,
  CheckCircle2,
} from 'lucide-vue-next';
import { useLanguage } from '@/lib/language';
import { usePwa } from '@/lib/pwa';

const { lang } = useLanguage();
const {
  showInstallModal,
  activePlatform,
  canPromptNative,
  promptInstall,
  closeInstallModal,
} = usePwa();

const qrDataUrl = ref('');
const copied = ref(false);

async function generateQr() {
  if (typeof window === 'undefined') return;
  try {
    const url = window.location.href.split('?')[0].split('#')[0];
    qrDataUrl.value = await QRCode.toDataURL(url, {
      width: 280,
      margin: 1.5,
      color: {
        dark: '#0f172a',
        light: '#ffffff',
      },
    });
  } catch (e) {
    console.error('Failed to generate QR code', e);
  }
}

async function handleNativeInstall() {
  await promptInstall();
}

async function copyAppUrl() {
  if (typeof window === 'undefined') return;
  try {
    const url = window.location.href.split('?')[0].split('#')[0];
    await navigator.clipboard.writeText(url);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  } catch (e) {
    // fallback
  }
}

watch(showInstallModal, (val) => {
  if (val && !qrDataUrl.value) {
    generateQr();
  }
});

onMounted(() => {
  if (showInstallModal.value) {
    generateQr();
  }
});
</script>
