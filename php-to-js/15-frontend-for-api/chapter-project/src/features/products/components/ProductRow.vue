<script setup>
import { RouterLink } from 'vue-router';
defineProps({
  product: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <li class="product-row">
    <RouterLink :to="`/products/${product.id}/edit`" class="name">{{ product.name }}</RouterLink>
    <span class="price">£{{ product.price.toFixed(2) }}</span>
  </li>
</template>

<style scoped>
.product-row {
  display: flex;
  justify-content: space-between;
  padding: 0.6rem 0;
  border-bottom: 1px solid #e5e7eb;
}
.name {
  color: #4338ca;
  text-decoration: none;
}
</style>
