import { ref } from 'vue';
import { productsApi } from '../../../api/products.js';

export function useProducts() {
  const products = ref([]);
  const meta = ref({ page: 1, per_page: 15, total: 0 });
  const loading = ref(false);
  const submitting = ref(false);
  const error = ref(null);
  const fieldErrors = ref({});

  async function load({ page = 1, q = '' } = {}) {
    loading.value = true;
    error.value = null;
    try {
      const response = await productsApi.list({ page, q });
      products.value = response.data;
      meta.value = response.meta;
    } catch (err) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  }

  async function get(id) {
    const response = await productsApi.get(id);
    return response.data;
  }

  async function create(payload) {
    submitting.value = true;
    fieldErrors.value = {};
    error.value = null;
    try {
      await productsApi.create(payload);
      return true;
    } catch (err) {
      if (err.status === 422) {
        fieldErrors.value = err.errors ?? {};
        return false;
      }
      error.value = err.message;
      return false;
    } finally {
      submitting.value = false;
    }
  }

  async function update(id, payload) {
    submitting.value = true;
    fieldErrors.value = {};
    error.value = null;
    try {
      await productsApi.update(id, payload);
      return true;
    } catch (err) {
      if (err.status === 422) {
        fieldErrors.value = err.errors ?? {};
        return false;
      }
      error.value = err.message;
      return false;
    } finally {
      submitting.value = false;
    }
  }

  async function remove(id) {
    error.value = null;
    try {
      await productsApi.remove(id);
      return true;
    } catch (err) {
      error.value = err.message;
      return false;
    }
  }

  return {
    products,
    meta,
    loading,
    submitting,
    error,
    fieldErrors,
    load,
    get,
    create,
    update,
    remove,
  };
}
