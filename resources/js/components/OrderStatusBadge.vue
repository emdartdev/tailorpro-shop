<template>
  <div class="relative inline-block text-left" @click.stop>
    <!-- Readonly mode for viewer -->
    <span
      v-if="isViewer"
      :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border', config.className, className]"
    >
      {{ config.label }}
    </span>

    <!-- Interactive badge with dropdown for admin / editor -->
    <div v-else class="relative">
      <button
        type="button"
        @click="isOpen = !isOpen"
        :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border transition-colors hover:opacity-85 focus:outline-none cursor-pointer', config.className, className]"
      >
        <span>{{ config.label }}</span>
        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>

      <div
        v-if="isOpen"
        class="fixed inset-0 z-40"
        @click="isOpen = false"
      ></div>

      <div
        v-if="isOpen"
        class="absolute right-0 mt-1 w-44 rounded-md bg-popover text-popover-foreground shadow-lg border border-border py-1 z-50 text-xs animate-in fade-in zoom-in-95 duration-100"
      >
        <button
          v-for="s in ALL_STATUSES"
          :key="s"
          type="button"
          @click="selectStatus(s)"
          :class="[
            'w-full text-left px-3 py-2 flex items-center gap-2 hover:bg-muted transition-colors cursor-pointer',
            s === status ? 'font-semibold bg-muted/50' : ''
          ]"
        >
          <span
            :class="[
              'inline-block w-2 h-2 rounded-full',
              s === 'pending' ? 'bg-amber-500' : s === 'due_payment' ? 'bg-violet-500' : 'bg-green-500'
            ]"
          ></span>
          <span>{{ STATUS_CONFIG[s].label }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuth } from '@/lib/auth';
import api from '@/lib/api';

const props = defineProps({
  orderId: { type: String, required: true },
  status: { type: String, default: 'pending' },
  className: { type: String, default: '' },
});

const emit = defineEmits(['statusChange']);

const { isViewer } = useAuth();
const isOpen = ref(false);

const STATUS_CONFIG = {
  pending: {
    label: "Pending",
    className: "bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800",
  },
  due_payment: {
    label: "Cutting Slip ~ Done",
    className: "bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400 border-violet-200 dark:border-violet-800",
  },
  delivered: {
    label: "Delivered",
    className: "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800",
  },
};

const ALL_STATUSES = ['pending', 'due_payment', 'delivered'];

const config = computed(() => STATUS_CONFIG[props.status] || STATUS_CONFIG.pending);

async function selectStatus(newStatus) {
  isOpen.value = false;
  if (newStatus === props.status) return;

  const updateData = { status: newStatus };
  if (newStatus === 'delivered') {
    updateData.delivered_date = new Date().toISOString().split('T')[0];
  }

  try {
    await api.put(`/orders/${props.orderId}`, updateData);
    emit('statusChange', newStatus);
  } catch (err) {
    console.error('Failed to update status', err);
  }
}
</script>
