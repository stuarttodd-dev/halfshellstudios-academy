<script setup>
import { onMounted } from 'vue';
import ProductCreateForm from './components/ProductCreateForm.vue';
import ProductRow from './components/ProductRow.vue';
import { useProducts } from './composables/useProducts.js';

const { products, loading, error, search, fieldErrors, load, create } = useProducts();

onMounted(() => {
  load();
});
</script>

<template>
  <section class="product-admin">
    <h1>Products</h1>

    <div class="toolbar">
      <label for="search">Search</label>
      <input id="search" v-model="search" type="search" placeholder="Filter by name" />
      <button type="button" @click="load">Reload</button>
    </div>

    <p v-if="loading" class="status">Loading…</p>
    <p v-else-if="error" class="status error">{{ error }}</p>
    <p v-else-if="products.length === 0" class="status">No products match your search.</p>

    <ul v-else class="product-list">
      <ProductRow v-for="product in products" :key="product.id" :product="product" />
    </ul>

    <ProductCreateForm :field-errors="fieldErrors" :create="create" @created="load" />
  </section>
</template>

<style scoped>
.product-admin {
  max-width: 520px;
  margin: 0 auto;
  padding: 1.5rem;
  font-family: system-ui, sans-serif;
}
.toolbar {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  margin-bottom: 1rem;
}
.toolbar input {
  flex: 1;
  padding: 0.5rem 0.65rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
}
.status {
  color: #6b7280;
}
.status.error {
  color: #dc2626;
}
.product-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
</style>
