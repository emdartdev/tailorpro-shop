<template>
  <div class="rounded-2xl border border-border bg-card p-6 shadow-xs max-w-xl space-y-6">
    <!-- Header -->
    <div class="flex items-start justify-between gap-4 pb-4 border-b border-border">
      <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
          <KeyRound class="h-5 w-5" />
        </div>
        <div>
          <h2 class="text-base font-semibold text-foreground">
            {{ lang === 'bn' ? 'পাসওয়ার্ড পরিবর্তন' : 'Change Password' }}
          </h2>
          <p class="text-xs text-muted-foreground mt-0.5">
            {{ lang === 'bn' ? 'আপনার একাউন্টের পাসওয়ার্ড নিরাপদ ও আপডেট রাখুন' : 'Update your account password with a strong, secure passkey' }}
          </p>
        </div>
      </div>

      <!-- Generate Strong Password Button -->
      <button
        type="button"
        @click="generateStrongPassword"
        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-border bg-muted/30 hover:bg-muted text-xs font-medium text-foreground transition-all cursor-pointer"
        :title="lang === 'bn' ? 'শক্তিশালী পাসওয়ার্ড তৈরি করুন' : 'Generate Strong Password'"
      >
        <Sparkles class="h-3.5 w-3.5 text-primary" />
        <span class="hidden sm:inline">{{ lang === 'bn' ? 'পাসওয়ার্ড সাজেস্ট' : 'Suggest' }}</span>
      </button>
    </div>

    <!-- Alert / Feedback Notification -->
    <div
      v-if="errorMessage"
      class="p-3 text-xs rounded-xl border border-destructive/30 bg-destructive/10 text-destructive flex items-start gap-2 animate-in fade-in-50"
    >
      <AlertCircle class="h-4 w-4 shrink-0 mt-0.5" />
      <span class="flex-1">{{ errorMessage }}</span>
    </div>

    <div
      v-if="successMessage"
      class="p-3 text-xs rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-start gap-2 animate-in fade-in-50"
    >
      <CheckCircle2 class="h-4 w-4 shrink-0 mt-0.5 text-emerald-500" />
      <span class="flex-1">{{ successMessage }}</span>
    </div>

    <!-- Generated Password Copied Toast -->
    <div
      v-if="generatedNotice"
      class="p-3 text-xs rounded-xl border border-blue-500/30 bg-blue-500/10 text-blue-700 dark:text-blue-300 flex items-center justify-between gap-2 animate-in fade-in-50"
    >
      <div class="flex items-center gap-2">
        <Sparkles class="h-4 w-4 text-blue-500 shrink-0" />
        <span>{{ lang === 'bn' ? 'শক্তিশালী পাসওয়ার্ড তৈরি ও কপি করা হয়েছে!' : 'Strong password generated and copied to clipboard!' }}</span>
      </div>
      <span class="font-mono font-bold text-xs bg-background/80 px-2 py-0.5 rounded border border-blue-500/20">{{ newPassword }}</span>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Current Password (Optional / When required) -->
      <div v-if="showCurrentPassword" class="space-y-1.5">
        <label class="block text-xs font-medium text-foreground">
          {{ lang === 'bn' ? 'বর্তমান পাসওয়ার্ড' : 'Current Password' }}
          <span class="text-muted-foreground font-normal">({{ lang === 'bn' ? 'ঐচ্ছিক' : 'Optional' }})</span>
        </label>
        <div class="relative">
          <input
            v-model="currentPassword"
            :type="showCurrent ? 'text' : 'password'"
            placeholder="••••••••"
            class="w-full h-10 pl-3 pr-10 text-xs rounded-xl border border-input bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono"
          />
          <button
            type="button"
            @click="showCurrent = !showCurrent"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground p-1 rounded-md transition-colors cursor-pointer"
            :title="showCurrent ? 'Hide' : 'Show'"
          >
            <EyeOff v-if="showCurrent" class="h-4 w-4" />
            <Eye v-else class="h-4 w-4" />
          </button>
        </div>
      </div>

      <!-- New Password -->
      <div class="space-y-1.5">
        <div class="flex items-center justify-between">
          <label class="block text-xs font-medium text-foreground">
            {{ lang === 'bn' ? 'নতুন পাসওয়ার্ড' : 'New Password' }}
          </label>
          <span
            v-if="newPassword"
            :class="[
              'text-[11px] font-semibold px-2 py-0.5 rounded-full border',
              strengthColorClass
            ]"
          >
            {{ strengthLabel }}
          </span>
        </div>

        <div class="relative">
          <input
            v-model="newPassword"
            :type="showNew ? 'text' : 'password'"
            placeholder="••••••••"
            minlength="6"
            required
            class="w-full h-10 pl-3 pr-10 text-xs rounded-xl border border-input bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono"
          />
          <button
            type="button"
            @click="showNew = !showNew"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground p-1 rounded-md transition-colors cursor-pointer"
            :title="showNew ? 'Hide' : 'Show'"
          >
            <EyeOff v-if="showNew" class="h-4 w-4" />
            <Eye v-else class="h-4 w-4" />
          </button>
        </div>

        <!-- Strength Meter Bar -->
        <div v-if="newPassword" class="space-y-1.5 pt-1 animate-in fade-in-50">
          <div class="grid grid-cols-4 gap-1.5 h-1.5">
            <div
              :class="[
                'rounded-full transition-all duration-300',
                strengthScore >= 1 ? strengthBgClass : 'bg-muted'
              ]"
            ></div>
            <div
              :class="[
                'rounded-full transition-all duration-300',
                strengthScore >= 2 ? strengthBgClass : 'bg-muted'
              ]"
            ></div>
            <div
              :class="[
                'rounded-full transition-all duration-300',
                strengthScore >= 3 ? strengthBgClass : 'bg-muted'
              ]"
            ></div>
            <div
              :class="[
                'rounded-full transition-all duration-300',
                strengthScore >= 4 ? strengthBgClass : 'bg-muted'
              ]"
            ></div>
          </div>
        </div>

        <!-- Realtime Password Requirements Checklist -->
        <div v-if="newPassword" class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 pt-2 text-[11px] animate-in fade-in-50">
          <div :class="['flex items-center gap-1.5', hasMinLength ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-muted-foreground']">
            <Check v-if="hasMinLength" class="h-3 w-3 stroke-[3]" />
            <X v-else class="h-3 w-3 text-muted-foreground/60" />
            <span>{{ lang === 'bn' ? 'কমপক্ষে ৬টি অক্ষর' : 'At least 6 characters' }}</span>
          </div>

          <div :class="['flex items-center gap-1.5', hasMixedCase ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-muted-foreground']">
            <Check v-if="hasMixedCase" class="h-3 w-3 stroke-[3]" />
            <X v-else class="h-3 w-3 text-muted-foreground/60" />
            <span>{{ lang === 'bn' ? 'বড় ও ছোট হাতের অক্ষর' : 'Uppercase & lowercase' }}</span>
          </div>

          <div :class="['flex items-center gap-1.5', hasNumber ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-muted-foreground']">
            <Check v-if="hasNumber" class="h-3 w-3 stroke-[3]" />
            <X v-else class="h-3 w-3 text-muted-foreground/60" />
            <span>{{ lang === 'bn' ? 'কমপক্ষে একটি সংখ্যা (0-9)' : 'At least one number' }}</span>
          </div>

          <div :class="['flex items-center gap-1.5', hasSpecial ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-muted-foreground']">
            <Check v-if="hasSpecial" class="h-3 w-3 stroke-[3]" />
            <X v-else class="h-3 w-3 text-muted-foreground/60" />
            <span>{{ lang === 'bn' ? 'স্পেশাল চিহ্ন (!@#$%^&*)' : 'Special character (!@#$)' }}</span>
          </div>
        </div>
      </div>

      <!-- Confirm Password -->
      <div class="space-y-1.5">
        <div class="flex items-center justify-between">
          <label class="block text-xs font-medium text-foreground">
            {{ lang === 'bn' ? 'পাসওয়ার্ড নিশ্চিত করুন' : 'Confirm Password' }}
          </label>
          <span
            v-if="confirmPassword"
            :class="[
              'text-[11px] font-semibold flex items-center gap-1',
              passwordsMatch ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'
            ]"
          >
            <Check v-if="passwordsMatch" class="h-3 w-3 stroke-[3]" />
            <AlertCircle v-else class="h-3 w-3" />
            {{ passwordsMatch ? (lang === 'bn' ? 'মিল রয়েছে ✓' : 'Passwords match') : (lang === 'bn' ? 'পাসওয়ার্ড মেলেনি' : 'Does not match') }}
          </span>
        </div>

        <div class="relative">
          <input
            v-model="confirmPassword"
            :type="showConfirm ? 'text' : 'password'"
            placeholder="••••••••"
            minlength="6"
            required
            class="w-full h-10 pl-3 pr-10 text-xs rounded-xl border border-input bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono"
          />
          <button
            type="button"
            @click="showConfirm = !showConfirm"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground p-1 rounded-md transition-colors cursor-pointer"
            :title="showConfirm ? 'Hide' : 'Show'"
          >
            <EyeOff v-if="showConfirm" class="h-4 w-4" />
            <Eye v-else class="h-4 w-4" />
          </button>
        </div>
      </div>

      <!-- Submit Action Button -->
      <div class="pt-2">
        <button
          type="submit"
          :disabled="loading || (confirmPassword && !passwordsMatch)"
          class="h-10 px-5 inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 hover:scale-[1.01] active:scale-[0.99] transition-all disabled:opacity-50 disabled:pointer-events-none cursor-pointer shadow-sm shadow-primary/20"
        >
          <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
          <KeyRound v-else class="h-4 w-4" />
          <span>{{ lang === 'bn' ? 'পাসওয়ার্ড পরিবর্তন নিশ্চিত করুন' : 'Update Password' }}</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import {
  KeyRound,
  Eye,
  EyeOff,
  Check,
  X,
  AlertCircle,
  CheckCircle2,
  Sparkles,
  Loader2,
} from 'lucide-vue-next';
import { useLanguage } from '@/lib/language';
import api from '@/lib/api';

const props = defineProps({
  endpoint: {
    type: String,
    default: '/auth/change-password',
  },
  showCurrentPassword: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['success']);

const { lang } = useLanguage();

const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');

const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const generatedNotice = ref(false);

// Real-time checks
const hasMinLength = computed(() => newPassword.value.length >= 6);
const hasMixedCase = computed(() => /[a-z]/.test(newPassword.value) && /[A-Z]/.test(newPassword.value));
const hasNumber = computed(() => /[0-9]/.test(newPassword.value));
const hasSpecial = computed(() => /[^a-zA-Z0-9]/.test(newPassword.value));

const strengthScore = computed(() => {
  if (!newPassword.value) return 0;
  let score = 0;
  if (newPassword.value.length >= 6) score += 1;
  if (newPassword.value.length >= 8 && (hasMixedCase.value || hasNumber.value)) score += 1;
  if (hasMixedCase.value && hasNumber.value) score += 1;
  if (hasSpecial.value && newPassword.value.length >= 8) score += 1;
  return Math.min(4, Math.max(1, score));
});

const strengthLabel = computed(() => {
  switch (strengthScore.value) {
    case 1:
      return lang.value === 'bn' ? 'দুর্বল' : 'Weak';
    case 2:
      return lang.value === 'bn' ? 'মোটামুটি' : 'Fair';
    case 3:
      return lang.value === 'bn' ? 'ভালো' : 'Good';
    case 4:
      return lang.value === 'bn' ? 'খুব শক্তিশালী' : 'Strong';
    default:
      return '';
  }
});

const strengthColorClass = computed(() => {
  switch (strengthScore.value) {
    case 1:
      return 'border-rose-500/30 bg-rose-500/10 text-rose-600 dark:text-rose-400';
    case 2:
      return 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400';
    case 3:
      return 'border-blue-500/30 bg-blue-500/10 text-blue-600 dark:text-blue-400';
    case 4:
      return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
    default:
      return 'border-border bg-muted text-muted-foreground';
  }
});

const strengthBgClass = computed(() => {
  switch (strengthScore.value) {
    case 1:
      return 'bg-rose-500';
    case 2:
      return 'bg-amber-500';
    case 3:
      return 'bg-blue-500';
    case 4:
      return 'bg-emerald-500';
    default:
      return 'bg-muted';
  }
});

const passwordsMatch = computed(() => {
  return newPassword.value && confirmPassword.value && newPassword.value === confirmPassword.value;
});

function generateStrongPassword() {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%^&*';
  let result = '';
  const array = new Uint32Array(12);
  crypto.getRandomValues(array);
  for (let i = 0; i < 12; i++) {
    result += chars[array[i] % chars.length];
  }
  newPassword.value = result;
  confirmPassword.value = result;
  showNew.value = true;
  showConfirm.value = true;

  if (navigator.clipboard) {
    navigator.clipboard.writeText(result).catch(() => {});
  }
  generatedNotice.value = true;
  setTimeout(() => {
    generatedNotice.value = false;
  }, 5000);
}

async function handleSubmit() {
  if (newPassword.value !== confirmPassword.value) {
    errorMessage.value = lang.value === 'bn' ? 'দুই ঘরের পাসওয়ার্ড মিলছে না!' : 'Passwords do not match.';
    return;
  }
  if (newPassword.value.length < 6) {
    errorMessage.value = lang.value === 'bn' ? 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।' : 'Password must be at least 6 characters.';
    return;
  }

  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    const payload = {
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    };
    if (currentPassword.value) {
      payload.current_password = currentPassword.value;
    }

    const res = await api.post(props.endpoint, payload);

    successMessage.value = res.data?.message || (lang.value === 'bn' ? 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে!' : 'Password updated successfully!');
    currentPassword.value = '';
    newPassword.value = '';
    confirmPassword.value = '';

    emit('success');
    setTimeout(() => {
      successMessage.value = '';
    }, 4000);
  } catch (err) {
    errorMessage.value =
      err.response?.data?.message ||
      err.response?.data?.error ||
      (lang.value === 'bn' ? 'পাসওয়ার্ড আপডেট করতে সমস্যা হয়েছে।' : 'Failed to update password.');
  } finally {
    loading.value = false;
  }
}
</script>
