<template>
  <main class="min-h-screen flex flex-col items-center justify-center bg-background p-4 py-8">
    <!-- Reset Link Sent Card -->
    <div v-if="resetSent" class="w-full max-w-md rounded-2xl border border-border bg-card p-5 sm:p-8 text-center space-y-4 shadow-xl animate-in fade-in-50">
      <Mail class="h-12 w-12 text-primary mx-auto" />
      <h2 class="text-xl font-semibold">{{ t('checkEmail') }}</h2>
      <p class="text-muted-foreground text-sm">
        {{ t('resetLinkSent') }} <strong>{{ email }}</strong>. {{ t('checkInbox') }}
      </p>
      <button
        type="button"
        @click="resetSent = false; forgotMode = false"
        class="w-full h-10 rounded-lg border border-input bg-background hover:bg-accent text-sm font-medium transition-colors"
      >
        {{ t('backToLogin') }}
      </button>
    </div>

    <!-- Forgot Password Card -->
    <div v-else-if="forgotMode" class="w-full max-w-md rounded-2xl border border-border bg-card p-5 sm:p-8 shadow-xl animate-in fade-in-50">
      <div class="text-center space-y-2 mb-6">
        <img src="/logo.png" alt="One Raymond" class="mx-auto w-12 h-12 rounded-xl mb-2 object-contain" />
        <h2 class="text-xl font-heading font-semibold">{{ t('forgotPassword') }}</h2>
        <p class="text-sm text-muted-foreground">{{ t('enterEmailForReset') }}</p>
      </div>

      <form @submit.prevent="handleForgotPassword" class="space-y-4">
        <div class="space-y-2">
          <label for="reset-email" class="text-xs font-medium text-foreground">{{ t('email') }}</label>
          <input
            id="reset-email"
            v-model="email"
            type="email"
            placeholder="admin@example.com"
            class="w-full h-10 px-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
            required
            autofocus
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full h-10 inline-flex items-center justify-center gap-2 rounded-lg bg-primary text-primary-foreground font-medium hover:bg-primary/90 transition-colors disabled:opacity-50"
        >
          <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
          <Mail v-else class="h-4 w-4" />
          {{ t('sendResetLink') }}
        </button>

        <button
          type="button"
          @click="forgotMode = false"
          class="w-full h-10 inline-flex items-center justify-center gap-2 rounded-lg hover:bg-accent text-sm text-muted-foreground hover:text-foreground transition-colors"
        >
          <ArrowLeft class="h-4 w-4" /> {{ t('backToLogin') }}
        </button>
      </form>
    </div>

    <!-- Standard Login Card -->
    <div v-else class="w-full max-w-md rounded-2xl border border-border bg-card p-5 sm:p-8 shadow-xl animate-in fade-in-50">
      <header class="text-center space-y-2 mb-6">
        <img src="/logo.png" alt="One Raymond" class="mx-auto w-12 h-12 rounded-xl mb-2 object-contain" />
        <h1 class="text-xl font-heading font-bold text-foreground">One Raymond TailorPro</h1>
        <p class="text-sm text-muted-foreground">{{ t('signInDesc') }}</p>
      </header>

      <div v-if="sessionExpired" class="mb-4 flex items-center gap-2 rounded-lg border border-destructive/30 bg-destructive/5 px-3 py-2 text-sm text-destructive">
        <ShieldAlert class="h-4 w-4 shrink-0" />
        <span>{{ t('sessionExpired') }}</span>
      </div>

      <div v-if="errorMessage" class="mb-4 flex items-center gap-2 rounded-lg border border-destructive/30 bg-destructive/10 px-3 py-2 text-sm text-destructive">
        <span>{{ errorMessage }}</span>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div class="space-y-1.5">
          <label for="email" class="text-xs font-medium text-foreground">{{ t('email') }}</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="admin@example.com"
            class="w-full h-10 px-3 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring"
            required
            autofocus
          />
        </div>

        <div class="space-y-1.5">
          <div class="flex items-center justify-between">
            <label for="password" class="text-xs font-medium text-foreground">{{ t('password') }}</label>
            <button type="button" @click="forgotMode = true" class="text-xs text-primary hover:underline">
              {{ t('forgotPassword') }}
            </button>
          </div>
          <div class="relative">
            <input
              id="password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              class="w-full h-10 pl-3 pr-10 text-sm rounded-lg border border-input bg-background focus:outline-none focus:ring-2 focus:ring-ring font-mono"
              required
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              aria-label="Toggle password visibility"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground p-1 rounded-md transition-colors cursor-pointer"
            >
              <EyeOff v-if="showPassword" class="h-4 w-4" />
              <Eye v-else class="h-4 w-4" />
            </button>
          </div>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full h-10 inline-flex items-center justify-center gap-2 rounded-lg bg-primary text-primary-foreground font-medium hover:bg-primary/90 transition-colors disabled:opacity-50 cursor-pointer"
        >
          <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
          <LogIn v-else class="h-4 w-4" />
          {{ t('signIn') }}
        </button>
      </form>
    </div>

    <!-- Semantic Public SEO Footer & Software Highlights -->
    <footer class="mt-8 text-center text-xs text-muted-foreground max-w-md space-y-2">
      <div class="flex items-center justify-center gap-2 text-[11px] font-medium text-foreground/75 flex-wrap">
        <span>Airman Uniform Tracking</span>
        <span>•</span>
        <span>Officer Measurements</span>
        <span>•</span>
        <span>Staff Payroll</span>
        <span>•</span>
        <span>Thermal Slips</span>
      </div>
      <p class="text-[11px] text-muted-foreground/80">
        © {{ new Date().getFullYear() }} One Raymond TailorPro ERP. Secure role-based management system.
      </p>
    </footer>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { LogIn, Loader2, ShieldAlert, ArrowLeft, Mail, Eye, EyeOff } from 'lucide-vue-next';
import { t } from '@/lib/language';
import { authState, login, setAuth } from '@/lib/auth';
import api from '@/lib/api';

const router = useRouter();
const route = useRoute();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);
const forgotMode = ref(false);
const resetSent = ref(false);
const errorMessage = ref('');
const sessionExpired = ref(false);

onMounted(() => {
  if (route.query.expired === '1') {
    sessionExpired.value = true;
  }
  if (authState.user) {
    if (authState.isViewer) {
      router.push('/staff');
    } else {
      router.push('/orders');
    }
  }
});

async function handleLogin() {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await login(email.value, password.value);
    if (res.user) {
      if (authState.isViewer) {
        router.push('/staff');
      } else {
        router.push('/orders');
      }
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Login failed. Please verify credentials.';
  } finally {
    loading.value = false;
  }
}

async function handleForgotPassword() {
  if (!email.value) return;
  loading.value = true;
  errorMessage.value = '';
  try {
    await api.post('/auth/forgot-password', { email: email.value });
    resetSent.value = true;
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Failed to send password reset link.';
  } finally {
    loading.value = false;
  }
}
</script>
