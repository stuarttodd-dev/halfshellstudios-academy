import { ref } from 'vue';

export const user = ref(null);
export const authReady = ref(false);

export async function bootstrapAuth() {
  user.value = localStorage.getItem('mockUser') ? { id: 1, email: 'dev@example.com' } : null;
  authReady.value = true;
}

export function login() {
  localStorage.setItem('mockUser', '1');
  user.value = { id: 1, email: 'dev@example.com' };
}

export function logout() {
  localStorage.removeItem('mockUser');
  user.value = null;
}

export function authGuard(to) {
  if (to.meta.requiresAuth && !user.value) {
    return { path: '/login', query: { redirect: to.fullPath } };
  }
  return true;
}
