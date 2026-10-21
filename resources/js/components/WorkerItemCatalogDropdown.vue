<template>
  <ItemCatalogDropdown
    :value="value"
    catalog-type="worker"
    :class-name="className"
    :trigger-class-name="triggerClassName"
    :show-price="showPrice"
    @select="(name, price) => emit('select', name, price)"
    @update:value="(val) => emit('update:value', val)"
  />
</template>

<script setup>
import ItemCatalogDropdown from './ItemCatalogDropdown.vue';

defineProps({
  value: { type: String, default: '' },
  className: { type: String, default: '' },
  triggerClassName: { type: String, default: '' },
  showPrice: { type: Boolean, default: true },
});

const emit = defineEmits(['select', 'update:value']);
</script>
