<script setup>
import { computed, onMounted, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import ProductRow from './components/ProductRow.vue';
import { useProducts } from './composables/useProducts.js';

const route = useRoute();
const router = useRouter();
const { products, meta, loading, error, load } = useProducts();

const page = computed(() => Number(route.query.page ?? 1));
const q = computed(() => String(route.query.q ?? ''));

async function refresh() {
  await load({ page: page.value, q: q.value });
}

function updateQuery(next) {
  router.push({
    path: '/products',
    query: {
      page: String(next.page ?? page.value),
      ...(next.q !== undefined ? { q: next.q || undefined } : q.value ? { q: q.value } : {}),
    },
  });
}

onMounted(refresh);
watch(() => [route.query.page, route.query.q], refresh);
</script>

<template>
  <section class="page">
    <header class="page-header">
      <h1>Products</h1>
      <RouterLink to="/products/new">Add product</RouterLink>
    </header>

    <div class="toolbar">
      <label for="search">Search</label>
      <input
        id="search"
        :value="q"
        type="search"
        placeholder="Filter by name"
        @input="updateQuery({ q: $event.target.value, page: 1 })"
      />
    </div>

    <p v-if="error" class="banner error">{{ error }}</p>
    <p v-if="loading" class="status">Loading…</p>
    <p v-else-if="products.length === 0" class="status">No products match your search.</p>

    <ul v-else class="product-list">
      <ProductRow v-for="product in products" :key="product.id" :product="product" />
    </ul>

    <div v-if="meta.total > meta.per_page" class="pager">
      <button type="button" :disabled="page <= 1" @click="updateQuery({ page: page - 1 })">
        Previous
      </button>
      <span>Page {{ meta.page }}</span>
      <button
        type="button"
        :disabled="page * meta.per_page >= meta.total"
        @click="updateQuery({ page: page + 1 })"
      >
        Next
      </button>
    </div>
  </section>
</template>

<style scoped>
.page {
  max-width: 640px;
  margin: 0 auto;
  padding: 1.5rem;
  font-family: system-ui, sans-serif;
}
.page-header,
.toolbar,
.pager {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  justify-content: space-between;
}
.toolbar input {
  flex: 1;
}
.product-list {
  list-style: none;
  padding: 0;
}
.banner.error {
  background: #fef2f2;
  color: #b91c1c;
  padding: 0.75rem;
}
.status {
  color: #6b7280;
}
</style>
