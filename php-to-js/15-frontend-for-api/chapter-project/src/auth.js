import { ref } from 'vue';
import { fetchMe, setAuthToken } from './api/client.js';

export const user = ref(null);
export const authReady = ref(false);

export async function bootstrapAuth() {
  const token = typeof localStorage !== 'undefined' ? localStorage.getItem('apiToken') : null;
  if (!token) {
    authReady.value = true;
    return;
  }

  setAuthToken(token);
  try {
    const response = await fetchMe();
    user.value = response.data;
  } catch {
    user.value = null;
    setAuthToken(null);
  } finally {
    authReady.value = true;
  }
}

export function login(token) {
  setAuthToken(token);
  return bootstrapAuth();
}

export function logout() {
  setAuthToken(null);
  user.value = null;
}

export function authGuard(to) {
  if (to.meta.requiresAuth && !user.value) {
    return { path: '/login', query: { redirect: to.fullPath } };
  }
  return true;
}
