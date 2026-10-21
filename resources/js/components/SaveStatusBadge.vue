<template>
  <span
    v-if="cfg"
    :class="[
      'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium whitespace-nowrap',
      cfg.cls,
      className
    ]"
    aria-live="polite"
  >
    <component :is="cfg.icon" :class="['h-3.5 w-3.5', status === 'saving' ? 'animate-spin' : '']" />
    {{ cfg.label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { CheckCircle2, Loader2, AlertCircle, FileEdit, Clock } from 'lucide-vue-next';

const props = defineProps({
  status: { type: String, default: 'idle' }, // 'idle' | 'pending' | 'saving' | 'saved' | 'error' | 'draft'
  editing: { type: Boolean, default: false },
  className: { type: String, default: '' },
});

const cfg = computed(() => {
  if (props.status === 'idle') return null;

  const map = {
    pending: {
      label: props.editing ? 'Unsaved changes…' : 'Draft (not saved yet)',
      icon: Clock,
      cls: 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-200 dark:border-amber-800',
    },
    saving: {
      label: 'Auto-saving…',
      icon: Loader2,
      cls: 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-950/60 dark:text-blue-200 dark:border-blue-800',
    },
    saved: {
      label: 'Save complete',
      icon: CheckCircle2,
      cls: 'bg-green-100 text-green-800 border-green-300 dark:bg-green-950/60 dark:text-green-200 dark:border-green-800',
    },
    error: {
      label: 'Save failed',
      icon: AlertCircle,
      cls: 'bg-red-100 text-red-800 border-red-300 dark:bg-red-950/60 dark:text-red-200 dark:border-red-800',
    },
    draft: {
      label: 'Draft saved locally',
      icon: FileEdit,
      cls: 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-900/60 dark:text-slate-300 dark:border-slate-700',
    },
  };

  return map[props.status] || null;
});
</script>
