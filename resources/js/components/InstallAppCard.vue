<template>
  <div class="rounded-2xl border border-primary/20 bg-gradient-to-br from-primary/8 via-card to-card p-6 shadow-sm space-y-6">
    <!-- Top Hero Section -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 pb-6 border-b border-border/70">
      <div class="flex items-start gap-4">
        <!-- App Icon with glow -->
        <div class="relative shrink-0">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-blue-700 flex items-center justify-center shadow-lg shadow-primary/25 ring-4 ring-primary/10 overflow-hidden">
            <img src="/logo.png" alt="One Raymond" class="w-12 h-12 object-contain filter drop-shadow" />
          </div>
          <span
            v-if="isStandalone"
            class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-white ring-2 ring-background"
            title="Installed & Active"
          >
            <Check class="h-3 w-3 stroke-[3]" />
          </span>
        </div>

        <!-- Title & Badges -->
        <div class="space-y-1.5 min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-xl font-heading font-bold text-foreground tracking-tight">
              {{ isStandalone ? (lang === 'bn' ? 'অ্যাপ ইনস্টল সম্পন্ন' : 'One Raymond App Installed') : (lang === 'bn' ? 'ওয়ান রেমন্ড অ্যাপ ইনস্টল করুন' : 'Install One Raymond App') }}
            </h2>
            <span
              v-if="isStandalone"
              class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2.5 py-0.5 text-xs font-semibold border border-emerald-500/20"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
              {{ lang === 'bn' ? 'নেটিভ অ্যাপ মোড' : 'Native App Active' }}
            </span>
            <span
              v-else-if="deferredPrompt"
              class="inline-flex items-center gap-1 rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-semibold border border-primary/20"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-primary animate-ping"></span>
              {{ lang === 'bn' ? 'ইনস্টল প্রস্তুত' : '1-Click Ready' }}
            </span>
            <span
              v-else
              class="inline-flex items-center gap-1 rounded-full bg-muted text-muted-foreground px-2.5 py-0.5 text-xs font-semibold border border-border"
            >
              {{ lang === 'bn' ? 'ওয়েব মোড' : 'Web Browser' }}
            </span>
          </div>

          <p class="text-sm text-muted-foreground leading-relaxed max-w-2xl">
            {{ isStandalone
              ? (lang === 'bn'
                  ? 'আপনি ওয়ান রেমন্ড টেইলরপ্রো নেটিভ অ্যাপ হিসেবে ব্যবহার করছেন। অফলাইন সাপোর্ট ও দ্রুত কাজ উপভোগ করুন।'
                  : "You're using the installed One Raymond app. Enjoy faster load times, offline support, and dedicated window access.")
              : (lang === 'bn'
                  ? 'ওয়ান রেমন্ড টেইলরপ্রো হোম স্ক্রিনে যোগ করুন—দ্রুত অ্যাক্সেস, অফলাইন ব্যবহার ও সত্যিকারের নেটিভ অ্যাপের অনুভূতি পেতে।'
                  : 'Add One Raymond TailorPro to your home screen for quick access, offline use, and a native app feel.') }}
          </p>
        </div>
      </div>

      <!-- Action Button (1-Click Install or Active state) -->
      <div class="flex flex-wrap items-center gap-3 shrink-0 w-full sm:w-auto">
        <template v-if="!isStandalone">
          <button
            v-if="deferredPrompt"
            type="button"
            @click="handleInstall"
            :disabled="installing"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-primary-foreground px-5 py-2.5 text-sm font-semibold shadow-md shadow-primary/25 hover:bg-primary/90 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer"
          >
            <Download class="h-4 w-4 animate-bounce" />
            <span>{{ lang === 'bn' ? 'সরাসরি অ্যাপ ইনস্টল করুন' : 'Install App Now' }}</span>
          </button>

          <button
            v-else
            type="button"
            @click="activePlatform = 'desktop'"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-primary-foreground px-5 py-2.5 text-sm font-semibold shadow-md shadow-primary/25 hover:bg-primary/90 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer"
          >
            <Compass class="h-4 w-4" />
            <span>{{ lang === 'bn' ? 'ইনস্টল পদ্ধতি দেখুন' : 'How to Install' }}</span>
          </button>
        </template>

        <div v-else class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-xs font-semibold">
          <CheckCircle2 class="h-4 w-4" />
          {{ lang === 'bn' ? 'ডিভাইসে ইনস্টল সম্পন্ন' : 'App Active & Synced' }}
        </div>
      </div>
    </div>

    <!-- Feature Highlights Bar -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <div class="flex items-center gap-2.5 p-3 rounded-xl bg-card border border-border/60 shadow-xs">
        <div class="h-8 w-8 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
          <Zap class="h-4 w-4" />
        </div>
        <div class="min-w-0">
          <div class="text-xs font-semibold truncate">{{ lang === 'bn' ? 'দ্রুত লঞ্চ' : 'Instant Launch' }}</div>
          <div class="text-[11px] text-muted-foreground truncate">{{ lang === 'bn' ? '১ ক্লিকেই ওপেন' : 'Direct from dock' }}</div>
        </div>
      </div>

      <div class="flex items-center gap-2.5 p-3 rounded-xl bg-card border border-border/60 shadow-xs">
        <div class="h-8 w-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
          <WifiOff class="h-4 w-4" />
        </div>
        <div class="min-w-0">
          <div class="text-xs font-semibold truncate">{{ lang === 'bn' ? 'অফলাইন সাপোর্ট' : 'Offline Ready' }}</div>
          <div class="text-[11px] text-muted-foreground truncate">{{ lang === 'bn' ? 'নেট ছাড়াও ডেটা' : 'Cached records' }}</div>
        </div>
      </div>

      <div class="flex items-center gap-2.5 p-3 rounded-xl bg-card border border-border/60 shadow-xs">
        <div class="h-8 w-8 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
          <Printer class="h-4 w-4" />
        </div>
        <div class="min-w-0">
          <div class="text-xs font-semibold truncate">{{ lang === 'bn' ? 'ক্লিন প্রিন্ট' : 'Clean Slips' }}</div>
          <div class="text-[11px] text-muted-foreground truncate">{{ lang === 'bn' ? 'ব্রাউজার বার ছাড়া' : 'No URL header' }}</div>
        </div>
      </div>

      <div class="flex items-center gap-2.5 p-3 rounded-xl bg-card border border-border/60 shadow-xs">
        <div class="h-8 w-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
          <Sparkles class="h-4 w-4" />
        </div>
        <div class="min-w-0">
          <div class="text-xs font-semibold truncate">{{ lang === 'bn' ? 'অটো আপডেট' : 'Auto-Updated' }}</div>
          <div class="text-[11px] text-muted-foreground truncate">{{ lang === 'bn' ? 'সর্বশেষ ভার্সন' : 'Always latest' }}</div>
        </div>
      </div>
    </div>

    <!-- Platform Selector Tabs -->
    <div class="space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-2 border-b border-border">
        <div class="flex items-center gap-1 sm:gap-2">
          <button
            type="button"
            @click="activePlatform = 'desktop'"
            :class="[
              'flex items-center gap-2 px-3 sm:px-4 py-2.5 text-xs sm:text-sm font-semibold transition-all relative border-b-2 -mb-px cursor-pointer',
              activePlatform === 'desktop'
                ? 'border-primary text-primary'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            ]"
          >
            <Monitor class="h-4 w-4" />
            <span>{{ lang === 'bn' ? 'ডেস্কটপ (Chrome/Edge)' : 'Desktop (Chrome/Edge)' }}</span>
          </button>

          <button
            type="button"
            @click="activePlatform = 'android'"
            :class="[
              'flex items-center gap-2 px-3 sm:px-4 py-2.5 text-xs sm:text-sm font-semibold transition-all relative border-b-2 -mb-px cursor-pointer',
              activePlatform === 'android'
                ? 'border-primary text-primary'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            ]"
          >
            <Smartphone class="h-4 w-4" />
            <span>{{ lang === 'bn' ? 'অ্যান্ড্রয়েড (Chrome)' : 'Android (Chrome)' }}</span>
          </button>

          <button
            type="button"
            @click="activePlatform = 'ios'"
            :class="[
              'flex items-center gap-2 px-3 sm:px-4 py-2.5 text-xs sm:text-sm font-semibold transition-all relative border-b-2 -mb-px cursor-pointer',
              activePlatform === 'ios'
                ? 'border-primary text-primary'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            ]"
          >
            <Apple class="h-4 w-4" />
            <span>{{ lang === 'bn' ? 'আইফোন / আইপ্যাড' : 'iPhone / iPad' }}</span>
          </button>

          <button
            type="button"
            @click="activePlatform = 'qr'"
            :class="[
              'flex items-center gap-2 px-3 sm:px-4 py-2.5 text-xs sm:text-sm font-semibold transition-all relative border-b-2 -mb-px cursor-pointer',
              activePlatform === 'qr'
                ? 'border-primary text-primary'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            ]"
          >
            <QrCode class="h-4 w-4" />
            <span>{{ lang === 'bn' ? 'মোবাইলে কিউআর স্ক্যান' : 'Scan QR for Mobile' }}</span>
          </button>
        </div>
      </div>

      <!-- TAB 1: DESKTOP (Chrome / Edge / Windows / Mac) -->
      <div v-if="activePlatform === 'desktop'" class="space-y-4 animate-in fade-in-50 duration-200">
        <!-- Omnibox Address Bar Interactive Mockup -->
        <div class="rounded-xl border border-primary/20 bg-muted/30 p-4 space-y-3">
          <div class="flex items-center justify-between text-xs text-muted-foreground font-medium">
            <span>{{ lang === 'bn' ? 'ব্রাউজার অ্যাড্রেস বার সিমুলেশন' : 'Browser Address Bar Demonstration' }}</span>
            <span class="text-primary font-semibold">{{ lang === 'bn' ? 'এড্রেস বারের ডান পাশে দেখুন' : 'Look at the right side of URL bar' }}</span>
          </div>

          <!-- Chrome/Edge Address Bar UI -->
          <div class="flex items-center gap-2 bg-background border border-border rounded-lg px-3 py-2 shadow-xs">
            <div class="flex items-center gap-1.5 text-muted-foreground">
              <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
              <Lock class="h-3.5 w-3.5" />
            </div>
            <span class="text-xs font-mono text-muted-foreground flex-1 truncate">
              {{ appUrl || 'http://127.0.0.1:8000' }}
            </span>
            <!-- The Install Icon Highlight -->
            <div class="flex items-center gap-2">
              <div class="relative group">
                <button
                  type="button"
                  @click="handleInstall"
                  class="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-primary/10 text-primary border border-primary/30 text-xs font-semibold hover:bg-primary hover:text-primary-foreground transition-all cursor-pointer ring-2 ring-primary/20 animate-pulse"
                >
                  <Download class="h-3.5 w-3.5" />
                  <span>Install</span>
                </button>
                <!-- Tooltip pointer -->
                <div class="absolute -top-8 right-0 bg-primary text-primary-foreground text-[10px] font-semibold px-2 py-0.5 rounded shadow-md whitespace-nowrap hidden sm:block">
                  {{ lang === 'bn' ? 'এখানে ক্লিক করুন 👆' : 'Click here to install! 👆' }}
                </div>
              </div>
              <Star class="h-3.5 w-3.5 text-muted-foreground" />
            </div>
          </div>
        </div>

        <!-- Step by Step instructions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div class="rounded-xl border border-border bg-card p-4 space-y-2">
            <div class="flex items-center gap-2 text-primary font-semibold text-xs">
              <span class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center font-bold">1</span>
              <span>{{ lang === 'bn' ? 'অ্যাড্রেস বার লক্ষ্য করুন' : 'Look at Address Bar' }}</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ lang === 'bn'
                ? 'Chrome বা Edge ব্রাউজারের ওপরের অ্যাড্রেস বারের ডান পাশে "Install" বা ডাউনলোড আইকন (⊕ বা ⬇) দেখা যাবে।'
                : 'In Chrome or Edge, find the "Install" or App Available icon (⊕ or ⬇) at the right end of the URL bar.' }}
            </p>
          </div>

          <div class="rounded-xl border border-border bg-card p-4 space-y-2">
            <div class="flex items-center gap-2 text-primary font-semibold text-xs">
              <span class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center font-bold">2</span>
              <span>{{ lang === 'bn' ? 'ইনস্টল বাটনে ক্লিক করুন' : 'Click Install' }}</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ lang === 'bn'
                ? 'আইকনে ক্লিক করলে পপ-আপ আসবে "Install One Raymond - TailorPro?"। এরপর "Install" বাটনে চাপুন।'
                : 'Click the icon. A prompt will ask "Install One Raymond - TailorPro?". Click the "Install" button.' }}
            </p>
          </div>

          <div class="rounded-xl border border-border bg-card p-4 space-y-2">
            <div class="flex items-center gap-2 text-primary font-semibold text-xs">
              <span class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center font-bold">3</span>
              <span>{{ lang === 'bn' ? 'ডেস্কটপ থেকে চালু করুন' : 'Open from Desktop' }}</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ lang === 'bn'
                ? 'ইনস্টল সম্পন্ন হলে অ্যাপটি সম্পূর্ণ আলাদা উইন্ডোতে চালু হবে এবং ডেস্কটপ ও টাস্কবারে পিন হয়ে যাবে।'
                : 'The app will launch in a clean standalone window and can be pinned to your Taskbar or Desktop.' }}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 text-xs text-muted-foreground bg-muted/40 p-3 rounded-xl border border-border">
          <Info class="h-4 w-4 text-primary shrink-0" />
          <span>
            {{ lang === 'bn'
              ? 'বিকল্প পদ্ধতি: ব্রাউজারের থ্রি-ডট মেনু (⋮) তে ক্লিক করুন → "Save and share" → "Install One Raymond TailorPro..." সিলেক্ট করুন।'
              : 'Alternative: Click browser menu (⋮) → "Save and share" → "Install One Raymond TailorPro...".' }}
          </span>
        </div>
      </div>

      <!-- TAB 2: ANDROID (Chrome) -->
      <div v-else-if="activePlatform === 'android'" class="space-y-4 animate-in fade-in-50 duration-200">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div class="rounded-xl border border-border bg-card p-4 space-y-2">
            <div class="flex items-center gap-2 text-primary font-semibold text-xs">
              <span class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center font-bold">1</span>
              <span>{{ lang === 'bn' ? 'ক্রোম মেনু ওপেন করুন' : 'Open Chrome Menu' }}</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ lang === 'bn'
                ? 'মোবাইলে Google Chrome দিয়ে এই সাইট ওপেন করে ওপরের ডান কোণে তিনটি ডট (⋮) মেনু বাটনে ট্যাপ করুন।'
                : 'Open this page in Google Chrome on Android. Tap the three-dot menu (⋮) in the top-right corner.' }}
            </p>
          </div>

          <div class="rounded-xl border border-border bg-card p-4 space-y-2">
            <div class="flex items-center gap-2 text-primary font-semibold text-xs">
              <span class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center font-bold">2</span>
              <span>{{ lang === 'bn' ? 'Install App সিলেক্ট করুন' : 'Select Install App' }}</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ lang === 'bn'
                ? 'মেনু তালিকায় "Install app" অথবা "Add to Home screen" অপশনে ট্যাপ করুন।'
                : 'Tap "Install app" (or "Add to Home screen") from the dropdown menu options.' }}
            </p>
          </div>

          <div class="rounded-xl border border-border bg-card p-4 space-y-2">
            <div class="flex items-center gap-2 text-primary font-semibold text-xs">
              <span class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center font-bold">3</span>
              <span>{{ lang === 'bn' ? 'কনফার্ম করুন' : 'Confirm Installation' }}</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ lang === 'bn'
                ? 'পপ-আপ আসলে "Install" চাপুন। অ্যাপটি সরাসরি আপনার ফোনের অ্যাপ ড্রয়ার ও হোম স্ক্রিনে চলে আসবে।'
                : 'Confirm by tapping "Install". The One Raymond icon will now sit on your Android home screen.' }}
            </p>
          </div>
        </div>

        <div v-if="deferredPrompt" class="p-4 rounded-xl bg-primary/10 border border-primary/20 flex flex-col sm:flex-row items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <Smartphone class="h-5 w-5 text-primary shrink-0" />
            <div class="text-xs">
              <div class="font-semibold text-foreground">{{ lang === 'bn' ? 'অ্যান্ড্রয়েড ইনস্টল প্রম্পট সক্রিয়' : 'Android Prompt Ready' }}</div>
              <div class="text-muted-foreground">{{ lang === 'bn' ? 'নিচের বাটনে চাপ দিয়ে সরাসরি ইনস্টল করতে পারেন।' : 'Click the button below to prompt directly.' }}</div>
            </div>
          </div>
          <button
            type="button"
            @click="handleInstall"
            class="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 cursor-pointer shadow-xs"
          >
            {{ lang === 'bn' ? 'এখনই ইনস্টল করুন' : 'Install on Android' }}
          </button>
        </div>
      </div>

      <!-- TAB 3: IOS (iPhone / iPad) -->
      <div v-else-if="activePlatform === 'ios'" class="space-y-4 animate-in fade-in-50 duration-200">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div class="rounded-xl border border-border bg-card p-4 space-y-2">
            <div class="flex items-center gap-2 text-primary font-semibold text-xs">
              <span class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center font-bold">1</span>
              <span>{{ lang === 'bn' ? 'Safari ব্রাউজারে খুলুন' : 'Open in Safari' }}</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ lang === 'bn'
                ? 'আইফোনে অবশ্যই Apple Safari ব্রাউজারে সাইটটি ওপেন করুন।'
                : 'Make sure to open this page using Apple Safari on your iPhone or iPad.' }}
            </p>
          </div>

          <div class="rounded-xl border border-border bg-card p-4 space-y-2">
            <div class="flex items-center gap-2 text-primary font-semibold text-xs">
              <span class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center font-bold">2</span>
              <span>{{ lang === 'bn' ? 'শেয়ার বাটনে চাপুন' : 'Tap Share Button' }}</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ lang === 'bn'
                ? 'স্ক্রিনের নিচের টুলবারে শেয়ার আইকন (উপরে তীর দেওয়া বক্স ⎋ / Share) ট্যাপ করুন।'
                : 'Tap the Share icon (square with an up-arrow) in the bottom navigation bar of Safari.' }}
            </p>
          </div>

          <div class="rounded-xl border border-border bg-card p-4 space-y-2">
            <div class="flex items-center gap-2 text-primary font-semibold text-xs">
              <span class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center font-bold">3</span>
              <span>{{ lang === 'bn' ? 'Add to Home Screen' : 'Add to Home Screen' }}</span>
            </div>
            <p class="text-xs text-muted-foreground leading-relaxed">
              {{ lang === 'bn'
                ? 'নিচে স্ক্রল করে "Add to Home Screen" (+) এ ট্যাপ করুন এবং ডানদিকের "Add" চাপুন।'
                : 'Scroll down, tap "Add to Home Screen" (+), and tap "Add" in the top-right corner.' }}
            </p>
          </div>
        </div>

        <div class="rounded-xl border border-blue-500/20 bg-blue-500/5 p-4 flex items-start gap-3">
          <Apple class="h-5 w-5 text-foreground shrink-0 mt-0.5" />
          <div class="text-xs space-y-1">
            <span class="font-semibold text-foreground">{{ lang === 'bn' ? 'আইওএস টিপস' : 'iOS Tip' }}:</span>
            <span class="text-muted-foreground">
              {{ lang === 'bn'
                ? 'হোম স্ক্রিন থেকে ওপেন করলে এটি ফুলস্ক্রিন অ্যাপ মোডে সাফারি বার ছাড়া চলবে।'
                : 'Launching from your Home Screen gives you a dedicated fullscreen experience without Safari URL bars.' }}
            </span>
          </div>
        </div>
      </div>

      <!-- TAB 4: QR CODE FOR MOBILE SCANNING -->
      <div v-else-if="activePlatform === 'qr'" class="space-y-4 animate-in fade-in-50 duration-200">
        <div class="flex flex-col md:flex-row items-center gap-6 p-4 rounded-xl border border-border bg-card">
          <!-- Offline QR Code Display -->
          <div class="flex flex-col items-center gap-3 shrink-0">
            <div class="p-3 rounded-2xl border border-border bg-white shadow-sm flex items-center justify-center">
              <img
                v-if="qrCodeDataUrl"
                :src="qrCodeDataUrl"
                alt="Scan to open on mobile"
                class="w-40 h-40 object-contain rounded-lg"
              />
              <div v-else class="w-40 h-40 flex items-center justify-center text-xs text-muted-foreground">
                <Loader2 class="h-6 w-6 animate-spin text-primary" />
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="handleCopy"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border hover:bg-muted text-xs font-semibold transition-colors cursor-pointer"
              >
                <Check v-if="copied" class="h-3.5 w-3.5 text-emerald-500" />
                <Copy v-else class="h-3.5 w-3.5" />
                <span>{{ copied ? (lang === 'bn' ? 'কপি হয়েছে!' : 'Copied!') : (lang === 'bn' ? 'লিঙ্ক কপি' : 'Copy link') }}</span>
              </button>

              <a
                v-if="qrCodeDataUrl"
                :href="qrCodeDataUrl"
                download="one-raymond-qr.png"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-border hover:bg-muted text-xs font-semibold transition-colors cursor-pointer"
              >
                <Download class="h-3.5 w-3.5" />
                <span>{{ lang === 'bn' ? 'QR সেভ' : 'Save QR' }}</span>
              </a>
            </div>
          </div>

          <!-- Network URL & Guide -->
          <div class="space-y-3 flex-1 min-w-0">
            <div>
              <h4 class="text-sm font-semibold text-foreground flex items-center gap-1.5">
                <QrCode class="h-4 w-4 text-primary" />
                <span>{{ lang === 'bn' ? 'মোবাইল থেকে সরাসরি স্ক্যান করুন' : 'Scan to open on mobile' }}</span>
              </h4>
              <p class="text-xs text-muted-foreground mt-1 leading-relaxed">
                {{ lang === 'bn'
                  ? 'আপনার মোবাইল ক্যামেরায় এই কিউআর কোড স্ক্যান করে সরাসরি অ্যাপটি ফোনে ওপেন করুন ও হোম স্ক্রিনে ইনস্টল করুন।'
                  : 'Point your smartphone camera at this QR code to quickly open the app on your mobile device and install it.' }}
              </p>
            </div>

            <!-- Wi-Fi / Local Network IP helper -->
            <div v-if="isLocalhost" class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 space-y-2">
              <div class="flex items-center gap-2 text-xs font-semibold text-amber-700 dark:text-amber-400">
                <Wifi class="h-3.5 w-3.5" />
                <span>{{ lang === 'bn' ? 'লোকাল ওয়াই-ফাই সংযোগ নির্দেশনা' : 'Local Wi-Fi Network Setup' }}</span>
              </div>
              <p class="text-[11px] text-amber-800/80 dark:text-amber-300/80 leading-relaxed">
                {{ lang === 'bn'
                  ? 'কম্পিউটারে 127.0.0.1 চললে ফোনে স্ক্যান করলে পাবেন না। ফোন ও কম্পিউটার একই ওয়াই-ফাইতে থাকলে আপনার পিসির লোকাল আইপি (যেমন 192.168.1.xxx) বসান:'
                  : 'When running on 127.0.0.1, your phone cannot connect directly. Enter your PC LAN IP (e.g. 192.168.1.xxx:8000) so both devices on the same Wi-Fi connect:' }}
              </p>
              <div class="flex gap-2">
                <input
                  v-model="customHost"
                  placeholder="e.g. 192.168.1.100:8000"
                  class="h-8 px-2.5 text-xs rounded-lg border border-input bg-background flex-1 focus:ring-1 focus:ring-primary"
                  @input="regenerateQr"
                />
                <button
                  type="button"
                  @click="resetHost"
                  class="h-8 px-3 rounded-lg border border-input bg-card text-xs hover:bg-muted font-medium"
                >
                  Reset
                </button>
              </div>
            </div>

            <!-- Target URL pill -->
            <div class="flex items-center gap-2 text-xs font-mono text-muted-foreground bg-muted/30 p-2.5 rounded-lg border border-border">
              <Globe class="h-3.5 w-3.5 text-primary shrink-0" />
              <span class="truncate flex-1">{{ targetUrl }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import QRCode from 'qrcode';
import {
  Download,
  CheckCircle2,
  Smartphone,
  Monitor,
  Apple,
  QrCode,
  Copy,
  Check,
  Compass,
  Zap,
  WifiOff,
  Printer,
  Sparkles,
  Lock,
  Star,
  Info,
  Globe,
  Wifi,
  Loader2,
} from 'lucide-vue-next';
import { useLanguage } from '@/lib/language';
import { usePwa } from '@/lib/pwa';

const { lang } = useLanguage();
const {
  deferredPrompt,
  isStandalone,
  isIos,
  isAndroid,
  promptInstall,
} = usePwa();

const activePlatform = ref('desktop');
const installing = ref(false);
const copied = ref(false);

const appUrl = ref('');
const customHost = ref('');
const qrCodeDataUrl = ref('');

const isLocalhost = computed(() => {
  if (typeof window === 'undefined') return false;
  return window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
});

const targetUrl = computed(() => {
  if (customHost.value.trim()) {
    const raw = customHost.value.trim();
    return raw.startsWith('http') ? raw : `http://${raw}`;
  }
  return appUrl.value;
});

async function generateQr() {
  try {
    const url = targetUrl.value;
    if (!url) return;
    qrCodeDataUrl.value = await QRCode.toDataURL(url, {
      width: 320,
      margin: 1.5,
      color: {
        dark: '#0f172a',
        light: '#ffffff',
      },
    });
  } catch (err) {
    console.error('Failed to generate QR code', err);
  }
}

function regenerateQr() {
  generateQr();
}

function resetHost() {
  customHost.value = '';
  generateQr();
}

async function handleInstall() {
  installing.value = true;
  try {
    await promptInstall();
  } finally {
    installing.value = false;
  }
}

async function handleCopy() {
  const urlToCopy = targetUrl.value;
  if (!urlToCopy) return;
  try {
    await navigator.clipboard.writeText(urlToCopy);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  } catch (err) {
    // fallback
  }
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    if (isIos.value) {
      activePlatform.value = 'ios';
    } else if (isAndroid.value) {
      activePlatform.value = 'android';
    } else {
      activePlatform.value = 'desktop';
    }

    const currentUrl = window.location.href.split('?')[0].split('#')[0];
    appUrl.value = currentUrl;
    generateQr();
  }
});
</script>
