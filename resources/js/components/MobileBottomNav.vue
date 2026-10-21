<template>
  <nav
    class="md:hidden fixed bottom-0 left-0 right-0 z-40 border-t border-border bg-card/95 backdrop-blur-md supports-[backdrop-filter]:bg-card/85 select-none transition-all"
    :style="{
      paddingBottom: 'env(safe-area-inset-bottom, 0px)',
    }"
  >
    <div class="flex items-center justify-around h-15 px-1 max-w-lg mx-auto">
      <!-- 1. Orders -->
      <router-link
        to="/"
        :class="[
          'flex flex-col items-center justify-center flex-1 h-full py-1 transition-colors rounded-lg min-w-0 min-h-[44px]',
          isActive('/') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground'
        ]"
      >
        <ShoppingBag class="h-5 w-5 shrink-0" :class="{ 'stroke-[2.5px]': isActive('/') }" />
        <span class="text-[10px] font-medium tracking-tight mt-0.5 truncate max-w-[64px]">{{ t('orders') }}</span>
      </router-link>

      <!-- 2. Airman Orders -->
      <router-link
        to="/airman"
        :class="[
          'flex flex-col items-center justify-center flex-1 h-full py-1 transition-colors rounded-lg min-w-0 min-h-[44px]',
          isActive('/airman') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground'
        ]"
      >
        <UserCheck class="h-5 w-5 shrink-0" :class="{ 'stroke-[2.5px]': isActive('/airman') }" />
        <span class="text-[10px] font-medium tracking-tight mt-0.5 truncate max-w-[64px]">{{ t('airmanShort') }}</span>
      </router-link>

      <!-- 3. Delivery Queue -->
      <router-link
        to="/delivery"
        :class="[
          'flex flex-col items-center justify-center flex-1 h-full py-1 transition-colors rounded-lg min-w-0 min-h-[44px]',
          isActive('/delivery') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground'
        ]"
      >
        <Truck class="h-5 w-5 shrink-0" :class="{ 'stroke-[2.5px]': isActive('/delivery') }" />
        <span class="text-[10px] font-medium tracking-tight mt-0.5 truncate max-w-[64px]">Delivery</span>
      </router-link>

      <!-- 4. Staff Management -->
      <router-link
        to="/staff"
        :class="[
          'flex flex-col items-center justify-center flex-1 h-full py-1 transition-colors rounded-lg min-w-0 min-h-[44px]',
          isActive('/staff') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground'
        ]"
      >
        <UsersRound class="h-5 w-5 shrink-0" :class="{ 'stroke-[2.5px]': isActive('/staff') }" />
        <span class="text-[10px] font-medium tracking-tight mt-0.5 truncate max-w-[64px]">{{ t('staff') }}</span>
      </router-link>

      <!-- 5. More / Menu Drawer Trigger -->
      <button
        type="button"
        @click="$emit('open-drawer')"
        class="flex flex-col items-center justify-center flex-1 h-full py-1 transition-colors rounded-lg min-w-0 min-h-[44px] text-muted-foreground hover:text-foreground cursor-pointer"
        aria-label="Open More Menu"
      >
        <Menu class="h-5 w-5 shrink-0" />
        <span class="text-[10px] font-medium tracking-tight mt-0.5 truncate max-w-[64px]">{{ t('menu') }}</span>
      </button>
    </div>
  </nav>
</template>

<script setup>
import { useRoute } from 'vue-router';
import { ShoppingBag, UserCheck, Truck, UsersRound, Menu } from 'lucide-vue-next';
import { useLanguage } from '@/lib/language';

defineEmits(['open-drawer']);

const route = useRoute();
const { t } = useLanguage();

function isActive(path) {
  if (!route) return false;
  if (path === '/') {
    return route.path === '/' || (route.path.startsWith('/orders') && !route.path.startsWith('/orders/new'));
  }
  return route.path === path || route.path.startsWith(path + '/');
}
</script>
