import { ref, watch } from 'vue';
import { createProduct, fetchProducts } from '../api/products.js';

export function useProducts() {
  const products = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const search = ref('');
  const fieldErrors = ref({});

  async function load() {
    loading.value = true;
    error.value = null;
    try {
      const { data } = await fetchProducts(search.value);
      products.value = data;
    } catch (err) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  }

  async function create(payload) {
    fieldErrors.value = {};
    try {
      await createProduct(payload);
      await load();
      return true;
    } catch (err) {
      if (err.status === 422) {
        fieldErrors.value = err.errors ?? {};
        return false;
      }
      error.value = err.message;
      return false;
    }
  }

  watch(search, () => {
    load();
  });

  return {
    products,
    loading,
    error,
    search,
    fieldErrors,
    load,
    create,
  };
}
