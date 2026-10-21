<template>
  <div class="min-h-screen flex items-center justify-center bg-background p-4">
    <!-- Success state -->
    <div v-if="success" class="w-full max-w-md rounded-2xl border border-border bg-card p-8 text-center space-y-4 shadow-xl animate-in fade-in-50">
      <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
        <CheckCircle2 class="h-8 w-8" />
      </div>
      <h2 class="text-xl font-heading font-bold text-foreground">{{ t('passwordUpdated') }}</h2>
      <p class="text-muted-foreground text-sm leading-relaxed">{{ t('passwordResetSuccess') }}</p>
      <button
        type="button"
        @click="goToLogin"
        class="w-full h-10 inline-flex items-center justify-center rounded-xl bg-primary text-primary-foreground font-semibold hover:bg-primary/90 transition-all cursor-pointer shadow-sm shadow-primary/25"
      >
        {{ t('goToDashboard') }}
      </button>
    </div>

    <!-- Password input form -->
    <div v-else class="w-full max-w-md rounded-2xl border border-border bg-card p-6 sm:p-8 shadow-xl animate-in fade-in-50 space-y-5">
      <div class="text-center space-y-2">
        <div class="mx-auto w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
          <KeyRound class="h-6 w-6" />
        </div>
        <h2 class="text-xl font-heading font-bold text-foreground">{{ t('setNewPassword') }}</h2>
        <p class="text-xs text-muted-foreground">{{ t('enterNewPasswordBelow') }}</p>
      </div>

      <!-- Error alert -->
      <div v-if="errorMessage" class="flex items-center gap-2 rounded-xl border border-destructive/30 bg-destructive/10 px-3 py-2.5 text-xs text-destructive">
        <AlertCircle class="h-4 w-4 shrink-0" />
        <span>{{ errorMessage }}</span>
      </div>

      <form @submit.prevent="handleReset" class="space-y-4">
        <!-- New Password -->
        <div class="space-y-1.5">
          <div class="flex items-center justify-between">
            <label for="new-password" class="text-xs font-medium text-foreground">{{ t('newPassword') }}</label>
            <span
              v-if="password"
              :class="[
                'text-[10px] font-semibold px-2 py-0.2 rounded-full border',
                strengthColorClass
              ]"
            >
              {{ strengthLabel }}
            </span>
          </div>

          <div class="relative">
            <input
              id="new-password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              class="w-full h-10 pl-3 pr-10 text-xs rounded-xl border border-input bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono"
              required
              minlength="6"
              autofocus
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground p-1 rounded-md transition-colors cursor-pointer"
            >
              <EyeOff v-if="showPassword" class="h-4 w-4" />
              <Eye v-else class="h-4 w-4" />
            </button>
          </div>

          <!-- Strength Meter Bar -->
          <div v-if="password" class="grid grid-cols-4 gap-1.5 h-1 pt-1 animate-in fade-in-50">
            <div :class="['rounded-full transition-all duration-300', strengthScore >= 1 ? strengthBgClass : 'bg-muted']"></div>
            <div :class="['rounded-full transition-all duration-300', strengthScore >= 2 ? strengthBgClass : 'bg-muted']"></div>
            <div :class="['rounded-full transition-all duration-300', strengthScore >= 3 ? strengthBgClass : 'bg-muted']"></div>
            <div :class="['rounded-full transition-all duration-300', strengthScore >= 4 ? strengthBgClass : 'bg-muted']"></div>
          </div>
        </div>

        <!-- Confirm Password -->
        <div class="space-y-1.5">
          <div class="flex items-center justify-between">
            <label for="confirm-password" class="text-xs font-medium text-foreground">{{ t('confirmPassword') }}</label>
            <span
              v-if="confirmPassword"
              :class="[
                'text-[10px] font-semibold flex items-center gap-1',
                passwordsMatch ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'
              ]"
            >
              <Check v-if="passwordsMatch" class="h-3 w-3 stroke-[3]" />
              {{ passwordsMatch ? 'Passwords match' : 'Does not match' }}
            </span>
          </div>

          <div class="relative">
            <input
              id="confirm-password"
              v-model="confirmPassword"
              :type="showConfirm ? 'text' : 'password'"
              placeholder="••••••••"
              class="w-full h-10 pl-3 pr-10 text-xs rounded-xl border border-input bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono"
              required
              minlength="6"
            />
            <button
              type="button"
              @click="showConfirm = !showConfirm"
              class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground p-1 rounded-md transition-colors cursor-pointer"
            >
              <EyeOff v-if="showConfirm" class="h-4 w-4" />
              <Eye v-else class="h-4 w-4" />
            </button>
          </div>
        </div>

        <button
          type="submit"
          :disabled="loading || (confirmPassword && !passwordsMatch)"
          class="w-full h-10 inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-primary-foreground font-semibold hover:bg-primary/90 transition-all disabled:opacity-50 cursor-pointer shadow-sm shadow-primary/25"
        >
          <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
          <KeyRound v-else class="h-4 w-4" />
          <span>{{ t('updatePassword') }}</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { KeyRound, CheckCircle2, Loader2, Eye, EyeOff, Check, AlertCircle } from 'lucide-vue-next';
import { t } from '@/lib/language';
import api from '@/lib/api';

const router = useRouter();

const password = ref('');
const confirmPassword = ref('');
const showPassword = ref(false);
const showConfirm = ref(false);

const loading = ref(false);
const success = ref(false);
const errorMessage = ref('');

const passwordsMatch = computed(() => {
  return password.value && confirmPassword.value && password.value === confirmPassword.value;
});

const strengthScore = computed(() => {
  if (!password.value) return 0;
  let score = 0;
  if (password.value.length >= 6) score += 1;
  if (password.value.length >= 8) score += 1;
  if (/[a-z]/.test(password.value) && /[A-Z]/.test(password.value)) score += 1;
  if (/[0-9]/.test(password.value) || /[^a-zA-Z0-9]/.test(password.value)) score += 1;
  return Math.min(4, Math.max(1, score));
});

const strengthLabel = computed(() => {
  switch (strengthScore.value) {
    case 1: return 'Weak';
    case 2: return 'Fair';
    case 3: return 'Good';
    case 4: return 'Strong';
    default: return '';
  }
});

const strengthColorClass = computed(() => {
  switch (strengthScore.value) {
    case 1: return 'border-rose-500/30 bg-rose-500/10 text-rose-600 dark:text-rose-400';
    case 2: return 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400';
    case 3: return 'border-blue-500/30 bg-blue-500/10 text-blue-600 dark:text-blue-400';
    case 4: return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
    default: return 'border-border bg-muted text-muted-foreground';
  }
});

const strengthBgClass = computed(() => {
  switch (strengthScore.value) {
    case 1: return 'bg-rose-500';
    case 2: return 'bg-amber-500';
    case 3: return 'bg-blue-500';
    case 4: return 'bg-emerald-500';
    default: return 'bg-muted';
  }
});

async function handleReset() {
  if (password.value !== confirmPassword.value) {
    errorMessage.value = 'Passwords do not match';
    return;
  }
  if (password.value.length < 6) {
    errorMessage.value = 'Password must be at least 6 characters';
    return;
  }

  loading.value = true;
  errorMessage.value = '';
  try {
    await api.post('/auth/reset-password', {
      password: password.value,
      password_confirmation: confirmPassword.value,
    });
    success.value = true;
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Failed to update password.';
  } finally {
    loading.value = false;
  }
}

function goToLogin() {
  router.push('/login');
}
</script>
