<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import ProductForm from './components/ProductForm.vue';
import { useProducts } from './composables/useProducts.js';

const route = useRoute();
const router = useRouter();
const { get, update, remove, submitting, fieldErrors, error } = useProducts();
const product = ref(null);

onMounted(async () => {
  product.value = await get(route.params.id);
});

async function onSubmit(payload) {
  const ok = await update(route.params.id, payload);
  if (!ok) return;
  router.push('/products');
}

async function onDelete() {
  if (!confirm('Delete this product?')) return;
  const ok = await remove(route.params.id);
  if (ok) router.push('/products');
}
</script>

<template>
  <section class="page">
    <header class="page-header">
      <h1>Edit product</h1>
      <RouterLink to="/products">Back to list</RouterLink>
    </header>

    <p v-if="error" class="banner error">{{ error }}</p>

    <ProductForm
      v-if="product"
      :model-value="product"
      :field-errors="fieldErrors"
      :submitting="submitting"
      submit-label="Save changes"
      @submit="onSubmit"
    />

    <button v-if="product" type="button" class="danger" @click="onDelete">Delete</button>
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
.banner.error {
  background: #fef2f2;
  color: #b91c1c;
  padding: 0.75rem;
}
.danger {
  margin-top: 1rem;
  color: #b91c1c;
}
</style>
