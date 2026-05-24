<script setup>
import { RouterLink, useRouter } from 'vue-router';
import ProductForm from './components/ProductForm.vue';
import { useProducts } from './composables/useProducts.js';

const router = useRouter();
const { create, submitting, fieldErrors } = useProducts();

async function onSubmit(payload) {
  const ok = await create(payload);
  if (!ok) return;
  router.push('/products');
}
</script>

<template>
  <section class="page">
    <header class="page-header">
      <h1>Add product</h1>
      <RouterLink to="/products">Back to list</RouterLink>
    </header>
    <ProductForm :field-errors="fieldErrors" :submitting="submitting" submit-label="Create product" @submit="onSubmit" />
  </section>
</template>

<style scoped>
.page {
  max-width: 640px;
  margin: 0 auto;
  padding: 1.5rem;
  font-family: system-ui, sans-serif;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
</style>
