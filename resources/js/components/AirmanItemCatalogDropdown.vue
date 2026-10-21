<template>
  <ItemCatalogDropdown
    :value="value"
    :model-value="modelValue || value"
    catalog-type="airman"
    :class-name="className"
    :trigger-class-name="triggerClassName"
    :show-price="showPrice"
    @select="(name, price, item) => emit('select', name, price, item)"
    @update:value="(val) => emit('update:value', val)"
    @update:model-value="(val) => emit('update:modelValue', val)"
    @items-updated="(items) => emit('items-updated', items)"
  />
</template>

<script setup>
import ItemCatalogDropdown from './ItemCatalogDropdown.vue';

defineProps({
  value: { type: String, default: '' },
  modelValue: { type: String, default: '' },
  className: { type: String, default: '' },
  triggerClassName: { type: String, default: '' },
  showPrice: { type: Boolean, default: false },
});

const emit = defineEmits(['select', 'update:value', 'update:modelValue', 'items-updated']);
</script>
