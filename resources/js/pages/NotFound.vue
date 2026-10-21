<template>
  <main class="flex min-h-screen items-center justify-center bg-background p-4">
    <div class="text-center max-w-md mx-auto p-8 rounded-2xl border border-border bg-card shadow-lg space-y-4">
      <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-destructive/10 text-destructive mb-2">
        <span class="text-2xl font-bold font-mono">404</span>
      </div>
      <h1 class="text-2xl font-heading font-bold text-foreground">Page Not Found</h1>
      <p class="text-sm text-muted-foreground leading-relaxed">
        The page you are looking for does not exist or may have been moved.
      </p>
      <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
        <router-link
          to="/"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-primary text-primary-foreground text-xs font-semibold hover:bg-primary/90 transition-colors shadow-xs"
        >
          Return to Dashboard
        </router-link>
        <router-link
          to="/login"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-input bg-background hover:bg-accent text-xs font-medium transition-colors"
        >
          Go to Login
        </router-link>
      </div>
    </div>
  </main>
</template>

<script setup>
import { onMounted } from 'vue';

onMounted(() => {
  document.title = '404 - Page Not Found | One Raymond TailorPro';
});
</script>
