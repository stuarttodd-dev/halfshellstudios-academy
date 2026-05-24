<script setup>
import { reactive } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useProducts } from './composables/useProducts.js';

const router = useRouter();
const { create, submitting, fieldErrors, error } = useProducts();
const form = reactive({ name: '', price: '' });

async function onSubmit() {
  const ok = await create({
    name: form.name,
    price: Number(form.price),
  });
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

    <p v-if="error" class="banner error">{{ error }}</p>

    <form class="create-form" @submit.prevent="onSubmit">
      <div class="field">
        <label for="name">Name</label>
        <input id="name" v-model="form.name" type="text" name="name" />
        <p v-if="fieldErrors.name" class="field-error">{{ fieldErrors.name[0] }}</p>
      </div>
      <div class="field">
        <label for="price">Price</label>
        <input id="price" v-model="form.price" type="number" step="0.01" min="0" name="price" />
        <p v-if="fieldErrors.price" class="field-error">{{ fieldErrors.price[0] }}</p>
      </div>
      <button type="submit" :disabled="submitting">
        {{ submitting ? 'Saving…' : 'Create product' }}
      </button>
    </form>
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
.field {
  margin-bottom: 0.75rem;
}
label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.25rem;
}
input {
  width: 100%;
  padding: 0.5rem 0.65rem;
}
.field-error {
  color: #dc2626;
  font-size: 0.8rem;
}
.banner.error {
  background: #fef2f2;
  color: #b91c1c;
  padding: 0.75rem;
  border-radius: 4px;
}
button[disabled] {
  opacity: 0.7;
  cursor: wait;
}
</style>
