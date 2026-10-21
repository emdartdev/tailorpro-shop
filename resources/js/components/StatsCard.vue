<template>
  <div class="bg-card text-card-foreground rounded-xl border border-border p-5 card-shadow hover:card-shadow-hover transition-all animate-fade-in">
    <div class="flex items-start justify-between">
      <div class="space-y-1">
        <p class="text-sm text-muted-foreground font-medium">{{ title }}</p>
        <p class="text-2xl font-bold tracking-tight text-foreground">{{ value }}</p>
        <p v-if="description" class="text-xs text-muted-foreground">{{ description }}</p>
      </div>
      <div :class="['p-2.5 rounded-xl flex items-center justify-center', variantClasses[variant] || variantClasses.default]">
        <slot name="icon" />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], required: true },
  description: { type: String, default: '' },
  variant: { type: String, default: 'default' },
});

const variantClasses = {
  default: 'bg-primary/10 text-primary',
  success: 'bg-green-500/10 text-green-600 dark:text-green-400',
  warning: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
  destructive: 'bg-destructive/10 text-destructive',
  info: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
};
</script>
