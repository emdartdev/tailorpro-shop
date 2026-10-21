<template>
  <div id="tailorpro-root" class="min-h-screen bg-background text-foreground font-sans">
    <template v-if="isPublicRoute">
      <router-view />
    </template>
    <template v-else>
      <AppLayout>
        <router-view />
      </AppLayout>
    </template>

    <!-- Global PWA Install Banner -->
    <PwaInstallBanner />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppLayout from '@/components/AppLayout.vue';
import PwaInstallBanner from '@/components/PwaInstallBanner.vue';

const route = useRoute();

const isPublicRoute = computed(() => {
  return route.meta?.public === true;
});
</script>
