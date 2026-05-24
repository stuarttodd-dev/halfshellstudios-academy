<script setup>
defineProps({
  product: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <li class="product-row">
    <span class="name">{{ product.name }}</span>
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
.price {
  color: #4b5563;
}
</style>
