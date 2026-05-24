import { ref, watch } from 'vue';
import { createProduct, fetchProducts } from '../api/products.js';
import { reportError } from '../observability/reportError.js';
import { productSchema, validateSchema } from '../schemas/productSchema.js';

export function useProducts() {
  const products = ref([]);
  const loading = ref(false);
  const submitting = ref(false);
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
      if (err.status === 500) {
        reportError({
          type: 'api',
          message: err.message,
          route: '/products',
          release: import.meta.env.VITE_RELEASE ?? 'dev',
        });
      }
    } finally {
      loading.value = false;
    }
  }

  async function create(payload) {
    fieldErrors.value = validateSchema(productSchema, payload);
    if (Object.keys(fieldErrors.value).length > 0) {
      return false;
    }

    submitting.value = true;
    error.value = null;
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
      reportError({
        type: 'api',
        message: err.message,
        route: '/products/new',
        release: import.meta.env.VITE_RELEASE ?? 'dev',
      });
      return false;
    } finally {
      submitting.value = false;
    }
  }

  watch(search, () => {
    load();
  });

  return {
    products,
    loading,
    submitting,
    error,
    search,
    fieldErrors,
    load,
    create,
  };
}
