<script setup>
import { onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import ProductRow from './components/ProductRow.vue';
import { useProducts } from './composables/useProducts.js';

const { products, loading, error, search, load } = useProducts();

onMounted(() => {
  load();
});
</script>

<template>
  <section class="page">
    <header class="page-header">
      <h1>Products</h1>
      <RouterLink to="/products/new">Add product</RouterLink>
    </header>

    <div class="toolbar">
      <label for="search">Search</label>
      <input id="search" v-model="search" type="search" placeholder="Filter by name" />
      <button type="button" @click="load">Reload</button>
    </div>

    <p v-if="error" class="banner error">{{ error }}</p>
    <p v-if="loading" class="status">Loading…</p>
    <p v-else-if="products.length === 0" class="status">No products match your search.</p>

    <ul v-else class="product-list">
      <ProductRow v-for="product in products" :key="product.id" :product="product" />
    </ul>
  </section>
</template>

<style scoped>
.page {
  max-width: 560px;
  margin: 0 auto;
  padding: 1.5rem;
  font-family: system-ui, sans-serif;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.toolbar {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  margin: 1rem 0;
}
.toolbar input {
  flex: 1;
  padding: 0.5rem 0.65rem;
}
.banner.error {
  background: #fef2f2;
  color: #b91c1c;
  padding: 0.75rem;
  border-radius: 4px;
}
.status {
  color: #6b7280;
}
.product-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
</style>
